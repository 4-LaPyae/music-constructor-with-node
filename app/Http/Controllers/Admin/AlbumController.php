<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\AlbumOperation;
use App\Fileoperations\MediaOperation;
use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AlbumRequest;
use App\Http\Resources\Admin\AlbumResource;
use App\Jobs\StoreAlbumMedia;
use App\Models\Media;
use App\Models\Singer;
use App\Models\Song;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AlbumController extends Controller
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
        $albumName = Request()->has('album_name') ? Request()->get('album_name') : '';

        $albums = Album::where('status', 1)
            ->where(function ($query) use ($albumName) {
                if (isset($albumName)) {
                    $query->where('name', 'LIKE', $albumName . '%')
                        ->orWhere('name', 'LIKE', '%' . $albumName . '%');
                }
                return $query;
            });

        return Response()->json([
            'data' => $albums
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->orderBy('updated_at', -1)
                ->get()
                ->map(function ($value) {
                    $value->front_cover =  ($value->front_cover == null) ? null : MediaOperation::produceFront($value);
                    $value->back_cover =  ($value->back_cover == null) ? null : MediaOperation::produceBack($value);
                    $value->poster =  ($value->poster == null) ? null : MediaOperation::producePoster($value);
                    return $value;
                }),
            'message' => 'Album list.',
            'total' => $albums->count(),
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
    public function store(AlbumRequest $request)
    {
        $validated = $request->validated();
        $validated['album_key'] = generateUUID(21);
        $validated['hits'] = 0;
        $validated['language'] = null;
        $validated['producer'] = null;
        $validated['front_cover'] =  $validated['front_cover'] ?? null;
        $validated['back_cover'] =  $validated['back_cover'] ?? null;
        $validated['poster'] = $validated['poster'] ?? null;
        $validated['status'] = 1;

        $album = Album::create($validated);

        // check image
        if (isset($validated['front_cover'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['front_cover']);
            $media_file = $operation->StoreAlbumImage();
            $type = "front_cover";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        }

        if (isset($validated['back_cover'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['back_cover']);
            $media_file = $operation->StoreAlbumImage();
            $type = "back_cover";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        }

        if (isset($validated['poster'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['poster']);
            $media_file = $operation->StoreAlbumImage();
            $type = "poster";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        }

        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producer_id =  Request()->has('producer_id') ? Request()->get('producer_id') : '';

        //find with album key 
        $album = Album::where('album_key', $id)->first(['name', 'album_key', 'front_cover', 'back_cover', 'poster', 'release_date']);
        $album->front_cover =  ($album->front_cover == null) ? null : MediaOperation::produceFront($album);
        $album->back_cover =  ($album->back_cover == null) ? null : MediaOperation::produceBack($album);
        $album->poster =  ($album->poster == null) ? null : MediaOperation::producePoster($album);

        if (!$album) {
            return Response()->json([
                'error' => true,
                'message' => "Nothing Album",
            ]);
        }

        // find song with album
        $songs = Song::where('album.album_key', $id)
            ->when($producer_id, function ($query) {
                $query->whereNull('producer_id');
            })
            // ->get(['song_key', 'title', 'singers', 'album']);
            ->get();


        $singers = array_merge(...$songs->pluck('singers'));

        $unique_singers = [];

        foreach ($singers as $number) {
            if (!in_array($number, $unique_singers)) {
                $unique_singers[] = $number;
            }
        }

        return Response()->json([
            'album' => $album,
            'songs' => $songs,
            'singers' => $unique_singers,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function edit(Album $album)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(AlbumRequest $request, $id)
    {
        $validated = $request->validated();
        $album = Album::where('album_key', $id)->first();

        // check image
        if (isset($validated['front_cover'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['front_cover']);
            $media_file = $operation->StoreAlbumImage();
            $type = "front_cover";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        } else {
            $validated['front_cover'] = $album->front_cover;
        }

        if (isset($validated['back_cover'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['back_cover']);
            $media_file = $operation->StoreAlbumImage();
            $type = "back_cover";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        } else {
            $validated['back_cover'] = $album->back_cover;
        }

        if (isset($validated['poster'])) {
            $operation = new AlbumOperation($album->album_key, null, $validated['poster']);
            $media_file = $operation->StoreAlbumImage();
            $type = "poster";
            StoreAlbumMedia::dispatchAfterResponse($album->album_key, $media_file, $type);
        } else {
            $validated['poster'] = $album->poster;
        }

        //update name in related album in song
        $songs = Song::where('album.album_key', $id)
            ->update([
                'album' => [
                    'name' => $validated['name'],
                    'album_key' => $album->album_key,
                ]
            ]);

        if ($album->update($validated)) {
            return new AlbumResource($album);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $album = Album::where("album_key", $id)->first();
        $album->status = 0;
        if ($album->save()) {
            return new AlbumResource($album);
        }
    }
}
