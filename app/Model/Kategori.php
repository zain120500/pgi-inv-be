<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id';
    protected $guarded = [''];


    function barangJenis()
    {
        return $this->hasMany('App\Model\BarangJenis', 'id_kategori');
	}

    function kategoriJenis()
    {
        return $this->hasMany('App\Model\KategoriJenisFpp', 'id', 'id_kategori_jenis_fpp');
	}

    function kategoriPic()
    {
        return $this->hasMany('App\Model\KategoriPicFpp', 'id_kategori_fpp', 'id');
	}

}
