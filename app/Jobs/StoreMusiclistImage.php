<?php

namespace App\Jobs;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Musiclist;
use MongoDB\BSON\ObjectId;

class StoreMusiclistImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $listId, $media_file, $new, $old;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($listId, $media_file)
    {
        $this->listId = $listId;
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

        Musiclist::where('_id', $this->listId)->update([
            'photo' => [
                // "id" => new ObjectId($media->_id),
                "id" => $media->_id,
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
