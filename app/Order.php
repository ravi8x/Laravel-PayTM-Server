<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_gateway_id', 'user_id', 'amount', 'status'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function orderItems()
    {
        return $this->hasMany('App\OrderItem');
    }
}
