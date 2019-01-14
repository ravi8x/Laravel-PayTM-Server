<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'amount'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function product(){
        return $this->hasOne('App\Product', 'id', 'product_id');
    }
}
