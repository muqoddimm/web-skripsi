<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public string $loginMethod;

    public function __construct($user, string $loginMethod = 'Google Account')
    {
        $this->user = $user;
        $this->loginMethod = $loginMethod;
    }

    public function build()
    {
        return $this->subject('[RJ TEKNIK] Registrasi Akun Berhasil - Workshop RJ TEKNIK Coffee Roaster')
                    ->view('emails.user_registered');
    }
}
