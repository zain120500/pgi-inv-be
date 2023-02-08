<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    protected $table = 'stok_barang';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    protected $hidden = array('created_at', 'updated_at');

}
