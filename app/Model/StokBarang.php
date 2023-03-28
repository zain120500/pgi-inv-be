<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    protected $table = 'stok_barang';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;

    protected $hidden = array('created_at', 'updated_at');

    function barangTipe()
    {
        return $this->hasOne('App\Model\BarangTipe', 'id', 'id_tipe');
    }

}
