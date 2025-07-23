<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'name',
        'original_name',
        'mime_type',
        'size',
        'path',
        'folder_id'
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}
