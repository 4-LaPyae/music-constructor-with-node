<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Traits\FillableTraits;

class Album extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'albums';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'name',
        'album_key',
        'hits',
        'front_cover',
        'back_cover',
        'poster',
        'release_date',
        'status',
        'producer'
    ];
}
