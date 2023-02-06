<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HistoryMemo extends Model
{
    protected $table = 'history_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    // function barangJenis()
    // {
    //     return $this->hasMany('App\Model\BarangJenis', 'id_kategori');
	// }
}
