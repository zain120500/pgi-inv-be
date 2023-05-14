<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;

    function UserInput()
    {
        return $this->hasOne('App\User', 'id', 'user_input');
	}

    function supplier()
    {
        return $this->hasOne('App\Model\Supplier', 'id', 'id_supplier');
	}

    function detail()
    {
        return $this->hasMany('App\Model\PembelianDetail', 'id_pembelian', 'id');
	}
}
