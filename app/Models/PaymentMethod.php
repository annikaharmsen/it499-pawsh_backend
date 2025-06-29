<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'last4',
        'bank_name',
        'active',
        'user_id',
        'address_id',
        'method_reference_id'
    ];

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function address(): HasOne {
        return $this->hasOne(Address::class);
    }
}
