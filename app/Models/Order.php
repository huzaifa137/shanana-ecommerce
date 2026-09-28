<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Order extends Model
{
    // Deleting an order only stamps `deleted_at`; the row and its items stay
    // in the database (order_items cascade on a *hard* delete), so an admin
    // mistake can be undone and sales records are never truly lost.
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'guest_name', 'guest_email', 'guest_phone',
        'total_amount', 'status', 'payment_method', 'payment_status', 'paid_at', 'shipping_info',
    ];

    protected $casts = [
        'shipping_info' => 'array',
        'paid_at'       => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Signed (tamper-proof) link to the payment page. Works for guests and
     * customers alike, so no login is needed to pay or retry.
     */
    public function paymentUrl(): string
    {
        return URL::temporarySignedRoute(
            'order.payment',
            now()->addDay(),
            ['orderNumber' => $this->order_number]
        );
    }

    /**
     * Whether the customer should be offered "Pay now" / "Retry payment".
     */
    public function canRetryPayment(): bool
    {
        if ($this->status === 'canceled' || $this->isPaid()) {
            return false;
        }

        $latest = $this->payments()->latest('id')->first();

        return ! $latest
            || $latest->status === 'failed'
            || ($latest->isPending() && $latest->created_at->lt(now()->subMinutes(3)));
    }

    /**
     * True when this order is waiting on (or needs) a payment.
     */
    public function needsPayment(): bool
    {
        return in_array($this->payment_status, ['unpaid', 'pending', 'failed'], true)
            && $this->status !== 'canceled';
    }

    public function getPaymentLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'Paid',
            'pending' => 'Awaiting payment',
            'failed'  => 'Payment failed',
            'unpaid'  => 'Unpaid',
            default   => '—',
        };
    }

    public function getPaymentBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'    => 'bg-success',
            'pending' => 'bg-warning text-dark',
            'failed'  => 'bg-danger',
            'unpaid'  => 'bg-secondary',
            default   => 'bg-light text-dark',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * True when no registered account is attached to this order.
     */
    public function isGuestOrder(): bool
    {
        return empty($this->user_id);
    }

    /**
     * Customer's display name, whether they checked out as an account
     * holder or as a guest.
     */
    public function getCustomerNameAttribute(): ?string
    {
        if ($this->user_id && $this->user) {
            return trim($this->user->first_name . ' ' . $this->user->last_name);
        }

        return $this->guest_name;
    }

    /**
     * Customer's email, whether they checked out as an account holder
     * or as a guest.
     */
    public function getCustomerEmailAttribute(): ?string
    {
        if ($this->user_id && $this->user) {
            return $this->user->email;
        }

        return $this->guest_email;
    }

    /**
     * Customer's phone number, whether they checked out as an account
     * holder or as a guest.
     */
    public function getCustomerPhoneAttribute(): ?string
    {
        if ($this->user_id && $this->user) {
            return $this->user->mobile;
        }

        return $this->guest_phone;
    }

    /**
     * Generate a unique, human-shareable order number (e.g. SHN-4F2A9C1B)
     * that a guest can use, together with the email they checked out
     * with, to look their order up later without an account.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $candidate = 'SHN-' . strtoupper(Str::random(8));
        } while (self::where('order_number', $candidate)->exists());

        return $candidate;
    }
}