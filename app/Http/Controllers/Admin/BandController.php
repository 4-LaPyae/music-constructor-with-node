<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\BandOperation;
use App\Fileoperations\MediaOperation;
use App\Models\Band;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BandRequest;
use App\Http\Resources\Admin\BandResource;
use App\Jobs\StoreBandMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class BandController extends Controller

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
        $bandName = Request()->has('band_name') ? Request()->get('band_name') : '';

        $bands = Band::where('delete_status', 1)
            ->where(function ($query) use ($bandName) {
                if (isset($bandName)) {
                    $query->where('name', 'LIKE', '%' . $bandName . '%');
                }
                return $query;
            });

        $projection = ['name', 'band_key', 'media'];

        return Response()->json([
            'data' => $bands->orderBy('id', 'desc')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get($projection)
                ->map(function ($value) {
                    $value->media =  ($value->media == null) ? null : MediaOperation::produceMedia($value);
                    return $value;
                }),
            'message' => 'Band list.',
            'total' => $bands->count(),
            'page' => (int)$page,
            'rowPerPages' => (int)$limit,
        ]);
    }

    // public function share()
    // {
    //     $url = URL::temporarySignedRoute('/brands', now()->addDay(30), [
    //         'id' => "123"
    //     ]);
    //     return $url;
    // }



    //      //Cache::put('key', "i am key ", $seconds = 10);

    //     // if (Cache::has('userdata')) {
    //     //     return Response()->json([
    //     //         'bands' => Cache::get('userdata'),
    //     //     ]);
    //     // }

    //     // return Cache::remember('userdata', 30, function () {
    //     //     return Band::with('medias')->get();
    //     // });


    //     // return Response()->json([
    //     //     'bands' => $value,
    //     // ]);

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
    public function store(BandRequest $request)
    {

        $validated = $request->validated();

        if (isset($validated['image'])) {
            //to avoid data too long error for image
            $image = $validated['image'];
        }
        $validated['band_key'] = generateUUID(21);

        $band = Band::create($validated);

        // check image
        if (isset($image)) {
            $operation = new BandOperation($band->id, null, $image);
            $media_file = $operation->StoreBandImage();

            StoreBandMedia::dispatchAfterResponse($band->id, $media_file);
        }

        return new BandResource($band);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Band  $band
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $band = Band::find($id);
        $band = Band::where('band_key', $id)->first(['name', 'band_key', 'media', 'establish_date']);
        $band->media = ($band->media == null) ? null : MediaOperation::produceMedia($band);

        return Response()->json([
            'band' => $band,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Band  $band
     * @return \Illuminate\Http\Response
     */
    public function edit(Band $band)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Band  $band
     * @return \Illuminate\Http\Response
     */
    public function update(BandRequest $request, $id)
    {
        $validated = $request->validated();

        //id = artist_key
        $band = Band::where('band_key', $id)->first();

        if (isset($validated['image'])) {
            //to avoid data too long error for image
            $image = $validated['image'];
            $validated['image'] = null;
        }

        // check image
        if (isset($image)) {
            $operation = new BandOperation($band->id, null, $image);
            $media_file = $operation->StoreBandImage();

            StoreBandMedia::dispatchAfterResponse($band->id, $media_file);
        } else {
            $validated['media_id'] = $band->media_id;
        }

        if ($band->update($validated)) {
            return new BandResource($band);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Band  $band
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $band = Band::find($id);
        $band->delete_status = 0;
        if ($band->save()) {
            return new BandResource($band);
        }
    }
}
