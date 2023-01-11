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
        return $this->hasMany('App\Model\BarangTipe', 'id_merk');
    }

    function barangJenis()
    {
        return $this->hasMany('App\Model\BarangJenis', 'id');
	}

}
