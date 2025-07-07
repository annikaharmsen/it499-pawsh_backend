<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'status',
        'userid',
        'shipping_addressid'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userid');
    }

    public function shipping_address(): BelongsTo {
        return $this->belongsTo(Address::class, 'shipping_addressid');
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class, 'orderid');
    }

    public function payments(): hasMany {
        return $this->hasMany(Payment::class, 'orderid');
    }
}
