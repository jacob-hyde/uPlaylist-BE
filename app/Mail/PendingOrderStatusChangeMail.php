<?php

namespace App\Mail;

use App\Models\Curator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingOrderStatusChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    private $_account;
    private $_is_subscribed;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Curator $account)
    {
        $this->_account = $account;
        $this->_is_subscribed = $account->user->subscribed('curator');
        $this->subject('uPlaylist Pending Curator Orders');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.pending-orders')
            ->with([
                'account' => $this->_account,
                'is_subscribed' => $this->_is_subscribed
            ]);
    }
}
