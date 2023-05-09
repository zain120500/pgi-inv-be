<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoBarang extends Model
{
    protected $table = 'internal_memo_barang';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    protected $casts = [
        'quantity' => 'integer'
    ];

    function internalMemoBarang()
    {
        return $this->hasOne('App\Model\StokBarang', 'id', 'id_barang');
    }
}
