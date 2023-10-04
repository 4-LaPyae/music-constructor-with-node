<?php

namespace App\Models;

use App\Traits\FillableTraits;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $connection = 'mongodb_atlas';
    protected $collection = 'contracts';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    protected $fillable = [
        '_id',
        'song_key',
        'producer_key',
        'contracts',
        'attachments'
    ];
}
