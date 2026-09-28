<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Payment $payment, protected bool $forAdmin = false)
    {
    }

    /**
     * Guests (anonymous notifiable) only get mail; real accounts also get
     * an in-app notification.
     */
    public function via($notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable
            ? ['mail']
            : ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $payment = $this->payment;
        $order   = $payment->order;
        $amount  = 'UGX ' . number_format($payment->amount);
        $paidAt  = ($payment->paid_at ?? now())->format('d M Y, H:i');

        if ($this->forAdmin) {
            return (new MailMessage)
                ->subject('Payment received - Order ' . $order->order_number)
                ->greeting('Hello,')
                ->line('A payment has been received.')
                ->line('Order Number: ' . $order->order_number)
                ->line('Customer: ' . ($order->customer_name ?: 'Guest'))
                ->line('Amount: ' . $amount)
                ->line('Mobile money number: ' . $payment->phone)
                ->line('Reference: ' . $payment->reference)
                ->action('View Order', url(route('admin.orders.show', $order->id)));
        }

        $mail = (new MailMessage)
            ->subject('Payment received - Order ' . $order->order_number)
            ->greeting('Hi ' . ($order->customer_name ?: 'Customer') . ',')
            ->line('We have received your payment. Thank you!')
            ->line('Order Number: ' . $order->order_number)
            ->line('Amount Paid: ' . $amount)
            ->line('Paid from: ' . $payment->phone)
            ->line('Payment Reference: ' . $payment->reference)
            ->line('Date: ' . $paidAt);

        if ($notifiable instanceof AnonymousNotifiable) {
            $mail->action('Track Your Order', url(route('order.track', [
                'order_number' => $order->order_number,
                'phone'        => $order->guest_phone,
            ])));
        } else {
            $mail->action('View Order', url(route('customer.order.view', $order->id)));
        }

        return $mail->line('Thank you for shopping with Shanana Beauty Products!');
    }

    public function toArray(object $notifiable): array
    {
        $order = $this->payment->order;

        return [
            'type'         => 'payment_received',
            'message'      => $this->forAdmin
                ? "Payment of UGX " . number_format($this->payment->amount) . " received for order {$order->order_number}."
                : "Your payment for order {$order->order_number} was received.",
            'order_number' => $order->order_number,
            'amount'       => $this->payment->amount,
            'url'          => $this->forAdmin
                ? route('admin.orders.show', $order->id)
                : route('customer.order.view', $order->id),
        ];
    }
}
