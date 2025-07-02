<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cartitems';

    protected $fillable = [
        'quantity',
        'productid',
        'userid'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'productid');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userid');
    }
}
