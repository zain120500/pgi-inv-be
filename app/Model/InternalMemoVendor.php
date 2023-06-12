<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoVendor extends Model
{
    protected $table = 'internal_memo_vendor';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
