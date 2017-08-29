<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $errorSubject, $error;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($errorSubject, $error)
    {
        $this->errorSubject = $errorSubject;
        $this->error = $error;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->errorSubject)
            ->view('emails.errors.main')
            ->with('error', $this->error);
    }
}
