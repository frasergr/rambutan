<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Itn extends Model
{
    protected $fillable = [
        'type',
        'status_code',
        'email',
        'order_id',
        'order_ref',
        'xml'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}
