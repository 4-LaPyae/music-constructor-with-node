<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\MusiclistOperation;
use App\Http\Controllers\Controller;
use App\Jobs\StoreMusiclistImage;
use App\Models\Group;
use App\Models\Media;
use App\Models\Musiclist;
use App\Models\Song;
use Illuminate\Http\Request;

class MusiclistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Musiclist::where("status", 1)->get();

        return Response()->json([
            "error" => false,
            "message" => "Music list",
            'data' => $list
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
            'name' => "required",
            "description" => 'required',
            'photo' => "image|nullable",
        ]);
        $validated['status'] = (int)1;

        $musiclist = Musiclist::create($validated);

        // check photo
        if (isset($validated['photo'])) {
            $operation = new MusiclistOperation($musiclist->_id, null, $validated['photo']);
            $media_file = $operation->StoreMusiclistImage();

            StoreMusiclistImage::dispatchAfterResponse($musiclist->_id, $media_file);
        }

        return Response()->json([
            "error" => false,
            "message" => "Musiclist Add successfully",
            'data' => $musiclist
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Musiclist  $musiclist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $songName = Request()->has('song_name') ? Request()->get('song_name') : '';

        //id = artist_key
        $musiclist = Musiclist::where('_id', $id)->first();
        // $artist->photo = ($artist->photo == null)  ? null : MediaOperation::produceMedia($artist);

        if (!$musiclist) {
            return Response()->json([
                'error' => true,
                'message' => "Nothing Musiclist",
            ]);
        }
        $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'producer', 'mr_file', 'contracts', 'band', 'recording', 'music_lists', 'attachments', 'amount'];

        // find song with artist
        $songs = Song::where("status", 1)
            ->where('music_lists.id', $id)
            ->where(function ($query) use ($songName) {
                if (isset($songName)) {
                    $query->where('title', 'LIKE', '%' . $songName . '%');
                }
                return $query;
            })
            ->orderBy('updated_at', -1)
            ->take(100)
            ->get($projection);


        return Response()->json([
            'musiclist' => $musiclist,
            'songs' => $songs,
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Musiclist  $musiclist
     * @return \Illuminate\Http\Response
     */
    public function edit(Musiclist $musiclist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Musiclist  $musiclist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => "required",
            "description" => 'required',
            'photo' => "image|nullable",
        ]);
        $validated['status'] = (int)1;

        $musiclist = Musiclist::where("_id", $id)->first();

        // check photo
        if (isset($validated['photo'])) {
            $operation = new MusiclistOperation($musiclist->_id, null, $validated['photo']);
            $media_file = $operation->StoreMusiclistImage();

            $media = Media::create([
                "media_type_id" => "IMAGE",
                "media_link" => $media_file
            ]);

            $validated['photo'] = [
                "id" => $media->_id,
                "media_link" => $media->media_link,
            ];

            // StoreMusiclistImage::dispatchAfterResponse($musiclist->_id, $media_file, $musiclist);
        } else {
            $validated['photo'] = $musiclist->photo;
            // //update name in related group in list
        }
        //change music list change in group 
        $addList = Group::where('musiclists.id', $id)
            ->push('musiclists', [
                'id' => (string) $id,
                'name' => $validated['name'],
                "description" => $validated['description'],
                "photo" => $validated['photo'],
            ], true);

        $removeList = Group::where('musiclists.id', $id)
            ->pull('musiclists', [
                'id' => (string) $id,
                'name' => $musiclist->name,
                "description" => $musiclist->description,
                "photo" => $musiclist->photo,
            ], true);

        //change music list change in song 
        // $addSong = Song::where('music_lists.id', $id)
        //     ->push('music_lists', [
        //         'id' => (string) $id,
        //         'name' => $validated['name'],
        //     ], true);

        // $removeSong = Song::where('music_lists.id', $id)
        //     ->pull('music_lists', [
        //         'id' => (string) $id,
        //         'name' => $musiclist->name,
        //     ], true);


        if ($musiclist->update($validated)) {
            return Response()->json([
                "error" => false,
                "message" => "Musiclist Updated successfully",
                'gprm' => $removeList,
                'gpadd' => $addList
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Musiclist  $musiclist
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $musiclist = Musiclist::where("_id", $id)->update([
            "status" => 0
        ]);
        if ($musiclist) {
            return Response()->json([
                "error" => false,
                "message" => "Musiclist Deleted successfully",
            ]);
        }
    }

    public function musicListSongDelete(Request $request)
    {
        
        $songKey = $request->song_key;
        $musicList = $request->musiclist;

        $removeSong = Song::where('song_key', $songKey)->pull('music_lists', [
            $musicList
        ], true);

        if ($removeSong) {
            return Response()->json([
                "error" => false,
                "message" => "Song Deleted in musiclist successfully",
            ]);
        }
    }
}
