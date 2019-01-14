<?php

namespace App\Http\Controllers\API;

use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function getTransactions(Request $request)
    {
        return Transaction::with('order')->get();
    }
}
