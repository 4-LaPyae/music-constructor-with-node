<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class DistributorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
           "id"=>$this->id,
           "name"=>$this->name,
           "distributor_key"=>$this->distributor_key,
           //"songs"=>SongResource::collection($this->songs) 
        ];
    }
}
