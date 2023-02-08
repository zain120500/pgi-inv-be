<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StokInventaris extends Model
{
    protected $table = 'stok_inventaris';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    protected $hidden = array('created_at', 'updated_at');
}
