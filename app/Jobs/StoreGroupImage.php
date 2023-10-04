<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Group;
use MongoDB\BSON\ObjectId;

class StoreGroupImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $groupId, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($groupId, $media_file)
    {
        $this->groupId = $groupId;
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

        Group::where('_id', $this->groupId)->update([
            'group_image' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
