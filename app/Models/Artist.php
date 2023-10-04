<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Traits\FillableTraits;

class Artist extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'artists';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'name',
        'artist_key',
        'media',
        'sex',
        'status'
    ];
}
