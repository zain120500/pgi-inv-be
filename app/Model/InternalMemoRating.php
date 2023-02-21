<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoRating extends Model
{
    protected $table = 'internal_memo_rating';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function internalMemo()
    {
        return $this->hasOne('App\Model\InternalMemo','id', 'id_internal_memo');
    }
}
