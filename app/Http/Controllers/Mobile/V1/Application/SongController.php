<?php

namespace App\Http\Controllers\Mobile\V1\Application;

use App\Fileoperations\MediaOperation;
use App\Fileoperations\SongOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SongRequest;
use App\Http\Resources\Admin\SongResource;
use App\Models\Album;
use App\Models\Singer;
use App\Models\Song;
use App\Models\Producer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\MakeSearchJob;
use App\Jobs\StoreSongData;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function rawSearch()
    {

        // $page = Request()->has('page') ? Request()->get('page') : 1;
        // $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $filterName = Request()->has('filter_name') ? Request()->get('filter_name') : '';
        $type = Request()->has('type') ?
            explode(',', Request()->get('type')) : NULL;

        // MakeSearchJob::dispatchAfterResponse($filterName, Request()->get("admin_id"));

        $arr1 = [];
        // $res = [];
        foreach ($type as $t) {
            switch ($t) {
                case "Song":
                    $data = $this->songSearch($filterName)->map(function ($value) {
                        // $value->type = "Song";
                        $value->media =  ($value->media == null) ? null : MediaOperation::produceMedia($value);
                        return $value;
                    });
                    $results = [];
                    foreach ($data as $d) {
                        $v = [
                            "type" => "Song",
                            "value" => $d
                        ];
                        array_push($results, $v);
                    }
                    break;
                case "Singer":
                    $data = $this->singerSearch($filterName)->map(function ($value) {
                        // $value->type = "Singer";
                        $value->media =  ($value->media == null) ? null : MediaOperation::produceMedia($value);
                        return $value;
                    });
                    $results = [];
                    foreach ($data as $d) {
                        $v = [
                            "type" => "Singer",
                            "value" => $d
                        ];
                        array_push($results, $v);
                    }
                    break;
                case "Album":
                    $data = $this->albumSearch($filterName)->map(function ($value) {
                        // $value->type = "Album";
                        $value->front_cover =  ($value->front_cover == null) ? null : MediaOperation::produceFront($value);
                        return $value;
                    });
                    $results = [];
                    foreach ($data as $d) {
                        $v = [
                            "type" => "Album",
                            "value" => $d
                        ];
                        array_push($results, $v);
                    }
                    break;
                    // case "Producer":
                    //     $results = $this->producerSearch($filterName);
                    //     break;
                default:
                    // $results = $this->allSearch($filterName);
                    $results = $this->producerSearch($filterName);
                    break;
            }

            array_push($arr1, ...$results);
            // $r = new \Random\Randomizer();
            // // Shuffle array:
            // $shuffle = $r->shuffleArray($arr1);

        }

        // Shuffle the array
        shuffle($arr1);

        // Map the array to a new array of objects
        $randomObjects = array_map(function ($object) {
            return (object) $object;
        }, $arr1);

        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Raw lists.',
            'data' => $randomObjects,
        ]);

        // for ($i = 0; $i < count($arr1); $i++) {
        //     for ($j = 0; $j < count($arr1[$i]); $j++) {
        //         array_push($res, $arr1[$i][$j]);
        //     }
        // }
        // return $res;
    }

    public function singerSearch($filterName)
    {
        $singers = Singer::where('name', 'LIKE', $filterName . '%')
            // ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->limit(5)
            ->get(['name', 'singer_key', 'media'])
            ->sortBy(function ($string) {
                return strlen($string);
            });


        return $singers;
    }

    public function songSearch($filterName)
    {
        $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'band', 'recording', 'media', 'mr_file', 'contracts', 'start', 'end'];
        $songs = Song::where('title', 'LIKE', $filterName . '%')
            // ->orWhere('title', 'LIKE', '%' . $filterName . '%')
            ->limit(5)
            ->get($projection)
            ->sortBy(function ($string) {
                return strlen($string);
            });

        return $songs;
    }

    public function albumSearch($filterName)
    {
        $albums = Album::where('name', 'LIKE', $filterName . '%')
            // ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->limit(5)
            ->get(['name', 'album_key', 'front_cover', 'release_date'])
            ->sortBy(function ($string) {
                return strlen($string);
            });


        return $albums;
    }

    public function producerSearch($filterName)
    {
        $producers = Producer::where('name', 'LIKE', $filterName . '%')
            ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->limit(5)
            ->get(['name', 'producer_key', 'media'])
            ->sortBy(function ($string) {
                return strlen($string);
            });

        return $producers;
    }

    public function index()
    {

        $list_id =  Request()->get('list_id');

        $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'band', 'recording', 'media', 'mr_file', 'contracts', 'start', 'end'];

        $songs = Song::where("status", 1)
            ->whereNotNull("contracts")
            ->where("music_lists.id", $list_id)
            ->limit(100)
            ->get($projection)
            ->map(function ($value) {
                $value->media = ($value->media == null) ? null : MediaOperation::produceMedia($value);
                return $value;
            });

        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Song lists.',
            'data' => $songs
        ]);
        //END
    }

    public function nextsong()
    {
        $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'band', 'recording', 'media', 'mr_file', 'contracts', 'start', 'end'];
        $song_key =  Request()->get('song_key');
        $song = Song::where('song_key', $song_key)
            ->first($projection);

        $conbineArr =  array_map(function ($value) {
            return $value;
        }, $song->singers);

        $album =  (array)$song->album;
        $artists =  (array)$song->artists;
        $music_lists =  (array)$song->music_lists == null ?
            (array)[
                [
                    "name" => "music1",
                    "music_key" => "mylist1"
                ],
                [
                    "name" => "music2",
                    "music_key" => "mylist2"
                ],
                [
                    "name" => "music3",
                    "music_key" => "mylist3"
                ],
            ] :
            (array)$song->music_lists;

        array_push($conbineArr, $album, ...$artists, ...$music_lists);

        // $r = new \Random\Randomizer();
        // // Shuffle array:
        // $shuffle = $r->shuffleArray($conbineArr);
        // $result = reset($shuffle);

        $rand_keys = array_rand($conbineArr, 2);
        $result =  $conbineArr[$rand_keys[0]];

        $count = $song::count();

        if (array_key_exists("album_key", $result)) {
            $song = Song::where("album.album_key", $result['album_key'])
                ->skip(rand(0, 10))
                ->first();
        } elseif (array_key_exists("singer_key", $result)) {
            $song = Song::where("singers.singer_key", $result['singer_key'])
                ->skip(rand(0, 10))
                ->first();
        } elseif (array_key_exists("artist_key", $result)) {
            $song = Song::where("artists.artist_key", $result['artist_key'])
                ->skip(rand(0, 10))
                ->first();
        } else {
            $song = Song::skip(rand(0, $count - 1))
                ->take(1)
                ->first();
        }

        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Next Song.',
            'data' => $song
        ]);
        //END
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Song  $song
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $song = Song::where('song_key', $id)->first();
        $song->attachments = $song->attachments == null ? null : MediaOperation::produceContract($song->attachments);


        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Song Detail.',
            'data' => $song,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Song  $song
     * @return \Illuminate\Http\Response
     */
    public function edit(Song $song)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Song  $song
     * @return \Illuminate\Http\Response
     */
    // public function update(SongRequest $request, $id)
    // {
    //     $validated = $request->validated();
    //     $song = Song::where('song_key', $id)->first();

    //     if (isset($validated['media'])) {
    //         $operation = new SongOperation($song->song_key, null, $validated['media']);
    //         $media_file = $operation->StoreSongImage();

    //         StoreSongData::dispatchAfterResponse($song->song_key, $media_file);
    //     } else {
    //         $validated['media'] = $song->media;
    //     }

    //     if ($song->update($validated)) {
    //         return new SongResource($song);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Song  $song
     * @return \Illuminate\Http\Response
     */
    public function destroy(Song $song)
    {
        //
    }
}
