<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Band;


class StoreBandMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $band_id, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($band_id, $media_file)
    {
        $this->band_id = $band_id;
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

        Band::where('id', $this->band_id)->update([
            'media_id' => $media->id
        ]);
    }
}
