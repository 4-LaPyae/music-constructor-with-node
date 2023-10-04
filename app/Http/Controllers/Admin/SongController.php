<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\MediaOperation;
use App\Fileoperations\SongOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SongRequest;
use App\Http\Resources\Admin\SongResource;
use App\Jobs\Mobile\MakeOTPEmailMobile;
use App\Jobs\Mobile\MakeOTPRequestMobile;
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
        $filterName = Request()->has('filter_name') ? Request()->get('filter_name') : '#';
        $type = Request()->has('type') ?
            explode(',', Request()->get('type')) : NULL;

        //    MakeSearchJob::dispatchAfterResponse($filterName, Request()->get("admin_id"));

        $arr1 = [];
        // $res = [];
        foreach ($type as $t) {
            switch ($t) {
                case "Song":
                    $results = $this->songSearch($filterName)->map(function ($value) {
                        $value->type = "Song";
                        return $value;
                    });
                    break;
                case "Singer":
                    $results = $this->singerSearch($filterName)->map(function ($value) {
                        $value->type = "Singer";
                        return $value;
                    });
                    break;
                case "Album":
                    $results = $this->albumSearch($filterName)->map(function ($value) {
                        $value->type = "Album";
                        return $value;
                    });
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
        }
        return $arr1;

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
        $songs = Song::where('title', 'LIKE', $filterName . '%')
            // ->orWhere('title', 'LIKE', '%' . $filterName . '%')
            ->limit(5)
            ->get(['title', 'song_key', 'singers', 'media'])
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
            ->get(['name', 'album_key', 'media', 'release_date'])
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


    public function allSearch($filterName)
    {

        $filterName = Request()->has('filter_name') ? Request()->get('filter_name') : '';

        $songs = Song::where('title', 'LIKE', $filterName . '%')
            ->orWhere('title', 'LIKE', '%' . $filterName . '%')
            ->select('id', 'title as song_name', 'song_key')
            ->orderByRaw('LENGTH(song_name) ASC')
            ->orderBy('title', 'asc')
            ->limit(5)
            ->get();

        $singers = Singer::with('media')
            ->where('name', 'LIKE', $filterName . '%')
            ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->select('id', 'name as singer_name', 'singer_key')
            ->orderByRaw('LENGTH(singer_name) ASC')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        $albums = Album::where('name', 'LIKE', $filterName . '%')
            ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->select('id', 'name as album_name', 'album_key')
            ->orderByRaw('LENGTH(album_name) ASC')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();

        $producers = Producer::select('id', 'name as producer_name', 'producer_key')
            ->where('name', 'LIKE', $filterName . '%')
            ->orWhere('name', 'LIKE', '%' . $filterName . '%')
            ->orderByRaw('LENGTH(name) ASC')
            ->orderBy('name', 'asc')
            ->limit(5)
            ->get();
        return array_merge(
            $songs->toArray(),
            $singers->toArray(),
            $albums->toArray(),
            $producers->toArray()
        );
        //  $list = $singers->merge($songs);
        // //return $list;

        // return $list->count() == 0 ? [] : $list->merge($albums, $producers);
    }

    public function index()
    {

        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $filterName = Request()->has('filter_name') ? Request()->get('filter_name') : null;

        $contract =  Request()->has('contract') ? Request()->get('contract') : null;
        $type = Request()->has('type') ? Request()->get('type') : '';

        $songs = Song::where("status", 1)
            ->where(function ($query) use ($contract) {
                if ($contract) {
                    $query->whereNotNull('contracts');
                } else {
                    $query->whereNull('contracts');
                }
            })
            ->where(function ($query) use ($filterName, $type) {
                if ($type == "Album") {
                    $query->where('album.name', 'LIKE', $filterName . '%');
                    //->orWhere('album.name', 'LIKE', '%' . $filterName . '%');
                } else if ($type == 'Singer') {
                    $query->where('singers.name', 'LIKE', $filterName . '%');
                    //->orWhere('singers.name', 'LIKE', '%' . $filterName . '%');
                } else if ($type == 'Artist') {
                    $query->where('artists.name', 'LIKE', $filterName . '%');
                    //->orWhere('singers.name', 'LIKE', '%' . $filterName . '%');

                } else if ($type == 'Producer') {
                    $query->where('producer.name', 'LIKE', $filterName . '%');
                    //->orWhere('singers.name', 'LIKE', '%' . $filterName . '%');
                } else {
                    $query->where('title', 'LIKE', $filterName . '%');
                    //->orWhere('title', 'LIKE', '%' . $filterName . '%');
                }
                return $query;
            });

        // $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'producer', 'mr_file', 'contracts', 'band', 'recording', 'music_lists', 'attachments', 'amount','start',];

        return Response()->json([
            'data' => $songs->limit($limit)
                ->offset(($page - 1) * $limit)
                ->orderBy('updated_at', -1)
                ->get()
                ->map(function ($value) {
                    $value->attachments = ($value->attachments == null) ? null : MediaOperation::produceContract($value->attachments);
                    return $value;
                }),
            'message' => 'Song lists.',
            'total' => $songs->count(),
            'page' => (int)$page,
            'rowPerPages' => (int)$limit,
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
    public function store(SongRequest $request)
    {
        $validated = $request->validated();

        $validated['song_key'] = generateUUID(21);
        $validated['titlea'] = null;
        $validated['solo_song'] = 0;
        $validated['duel_song'] = 0;
        $validated['group_song'] = 0;
        $validated['status'] = 1;
        $validated['hits'] = 0;
        $validated['contracts'] = null;
        $validated['attachments'] = null;
        $validated['contract_status'] = 1;
        $validated['mr'] = null;
        $validated['language'] = null;
        $validated['lyric'] = null;
        $validated['media'] = ($validated['media'] == null) ? null : $validated['media'];
        $validated['generes'] = null;
        $validated['amount'] = 0;
        $validated['mr_file'] = (object)[
            "track1" => null,
            "track2" =>  null,
            "lrc" =>  null,
            "zip" =>  null
        ];
        // $validated['start'] = null;
        // $validated['end'] = null;

        $song = Song::create($validated);

        // check image
        if (isset($validated['media'])) {
            $operation = new SongOperation($song->song_key, null, $validated['media']);
            $media_file = $operation->StoreSongImage();

            StoreSongData::dispatchAfterResponse($song->song_key, $media_file);
        }

        return new SongResource($song);
    }

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
            'songs' => $song
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
    public function update(SongRequest $request, $id)
    {
        $validated = $request->validated();

        $song = Song::where('song_key', $id)->first();

        if (isset($validated['media'])) {
            $operation = new SongOperation($song->song_key, null, $validated['media']);
            $media_file = $operation->StoreSongImage();

            StoreSongData::dispatchAfterResponse($song->song_key, $media_file);
        } else {
            $validated['media'] = $song->media;
        }

        if ($song->update($validated)) {
            return new SongResource($song);
        }
    }

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

    public function test()
    {
        // return MakeOTPRequestMobile::dispatchAfterResponse("09420717526", "000000");

        // return MakeOTPEmailMobile::dispatchAfterResponse("winthuaung00@gmail.com", "000000");
    }
}
