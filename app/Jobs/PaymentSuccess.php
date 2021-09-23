<?php

namespace App\Jobs;

use App\Mail\BuyerCuratorOrderMail;
use App\Mail\SellerCuratorOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use KnotAShell\Orders\Models\Payment;

class PaymentSuccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;
    public $recurring;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, bool $recurring)
    {
        $this->payment = $payment;
        $this->recurring = $recurring;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->payment->order->product_type->type !== 'curator') {
            return;
        }

        $corders = $this->payment->paymentables->pluck('paymentable');
        Mail::to($corders[0]->user->email)->queue(new BuyerCuratorOrderMail($corders, convertCentsToDollars($this->payment->amount)));
        collect($corders)->groupBy('curator_id')->each(function ($corders) {
            $amount = convertCentsToDollars($corders->sum('playlist_price'));
            Mail::to($corders[0]->curator->user->email)->queue(new SellerCuratorOrderMail($corders, $amount, $corders[0]->curator));
        });
    }
}
