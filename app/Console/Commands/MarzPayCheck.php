<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MarzPayCheck extends Command
{
    protected $signature = 'marzpay:check
                            {--phone= : Also send a REAL test collection prompt to this number (e.g. +256772123456)}
                            {--amount=500 : Amount in UGX for the test collection (Marz Pay minimum is 500)}';

    protected $description = 'Check the Marz Pay credentials in .env and whether MTN/Airtel collection is enabled for your country';

    public function handle(): int
    {
        $key     = (string) config('services.marzpay.key');
        $secret  = (string) config('services.marzpay.secret');
        $base    = rtrim((string) config('services.marzpay.base_url'), '/');
        $country = strtoupper((string) config('services.marzpay.country', 'UG'));

        if ($key === '' || $secret === '') {
            $this->error('MARZPAY_API_KEY / MARZPAY_API_SECRET are empty. Add them to .env, then run: php artisan config:clear');

            return self::FAILURE;
        }

        $this->line('Key:      ' . substr($key, 0, 8) . '…  (' . strlen($key) . ' chars)');
        $this->line('Secret:   ' . strlen($secret) . ' chars');
        $this->line('Base:     ' . $base);
        $this->line('Country:  ' . $country);

        if ($key !== trim($key) || $secret !== trim($secret) || preg_match('/["\'\s]/', $key . $secret)) {
            $this->warn('The key or secret contains spaces or quotes. Remove them in .env.');
        }

        $http = Http::withBasicAuth($key, $secret)->acceptJson()->timeout(20);

        // 1. Are the credentials accepted, and which collection services does this key have?
        $services = $http->get($base . '/collect-money/services');
        $this->line('');
        $this->line('GET /collect-money/services -> HTTP ' . $services->status());
        $this->line(substr((string) $services->body(), 0, 1500));

        if (in_array($services->status(), [401, 403], true)) {
            $this->error('Marz Pay rejected the credentials. Re-copy the API key and secret from your Marz Pay dashboard (no quotes/spaces), then php artisan config:clear.');

            return self::FAILURE;
        }

        $providers = $this->providersFor($services->json() ?? [], $country);

        $this->line('');
        if ($services->successful() && $providers) {
            $this->info("Collection is enabled for {$country}: " . implode(', ', $providers));
        } else {
            $this->error("This API key has NO collection service enabled for {$country}.");
            $this->line('This is an account setting, not a code problem. The same request works in your other');
            $this->line('system because that key\'s account has MTN / Airtel collection switched on. To fix:');
            $this->line('  1. Log in to https://wallet.wearemarz.com with the account that owns THIS key.');
            $this->line('  2. Enable / subscribe to Mobile Money Collection (MTN + Airtel Uganda) for the account.');
            $this->line('     If you cannot find it, ask Marz Pay support (support@wearemarz.com) to enable it.');
            $this->line('  3. Or put the API key/secret of the account that already collects money in .env');
            $this->line('     (php artisan config:clear), then run this command again.');
        }

        // 2. Optional: send a real prompt using exactly what the checkout sends.
        if ($phone = $this->option('phone')) {
            return $this->sendTest($http, $base, $country, (string) $phone, (int) $this->option('amount'), (bool) $providers);
        }

        $this->line('');
        $this->line('Tip: php artisan marzpay:check --phone=+2567XXXXXXXX  sends a real UGX 500 test prompt.');

        return $services->successful() && $providers ? self::SUCCESS : self::FAILURE;
    }

    protected function sendTest($http, string $base, string $country, string $phone, int $amount, bool $enabled): int
    {
        $phone = app(\App\Services\MarzPayService::class)->normalizePhone($phone);

        if (! $this->confirm("Send a REAL prompt of UGX {$amount} to {$phone}?", false)) {
            return self::SUCCESS;
        }

        $payload = [
            'amount'       => $amount,
            'phone_number' => $phone,
            'country'      => $country,
            'reference'    => (string) Str::uuid(),
            'description'  => 'Marz Pay connection test',
        ];

        $response = $http->asForm()->post($base . '/collect-money', $payload);

        $this->line('');
        $this->line('POST /collect-money -> HTTP ' . $response->status());
        $this->line(substr((string) $response->body(), 0, 1500));

        if ($response->successful() && strtolower((string) data_get($response->json(), 'status')) !== 'error') {
            $this->info('Prompt sent. Approve it on the phone with the PIN.');

            return self::SUCCESS;
        }

        $this->error('Marz Pay refused the collection. The message above is the exact reason.');

        return self::FAILURE;
    }

    /**
     * Provider names for a country from the /collect-money/services response
     * (data.countries.{CC}.providers), tolerant of list-of-strings / list-of-objects.
     */
    protected function providersFor(array $json, string $country): array
    {
        $list = data_get($json, "data.countries.{$country}.providers");

        if (! is_array($list)) {
            return [];
        }

        $names = [];
        foreach ($list as $k => $item) {
            $names[] = is_array($item)
                ? (string) ($item['name'] ?? $item['provider'] ?? $item['code'] ?? $k)
                : (is_string($item) ? $item : (string) $k);
        }

        return array_values(array_filter($names, fn ($n) => $n !== ''));
    }
}
