<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoFile extends Model
{
    protected $table = 'internal_memo_files';
    protected $primaryKey = 'id';
    protected $guarded = [''];

    public function getPathAttribute()
    {
        return getFiles($this->attributes['path']);
    }

    public function getPathVideoAttribute()
    {
        return getFiles($this->attributes['path_video']);
    }
}
