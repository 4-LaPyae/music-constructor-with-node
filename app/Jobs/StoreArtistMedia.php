<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Artist;
use MongoDB\BSON\ObjectId;


class StoreArtistMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $artist_id, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($artist_id, $media_file)
    {
        $this->artist_id = $artist_id;
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
            "media_type_id" => "IMAGE",
            "media_link" => $this->media_file
        ]);

        // Artist::where('id', $this->artist_id)->update([
        //     'media_id' => $media->id
        // ]);

        Artist::where('artist_key', $this->artist_id)->update([
            'media' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
