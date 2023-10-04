<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'countries';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'id',
        'code',
        'country_name',
        'phone',
        'status'
    ];
}
