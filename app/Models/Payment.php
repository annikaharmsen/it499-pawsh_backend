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
        'transaction_referenceid',
        'orderid',
        'payment_methodid'
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class, 'orderid');
    }
}
