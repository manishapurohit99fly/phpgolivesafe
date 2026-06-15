<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminTwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otp;
    public $first_name;

    /**
     * Create a new message instance.
     */
    public function __construct($otp, $first_name)
    {
        $this->otp = $otp;
        $this->first_name = $first_name;
    }

    public function build()
    {
        return $this->subject('Your Admin Verification Code')
                    ->view('emails.admin_otp')
                    ->with([
                        'otp' => $this->otp,
                        'first_name' => $this->first_name,
                    ]);
    }
}

