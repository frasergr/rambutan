<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'status',
        'original_order_id',
        'name',
        'email',
        'region',
        'country',
        'zip_postal',
        'phone',
        'language',
        'ip',
        'currency',
        'shipping_name',
        'shipping_address',
        'shipping_address2',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_postal_code',
        'query',
    ];
}