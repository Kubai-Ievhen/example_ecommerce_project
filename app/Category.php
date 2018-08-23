<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function categoryImage(){
        return $this->belongsTo('App\ProductImage', 'product_image_id','id');
    }

    public function parameters(){
        return $this->hasMany('App\CategoryParameter');
    }
}
