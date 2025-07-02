<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'orderitems';

    protected $fillable = [
        'unitprice',
        'quantity',
        'productid',
        'orderid'
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class, 'productid');
    }

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class, 'orderid');
    }
}
