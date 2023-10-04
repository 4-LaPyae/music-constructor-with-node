<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use App\Traits\FillableTraits;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\ImagePathTraits;
use Jenssegers\Mongodb\Eloquent\Model;

class Media extends Model
{
    use HasFactory, ImagePathTraits;

    //mongodb
    // protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'medias';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        '_id',
        'media_type_id',
        'media_link',
        'description'
    ];

    //public $table = 'medias';
    // protected $connection = "MediasDB";
    //     protected $fillable = [
    //         'media_type_id',
    //         'media_link',
    //         'description'
    //     ];

    // protected function mediaLink(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $this->url . $value,
    //     );
    // }
    // public function producer()
    // {
    //     return $this->belongsTo(Producer::class);
    // }
    // public function recording()
    // {
    //     return $this->belongsTo(Recording::class);
    // }
}
