<?php

namespace App\Jobs;

use App\Models\WordSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MakeSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $search_keyword,$user_id;
   
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($search_keyword,$user_id)
    {
        $this->search_keyword = $search_keyword;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = str_replace(" ", '', $this->search_keyword);

        if (!empty($result)) {
            $search_word = strtolower($result);
            $data = [
                'user_id' => $this->user_id,
                "word" => $this->search_keyword,
                'search_keyword' => $search_word,
            ];

            WordSearch::create($data);
        }
    }
}