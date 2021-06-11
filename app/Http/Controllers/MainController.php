<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Payment;
use Monolog\SignalHandler;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Session;

class MainController extends Controller
{
    public function index(){
        return view('index');
    }

    public function success(){
        return view('success');
    }

    //rzp_test_uyyV59TNJnTcpp
    //XBNPLynyVNLWVGZghzzoNUpe


    public function payment(Request $request){

        $amount = $request->input('amount');

        $api = new Api('rzp_test_uyyV59TNJnTcpp', 'XBNPLynyVNLWVGZghzzoNUpe');
        $order  = $api->order->create(array('receipt' => '123', 'amount' => $amount * 100 , 'currency' => 'INR')); // Creates order
        $orderId = $order['id']; 

        $user_pay = new Payment();
        $user_pay->price = $amount;
        $user_pay->status = 'Pending';
        $user_pay->order_id = $orderId;
        $user_pay->transaction_id ='';
        $user_pay->save();

        $data = array(
            'order_id' => $orderId,
            'amount' => $amount
        );

        // Session::put('order_id', $orderId);
        // Session::put('amount' , $amount);

       
        return redirect()->route('home')->with('data', $data);




    }


    public function pay(Request $request){
        $data = $request->all();
        $user = Payment::where('order_id', $data['razorpay_order_id'])->first();
        $user->status = 'Done';
        $user->transaction_id = $data['razorpay_payment_id'];

        $api = new Api('rzp_test_uyyV59TNJnTcpp', 'XBNPLynyVNLWVGZghzzoNUpe');
        

        try{
        $attributes = array(
             'razorpay_signature' => $data['razorpay_signature'],
             'razorpay_payment_id' => $data['razorpay_payment_id'],
             'razorpay_order_id' => $data['razorpay_order_id']
        );
        $order = $api->utility->verifyPaymentSignature($attributes);
        $success = true;
    }catch(SignatureVerificationError $e){

        $success = false;
    }

        
    if($success){
        $user->save();
        return redirect('success');
    }else{

        return redirect()->route('error');
    }

      

       

    }


    public function error(){
        return view('error');
    }

}
