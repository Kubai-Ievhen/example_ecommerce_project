<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersFile extends Model
{
    //
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function typeFile(){
        return $this->belongsTo('App\TypeUsersFile');
    }
}
