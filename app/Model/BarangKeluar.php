<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public $timestamps = false;

    public function barangTipe()
    {
        return $this->hasOne('App\Model\BarangTipe', 'id', 'id_tipe');
    }
}
