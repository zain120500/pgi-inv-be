<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class HistoryMemo extends Model
{
    protected $table = 'history_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];

//    protected $casts = [
//        'created_at' => 'datetime:Y-m-d h:i:s',
//        'updated_at' => 'datetime:Y-m-d h:i:s',
//        'deleted_at' => 'datetime:Y-m-d h:i:s'
//    ];

    // function barangJenis()
    // {
    //     return $this->hasMany('App\Model\BarangJenis', 'id_kategori');
	// }
}
