<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoPengiriman extends Model
{
    protected $table = '_pengiriman_im_';
    protected $guarded = [''];
    public $timestamps = false;

}
