<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserStaffCabang extends Model
{
    protected $table = '_user_staff_cabang';
    protected $guarded = [''];

    public $timestamps = false;

    function cabang()
    {
        return $this->hasMany('App\Model\Cabang','id','cabang_id');
    }

    function user()
    {
        return $this->hasMany('App\User','id','user_staff_id');
    }

    function role()
    {
        return $this->hasMany('App\Model\Role','id','role_id');
    }

}
