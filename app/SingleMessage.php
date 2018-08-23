<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SingleMessage extends Model
{
    public function users(){
        return $this->hasMany('App\SingleMailToUser');
    }

    public function groups(){
        return $this->hasMany('App\SingleMailToGroup');
    }
}
