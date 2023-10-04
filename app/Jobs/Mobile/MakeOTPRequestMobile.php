<?php

namespace App\Jobs\Mobile;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class MakeOTPRequestMobile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $mobile_number;
    private $otp;

    private $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImQ1NDdhMGY5ZWM0NGI5ZDM5NTE0NThmODFkNTk4YjUzZWIyOTk4ZjZiZmI4YjU0ZGEwMDg2YjZmMTY1MGQ5ZDk1M2Q3NTY2ZGI1Y2QxMWEwIn0.eyJhdWQiOiIzIiwianRpIjoiZDU0N2EwZjllYzQ0YjlkMzk1MTQ1OGY4MWQ1OThiNTNlYjI5OThmNmJmYjhiNTRkYTAwODZiNmYxNjUwZDlkOTUzZDc1NjZkYjVjZDExYTAiLCJpYXQiOjE2NTgzMDA4NTcsIm5iZiI6MTY1ODMwMDg1NywiZXhwIjoxNjg5ODM2ODU3LCJzdWIiOiIxMjMyIiwic2NvcGVzIjpbXX0.twDYXKdSfgnLsV12ZcjmAwOWw-TBcN60Yj_Z25o82YtJg_v3KYDa2EiXSw2k4UwC2wK6DzPRa9hJOYTHKf69VcHfqkaa6-FjuU5HEyrbb3ciLigsYaULOEstpRQzvl9tX-0Y6_Zo_bFclrgrt7LYw8sQRZRGZ6DebcNROk7nwdCCjbocrhNirME311MGqRBATuarA2ahQ0QKTpbNLsw4gUEsgjrt0JwTNBU5VV0PtS3rQU2EnZpW2LcYAS83GBcw1gSTZmCdnlHT4gTHBDDVFLSRq74dNW6lQQnLfzOo-rhzTOp9BmmTTHM4HHjZW5vmoGa0Ksm1At7pkGB4sENOyXZkbRC3nhlmtyS5jCFp0BaLm1BxgM5l7S9zzd5-YUGiicMRcoADh81jbcq3NDGvB5D3dCQ_1CmoAp9C_7jxzyte49F1RNi8nGKttQOu1kNjEKRXiU2Vrw4atDZgxcWU1_2ZoYlos_mOFTpLkG1wn0otCKXU1G4H0lswINDqowXGxi08xSkyaMR7XYKw5j9oMAsUlmB3KZHXaohE4rXHrNi-vQpcxn2d4ebw5shfV8RbSWZhf_2A2K-zRxU1IsE5c3y6pzVkRzVECzmBZRvUFQf9DX5M0iYDXtFyr7IWYzjfS9-Suy8xnNQticIloA6rgLtCBaL6M7ppvXN3iDUnUhA";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mobile_number, $otp)
    {
        $this->mobile_number = $mobile_number;
        $this->otp = $otp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('https://boomsms.net/api/sms/json', [
            'from' => 'Gabarmart',
            'text' => 'Use OTP ' . $this->otp . ' to login',
            'to' => $this->mobile_number
        ]);

        //dd($response);
        // return $response;
    }
}
