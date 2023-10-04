<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\ArtistOperation;
use App\Fileoperations\MediaOperation;
use App\Models\Artist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArtistRequest;
use App\Http\Resources\Admin\ArtistResource;
use App\Jobs\StoreArtistMedia;
use App\Models\Song;
use Illuminate\Support\Facades\DB;

class ArtistController extends Controller
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
        $artistName = Request()->has('artist_name') ? Request()->get('artist_name') : '';

        $artists = Artist::where('status', 1)
            ->where(function ($query) use ($artistName) {
                if (isset($artistName)) {
                    $query->where('name', 'LIKE', '%' . $artistName . '%');
                }
                return $query;
            });

        return Response()->json([
            'data' => $artists
                ->orderBy('created_at', -1)
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get()
                ->map(function ($value) {
                    $value->media =  ($value->media == null) ? null : MediaOperation::produceMedia($value);
                    return $value;
                }),
            'message' => 'Artist list.',
            'total' => $artists->count(),
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
    public function store(ArtistRequest $request)
    {

        $validated = $request->validated();

        $validated['artist_key'] = generateUUID(21);
        $validated['status'] = (int)1;
        $validated['sex'] = (int)1;
        $validated['image'] = $validated['image'] ?? null;

        $artist = Artist::create($validated);

        // check image
        if (isset($validated['image'])) {
            $operation = new ArtistOperation($artist->artist_key, null, $validated['image']);
            $media_file = $operation->StoreArtistImage();

            StoreArtistMedia::dispatchAfterResponse($artist->artist_key, $media_file);
        }

        return new ArtistResource($artist);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Artist  $artist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //id = artist_key
        $artist = Artist::where('artist_key', $id)->first(['artist_key', 'name', 'media']);
        $artist->media = ($artist->media == null)  ? null : MediaOperation::produceMedia($artist);

        if (!$artist) {
            return Response()->json([
                'error' => true,
                'message' => "Nothing Artist",
            ]);
        }

        // find song with artist
        $songs = Song::where("status", 1)
            ->where('artists.artist_key', $id)
            ->get(['song_key', 'title', 'singers', 'album', 'contracts', 'mr', 'mr_file']);

        return Response()->json([
            'artist' => $artist,
            'songs' => $songs,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Artist  $artist
     * @return \Illuminate\Http\Response
     */
    public function edit(Artist $artist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Artist  $artist
     * @return \Illuminate\Http\Response
     */
    public function update(ArtistRequest $request, $id)
    {
        $validated = $request->validated();

        //id = artist_key
        $artist = Artist::where('artist_key', $id)->first();

        // check image
        if (isset($validated['image'])) {
            $operation = new ArtistOperation($artist->artist_key, null, $validated['image']);
            $media_file = $operation->StoreArtistImage();

            StoreArtistMedia::dispatchAfterResponse($artist->artist_key, $media_file);
        } else {
            $validated['media'] = $artist->media;
        }

        //update name in related artist in song
        $addArtist = Song::where('artists.artist_key', $id)->push('artists', [
            'name' => $validated['name'],
            "artist_key" => $id
        ], true);

        $removeArtist = Song::where('artists.artist_key', $id)->pull('artists', [
            'name' => $artist->name,
            "artist_key" => $id
        ], true);

        $artistUpdate = $artist->update($validated);
        if ($artistUpdate) {
            return new ArtistResource($artist);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Artist  $artist
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $artist = Artist::where('artist_key', $id)->update([
        //     'delete_status' => 0
        // ]);

        $artist = Artist::find($id);
        $artist->status = 0;
        if ($artist->save()) {
            return new ArtistResource($artist);
        }
    }
}
