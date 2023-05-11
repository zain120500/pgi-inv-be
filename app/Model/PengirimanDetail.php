<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PengirimanDetail extends Model
{
    protected $table = 'pengiriman_detail';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    public $timestamps = false;


}
