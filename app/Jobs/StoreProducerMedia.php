<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use App\Models\Producer;
use MongoDB\BSON\ObjectId;


class StoreProducerMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $producer_key, $media_file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($producer_key, $media_file)
    {
        $this->producer_key = $producer_key;
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

        Producer::where('producer_key', $this->producer_key)->update([
            'media' => [
                "id" => new ObjectId($media->_id),
                "media_link" => $media->media_link,
            ]
        ]);
    }
}
