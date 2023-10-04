<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use App\Traits\FillableTraits;
use Jenssegers\Mongodb\Eloquent\Model;

class Producer extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'producers';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'name',
        'producer_key',
        'status',
        'media'
    ];
}
