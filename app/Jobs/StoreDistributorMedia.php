<?php

namespace App\Jobs;

use App\Models\Distributor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Producer;


class StoreDistributorMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $distributor_id, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($distributor_id, $media_file)
    {
        $this->distributor_id = $distributor_id;
        $this->media_file = $media_file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $media = Media::create([
            "media_type_id" => 1,
            "media_link" => $this->media_file
        ]);

        Distributor::where('id', $this->distributor_id)->update([
            'media_id' => $media->id
        ]);
    }
}
