<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KategoriJenisFpp extends Model
{
    protected $table = 'kategori_jenis_fpp';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    protected $hidden = array('created_at', 'updated_at');

    function kategori()
    {
        return $this->hasMany('App\Model\KategoriFpp','id_kategori_jenis_fpp');
	}
}
