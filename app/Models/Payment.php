<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'amount',
        'status',
        'transaction_reference_id',
        'order_id',
        'payment_method_id'
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function method(): HasOne {
        return $this->hasOne(PaymentMethod::class);
    }
}
