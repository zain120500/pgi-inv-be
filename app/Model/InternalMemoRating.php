<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoRating extends Model
{
    protected $table = 'internal_rating_memo';
    protected $primaryKey = 'id';
    protected $guarded = [''];
}
