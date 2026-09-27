<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Switch authentication and order-tracking from email to phone.
     *
     * - users.mobile becomes required and unique (login key).
     * - users.email becomes nullable (still stored when provided,
     *   used for welcome/reset emails, but no longer the login key).
     * - orders gets a composite index on (order_number, guest_phone)
     *   to make phone-based tracking look-ups fast.
     *
     * Written with raw SQL instead of Blueprint::change(), because that
     * requires doctrine/dbal, which is not installed in this project
     * (composer.json has no doctrine/dbal entry) and would otherwise make
     * `php artisan migrate` fail immediately.
     */
    public function up(): void
    {
        // Safety backfill: mobile is about to become NOT NULL + UNIQUE.
        // Any existing account with no mobile number (or one shared with
        // another account) would make that constraint fail to apply, so
        // give each blank one a distinct placeholder first. These accounts
        // should have their real number collected from the admin panel
        // afterwards. Duplicate *non-blank* numbers, if any, need manual
        // resolution before this migration can run — decide which record
        // keeps the number through direct DB access or the admin panel.
        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('mobile')->orWhere('mobile', '');
            })
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['mobile' => 'UNSET-' . $user->id]);
            });

        // email was unique at creation — drop that constraint before making
        // it nullable, since it's no longer the login key.
        DB::statement('ALTER TABLE `users` DROP INDEX `users_email_unique`');
        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NULL');

        // mobile is now the login key — must be unique and present.
        DB::statement('ALTER TABLE `users` MODIFY `mobile` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `users` ADD UNIQUE `users_mobile_unique` (`mobile`)');

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['order_number', 'guest_phone'], 'orders_number_phone_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_number_phone_index');
        });

        DB::statement('ALTER TABLE `users` DROP INDEX `users_mobile_unique`');
        DB::statement('ALTER TABLE `users` MODIFY `mobile` VARCHAR(255) NULL');

        DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `users` ADD UNIQUE `users_email_unique` (`email`)');
    }
};
