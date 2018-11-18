<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

// TODO - avoid require and make it autoload
require_once app_path() . '/Library/PaytmKit/lib/encdec_paytm.php';

class OrderController extends Controller
{
    public function prepareOrder(Request $request)
    {
        $input = $request->all();
        $findme   = 'REFUND';
        $findmepipe = '|';
        $paramList = array();
        foreach($input as $key=>$value)
        {
            $pos = strpos($value, $findme);
            $pospipe = strpos($value, $findmepipe);
            if ($pos === false || $pospipe === false)
            {
                $paramList[$key] = $value;
            }
        }

        // TODO - create order in db
        $checkSum = getChecksumFromArray($paramList, Config::get('services.paytm-wallet.merchant_key'));

        $response = [];
        $response['checksum'] = $checkSum;
        $response['order_id'] = $paramList['ORDER_ID'];

        return $response;
    }

    public function verifyChecksum(Request $request)
    {
        $input = $request->all();
        $checksum = $input['check_sum'];
        unset($input['check_sum']);
        $verifyChecksum = verifychecksum_e($input, Config::get('services.paytm-wallet.merchant_key'), $checksum);
        return $verifyChecksum;
    }
}
