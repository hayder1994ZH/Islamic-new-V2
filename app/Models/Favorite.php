<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $guarded =[];

    public function file() {
        return $this->belongsTo(Files::class, 'file_id', 'id');
    }

    public function filed() {
        return $this->belongsTo(Files::class, 'file_id', 'id')->with('categories')->where('is_deleted', 0);
    }
}
