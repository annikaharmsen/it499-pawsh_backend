<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'street_one',
        'street_two',
        'city',
        'state',
        'postalcode',
        'country',
        'name',
        'userid'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userid');
    }
}
