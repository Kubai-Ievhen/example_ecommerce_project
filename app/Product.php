<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function prices(){
        return $this->hasMany('App\Price');
    }

    public function preview(){
        return $this->belongsTo('App\ProductPreview', 'preview_image_id', 'id');
    }

    public function previews(){
        return $this->hasMany('App\ProductPreview');
    }

    public function imgComponents(){
        return $this->hasMany('App\ProductComponentsImage');
    }

    public function textComponents(){
        return $this->hasMany('App\ProductComponentsText');
    }

    public function category(){
        return $this->belongsTo('App\Category');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
