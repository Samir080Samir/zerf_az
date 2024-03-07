<?php

namespace App\Http\Controllers\Web\Templates\Default;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Adversity\app\Models\Adversity;
use Modules\Ad\app\Models\Ad;
use Modules\Payment\app\Models\Payment;
use NPA\PayRiff\Exceptions\RequestExceptionHandler;
use NPA\PayRiff\PayRiff;

class PaymentController extends Controller
{
    /**
     * @throws RequestExceptionHandler
     */
    public function pay(Request $request)
    {
        $typeQuery = $request->input('type');

        $serviceType = key($typeQuery);
        $servicePeriod = reset($typeQuery);

        $adId = $request->input('id');
        $paymentType = $request->input('payment');

        $amount = Adversity::where([
            ['type', '=', $serviceType],
            ['period', '=', $servicePeriod],
        ])->first()?->getAttribute('price');

        if ($paymentType == 'online') {
            $payRiff = new PayRiff();

            $payRiff->setEncryptionToken(time() . Str::uuid());
            $payRiff->setMerchantNo('ES1092456');
            $payRiff->setSecretKey('6536B8C22BB84E728A8DA5605B2B95E3');

            $response = $payRiff->createOrder(
                amount: $amount,
                description: 'Buy status',
                approveURL: route('pay-riff-callback'),
                cancelURL: route('pay-riff-callback'),
                declineURL: route('pay-riff-callback')
            );

            $url = $response;
            $transactionId = $payRiff->getSessionId();
        } else {

            if (auth()->check() && $amount > auth()->user()->getAttribute('balance')) {
                return redirect()->route('web:balance')->withErrors(['Replenish balance for further action!']);
            } else {
                $ad = Ad::find($adId);

                switch ($serviceType) {
                    case('up'):
                        $ad->update([
                            'created_at' => Carbon::now()
                        ]);
                        break;
                    case('vip'):
                    case('premium'):
                        $ad->update([
                            'type' => $serviceType,
                            'created_at' => Carbon::now()
                        ]);
                        break;
                }

                auth()->user()->decrement('balance', $amount);
            }


            $url = null;
            $transactionId = Str::upper(Str::random(32));
        }

        Payment::create([
            'transaction_id' => $transactionId,
            'ad_id' => $adId,
            'amount' => $amount,
            'user_id' => auth()->id(),
            'payment_type' => $paymentType,
            'service_type' => $serviceType,
            'service_period' => $servicePeriod,
            'status' => $paymentType == 'balance' ? 'paid' : 'waiting',
            'url' => $url
        ]);

        return !is_bool($url) ? redirect()->to($url) : redirect()->back();
    }

    /**
     * @throws RequestExceptionHandler
     */
    public function addBalance(Request $request)
    {
        $amount = $request->input('amount');

        $payRiff = new PayRiff();

        $payRiff->setEncryptionToken(time() . Str::uuid());
        $payRiff->setMerchantNo('ES1092456');
        $payRiff->setSecretKey('6536B8C22BB84E728A8DA5605B2B95E3');

        $response = $payRiff->createOrder(
            amount: $amount,
            description: 'Add balance',
            approveURL: route('pay-riff-callback'),
            cancelURL: route('pay-riff-callback'),
            declineURL: route('pay-riff-callback')
        );

        $transactionId = $payRiff->getSessionId();

        Payment::create([
            'transaction_id' => $transactionId,
            'ad_id' => null,
            'amount' => $amount,
            'user_id' => auth()->id(),
            'payment_type' => 'online',
            'service_type' => 'balance',
            'service_period' => null,
            'url' => $response
        ]);

        return !is_bool($response) ? redirect()->to($response) : redirect()->back();
    }
}
