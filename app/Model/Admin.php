<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
