<?php

namespace App\Models;

use App\Traits\FillableTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Enduser extends Model
{
    use HasFactory;

    //mongodb
    // protected $connection = 'mongodb';
    protected $connection = 'mongodb_atlas';
    protected $collection = 'endusers';

    protected $hidden = [
        'created_at',
        'updated_at',
        '_id'
    ];

    protected $fillable = [
        'name',
        'email',
        'username',
        'profile',
        'phone',
        'auth_token',
        'token_expired_at',
        'platform',
        'otp',
        'user_id',
        'status',
        'fcm_token',
        'device_id',
        'version',
        'user_master_key',
        'login_devices',
        'current_devices'
    ];
}
