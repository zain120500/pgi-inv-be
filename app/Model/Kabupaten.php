<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $table = 'kabupaten_kota';
    protected $primaryKey = 'id';
    protected $guarded = [''];


}
