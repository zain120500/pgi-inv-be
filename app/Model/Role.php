<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    public function user()
    {
        return $this->hasMany('App\User');
    }

}
