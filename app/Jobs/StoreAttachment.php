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
use App\Fileoperations\ProducerOperation;
use MongoDB\BSON\ObjectId;


class StoreAttachment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $songs, $images, $producer_id, $type;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($songs, $images, $producer_id, $type)
    {
        $this->songs = $songs;
        $this->images = $images;
        $this->producer_id = $producer_id;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $arr = [];
        foreach ($this->images as $image) {

            $operation = new ProducerOperation($this->producer_id, $image, null, null);

            $media = Media::create([
                "media_type" => "IMAGE",
                "media_link" => $operation->StoreContractBase64Image()
            ]);
            array_push($arr, $media);
        }

        $attachments = array_map(function ($element) {
            $element = [
                "id" => new ObjectId($element->_id),
                "media_link" => $element->media_link,
            ];
            return $element;
        }, $arr);

        // $result = array_filter($attachments, function ($item) {
        //     return is_object($item) || is_array($item);
        // });

        $song =  Song::where('song_key', $this->songs)->first();

        if ($this->type == "update") {
            $data = array_merge((array)$song->attachments, $attachments);
            Song::where('song_key', $this->songs)->update([
                'attachments' => $data,
            ]);
        } else {
            Song::whereIn('song_key', $this->songs)->update([
                'attachments' => $attachments,
            ]);
        }
    }
}
