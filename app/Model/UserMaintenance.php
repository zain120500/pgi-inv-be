<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserMaintenance extends Model
{
    protected $table = 'user_maintenance';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function internalMemoMaintenance()
    {
        return $this->hasMany('App\Model\InternalMemoMaintenance', 'id_user_maintenance', 'id');
    }
}
