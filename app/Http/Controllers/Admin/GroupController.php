<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\GroupOperation;
use App\Fileoperations\MediaOperation;
use App\Http\Controllers\Controller;
use App\Jobs\StoreGroupImage;
use App\Models\Group;
use App\Models\Media;
use App\Models\Musiclist;
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

    public function store(Request $request)
    {

        $validated = $request->validate([
            "group_name" => 'required',
            "group_image" => 'nullable',
            "group_type" => 'nullable',
            "musiclists" => 'nullable',
        ]);

        $validated['status'] =  1;
        $validated['order_id'] =  0;

        $group = Group::create($validated);

        // check photo
        if (isset($validated['group_image'])) {
            $operation = new GroupOperation($group->_id, $validated['group_image'], null);
            $media_file = $operation->StoreGroupBase64Image();

            StoreGroupImage::dispatchAfterResponse($group->_id, $media_file);
        }

        return Response()->json([
            "error" => false,
            "message" => "Group list",
            'data' => $group
        ]);
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            "group_name" => 'required',
            "group_image" => 'nullable',
            "musiclists" => 'nullable',

        ]);

        $validated['group_image'] =  $validated['group_image'] ?? null;
        $validated['status'] =  1;

        $group = Group::where("_id", $id)->first();

        // check photo
        if (isset($validated['group_image'])) {
            $operation = new GroupOperation($group->_id, $validated['group_image'], null);
            $media_file = $operation->StoreGroupBase64Image();

            StoreGroupImage::dispatchAfterResponse($group->_id, $media_file);
        } else {
            $validated['group_image'] = $group->group_image;
        }

        if ($group->update($validated)) {
            return Response()->json([
                "error" => false,
                "message" => "Group list",
                'data' => $group
            ]);
        }
    }

    public function show($id)
    {
        $gplist = Group::where('_id', $id)->first();

        return Response()->json([
            'list' => $gplist,
        ]);
    }


    public function destroy($id)
    {
        $group = Group::where('_id', $id)->update([
            "status" => 0
        ]);

        return Response()->json([
            "error" => false,
            "message" => "Group list successfully deleted",
        ]);
    }
}
