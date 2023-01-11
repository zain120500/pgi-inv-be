<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    protected $table = '_role_menu';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    
    public function role()
    {
        return $this->belongTo('App\Model\Role');
    }

    public function getMenu()
    {
        return $this->hasOne('App\Model\Menu');
    }
    
}
