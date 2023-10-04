<?php

namespace App\Models;

use App\Traits\FillableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory,FillableTraits;
    protected $with = ['medias','songs'];
    public function medias(){
        return $this->belongsTo(Media::class,'media_id','id');
    }

    public function songs(){
        return $this->hasMany(Song::class);
    }
}
