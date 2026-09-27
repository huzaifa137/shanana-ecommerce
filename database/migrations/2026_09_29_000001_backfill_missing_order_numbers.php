<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Orders placed before the `order_number` column existed have NULL there.
     * Give each one a unique SHN-XXXXXXXX number so the admin screens can
     * identify every order. New orders already get one in
     * OrderController::placeOrder().
     */
    public function up(): void
    {
        Order::whereNull('order_number')->orderBy('id')->each(function (Order $order) {
            $order->order_number = Order::generateOrderNumber();
            $order->save();
        });
    }

    public function down(): void
    {
        // Intentionally left empty: generated numbers may already have been
        // shared with customers, so they are not removed on rollback.
    }
};
