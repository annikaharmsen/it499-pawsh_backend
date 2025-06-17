<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'status',
        'user_id',
        'address_id'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): BelongsTo {
        return $this->belongsTo(Payment::class);
    }
}
