<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MarzPayCheck extends Command
{
    protected $signature = 'marzpay:check';

    protected $description = 'Check that the Marz Pay API credentials in .env are loaded and accepted';

    public function handle(): int
    {
        $key    = (string) config('services.marzpay.key');
        $secret = (string) config('services.marzpay.secret');
        $base   = rtrim((string) config('services.marzpay.base_url'), '/');

        if ($key === '' || $secret === '') {
            $this->error('MARZPAY_API_KEY / MARZPAY_API_SECRET are empty. Add them to .env, then run: php artisan config:clear');

            return self::FAILURE;
        }

        $this->line('Key:     ' . substr($key, 0, 8) . '…  (' . strlen($key) . ' chars)');
        $this->line('Secret:  ' . strlen($secret) . ' chars');
        $this->line('Base:    ' . $base);

        if ($key !== trim($key) || $secret !== trim($secret)
            || preg_match('/["\'\s]/', $key . $secret)) {
            $this->warn('The key or secret contains spaces or quotes. Remove them in .env.');
        }

        $http = Http::withBasicAuth($key, $secret)->acceptJson()->timeout(20);

        // What this API key is actually subscribed to (MTN / Airtel collection etc.)
        $subscribed = $http->get($base . '/services');
        $this->line('');
        $this->line('GET /services -> HTTP ' . $subscribed->status());
        $this->line(substr((string) $subscribed->body(), 0, 1500));

        $response = $http->get($base . '/collect-money/services');

        $this->line('');
        $this->line('GET /collect-money/services -> HTTP ' . $response->status());
        $this->line(substr((string) $response->body(), 0, 1500));
        $this->line('');

        if ($response->successful()) {
            $this->info('Credentials accepted.');

            return self::SUCCESS;
        }

        $this->error('Marz Pay rejected the request. Re-copy the API key and secret from your Marz Pay dashboard.');

        return self::FAILURE;
    }
}
