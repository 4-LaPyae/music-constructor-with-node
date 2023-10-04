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


class UpdateAlbumProducer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $album, $producer_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($album, $producer_id)
    {
        $this->album = $album;
        $this->producer_id = $producer_id;
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
