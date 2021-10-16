<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ]; 
}
