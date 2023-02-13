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
    
}
