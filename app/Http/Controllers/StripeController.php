<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Omnipay\Omnipay;
use App\Models\User;
use App\Models\Payment;
use Mail;

class StripeController extends Controller
{
    public function create()
    {
        return view('stripe.create');
    }

    public function store(Request $request)
    {
        request()->validate([
            'amount' => 'required|numeric|gte:1',
            ]);
        
        // Credit Card Donation
        if ($request->input('stripeToken')) {

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

            $response = $stripe->charges->create([
                'amount' => $request->input('amount')*100,
                'currency' => 'USD',
                'source' => $request->input('stripeToken'),
            ]);

            // payment was successful: insert transaction data into the database
            if (is_null($response->failure_code)) {

                $payment = new Payment;
                $payment->payment_id = $response->id;
                $payment->amount = $response->amount;
                $payment->currency = 'USD';

                if (Auth::check()) {
                    $payment->user_id = Auth::user()->id;
                }

                if ($request->input('email')) {
                    $payment->email =  request('email');
                }

                $payment->save();
                // $this->notify("$payment->amount");
                return back()->with('success', "Thank you! Your payment id is " . $response->id);
            } else {
                return back()->with('Fail', "Transaction Failed " . $response->failure_message);
            }
        }
        // Bitcoin Donation
        else {
            $payment = new Payment;

            $payment->currency = 'BTC';

            if (Auth::check()) {
                $payment->user_id = Auth::user()->id;
            }

            if ($request->input('email')) {
                $payment->email =  request('email');
            }


            if ($request->input('amount')) {
                $payment->amount =  request('amount');
            }
            $payment->payment_id = Str::random(20);
            $payment->save();

            // $this->notify("Bitcoin donation recevied $payment->amount");

            return back()->with('success', 'Thank you for your donation!');
        }
    }
  
    public function notify(string $comment)
    {
        Mail::send(
            'mail.emailnotify',
            [
                'comment' => $comment
            ],
            function ($message) {
                $message->from('chief@myfamilycookbook.org');
                $message->to('chief@myfamilycookbook.org', 'Chief')
                    ->subject('Notification');
            }
        );
    }

    public function storeOmni(Request $request)
    {
        // Credit Card Donation
        if ($request->input('stripeToken')) {

            $gateway = Omnipay::create('Stripe');
            $gateway->setApiKey(env('STRIPE_SECRET'));

            $token = $request->input('stripeToken');

            $response = $gateway->purchase([
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'amount' => $request->input('amount'),
                'currency' => 'USD',
                'token' => $token,
            ])->send();

            if ($response->isSuccessful()) {
                // payment was successful: insert transaction data into the database
                $arr_payment_data = $response->getData();

                $isPaymentExist = Payment::where('payment_id', $arr_payment_data['id'])->first();

                if (!$isPaymentExist) {
                    $payment = new Payment;
                    $payment->payment_id = $arr_payment_data['id'];
                    $payment->amount = $arr_payment_data['amount'] / 100;
                    $payment->currency = 'USD';

                    if (Auth::check()) {
                        $payment->user_id = Auth::user()->id;
                    }

                    if ($request->input('email')) {
                        $payment->email =  request('email');
                    }

                    $payment->save();
                }
                $this->notify("$payment->amount");

                return back()->with('success', "Thank you! Your payment id is " . $arr_payment_data['id']);
            } else {
                // payment failed: display message to customer
                return $response->getMessage();
            }
        }
    }
}
