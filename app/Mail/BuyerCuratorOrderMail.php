<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuyerCuratorOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_orders;
    private $_total;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orders, float $total)
    {
        $this->_orders = $orders;
        $this->_total = number_format($total, 2, '.', ' ');
        $this->subject('uPlaylist Order Confirmation');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.buyer-order')
            ->with([
                'track' => $this->_orders[0]->user_track,
                'orders' => $this->_orders,
                'total' => $this->_total,
            ]);
    }
}
