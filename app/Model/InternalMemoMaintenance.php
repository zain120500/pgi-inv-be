<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoMaintenance extends Model
{
    protected $table = 'internal_memo_maintenance';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    function internalMemo()
    {
        return $this->hasOne('App\Model\InternalMemo', 'id', 'id_internal_memo');
    }

    function userMaintenance()
    {
        return $this->hasOne('App\Model\UserMaintenance', 'id', 'id_user_maintenance');
    }
}
