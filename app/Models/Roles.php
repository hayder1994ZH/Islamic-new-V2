<?php

namespace App\Models;

use App\Models\User;
use App\Models\Permissions;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted', 'created_at', 'updated_at'
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
