<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pemakaian extends Model
{
    protected $table = 'pemakaian';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public $timestamps = false;

    function barangTipe()
    {
        return $this->hasOne('App\Model\BarangTipe', 'id', 'id_tipe');
    }

    function cabang()
    {
        return $this->hasOne('App\Model\Cabang', 'kode', 'pic')->select(['name', 'alamat']);
    }
}
