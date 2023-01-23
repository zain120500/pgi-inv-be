<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangTipe extends Model
{
    protected $table = 'barang_tipe';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public function barangMerk()
    {
        return $this->belongsTo('App\Model\BarangMerk', 'id_merk');
    }

    function barangKeluar()
    {
        return $this->hasMany('App\Model\BarangKeluar', 'id_tipe');
	}

}
