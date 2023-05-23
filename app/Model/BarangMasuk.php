<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    public function barangTipe()
    {
        return $this->hasMany('App\Model\BarangTipe', 'id', 'id_tipe');
    }

    public function barangTipee()
    {
        return $this->hasOne('App\Model\BarangTipe', 'id', 'id_tipe');
    }

}
