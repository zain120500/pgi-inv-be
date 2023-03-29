<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoBarang extends Model
{
    protected $table = 'internal_memo_barang';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function internalMemoBarang()
    {
        return $this->hasMany('App\Model\InternalMemoBarang', 'id_internal_memo', 'id');
    }
}
