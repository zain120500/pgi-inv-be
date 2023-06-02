<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TblJabatan extends Model
{
    protected $table = 'tbl_jabatan';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;
}
