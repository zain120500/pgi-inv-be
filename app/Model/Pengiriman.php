<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'pengiriman';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;

    function detail()
    {
        return $this->hasMany('App\Model\PengirimanDetail', 'id_pengiriman', 'id');
	}

    function cabangPengirim()
    {
        return $this->hasOne('App\Model\Cabang', 'kode', 'pengirim');
    }

    function cabangPenerima()
    {
        return $this->hasOne('App\Model\Cabang', 'kode', 'penerima');
    }

}
