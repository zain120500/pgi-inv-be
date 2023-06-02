<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StokInventaris extends Model
{
    protected $table = 'stok_inventaris';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;


    public function barangTipe()
    {
        return $this->belongsTo('App\Model\BarangTipe', 'id_tipe', 'id');
    }

    public function karyawan()
    {
        return $this->belongsTo('App\Model\TblKaryawan', 'pemakai', 'nik');
    }
}
