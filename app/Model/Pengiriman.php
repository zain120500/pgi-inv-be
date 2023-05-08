<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function detail()
    {
        return $this->hasMany('App\Model\PengirimanDetail', 'id_pengiriman', 'id');
	}

    function cabangPengirim()
    {
        return $this->hasOne('App\Model\StokPusat', 'kode_cabang', 'pengirim');
    }

    function cabangPenerima()
    {
        return $this->hasOne('App\Model\Cabang', 'kode', 'penerima');
    }

}
