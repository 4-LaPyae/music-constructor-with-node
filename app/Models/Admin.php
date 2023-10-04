<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;
use App\Traits\FillableTraits;

class Admin extends Model
{
    use HasFactory;

    //mongodb
    //protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'admins';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'email',
        'password',
        'name',
        'media',
        'role',
        'status',
        'last_login',
        'auth_token',
        'token_expired_at'
    ];
}
