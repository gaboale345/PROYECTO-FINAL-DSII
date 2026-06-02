<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use SerializesModels;

    public User $user;
    public string $code;

    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function build()
    {
        return $this
            ->subject('Código de verificación de cuenta')
            ->view('emails.verification_code');
    }
}
