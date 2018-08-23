<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    public function avatar(){
        return $this->belongsTo('App\UsersFile', 'users_files_id','id');
    }
}
