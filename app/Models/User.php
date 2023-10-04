<?php

namespace App\Models;

use App\Traits\FillableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    //mongodb
    // protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'users';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'profile',
        'phone',
        'auth_token',
        'token_expired_at',
        'otp',
        'user_id',
        'role',
        'last_login',
        'status',
    ];
}
