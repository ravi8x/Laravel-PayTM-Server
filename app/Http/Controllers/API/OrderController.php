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

    public function prepareOrder(Request $request){
        $input = $request->all();
        $bodyContent = json_decode($request->getContent());

        $orderItemsArray = $bodyContent->orderItems;
        $orderId = $bodyContent->orderId;

        foreach ($orderItemsArray as $orderItem) {
            $this->addItem($orderId, $orderItem->productId, $orderItem->quantity);
        }

        $order = $this->createOrGetOrder($orderId);

        echo json_encode($order);
    }

    /*
     * Adding an item to cart
     * Creates or fetches order
     * Insert the product in OrderItems table
     */
    public function addItem($orderId, $productId, $quantity)
    {
        // $input = $request->all();
        // $productId = isset($input['product_id']) ? $input['product_id'] : -1;
        // $quantity = isset($input['quantity']) ? $input['quantity'] : 1;

        $order = $this->createOrGetOrder($orderId);

        $product = Product::where(['id' => $productId])->first();

        if (!$product) {
            return Response::json($this->getErrorResponse('Product not found'), 404);
        }

        $amount = $product->price * $quantity;

        $orderItem = OrderItem::firstOrNew(array('order_id' => $order['order_id'], 'product_id' => $productId));
        $orderItem->quantity = $quantity;
        $orderItem->amount = $amount;
        $orderItem->save();

        return $order;
    }

    /*
     * Fetches single order along with order items
     */
    public function getOrder(Request $request, $id)
    {
        $order = Order::where(['order_id' => $id])->first();

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

        $orderId = $input['ORDER_ID'];
        $merchantMid = $input['MID'];
        $merchantKey = Config::get('services.paytm-wallet.merchant_key');

        $paytmParams["MID"] = $merchantMid;
        $paytmParams["ORDERID"] = $orderId;
        $paytmChecksum = getChecksumFromArray($paytmParams, $merchantKey);
        $paytmParams['CHECKSUMHASH'] = urlencode($paytmChecksum);
        $postData = "JsonData=" . json_encode($paytmParams, JSON_UNESCAPED_SLASHES);
        $connection = curl_init(); // initiate curl
        // $transactionURL = "https://securegw.paytm.in/merchant-status/getTxnStatus"; // for production
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

        $order = Order::where(['id' => $orderId])->first();
        if($order){
            $order->status = $responseData['STATUS'];
            $order->save();
        }

        // inserting transaction
        $transaction = Transaction::create(['status' => $responseData['STATUS'], 'order_id' => $orderId, 'transaction_id' => Uuid::generate()->string, 'raw_data' => $responseReader]);
        $transaction->save();

        echo json_encode($responseData);
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

    private function createOrGetOrder($orderId)
    {
        $order = Order::where('order_id', '=', $orderId)->where('status', '=', 'NEW')->first();

        // Order::where('order_id', $orderId)->where('status', 'NEW')->first();

        // echo 'Order: ' + dd($order);

        if (!$order) {
            $order = Order::create(['status' => 'NEW', 'user_id' => 1, 'order_id' => $orderId]);
            $order->save();
        }
        return $order;
    }

    private function getOrderTotal($orderId)
    {
        return OrderItem::where(['order_id' => $orderId])->sum('amount');
    }

    private function getErrorResponse($message)
    {
        $error = [];
        $error['message'] = $message;
        return $error;
    }

}
