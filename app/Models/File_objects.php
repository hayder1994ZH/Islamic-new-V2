<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File_objects extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted','files_id'
    ];

    public function file()
    {
        return $this->belongsTo(Files::class, 'files_id', 'id');
    }

}
