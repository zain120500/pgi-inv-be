<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    protected $guarded = [''];

}
