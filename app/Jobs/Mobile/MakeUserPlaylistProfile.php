<?php

namespace App\Jobs\Mobile;

use App\Models\Media;
use App\Models\Playlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeUserPlaylistProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $playlistId, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($playlistId, $media_file)
    {
        $this->playlistId = $playlistId;
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

        Playlist::where('_id', $this->playlistId)->update([
            'media' => [
                // "id" => new ObjectId($media->_id),
                "id" => $media->_id,
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
