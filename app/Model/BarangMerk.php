<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangMerk extends Model
{
    protected $table = 'barang_merk';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    public function barangTipe()
    {
        return $this->belongTo('App\Model\BarangTipe', 'id_merk');
    }

}
