<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BarangPengiriman extends Model
{
    protected $table = 'barang_pengiriman';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
