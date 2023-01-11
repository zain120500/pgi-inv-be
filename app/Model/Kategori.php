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


}
