<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $mailable;

    private string $toEmail;

    /**
     * Create a new job instance.
     */
    public function __construct( string $toEmail, Mailable $mailable)
    {
        $this->mailable = $mailable;
        $this->toEmail = $toEmail;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Mail::to($this->toEmail)->send($this->mailable);
    }
}
