<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    protected $table = 'pembelian_detail';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    
    function tipeBarang()
    {
        return $this->hasOne('App\Model\BarangTipe', 'id', 'id_tipe');
	}

    function cabang()
    {
        return $this->hasOne('App\Model\Cabang', 'id', 'id_gudang');
	}

    function pembelian()
    {
        return $this->hasOne('App\Model\Pembelian', 'id', 'id_pembelian');
	}

}
