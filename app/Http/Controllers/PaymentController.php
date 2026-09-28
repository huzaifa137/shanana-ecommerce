<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MarzPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Payment page: shows "approve on your phone", success, or retry.
     * Reached through a signed link, so guests can use it too.
     */
    public function show(string $orderNumber, MarzPayService $marz)
    {
        $order   = $this->findOrder($orderNumber);
        $payment = $order->payments()->latest('id')->first();

        if ($payment && $payment->isPending()) {
            $marz->sync($payment);
            $order->refresh();
            $payment = $order->payments()->latest('id')->first();
        }

        return view('Ecommerce.order-payment', compact('order', 'payment'));
    }

    /**
     * JSON endpoint polled by the payment page while waiting for approval.
     */
    public function status(string $orderNumber, MarzPayService $marz)
    {
        $order   = $this->findOrder($orderNumber);
        $payment = $order->payments()->latest('id')->first();

        if ($payment && $payment->isPending()) {
            $marz->sync($payment);
            $order->refresh();
            $payment = $order->payments()->latest('id')->first();
        }

        return response()->json([
            'payment_status' => $order->payment_status,
            'attempt_status' => $payment?->status,
            'can_retry'      => $order->canRetryPayment(),
        ]);
    }

    /**
     * Send a fresh payment prompt (after a failure / timeout).
     */
    public function retry(Request $request, string $orderNumber, MarzPayService $marz)
    {
        $order = $this->findOrder($orderNumber);

        if (! $order->canRetryPayment()) {
            return redirect($order->paymentUrl())
                ->with('error', 'This order cannot be paid for right now.');
        }

        $request->merge(['payment_phone' => $marz->cleanPhone($request->input('payment_phone'))]);

        $data = $request->validate([
            'payment_phone' => ['required', 'string', 'regex:' . MarzPayService::PHONE_REGEX],
        ], [
            'payment_phone.regex' => 'Enter your mobile money number with the country code, e.g. +256772123456.',
        ]);

        // Second line of defence behind the disabled button: only one prompt can be
        // started per order at a time (double click, two tabs, refresh + resubmit).
        $lock = 'marzpay:retry:' . $order->id;

        if (! Cache::add($lock, 1, 30)) {
            return redirect($order->paymentUrl());
        }

        try {
            $marz->startPayment($order, $data['payment_phone']);
        } finally {
            Cache::forget($lock);
        }

        return redirect($order->paymentUrl());
    }

    /**
     * Marz Pay webhook. The body is never trusted: we only use it to find
     * our payment, then ask Marz Pay directly for the real status.
     */
    public function callback(Request $request, MarzPayService $marz)
    {
        Log::info('marzpay.callback', $request->all());

        $ids  = [];
        $body = $request->all();
        array_walk_recursive($body, function ($value, $key) use (&$ids) {
            if (in_array($key, ['reference', 'uuid', 'transaction_id', 'collection_id'], true)
                && is_string($value) && $value !== '') {
                $ids[] = $value;
            }
        });

        if ($ids) {
            $payment = Payment::whereIn('reference', $ids)
                ->orWhereIn('provider_uuid', $ids)
                ->first();

            if ($payment) {
                $marz->sync($payment);
            }
        }

        return response()->json(['received' => true]);
    }

    protected function findOrder(string $orderNumber): Order
    {
        return Order::where('order_number', $orderNumber)->firstOrFail();
    }
}
