<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\MediaOperation;
use App\Models\Song;
use App\Models\Album;
use App\Models\Media;
use App\Models\Producer;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Fileoperations\RecordingOperation;
use App\Http\Requests\Admin\RecordingRequest;
use App\Http\Resources\Admin\RecordingResource;
use App\Jobs\StoreRecordingData;

class RecordingController extends Controller
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
        $recordingName = Request()->has('recording_name') ? Request()->get('recording_name') : '';

        $recordings = Recording::where('delete_status', 1)
            ->where(function ($query) use ($recordingName) {
                if (isset($recordingName)) {
                    $query->where('name', 'LIKE', $recordingName . '%')
                        ->orWhere('name', 'LIKE', '%' . $recordingName . '%');;
                }
                return $query;
            });
        $projection  = ['name', 'recording_key', 'media'];
        // return $recordings;
        return Response()->json([
            'data' => $recordings->orderBy('created_at', '-1')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get($projection)
                ->map(function ($value) {
                    $value->media = ($value->media == null) ? null : MediaOperation::produceMedia($value);
                    return $value;
                }),

            'message' => 'Recording list.',
            'total' => $recordings->count(),
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

    public function store(RecordingRequest $request)
    {
        $validated = $request->validated();

        $validated['recording_key'] = generateUUID(21);
        $validated['delete_status'] = 1;

        $recording = Recording::create($validated);

        // check image
        if (isset($validated['image'])) {
            $operation = new RecordingOperation($recording->recording_key, null, $validated['image']);
            $media_file = $operation->StoreRecordingImage();

            StoreRecordingData::dispatchAfterResponse($recording->recording_key, $media_file);
        }

        return new RecordingResource($recording);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $songName = Request()->has('song_name') ? Request()->get('song_name') : '';
        // return $songName;

        $recording = Recording::where('recording_key', $id)->first(['name', 'recording_key', 'media']); //End Recording
        $recording->media = ($recording->media ==  null) ? null : MediaOperation::produceMedia($recording);

        return $recording;

        $songs = Song::where('delete_status', 1)
            ->where('recording_key', '=', $id)
            ->where(function ($query) use ($songName) {
                if (isset($songName)) {
                    $query->where('songs.title', 'LIKE', $songName . '%')
                        ->where('songs.title', 'LIKE', '%' . $songName . '%');;
                }
                return $query;
            });

        return $songs;
        //End songs

        return Response()->json([
            'recording' => $recording,
            'songs' => [
                'data' => $songs->limit($limit)->offset(($page - 1) * $limit)->get(),
                'total' => $songs->count(),
                'page' => (int)$page,
                'rowPerPages' => (int)$limit,
            ]

        ]);
    } //End Detail

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Http\Response
     */
    public function edit(Recording $recording)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Http\Response
     */

    public function update(RecordingRequest $request, $id)
    {

        $validated = $request->validated();

        $recording = Recording::where('recording_key', $id)->first();

        if (isset($validated['image'])) {
            // $recording->unset('media');
            $operation = new RecordingOperation($recording->recording_key, null, $validated['image']);
            $media_file = $operation->StoreRecordingImage();

            StoreRecordingData::dispatchAfterResponse($recording->recording_key, $media_file);
        }

        if ($recording->update($validated)) {
            return new RecordingResource($recording);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recording  $recording
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $recording = Recording::where('recording_key', $id)->first();
        $recording->delete_status = 0;
        if ($recording->save()) {

            return new RecordingResource($recording);
        }
    }
}
