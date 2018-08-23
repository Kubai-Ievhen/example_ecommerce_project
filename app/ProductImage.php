<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    public function imageType(){
        return $this->belongsTo('App\ImagesProductType');
    }
    public function iconsGroup(){
        return $this->belongsTo('App\IconsGroup','icons_group_id', 'id');
    }
}
