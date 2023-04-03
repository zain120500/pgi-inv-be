<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    protected $hidden = array('created_at', 'updated_at', 'updated_by_id','created_by_id','old_id');


    public function user()
    {
        return $this->hasMany('App\User');
    }

    public function roleMenu()
    {
        return $this->hasMany('App\Model\RoleMenu');
    }

}
