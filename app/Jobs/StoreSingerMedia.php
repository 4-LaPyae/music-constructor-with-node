<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Singer;
use MongoDB\BSON\ObjectId;


class StoreSingerMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $singer_key, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($singer_key, $media_file)
    {
        $this->singer_key = $singer_key;
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

        Singer::where('singer_key', $this->singer_key)->update([
            'media' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
