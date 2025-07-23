<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

     public function files()
    {
        return $this->hasMany(File::class);
    }
}
