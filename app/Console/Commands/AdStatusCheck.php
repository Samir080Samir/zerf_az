<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Payment\app\Models\Payment;

class AdStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ad-status-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking the expiration date of VIP or Premium status';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Payment::paid()->notExpired()->typeVipPremium()->chunk(1000, function ($payments) {
            foreach ($payments as $payment) {
                $ad = $payment->ad();

                // Проверяем, что ad существует
                if ($ad) {
                    $servicePeriodInDays = $payment->getAttribute('service_period');

                    // Проверяем, прошел ли указанный период
                    if ($servicePeriodInDays <= 0) {
                        // Обновляем тип ad на 'free'
                        $ad->update(['type' => 'free']);
                        $payment->update(['expired' => true]);
                    }
                }
            }
        });
    }
}
