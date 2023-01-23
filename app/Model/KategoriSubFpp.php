<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class KategoriSubFpp extends Model
{
    protected $table = 'kategori_sub_fpp';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    protected $hidden = array('created_at', 'updated_at');


    function kategori()
    {
        return $this->hasOne('App\Model\KategoriFpp', 'id', 'id_kategori_fpp');
	}
}
