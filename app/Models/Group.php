<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use App\Traits\FillableTraits;
use Jenssegers\Mongodb\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'groups';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'group_name',
        'group_image',
        'group_type',
        'musiclists',
        'status',
        'order_id'
    ];
}
