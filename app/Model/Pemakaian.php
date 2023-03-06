<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pemakaian extends Model
{
    protected $table = 'pemakaian';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public $timestamps = false;
}
