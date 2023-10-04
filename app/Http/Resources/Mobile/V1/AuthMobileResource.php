<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthMobileResource extends JsonResource
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
            'error' => false,
            'authorize' => true,
            'message' => 'Successfully',
            'data' => parent::toArray($request),
        ];
    }
}