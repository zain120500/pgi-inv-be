<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'cabang';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    function kabupatenKota()
    {
        return $this->hasOne('App\Model\Kabupaten','id','kabupaten_kota_id');
    }

    public function scopeWhereLike($query, $column, $value)
    {
        return $query->where($column, 'like', '%'.$value.'%');
    }

    public function scopeOrWhereLike($query, $column, $value)
    {
        return $query->orWhere($column, 'like', '%'.$value.'%');
    }

}
