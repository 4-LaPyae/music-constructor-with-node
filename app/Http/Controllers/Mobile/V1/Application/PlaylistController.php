<?php

namespace App\Http\Controllers\Mobile\V1\Application;

use App\Fileoperations\MediaOperation;
use App\Fileoperations\PlaylistOperation;
use App\Http\Controllers\Controller;
use App\Jobs\Mobile\MakeUserPlaylist;
use App\Jobs\Mobile\MakeUserPlaylistProfile;
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
        })->get(['name', 'media']);

        return Response()->json([
            'error' => false,
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


        // $validated = $request->validated([
        //     // "user_id" => 'required',
        //     'name' => "required",
        //     "image" => 'image|nullable',
        //     "songs" => 'required',
        // ]);

        //$validated['songs'] = $validated['songs'] == null ? [] :  $validated['songs'];
        // $name = str_replace(' ', '', (strtolower($validated['name'])));

        $exitPlaylist = Playlist::where([
            ["user_id", Request()->user_id],
            ['name', $request->name],
        ])->first(['name']);

        if ($exitPlaylist && $exitPlaylist->name) {
            return Response()->json([
                'error' => true,
                "message" => "Your name is similar in your playlists"
            ]);
        }
        if ($request->playlist_id) {
            //song add exiting playlist
            $playlist =  Playlist::where([
                ['_id', $request->playlist_id],
                ['user_id', Request()->user_id]
            ])->update([
                'name' => $request->name,
                'media' => null,
                'description' => $request->description ?? null,
                'songs' => $request->songs
            ]);
            $type = "updated";
        } else {
            $playlist =  Playlist::create([
                'user_id' => Request()->user_id,
                'name' => $request->name,
                'media' => null,
                'description' => null,
                'songs' => $request->songs
            ]);
            $type = "added";
        }

        if (isset($request->image)) {
            $operation = new PlaylistOperation($playlist->_id,  $request->image, null);
            $media_file = $operation->StorePlaylistBase64Image();

            MakeUserPlaylistProfile::dispatchAfterResponse($playlist->_id, $media_file);
        }

        return Response()->json([
            'error' => false,
            'authorize' => true,
            "message" => "Playlist " . $type . " successfully",
            'data' => $playlist
        ]);
    }

    public function addSongsPlaylist(Request $request)
    {

        // $validated = $request->validate([
        //     // "user_id" => 'required',
        //     "playlist_id" => "required",
        //     "songs" => 'nullable',
        // ]);

        // $songArr =  array_map(function ($value) {
        //     return  Song::where('song_key', $value)
        //         ->first()
        //         ->toArray();
        // }, $validated['song_key']);

        if ($request->songs) {
            Playlist::where([
                ["_id", $request->playlist_id],
                ["user_id", Request()->user_id]
            ])->push("songs", $request->songs, true);
        }

        $playlist = Playlist::where('_id', $request->playlist_id)->first();

        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Add Songs Playlists.',
            'data' => $playlist,
        ]);
    }

    public function deleteSongsPlaylist(Request $request)
    {

        // $validated = $request->validate([
        //     "user_id" => 'required',
        //     "playlist_id" => "required",
        //     "song_key" => 'required',
        // ]);
        // $song = Song::where('song_key', $validated['song_key'])->first(["title", "song_key", "mr", "mr_file", "singers", "media"])->toArray();

        if ($request->songs) {
            Playlist::where([
                ["_id", $request->playlist_id],
                ["user_id", Request()->user_id]
            ])->pull("songs", $request->songs, true);
        }

        $playlist = Playlist::where('_id', $request->playlist_id)->first();

        return Response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'Remove Songs Playlists.',
            'data' => $playlist,
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
            'error' => false,
            'authorize' => true,
            'message' => 'Playlists Detail',
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
        // $validated = $request->validate([
        //     'name' => "required",
        // ]);

        // $validated['songs'] = null;

        $playlist = Playlist::where('_id', $id)->first();

        if (isset($request->image)) {
            $operation = new PlaylistOperation($playlist->_id,  $request->image, null);
            $media_file = $operation->StorePlaylistBase64Image();

            MakeUserPlaylistProfile::dispatchAfterResponse($playlist->_id, $media_file);
        } else {
            $playlist->media = $playlist->media;
        }
        $playlist->name = $request->name;
        $playlist->songs = $request->songs;

        if ($playlist->update()) {
            return Response()->json([
                'error' => false,
                'authorize' => true,
                "message" => "Playlist updated successfully",
                'data' => $playlist
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $playlist = Playlist::where('_id', $id)->first();
        if ($playlist->delete()) {
            return Response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'Playlists deleted successfully',
                'data' => $playlist,
            ]);
        }
    }
}
