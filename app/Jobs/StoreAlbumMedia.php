<?php

namespace App\Jobs;

use App\Models\Album;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Artist;
use MongoDB\BSON\ObjectId;


class StoreAlbumMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $album_key, $media_file, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($album_key, $media_file, $type)
    {
        $this->album_key = $album_key;
        $this->media_file = $media_file;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $media = Media::create([
            "media_type" => "IMAGE",
            "media_link" => $this->media_file
        ]);

        switch ($this->type) {
            case "front_cover":
                Album::where('album_key', $this->album_key)->update([
                    'front_cover' => [
                        "id" => new ObjectId($media->_id),
                        "media_link" => $media->media_link,
                    ]
                ]);
                break;
            case "back_cover":
                Album::where('album_key', $this->album_key)->update([
                    'back_cover' => [
                        "id" => new ObjectId($media->_id),
                        "media_link" => $media->media_link,
                    ]
                ]);
                break;
            default:
                Album::where('album_key', $this->album_key)->update([
                    'poster' => [
                        "id" => new ObjectId($media->_id),
                        "media_link" => $media->media_link,
                    ]

                ]);
        }
    }
}
