<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'kelurahan_desa';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
