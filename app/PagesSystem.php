<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PagesSystem extends Model
{
    public $STATUSES = [
        ['id'=>0, 'name'=>'Unpublished'],
        ['id'=>1, 'name'=>'Publish'],
        ['id'=>2, 'name'=>'Draft']
    ];


}
