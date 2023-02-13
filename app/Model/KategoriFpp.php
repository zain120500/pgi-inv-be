<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KategoriFpp extends Model
{
    protected $table = 'kategori_fpp';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    protected $hidden = array('created_at', 'updated_at');


    function kategoriJenis()
    {
        return $this->hasMany('App\Model\KategoriJenisFpp','id', 'id_kategori_jenis_fpp');
	}

    function kategoriSub()
    {
        return $this->hasMany('App\Model\KategoriSubFpp','id_kategori_fpp');
	}
}
