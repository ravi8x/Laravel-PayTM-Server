<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class AppController extends Controller
{
    public function getAppConfig(Request $request){
        $config = array();
        $config['merchant_id'] = Config::get('services.paytm-wallet.merchant_id');
        $config['channel'] = Config::get('services.paytm-wallet.channel');
        $config['industry_type'] = Config::get('services.paytm-wallet.industry_type');
        $config['website'] = Config::get('services.paytm-wallet.merchant_website');
        return $config;
    }
}
