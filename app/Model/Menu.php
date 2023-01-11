<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id';
    protected $guarded = [''];



    // public function roleMenu()
    // {
    //     return $this->belongTo('App\Model\RoleMenu');
    // }

    // public function setMenu()
    // {
    //     return $this->hasMany('App\Model\RoleMenu');
    // }

}
