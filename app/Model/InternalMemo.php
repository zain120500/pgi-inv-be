<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemo extends Model
{
    protected $table = 'internal_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function cabang()
    {
        return $this->hasOne('App\Model\Cabang','id','id_cabang');
	}

    function devisi()
    {
        return $this->hasOne('App\Model\Devisi','DivisiID', 'id_devisi');
	}

    function kategoriJenis()
    {
        return $this->hasOne('App\Model\KategoriJenisFpp','id', 'id_kategori_jenis_fpp');
	}

    function kategori()
    {
        return $this->hasOne('App\Model\KategoriFpp','id', 'id_kategori_fpp');
	}

    function createdBy()
    {
        return $this->hasOne('App\User','id','created_by');
	}

    function kategoriSub()
    {
        return $this->hasOne('App\Model\KategoriSubFpp','id','id_kategori_sub_fpp');
	}

    function MemoFile()
    {
        return $this->hasMany('App\Model\InternalMemoFile','id_internal_memo','id');
	}

    function historyMemo()
    {
        return $this->hasOne('App\Model\HistoryMemo','id_internal_memo','id');
	}

    function listHistoryMemo()
    {
        return $this->hasMany('App\Model\HistoryMemo','id_internal_memo','id');
    }


}
