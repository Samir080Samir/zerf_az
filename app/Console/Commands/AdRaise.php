<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\Payment\app\Models\Payment;

class AdRaise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ad-raise';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ad Raise';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Payment::paid()->notExpired()->typeUp()->where('created_at', '<', Carbon::now()->subHours(8))->where('service_period', '>', 0)->chunk(1000, function ($payments) {
            foreach ($payments as $payment) {
                $ad = $payment->ad();

                // Проверяем, что ad существует
                if ($ad) {
                    // Обновляем дату создания ad на текущую
                    $ad->update(['created_at' => Carbon::now()]);
                    // Уменьшаем service_period
                    $payment->decrement('service_period');
                    if ($payment->getAttribute('service_period') == 0) {
                        $payment->update(['expired' => true]);
                    }
                }
            }
        });
    }
}
