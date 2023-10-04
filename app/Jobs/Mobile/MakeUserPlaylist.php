<?php

namespace App\Jobs\Mobile;

use App\Models\Playlist;
use App\Models\Review;
use App\Models\UserCollected;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeUserPlaylist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $user, $name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $name = null)
    {
        $this->user = $user;
        $this->name = $name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $name = ["My Favourite", "My Club", "My Karaoke", "My Party"];
        $user_id = $this->user;

        if ($this->name) {
            Playlist::create([
                "user_id" => $user_id,
                "name" => $this->name,
                "songs" => [],
                "image" => null,
            ]);
        } else {
            array_map(function ($element) use ($user_id) {
                $element = Playlist::create([
                    "user_id" => $user_id,
                    "name" => $element,
                    "songs" => [],
                    "image" => null,
                ]);
                return $element;
            }, $name);
        }
    }
}
