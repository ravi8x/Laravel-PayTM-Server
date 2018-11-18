<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['transaction_id', 'status', 'raw_data'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
