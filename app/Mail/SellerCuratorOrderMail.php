<?php

namespace App\Mail;

use App\Models\Curator;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerCuratorOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_orders;
    private $_total;
    private $_curator;
    private $_fee;
    /**
     * @var string
     */


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orders, float $total, Curator $curator)
    {
        $this->_orders = $orders;
        $this->_total = number_format($total, 2, '.', ' ');
        $this->_curator = $curator;

        if ($curator->user->subscribed('curator')) {
            $fee = round(($total) * 0.15, 2, PHP_ROUND_HALF_DOWN);
            $this->_fee = number_format($fee, 2, '.', ' ');
        } else {
            $this->_fee = number_format($this->_total / 2, 2, '.', ' ');
        }

        $this->subject('New Curator Order on uPlaylist!');

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user_track = $this->_orders->first()->user_track;
        $data = [
            'buyer_name' => $this->_orders->first()->user->first_name ? $this->_orders->first()->user->first_name . ' ' . $this->_orders->first()->user->last_name : null,
            'track_name' => $user_track->name,
            'track_genre' => $user_track->genre->name,
            'track_url' => $user_track->url,
            'orders' => $this->_orders,
            'total' => $this->_total,
            'fee' => $this->_fee,
            'curator_total' => convertCentsToDollars(collect($this->_orders)->sum('amount')),
            'payout_amount' => convertCentsToDollars($this->_curator->payout_amount),
            'orders_link' => config('app.fe_url') . 'login'
        ];
        return $this->view('mail.seller-order')->with($data);
    }
}
