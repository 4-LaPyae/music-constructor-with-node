<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Fileoperations\MediaOperation;
use App\Fileoperations\ProducerOperation;
use App\Models\Producer;
use App\Models\Song;
use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\ProducerResource;
use App\Http\Requests\Admin\ProducerRequest;
use App\Jobs\StoreAttachment;
use App\Jobs\StoreProducerMedia;
use App\Models\Contract;
use MongoDB\BSON\ObjectId;


class ProducerController extends Controller
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
        $producerName = Request()->has('producer_name') ? Request()->get('producer_name') : '';

        $producers = Producer::where('status', 1)
            ->where(function ($query) use ($producerName) {
                if (isset($producerName)) {
                    $query->where('name', 'LIKE', '%' . $producerName . '%');
                }
                return $query;
            });

        $projection  = ['name', 'producer_key', 'media'];

        return Response()->json([
            'data' => $producers->orderBy('created_at', '-1')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get($projection)
                ->map(function ($value) {
                    $value->media = ($value->media == null) ? null : MediaOperation::produceMedia($value);
                    return $value;
                }),
            'message' => 'Producer list.',
            'total' => $producers->count(),
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
    public function store(ProducerRequest $request)
    {
        $validated = $request->validated();
        $validated['producer_key'] = generateUUID(21);
        $validated['status'] = 1;

        $producer = Producer::create($validated);

        // check image
        if (isset($validated['image'])) {
            $operation = new ProducerOperation($producer->producer_key, null, $validated['image']);
            $media_file = $operation->StoreProducerImage();

            StoreProducerMedia::dispatchAfterResponse($producer->producer_key, $media_file);
        }

        return new ProducerResource($producer);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Producer  $producer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $producer = Producer::find($id);
        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $songName = Request()->has('song_name') ? Request()->get('song_name') : '';


        $producer = Producer::where('producer_key', $id)->first(['name', 'producer_key', 'media']);
        $producer->media = ($producer->media == null) ? null : MediaOperation::produceMedia($producer);

        // find song with producer
        $songs = Song::where("status", 1)
            ->where("contract_status", 1)
            ->where('producer.producer_key', $id)
            ->where(function ($query) use ($songName) {
                if (isset($songName)) {
                    $query->where('title', 'LIKE', '%' . $songName . '%');
                }
                return $query;
            });

        $projection = ['song_key', 'title', 'singers', 'album', 'artists', 'producer', 'mr_file', 'contracts', 'band', 'recording', 'music_lists', 'attachments', 'amount'];

        return Response()->json([
            'producer' => $producer,
            'songs' => [
                'data' => $songs->limit($limit)
                    ->offset(($page - 1) * $limit)
                    ->orderBy('updated_at', -1)
                    ->get($projection)
                    ->map(function ($value) {
                        $value->attachments = ($value->attachments == null) ? null : MediaOperation::produceContract($value->attachments);
                        return $value;
                    }),
                'total' => $songs->count(),
                'page' => (int)$page,
                'rowPerPages' => (int)$limit,
            ]
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Producer  $producer
     * @return \Illuminate\Http\Response
     */
    public function edit(Producer $producer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producer  $producer
     * @return \Illuminate\Http\Response
     */
    public function update(ProducerRequest $request, $id)
    {
        $validated = $request->validated();
        // $validated['image'] = $validated['image'] ?? null;

        $producer = Producer::where('producer_key', $id)->first(['name', 'producer_key', 'media']);

        if (isset($validated['image'])) {
            $operation = new ProducerOperation($producer->producer_key, null, $validated['image']);
            $media_file = $operation->StoreProducerImage();

            StoreProducerMedia::dispatchAfterResponse($producer->producer_key, $media_file);
        } else {
            $validated['media'] = $producer->media;
        }

        //update name in related producer in song
        Song::where('producer.producer_key', $id)
            ->update([
                'producer' => [
                    'name' => $validated['name'],
                    'producer_key' => $producer->producer_key,
                ]
            ]);

        if ($producer->update($validated)) {
            return new ProducerResource($producer);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Producer  $producer
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        $producer = Producer::where("producer_key", $id)->first();
        $producer->status = 0;
        if ($producer->save()) {
            return new ProducerResource($producer);
        }
    }

    public function addContractSongs(Request $request)
    {

        $validated = $request->validate([
            'producer_key' => 'required',
            'song_id' => 'nullable',
            'images' => 'nullable',
            'contracts' => 'nullable',
            'amount' => 'nullable'
        ]);

        $songKeys = $validated['song_id'] ?? NULL;
        $images = $validated['images'] ?? NULL;
        $producer_key = $validated['producer_key'] ?? NULL;
        $contracts = $validated['contracts'] ?? NULL;

        $type = "add";
        $producer = Producer::where('producer_key', $producer_key)->first(['producer_key', 'name']);

        if ($producer_key) {

            Song::whereIn('song_key', $songKeys)->update([
                'producer' => ["name" => $producer->name, "producer_key" => $producer_key],
                'amount' => $validated['amount'],
                'contracts' => $contracts
            ]);

            StoreAttachment::dispatchAfterResponse($songKeys, $images, $producer_key, $type);
        }


        return Response()->json([
            'error' => false,
            'message' => 'Producer contracted songs successfully.',
        ]);
    }

    public function updateContractSongs(Request $request)
    {
        $validated = $request->validate([
            'producer_key' => 'required',
            'song_id' => 'nullable',
            'images' => 'nullable',
            'contracts' => 'nullable',
            'attachments' => 'nullable',
            'amount' => 'nullable'
        ]);

        $songKeys = $validated['song_id'] ?? NULL;
        $images = $validated['images'] ?? NULL;
        $producer_key = $validated['producer_key'] ?? NULL;
        $contracts = $validated['contracts'] ?? NULL;
        $attachments = $validated['attachments'] ?? NULL;
        $type = "update";

        $olds = array_map(function ($element) {
            $element = [
                "id" => new ObjectId($element["id"]),
                "media_link" => $element['media_link'],
            ];
            return $element;
        }, $attachments);

        $producer = Producer::where('producer_key', $producer_key)->first(['producer_key', 'name']);

        if ($producer_key) {

            Song::where('song_key', $songKeys)
                ->update([
                    'producer' => ["name" => $producer->name, "producer_key" => $producer_key],
                    'amount' => $validated['amount'],
                    'contracts' => [
                        'audio' => $contracts['audio'],
                        'audioKaraoke' => $contracts['audioKaraoke'],
                        'video' => $contracts['video'],
                        'videoKaraoke' => $contracts['videoKaraoke'],
                    ],
                    'attachments' => $olds
                ]);

            if ($images) {
                StoreAttachment::dispatchAfterResponse($songKeys, $images, $producer_key, $type);
            }
        }


        return Response()->json([
            'error' => false,
            'message' => 'songs updated contract successfully.',
        ]);
    }

    public function deleteContractSong(Request $request)
    {

        $song_key = $request['song_key'];
        $producer_key = $request['producer_key'];
        $contracts = $request['contracts'];
        $attachments = $request['attachments'];

        $producer = Producer::where("producer_key", $producer_key)->first();

        if ($producer) {
            Song::where('song_key', $song_key)->update([
                'contract_status' => 0,
                'producer' => NULL
            ]);

            $contract =  Contract::create([
                "song_key" => $song_key,
                "producer_key" => $producer_key,
                "contracts" => $contracts,
                "attachments" => $attachments,
            ]);

            return Response()->json([
                'error' => false,
                'message' => 'Successfully delete contract song!',
            ]);
        }
    }
}
