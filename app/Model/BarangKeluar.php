<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    public function barangTipe()
    {
        return $this->belongTo('App\Model\BarangTipe');
    }
}
