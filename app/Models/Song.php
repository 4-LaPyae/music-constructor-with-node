<?php

namespace App\Models;

use App\Traits\FillableTraits;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Song extends Model
{
    use HasFactory;
    // protected $with = ['singer'];

    //mongodb
    // protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'songs';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];


    protected $fillable = [
        'title',
        'titlea',
        'song_key',
        'hits',
        'media',
        'singers',
        'band',
        'album',
        'artists',
        'solo_song',
        'duet_song',
        'group_song',
        'producer',
        'contracts',
        'contract_status',
        'mr',
        'mr_file',
        'language',
        'lyric',
        'status',
        'attachments',
        'recording',
        'generes',
        'music_lists',
        'amount',
        'start',
        'end'
    ];
}
