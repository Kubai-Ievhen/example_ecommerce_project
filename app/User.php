<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function role(){
        return $this->belongsTo('App\UserGroup');
    }

    public function group(){
        return $this->hasOne('App\UserGroup','id','group_id');
    }

    public function avatar(){
        return $this->hasOne('App\UserGroup','id','group_id');
    }

    public function facebook(){
        return $this->hasOne('App\UserFacebookProfile');
    }

    public function profile(){
        return $this->hasOne('App\UserProfile');
    }

    public function files(){
        return $this->hasMany('App\UsersFile');
    }

    public function addresses(){
        return $this->hasMany('App\UsersAddress');
    }

    public function organization(){
        return $this->hasOne('App\OrganizationData');
    }
}
