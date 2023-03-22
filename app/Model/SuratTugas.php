<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SuratTugas extends Model
{
    protected $table = 'surat_tugas';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function imMaintenance()
    {
        return $this->hasMany('App\Model\InternalMemoMaintenance','id_internal_memo', 'id');
    }
}
