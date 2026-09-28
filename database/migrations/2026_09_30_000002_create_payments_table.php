<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per mobile-money payment attempt for an order (a customer
     * can retry after a failed/declined prompt).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->uuid('reference')->unique();
            $table->string('provider')->default('marzpay');
            $table->string('provider_uuid')->nullable()->index();
            $table->string('phone', 30);
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending')->index(); // pending | successful | failed
            $table->string('failure_reason')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
