<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\Recording;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MongoDB\BSON\ObjectId;


class StoreRecordingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $recording_key, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($recording_key, $media_file)
    {
        $this->recording_key = $recording_key;
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
            "media_type" => "IMAGE",
            "media_link" => $this->media_file
        ]);

        Recording::where('recording_key', $this->recording_key)->update([
            'media' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
