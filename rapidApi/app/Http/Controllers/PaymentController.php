<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\OrderController;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Paystack;

class PaymentController extends Controller
{
    //

    public function redirectToGateway(PaymentRequest  $request)
    {
        try{
            $amount = 0;
            $quantity = 0;
            $reference = Paystack::genTranxRef();

            foreach($request->products as $product) {
                $product   = (object) $product;
                $amount   += Product::find($product->id)->price * $product->quantity;
                // Log::info( "test", [Product::find($product->id)] );
                $quantity += $product->quantity;
            }

            $orderCreator = new OrderController();
            $orderCreator->storeOrder($request, $reference);

            $response = Paystack::getAuthorizationUrl([
                "email"         => $request->email,
                "amount"        => $amount * 100,
                "quantity"      => $quantity,
                "currency"      => "GHS", // change as per need
                "reference"     => $reference,
                "metadata"      => json_encode(['products' => $request->product_map]), // this should be related data
            ]);
            
            return $response->url;
        }
        catch(\Exception $e) {

            report($e);

            return response([
                'msg'=>'The paystack token has expired. Please refresh the page and try again.', 
                'type'=>'error',
                'amount' => $amount,
            ], 500);

        }
    }

   public function handleGatewayCallback(Request  $request)
    {
        $paymentDetails = Paystack::getPaymentData();

        if($paymentDetails['status'] and $paymentDetails['data']['status'] = "success") {
            Order::where('reference', $paymentDetails['data']['reference'])->update([
                "status" => "processing"
            ]);

            OrderProduct::where('reference', $paymentDetails['data']['reference'])->update([
                "status" => "processing"
            ]);
        }

        return redirect("https://rapidcrewtechgh.com/order/response?status=".$paymentDetails['data']['status']);

    }
}
