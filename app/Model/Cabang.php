<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'cabang';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function kabupatenKota()
    {
        return $this->hasOne('App\Model\Kabupaten','id','kabupaten_kota_id');
    }


}
