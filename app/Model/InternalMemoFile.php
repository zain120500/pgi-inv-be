<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InternalMemoFile extends Model
{
    protected $table = 'internal_memo_files';
    protected $primaryKey = 'id';
    protected $guarded = [''];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];

    public function getPathAttribute()
    {
        return getFiles($this->attributes['path']);
    }
}
