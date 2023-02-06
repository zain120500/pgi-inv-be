<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KategoriPicFpp extends Model
{
    protected $table = 'kategori_pic_fpp';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
	}

    function devisi()
    {
        return $this->hasOne('App\Model\Devisi', 'DivisiID', 'devisi_id');
	}

    function kategori()
    {
        return $this->hasMany('App\Model\KategoriFpp', 'id', 'id_kategori_fpp');
	}
}
