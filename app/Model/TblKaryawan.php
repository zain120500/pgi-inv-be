<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TblKaryawan extends Model
{
    protected $table = 'tbl_karyawan';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;

    public function jabatan()
    {
        return $this->hasOne('App\Model\TblJabatan', 'id_jabatan', 'id_jabatan');
    }
}
