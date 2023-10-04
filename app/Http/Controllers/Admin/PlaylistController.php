<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\MediaOperation;
use App\Http\Controllers\Controller;
use App\Jobs\Mobile\MakeUserPlaylist;
use App\Models\Enduser;
use App\Models\Playlist;
use App\Models\Song;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $user_id = Request()->has('user_id') ? Request()->get('user_id') : NULL;

        $playlist = Playlist::when($user_id, function ($query) use ($user_id) {
            $query->where("user_id", $user_id);
        })->get();

        return Response()->json([
            "error" => false,
            'authorize' => true,
            'message' => 'Play lists.',
            'data' => $playlist,

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            "user_id" => 'required',
            'name' => "required",
        ]);
        $validated['songs'] = [];
        // $name = str_replace(' ', '', (strtolower($validated['name'])));

        $playlist = Playlist::where([
            ["user_id", $validated['user_id']],
            ['name', $validated['name']],
        ])->first(['name']);

        if ($playlist && $playlist->name) {
            return Response()->json([
                'error' => true,
                "message" => "Your name is similar in your playlists"
            ]);
        }

        $playlist =  Playlist::create($validated);

        return Response()->json([
            'playlist' => $playlist
        ]);
    }

    public function addSongPlaylist(Request $request)
    {

        $validated = $request->validate([
            "user_id" => 'required',
            "playlist_id" => "required",
            "song_key" => 'nullable',
        ]);

        $songArr =  array_map(function ($value) {
            return  Song::where('song_key', $value)
                ->first()
                ->toArray();
        }, $validated['song_key']);

        return $songArr;




        if ($song) {
            Playlist::where([
                ["_id", $validated['playlist_id']],
                ["user_id", $validated['user_id']]
            ])->push("songs", $song, true);
        }

        $playlist = Playlist::where('_id', $validated['playlist_id'])->first();

        return Response()->json([
            'playlist' => $playlist
        ]);
    }

    public function deleteSongPlaylist(Request $request)
    {
        $validated = $request->validate([
            "user_id" => 'required',
            "playlist_id" => "required",
            "song_key" => 'required',
        ]);
        $song = Song::where('song_key', $validated['song_key'])->first(["title", "song_key", "mr", "mr_file", "singers", "media"])->toArray();

        if ($song) {
            Playlist::where([
                ["_id", $validated['playlist_id']],
                ["user_id", $validated['user_id']]
            ])->pull("songs", $song, true);
        }

        $playlist = Playlist::where('_id', $validated['playlist_id'])->first();

        return Response()->json([
            'playlist' => $playlist
        ]);
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //find with album key 
        $playlist = Playlist::where('_id', $id)->first();

        return Response()->json([
            'data' => $playlist,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function edit(Playlist $playlist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => "required",
        ]);
        $validated['songs'] = null;

        $playlist = Playlist::where('_id', $id)->first();

        if ($playlist->update($validated)) {
            return Response()->json([
                'playlist' => $playlist
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function destroy(Playlist $playlist)
    {
        //
    }
}
