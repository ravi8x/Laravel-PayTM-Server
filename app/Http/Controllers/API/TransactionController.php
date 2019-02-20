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

		})->orderBy('id', 'DESC')->get();

        // TODO - optimize the below JSON preparation
        foreach ($transactions as $transaction) {
            $tmp = [];
            $tmp['transaction_id'] = $transaction['transaction_id'];
            $tmp['status'] = $transaction['status'];
            $tmp['order_id'] = $transaction['order_id'];
            $tmp['created_at'] = $transaction['created_at']->toDateTimeString();
            $tmp['order'] = $transaction->order;
            $orderItems = $transaction->order->orderItems;

            foreach ($orderItems as $orderItem) {
                $items = [];
                $items['quantity'] = $orderItem['quantity'];
                $items['amount'] = $orderItem['amount'];
                $items['product']['id'] = $orderItem['product']['id'];
                $items['product']['name'] = $orderItem['product']['name'];
                $items['product']['price'] = $orderItem['product']['price'];
                $items['product']['image'] = $orderItem['product']['image'];

                $tmp['order_items'] = $items;
            }

            array_push($response, $tmp);
        }

        return $response;
    }
}
