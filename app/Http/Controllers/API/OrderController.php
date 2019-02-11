<?php

namespace App\Http\Controllers\API;

use App\Order;
use App\OrderItem;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Webpatser\Uuid\Uuid;

// TODO - avoid require and make it autoload
require_once app_path() . '/Library/PaytmKit/lib/encdec_paytm.php';

class OrderController extends Controller
{

    /**
    Preparing new order
    Always Creates or fetches the order with status `NEW`
    return orderId and order Items
    */
    public function prepareOrder(Request $request){
        $input = $request->all();
        $bodyContent = json_decode($request->getContent());

        $orderItemsArray = $bodyContent->orderItems;

        $order = $this->createOrGetOrder();
        if($order){
            foreach ($orderItemsArray as $orderItem) {
                $this->addItem($order, $orderItem->productId, $orderItem->quantity);
            }

            $orderItems = $this->getOrderItems($order->id);
            $res = array();
            $res['id'] = $order->id;
            $res['order_gateway_id'] = $order->order_gateway_id;
            $res['status'] = $order->status;
            $res['amount'] = $this->getOrderTotal($order->id);

            $items = [];
            foreach ($orderItems as $orderItem) {
                $tmp = [];
                $tmp['quantity'] = $orderItem['quantity'];
                $tmp['amount'] = $orderItem['amount'];
                $tmp['product']['id'] = $orderItem['product']['id'];
                $tmp['product']['name'] = $orderItem['product']['name'];
                $tmp['product']['price'] = $orderItem['product']['price'];
                $tmp['product']['image'] = $orderItem['product']['image'];

                array_push($items, $tmp);
            }
            $res['order_items'] = $items;
            return $res;
        }else{
            return response()->json(['error' => 'Unable to create order!'], 412);
        }
        
        
    }

    public function getOrderItems($orderId){
        return OrderItem::select('product_id', 'quantity', 'amount')->where(['order_id' => $orderId])->with('product')->get();
    }

    /*
     * Adding an item to cart
     * Creates or fetches order
     * Insert the product in OrderItems table
     */
    public function addItem($order, $productId, $quantity)
    {
        $product = Product::where(['id' => $productId])->first();

        if (!$product) {
            return Response::json($this->getErrorResponse('Product not found'), 404);
        }

        $amount = $product->price * $quantity;

        $orderItem = OrderItem::firstOrNew(array('order_id' => $order['id'], 'product_id' => $productId));
        $orderItem->quantity = $quantity;
        $orderItem->amount = $amount;
        $orderItem->save();
        return $orderItem;
    }

    /*
     * Fetches single order along with order items
     */
    public function getOrder(Request $request, $id)
    {
        $userId = auth('api')->user()->id;
        $order = Order::where(['id' => $id])->where('user_id', '=', $userId)->first();

        if (!$order) {
            return Response::json($this->getErrorResponse('Order not found'), 404);
        }

        $orderItems = OrderItem::select('product_id', 'quantity', 'amount')->where(['order_id' => $id])->with('product')->get();
        $orderAmount = $this->getOrderTotal($id);

        $items = [];
        foreach ($orderItems as $orderItem) {
            $tmp = [];
            $tmp['quantity'] = $orderItem['quantity'];
            $tmp['amount'] = $orderItem['amount'];
            $tmp['product']['id'] = $orderItem['product']['id'];
            $tmp['product']['name'] = $orderItem['product']['name'];
            $tmp['product']['price'] = $orderItem['product']['price'];
            $tmp['product']['image'] = $orderItem['product']['image'];

            array_push($items, $tmp);
        }

        $order['order_items'] = $items;
        $order['amount'] = $orderAmount;

        return $order;
    }

    public function checkTransactionStatus(Request $request)
    {
        $input = $request->all();
        $userId = auth('api')->user()->id;
        $orderId = $input['order_gateway_id'];
        $order = Order::where(['order_gateway_id' => $orderId])->where('user_id', '=', $userId)->first();
        if(!$order){
            // Order not found
            return Response::json($this->getErrorResponse('Order not found'), 404);
        }

        $merchantMid = Config::get('services.paytm-wallet.merchant_id');
        $merchantKey = Config::get('services.paytm-wallet.merchant_key');

        $paytmParams["MID"] = $merchantMid;
        $paytmParams["ORDERID"] = $orderId;
        $paytmChecksum = getChecksumFromArray($paytmParams, $merchantKey);
        $paytmParams['CHECKSUMHASH'] = urlencode($paytmChecksum);
        $postData = "JsonData=" . json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $connection = curl_init(); // initiate curl
        // $transactionURL = "https://securegw.paytm.in/merchant-status/getTxnStatus"; // for production
        // TODO - configure this url b/w staging and production
        $transactionURL = "https://securegw-stage.paytm.in/merchant-status/getTxnStatus";
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_URL, $transactionURL);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $responseReader = curl_exec($connection);
        $responseData = json_decode($responseReader, true);

        

        if($order){
            $order->status = $this->getPaymentStatus($responseData['STATUS']);
            $order->save();
        }

        // inserting transaction
        $transaction = Transaction::create(['status' => $responseData['STATUS'], 'order_id' => $order->id, 'transaction_id' => Uuid::generate()->string, 'raw_data' => $responseReader]);
        $transaction->save();

        $order['amount'] = $this->getOrderTotal($order->id);
        return $order;
    }

    private function getPaymentStatus($status){
        $statusList = array('TXN_SUCCESS' => 'COMPLETED', 'TXN_FAILURE' => 'FAILED', 'PENDING' => 'PROCESSING');
        return $statusList[$status];
    }

    /* Generates checksum using request params
     * */
    public function generateCheckSum(Request $request)
    {
        $input = $request->all();
        $findme = 'REFUND';
        $findmepipe = '|';
        $paramList = array();
        foreach ($input as $key => $value) {
            $pos = strpos($value, $findme);
            $pospipe = strpos($value, $findmepipe);
            if ($pos === false || $pospipe === false) {
                $paramList[$key] = $value;
            }
        }

        $checkSum = getChecksumFromArray($paramList, Config::get('services.paytm-wallet.merchant_key'));
        $response = [];
        $response['checksum'] = $checkSum;

        return $response;
    }

    /**
     * Verify checksum won't be needed / won't work in PayTM SDK2.0
     **/
    /* public function verifyChecksum(Request $request)
    {
        $input = $request->all();
        $checksum = $input['check_sum'];
        unset($input['check_sum']);
        $verifyChecksum = verifychecksum_e($input, Config::get('services.paytm-wallet.merchant_key'), $checksum);
        return $verifyChecksum;
    }*/

    private function createOrGetOrder()
    {
        $userId = auth('api')->user()->id;
        $order = Order::where('user_id', '=', $userId)->where('status', '=', 'NEW')->first();
        if (!$order) {
            $order = Order::create(['status' => 'NEW', 'user_id' => $userId, 'order_gateway_id' => Uuid::generate()->string]);
        }
        $order->order_gateway_id = Uuid::generate()->string;
        if($order->save())
            return $order;
    }

    private function getOrderTotal($orderId)
    {
        return OrderItem::where(['order_id' => $orderId])->sum('amount');
    }

    private function getErrorResponse($message)
    {
        $error = [];
        $error['error'] = $message;
        return $error;
    }

}
