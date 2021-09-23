<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CuratorOrderUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->_order = $order;
        $this->subject('uPlaylist Order Updated');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.order-updated')
            ->with([
                'order' => $this->_order,
            ]);
    }
}
