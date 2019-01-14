<?php

namespace App\Http\Controllers\API;

use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function getProducts(Request $request){
        return Product::select('id', 'name', 'image', 'price')->get();
    }
}