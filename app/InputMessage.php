<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InputMessage extends Model
{
    //
    public function sender(){
        return $this->belongsTo('App\User','sender_user_id','id');
    }

    public function recipient(){
        return $this->belongsTo('App\User','recipient_user_id','id');
    }
}
