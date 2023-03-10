<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TblCabang extends Model
{
    protected $table = 'tbl_cabang';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;
}
