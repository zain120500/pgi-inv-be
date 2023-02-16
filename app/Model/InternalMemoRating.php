<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoRating extends Model
{
    protected $table = 'internal_memo_rating';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
