<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Mobile-money collections through Marz Pay (MTN / Airtel, Uganda).
 *
 * Flow: startPayment() sends a payment prompt to the customer's phone ->
 * the customer approves with their PIN -> Marz Pay calls our webhook and/or
 * the payment page polls -> sync() asks Marz Pay for the real status
 * (we never trust the webhook body on its own) -> markSuccessful().
 */
class MarzPayService
{
    /** International format only: + then country code and number, e.g. +256772123456 */
    public const PHONE_REGEX = '/^\+[1-9][0-9]{8,14}$/';

    protected const COUNTRIES = [
        '256' => 'UG', '254' => 'KE', '255' => 'TZ', '250' => 'RW',
        '233' => 'GH', '234' => 'NG', '260' => 'ZM', '265' => 'MW',
    ];

    protected const UNAVAILABLE = 'Payments are temporarily unavailable. Please try again shortly.';

    protected const SUCCESS = ['completed', 'successful', 'success', 'paid', 'approved', 'settled'];
    protected const FAILED  = ['failed', 'failure', 'cancelled', 'canceled', 'rejected', 'declined',
        'expired', 'timeout', 'timed_out', 'error', 'reversed'];

    /**
     * Create a payment attempt for the order and send the prompt.
     */
    public function startPayment(Order $order, string $phone): Payment
    {
        $phone = $this->normalizePhone($phone);

        $payment = Payment::create([
            'order_id'  => $order->id,
            'reference' => (string) Str::uuid(),
            'phone'     => $phone,
            'amount'    => $order->total_amount,
            'status'    => 'pending',
        ]);

        $order->update(['payment_status' => 'pending']);

        if (! config('services.marzpay.key') || ! config('services.marzpay.secret')) {
            Log::error('marzpay.missing_credentials: set MARZPAY_API_KEY and MARZPAY_API_SECRET in .env, then run php artisan config:clear');
            $this->markFailed($payment, self::UNAVAILABLE);

            return $payment->refresh();
        }

        $payload = array_filter([
            'amount'       => (int) round($order->total_amount),
            'phone_number' => $payment->phone,
            'reference'    => $payment->reference,
            'description'  => 'Order ' . $order->order_number,
            'callback_url' => $this->callbackUrl(),
            'country'      => $this->countryFor($phone),
        ], fn ($v) => $v !== null && $v !== '');

        try {
            // Same encoding as the proven alhilal-online-academy integration (form fields;
            // the official SDK uses multipart, which is equivalent for these scalar fields).
            $response = $this->client()->asForm()->post($this->url('/collect-money'), $payload);
            $json     = $response->json() ?? [];
        } catch (ConnectionException $e) {
            report($e);
            $this->markFailed($payment, 'Could not reach the payment provider. Please try again.');

            return $payment->refresh();
        } catch (\Throwable $e) {
            report($e);
            $this->markFailed($payment, 'Could not start the payment. Please try again.');

            return $payment->refresh();
        }

        if ($response->failed() || strtolower((string) data_get($json, 'status')) === 'error') {
            Log::warning('marzpay.collect_failed', [
                'http'    => $response->status(),
                'country' => $payload['country'] ?? null,
                'phone'   => $payment->phone,
                'body'    => $json,
            ]);

            $code    = strtoupper((string) data_get($json, 'error_code'));
            $message = (string) data_get($json, 'message');

            // The API key's Marz Pay account has no MTN/Airtel *collection* service
            // switched on for this country. Nothing the customer or this code can fix:
            // it is enabled in the Marz Pay dashboard.
            $serviceNotEnabled = in_array($code, ['SERVICE_NOT_SUBSCRIBED', 'SERVICE_NOT_AVAILABLE', 'NO_SERVICES_AVAILABLE'], true)
                || preg_match('/no collection services|not available for|not subscribed|service.{0,20}(disabled|inactive|not enabled)/i', $message);

            if ($serviceNotEnabled) {
                Log::error(
                    'marzpay.collection_service_not_enabled: this API key has no active MTN/Airtel collection service for '
                    . ($payload['country'] ?? '?') . '. Enable Collections in the Marz Pay dashboard, or use the key of the '
                    . 'account that has it. Run: php artisan marzpay:check'
                );
            }

            // Credential / account problems are ours to fix, not the customer's:
            // never show them provider internals (admins see them on the order page).
            $authProblem = $serviceNotEnabled
                || in_array($response->status(), [401, 403], true)
                || in_array($code, ['UNAUTHORIZED', 'FORBIDDEN', 'ACCOUNT_FROZEN'], true)
                || preg_match('/credential|unauthori[sz]ed|api key|forbidden/i', $message);

            $this->markFailed(
                $payment,
                $authProblem
                    ? self::UNAVAILABLE
                    : (string) ($message ?: 'The payment provider rejected the request.'),
                $json
            );

            return $payment->refresh();
        }

        $payment->update([
            'provider_uuid'     => data_get($json, 'data.transaction.uuid')
                ?? data_get($json, 'data.collection_id')
                ?? data_get($json, 'data.uuid'),
            'provider_response' => $json,
        ]);

        // The initiate response can already carry a final status.
        $this->applyStatus($payment, $this->parseStatus($json), $json);

        return $payment->refresh();
    }

    /**
     * Ask Marz Pay for the current status of a payment and apply it.
     */
    public function sync(Payment $payment): Payment
    {
        if (! $payment->isPending()) {
            return $payment;
        }

        try {
            $id       = $payment->provider_uuid ?: $payment->reference;
            $response = $this->client()->get($this->url('/collect-money/' . $id));

            if ($response->successful()) {
                $json = $response->json() ?? [];
                $this->applyStatus($payment, $this->parseStatus($json), $json);
            }
        } catch (\Throwable $e) {
            // Network hiccup: stay pending, the next poll / webhook retries.
            report($e);
        }

        return $payment->refresh();
    }

    protected function applyStatus(Payment $payment, string $status, array $json): void
    {
        if ($status === 'successful') {
            $this->markSuccessful($payment, $json);
        } elseif ($status === 'failed') {
            $reason = data_get($json, 'data.transaction.failure_reason')
                ?? data_get($json, 'data.transaction.message')
                ?? data_get($json, 'message')
                ?? 'The payment was declined or cancelled.';
            $this->markFailed($payment, (string) $reason, $json);
        }
    }

    /**
     * Idempotent: safe to call from the webhook and the poller at once.
     */
    public function markSuccessful(Payment $payment, array $json = []): void
    {
        $justPaid = DB::transaction(function () use ($payment, $json) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->isSuccessful()) {
                return false;
            }

            $locked->update([
                'status'            => 'successful',
                'failure_reason'    => null,
                'paid_at'           => now(),
                'provider_response' => $json ?: $locked->provider_response,
            ]);

            $order = Order::withTrashed()->whereKey($locked->order_id)->lockForUpdate()->first();

            if ($order) {
                $order->payment_status = 'paid';
                $order->paid_at        = now();
                if ($order->status === 'pending') {
                    $order->status = 'processing'; // paid: ready to be handled
                }
                $order->save();
            }

            return true;
        });

        if ($justPaid) {
            $this->notifyPaid($payment->fresh());
        }
    }

    public function markFailed(Payment $payment, string $reason, array $json = []): void
    {
        DB::transaction(function () use ($payment, $reason, $json) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->isSuccessful()) {
                return;
            }

            $locked->update([
                'status'            => 'failed',
                'failure_reason'    => Str::limit($reason, 250, ''),
                'provider_response' => $json ?: $locked->provider_response,
            ]);

            $order = Order::withTrashed()->whereKey($locked->order_id)->lockForUpdate()->first();

            if ($order && $order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'failed']);
            }
        });
    }

    /**
     * Email the customer their payment confirmation and tell the admins.
     * Best-effort: a mail problem must never undo a recorded payment.
     */
    protected function notifyPaid(Payment $payment): void
    {
        $order = Order::withTrashed()->with('user')->find($payment->order_id);

        if (! $order) {
            return;
        }

        try {
            if ($order->user_id && $order->user) {
                $order->user->notify(new PaymentReceivedNotification($payment));
            } elseif (! empty($order->guest_email)) {
                Notification::route('mail', $order->guest_email)
                    ->notify(new PaymentReceivedNotification($payment));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            foreach (User::where('user_role', '!=', '1')->get() as $admin) {
                $admin->notify(new PaymentReceivedNotification($payment, true));
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Turn a Marz Pay response into pending | successful | failed.
     * Only the transaction-level status counts: the top-level "status"
     * ("success") just says the API call itself worked.
     */
    public function parseStatus(array $json): string
    {
        $raw = data_get($json, 'data.transaction.status')
            ?? data_get($json, 'data.collection.status')
            ?? data_get($json, 'data.status');

        $raw = strtolower(trim((string) $raw));

        if (in_array($raw, self::SUCCESS, true)) {
            return 'successful';
        }

        if (in_array($raw, self::FAILED, true)) {
            return 'failed';
        }

        return 'pending';
    }

    /**
     * Strip spaces, dashes, dots and brackets, keeping a single leading +.
     */
    public function cleanPhone(?string $phone): string
    {
        $phone = trim((string) $phone);
        $plus  = str_starts_with($phone, '+') ? '+' : '';

        return $plus . preg_replace('/\D+/', '', $phone);
    }

    /**
     * International format (+2567XXXXXXXX). Numbers already starting with
     * + keep their own country code; a bare local number (0772...) is
     * assumed to be Ugandan.
     */
    public function normalizePhone(?string $phone): string
    {
        $clean = $this->cleanPhone($phone);

        if (str_starts_with($clean, '+')) {
            return $this->dropTrunkZero($clean);
        }

        if (str_starts_with($clean, '00')) {
            return '+' . substr($clean, 2);
        }

        if (str_starts_with($clean, '256')) {
            return '+' . $clean;
        }

        return '+256' . ltrim($clean, '0');
    }

    /**
     * A local trunk 0 typed after the country code (+2560772...) makes the
     * number invalid; remove it: +2560772123456 -> +256772123456.
     */
    protected function dropTrunkZero(string $phone): string
    {
        $digits = ltrim($phone, '+');

        foreach (array_keys(self::COUNTRIES) as $dial) {
            $dial = (string) $dial;
            if (str_starts_with($digits, $dial . '0')) {
                return '+' . $dial . substr($digits, strlen($dial) + 1);
            }
        }

        return $phone;
    }

    /**
     * Marz Pay country code for a +CCXXXXXXXX number (falls back to config).
     */
    public function countryFor(string $phone): string
    {
        $digits = ltrim($phone, '+');

        foreach (self::COUNTRIES as $dial => $country) {
            if (str_starts_with($digits, (string) $dial)) {
                return $country;
            }
        }

        return (string) config('services.marzpay.country', 'UG');
    }

    protected function callbackUrl(): ?string
    {
        $url  = route('marzpay.callback');
        $host = (string) parse_url($url, PHP_URL_HOST);

        // Marz Pay can't reach a local machine; the payment page polls instead.
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return null;
        }

        return $url;
    }

    protected function client()
    {
        return Http::withBasicAuth(
            (string) config('services.marzpay.key'),
            (string) config('services.marzpay.secret')
        )->acceptJson()->timeout(30);
    }

    protected function url(string $path): string
    {
        return rtrim((string) config('services.marzpay.base_url'), '/') . $path;
    }
}
