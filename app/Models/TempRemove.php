<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRemove extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted',
    ];
    
}
