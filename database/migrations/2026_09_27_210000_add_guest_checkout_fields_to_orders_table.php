<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Lets a visitor place an order without an account. `user_id` stays
     * nullable (it already was) for guest orders, and we add a small set
     * of standard guest-contact columns plus a unique, human-shareable
     * order number so a guest can be looked up / tracked using only the
     * information they submitted at checkout (order number + email).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('guest_phone')->nullable()->after('guest_email');

            $table->index(['order_number', 'guest_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_number', 'guest_email']);
            $table->dropColumn(['order_number', 'guest_name', 'guest_email', 'guest_phone']);
        });
    }
};
