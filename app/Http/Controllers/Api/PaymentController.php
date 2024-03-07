<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Payment\app\Models\Payment;

class PaymentController extends Controller
{
    public final function payRiffCallback(Request $request)
    {
        if ($request->method() == 'POST') {
            $data = $request->toArray();

            $paymentStatus = $data['payload']['orderStatus'];
            $paymentId = $data['payload']['sessionId'];

            $payment = Payment::firstWhere('transaction_id', '=', $paymentId);

            $status = match ($paymentStatus) {
                'APPROVED' => 'paid',
                'CREATED' => 'waiting',
                default => 'rejected',
            };

            optional($payment)->update([
                'status' => $status
            ]);

            if ($status == 'paid' && $payment) {
                switch ($payment->getAttribute('service_type')) {
                    case('up'):
                        $payment->ad()->update([
                            'created_at' => Carbon::now()
                        ]);
                        break;
                    case('vip'):
                    case('premium'):
                        $payment->ad()->update([
                            'type' => $payment->getAttribute('service_type'),
                            'created_at' => Carbon::now()
                        ]);
                        break;
                    case('balance'):
                        $payment->user()->increment('balance', $payment->getAttribute('amount'));
                }
            }

            return response()->json();

        } else {
            return redirect()->route('web:index')->with('success',__('The payment was successful!'));
        }
    }
}
