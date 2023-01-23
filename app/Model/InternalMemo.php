<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemo extends Model
{
    protected $table = 'internal_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
