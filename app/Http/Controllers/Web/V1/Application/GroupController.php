<?php

namespace App\Http\Controllers\Web\V1\Application;

use App\Fileoperations\MediaOperation;
use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{

    public function index()
    {

        $gplist = Group::where("status", 1)
            ->orderBy('order_id', "desc")
            ->get()
            ->map(function ($value) {
                $value->group_image =  ($value->group_image == null) ? null : MediaOperation::produceGroupMedia($value);
                return $value;
            });


        return Response()->json([
            'data' => $gplist,
            'message' => 'Group lists.',
        ]);
    }

    // public function store(Request $request)
    // {

    //     $validated = $request->validate([
    //         "group_name" => 'required',
    //         "playlists" => 'required',
    //         "photo" => 'nullable',
    //     ]);

    //     $validated['photo'] =  $validated['photo'] ?? null;
    //     $validated['hits'] =  0;
    //     $validated['status'] =  1;

    //     $gplist = Group::create($validated);

    //     // check image
    //     // if (isset($validated['media'])) {
    //     //     $operation = new SingerOperation($singer->singer_key, null, $validated['media']);
    //     //     $media_file = $operation->StoreSingerImage();

    //     //     StoreSingerMedia::dispatchAfterResponse($singer->singer_key, $media_file);
    //     // }

    //     return Response()->json([
    //         "error" => false,
    //         "message" => "Group list",
    //         'data' => $gplist
    //     ]);
    // }

    public function show($id)
    {
        //find with album key 
        $gplist = Group::where('_id', $id)->first();

        return Response()->json([
            'list' => $gplist,
        ]);
    }
}
