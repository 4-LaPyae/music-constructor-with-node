<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\ArtistOperation;
use App\Fileoperations\MediaOperation;
use App\Fileoperations\SingerOperation;
use App\Models\Singer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SingerRequest;
use App\Http\Resources\Admin\SingerResource;
use App\Jobs\StoreArtistMedia;
use App\Jobs\StoreSingerMedia;
use App\Models\Song;


class SingerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $singerName = Request()->has('singer_name') ? Request()->get('singer_name') : '';

        $singers = Singer::where("status", 1)
            ->where(function ($query) use ($singerName) {
                if (isset($singerName)) {
                    $query->where('name', 'LIKE', $singerName . '%')
                        ->orWhere('name', 'LIKE', '%' . $singerName . '%');
                }
                return $query;
            })->orderBy('created_at', -1);
        $projection = ['name', 'singer_key', 'media', 'sex', 'alias'];

        return Response()->json([
            'data' => $singers
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get($projection)
                ->map(function ($value) {
                    $value->media =   ($value->media == null) ? null : MediaOperation::produceMedia($value);
                    return $value;
                }),
            'message' => 'Singer list.',
            'total' => $singers->count(),
            'page' => (int)$page,
            'rowPerPages' => (int)$limit,
        ]);
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
    public function store(SingerRequest $request)
    {

        $validated = $request->validated();

        $validated['singer_key'] = generateUUID(21);
        $validated['hits'] = (int)0;
        $validated['language'] = (int)1;
        $validated['sex'] = (int)$validated['sex'];
        $validated['status'] = (int)1;
        $validated['media'] =  $validated['media'] ?? null;

        $singer = Singer::create($validated);

        // check image
        if (isset($validated['media'])) {
            $operation = new SingerOperation($singer->singer_key, null, $validated['media']);
            $media_file = $operation->StoreSingerImage();

            StoreSingerMedia::dispatchAfterResponse($singer->singer_key, $media_file);
        }

        return new SingerResource($singer);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Singer  $singer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $songName = Request()->has('song_name') ? Request()->get('song_name') : '';

        $singer = Singer::where('singer_key', $id)->first(['name', 'singer_key', 'media']);

        //$singer = $singer->media == null ? null : MediaOperation::produceMedia($singer);

        // find song with singer
        $songs = Song::where("status", 1)
            ->where('singers.singer_key', $id)
            ->where(function ($query) use ($songName) {
                if (isset($songName)) {
                    $query->where('title', 'LIKE', '%' . $songName . '%');
                }
                return $query;
            });

            $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'producer', 'mr_file', 'contracts', 'band', 'recording', 'music_lists', 'media'];

        return Response()->json([
            'singer' => $singer,
            'songs' => [
                'data' => $songs->limit($limit)
                    ->offset(($page - 1) * $limit)
                    ->orderBy('hits', 'desc')
                    ->get($projection),
                'total' => $songs->count(),
                'page' => (int)$page,
                'rowPerPages' => (int)$limit,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Singer  $singer
     * @return \Illuminate\Http\Response
     */
    public function edit(Singer $singer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Singer  $singer
     * @return \Illuminate\Http\Response
     */
    public function update(SingerRequest $request, $id)
    {
        $validated = $request->validated();
        $singer = Singer::where('singer_key', $id)->first();

        // check image
        if (isset($validated['media'])) {
            $operation = new SingerOperation($singer->singer_key, null, $validated['media']);
            $media_file = $operation->StoreSingerImage();

            StoreSingerMedia::dispatchAfterResponse($singer->singer_key, $media_file);
        } else {
            $validated['media'] = $singer->media;
        }

        //update name in related singer in song
        $addSinger = Song::where('singers.singer_key', $id)->push('singers', [
            'name' => $validated['name'],
            "singer_key" => $id
        ], true);

        $removeSinger = Song::where('singers.singer_key', $id)->pull('singers', [
            'name' => $singer->name,
            "singer_key" => $id
        ], true);

        // return response()->json([
        //     "addSinger" => $singer->name,
        //     "removeSinger" => $removeSinger
        // ]);

        if ($singer->update($validated)) {
            return new SingerResource($singer);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Singer  $singer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $singer = Singer::where("singer_key", $id)->first();
        $singer->status = 0;
        if ($singer->save()) {
            return new SingerResource($singer);
        }
    }
}
