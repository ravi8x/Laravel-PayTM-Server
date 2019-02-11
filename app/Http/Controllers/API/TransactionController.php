<?php

namespace App\Http\Controllers\API;

use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function getTransactions(Request $request)
    {
    	$response = [];
    	$userId = auth('api')->user()->id;

        $transactions = Transaction::whereHas('order', function($q) use ($userId)
		{
		    $q->where('user_id','=', $userId);

		})->get();

        foreach ($transactions as $transaction) {
            $tmp = [];
            $tmp['id'] = $transaction['transaction_id'];
            $tmp['status'] = $transaction['status'];
            $tmp['order_id'] = $transaction['order_id'];
            $tmp['created_at'] = $transaction['created_at']->toDateTimeString();
            $transaction->order->orderItems;
            $tmp['order'] = $transaction->order;
            // $tmp['order_items'] = $transaction->order->orderItems;

            array_push($response, $tmp);
        }

        return $response;
    }
}
