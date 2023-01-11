<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangJenis extends Model
{
    protected $table = 'barang_jenis';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    public function kategori()
    {
        return $this->belongTo('App\Model\Kategori');
    }

    public function barangKategori()
    {
        return $this->hasOne('App\Model\Kategori', 'id');
    }

}
