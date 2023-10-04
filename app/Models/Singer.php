<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use App\Traits\FillableTraits;
use Jenssegers\Mongodb\Eloquent\Model;

class Singer extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'singers';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'name',
        'alias',
        'singer_key',
        'hits',
        'media',
        'sex',
        'language',
        'status'
    ];
    
}
