<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * A guest customer is notified through an on-demand (anonymous)
     * notifiable, which has no `notifications` table row to write to,
     * so it only ever gets the `mail` channel. Real accounts (a
     * registered customer or an admin) keep both channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return $notifiable instanceof AnonymousNotifiable
            ? ['mail']
            : ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $order = $this->order;
        $name  = $order->customer_name ?: 'Customer';

        $mail = (new MailMessage)
            ->subject('Order Confirmation - #' . $order->order_number)
            ->greeting('Hi ' . $name . ',')
            ->line('A new order has been placed.')
            ->line('Order Number: ' . $order->order_number)
            ->line('Total: Ugx ' . number_format($order->total_amount));

        if ($notifiable instanceof AnonymousNotifiable) {
            // Guests have no account/dashboard to view the order in, so
            // point them at the public order-tracking page instead.
            $mail->action('Track Your Order', url(route('order.track', [
                'order_number' => $order->order_number,
                'email'        => $order->guest_email,
            ])));
        } else {
            $mail->action('View Order', url(route('customer.order.view', $order->id)));
        }

        return $mail->line('Thank you for shopping with Shanana Beauty Products!');
    }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'message' => "Order #{$this->order->id} status changed to {$this->status}.",
    //         'url'     => route('admin.order.view', $this->order->id),
    //         'icon'    => $this->status == 'delivered' ? '✅' : '📦',
    //     ];
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}