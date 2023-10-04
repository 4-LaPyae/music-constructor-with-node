<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\DistributorOperation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DistributorRequest;
use App\Http\Resources\Admin\DistributorResource;
use App\Http\Resources\Admin\SongResource;
use App\Jobs\StoreDistributorMedia;
use App\Models\Distributor;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $distributor_name = request()->has('name') ? request()->get('name') : null;
        $distributor =Distributor::when(function($query) use ($distributor_name){
                            if(isset($distributor_name)){
                                $query->where('distributors.name','LIKE','%'.$distributor_name.'%');
                            }
                            return $query;
                        })
                        ->where('delete_status',1)
                        ->orderBy('id','desc')
                        ->get();
        return response()->json([
                            "error"=>false,
                            "message"=>"Disributor Lists",
                            'count'=>$distributor->count(),
                            "data"=>DistributorResource::collection($distributor)
                        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DistributorRequest $request)
    {
      $validator = $request->validated();
      $checkimage = $validator['image'];
      if(isset($checkimage)){
        $image = $checkimage;
      }
      
    $validator['distributor_key'] = generateUUID(21);
    $distributor =  Distributor::create($validator);
    if(isset($image)){
       $opeartion = new DistributorOperation($distributor->id,null,$image);
       $media_file = $opeartion->storeDistributorImage();
       StoreDistributorMedia::dispatchAfterResponse($distributor->id,$media_file);
    }
    return response()->json([
        "error"=>false,
        "message"=>"Disributor Inserted",
        "data"=>DistributorResource::collection($distributor)
    ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Distributor  $distributor
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $name = request()->has('name') ? request()->get('name') : null;
        $distributor = Distributor::where('distributor_key',$id)->first();
        $songs = Song::where('distributor_id',$distributor->id)
                   ->when(function ($query) use ($name){
                    if($name){
                        $query->where('title','LIKE','%' .$name . '%');
                    }
                    return $query;
                   })
                    ->get();
        return response()->json([
            "distributor"=>$distributor->name,
            "total"=>$songs->count(),
            "songs"=>SongResource::collection($songs),            
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Distributor  $distributor
     * @return \Illuminate\Http\Response
     */
    public function edit(Distributor $distributor)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Distributor  $distributor
     * @return \Illuminate\Http\Response
     */
    public function update(DistributorRequest $request, $id)
    {
        $distributor = Distributor::where('distributor_key',$id)
        ->first();
        $validator = $request->validated();
        $checkimage = $validator['image'];
        if (!is_null($checkimage)) {
            $operation = new DistributorOperation($distributor->id, null, $checkimage);
            $media_file = $operation->storeDistributorImage();

            StoreDistributorMedia::dispatchAfterResponse($distributor->id, $media_file);
        } else {
            $validator['media_id'] = $distributor->media_id;
        }

        if ($distributor->update($validator)) {
            return response()->json([
                "error"=>false,
                "message"=>"Disributor Updated",
                "data"=>DistributorResource::collection($distributor)
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Distributor  $distributor
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $distributor = Distributor::where('distributor_key',$id)
                        ->first();
      $distributor->delete_status = 0;
      $distributor->save();
      return response()->json([
        "error"=>false,
        "message"=>"Distributor Deleted successfully",
        "data"=>new DistributorResource($distributor)
    ]);
    }
}
