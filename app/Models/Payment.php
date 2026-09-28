<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'reference', 'provider', 'provider_uuid', 'phone', 'amount',
        'status', 'failure_reason', 'provider_response', 'paid_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'paid_at'           => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}
