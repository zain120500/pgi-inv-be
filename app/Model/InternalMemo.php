<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemo extends Model
{
    protected $table = 'internal_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];

//    protected $casts = [
//        'created_at' => 'datetime:Y-m-d h:i:s',
//        'updated_at' => 'datetime:Y-m-d h:i:s',
//        'deleted_at' => 'datetime:Y-m-d h:i:s'
//    ];

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

    function memoMaintenance()
    {
        return $this->hasMany('App\Model\InternalMemoMaintenance', 'id_internal_memo', 'id');
    }

    public function memoMaintenanceCount()
    {
        return $this->memoMaintenance()->where('flag', 1);
    }

    public function maintenanceUser()
    {
        $user = UserMaintenance::where('user_id', auth()->user()->id)->first();
        return $this->memoMaintenance()->where('id_user_maintenance','=', $user->id);
    }

    public function totalUserMaintenance()
    {
        return $this->memoMaintenance();
    }

    function memoRating()
    {
        return $this->hasMany('App\Model\InternalMemoRating', 'id_internal_memo', 'id');
    }

    function internalMemoBarang()
    {
        return $this->hasMany('App\Model\InternalMemoBarang', 'id_internal_memo', 'id');
    }

    function internalMemoVendor()
    {
        return $this->hasOne('App\Model\InternalMemoVendor', 'id_internal_memo', 'id');
    }

}
