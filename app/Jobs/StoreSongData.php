<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Song;
use MongoDB\BSON\ObjectId;

class StoreSongData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $song_key, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($song_key, $media_file)
    {
        $this->song_key = $song_key;
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

        Song::where('song_key', $this->song_key)->update([
            'media' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
