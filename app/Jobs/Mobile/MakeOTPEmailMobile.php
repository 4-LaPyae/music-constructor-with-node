<?php

namespace App\Jobs\Mobile;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeOTPEmailMobile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email;
    private $otp;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [
            'title' => 'Totamusic login',
            'body' => 'Your OTP code is ' . $this->otp
        ];

        \Mail::to($this->email)->send(new \App\Mail\MyTestMail($details));

        dd("Email is Sent.");
    }
}
