<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class WordSearch extends Model
{
    use HasFactory;

    //mongodb
    protected $connection = 'mongodb';
    protected $collection = 'word_search';

    protected $hidden = ['_id'];

    protected $fillable = [
        "word",
        'search_keyword',
        'user_id',
    ];
}