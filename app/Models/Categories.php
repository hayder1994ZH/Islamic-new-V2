<?php

namespace App\Models;

use App\Models\Subcategories;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ]; 

    public function vocalist()
    {
		return $this->hasMany(Vocalist::class, 'category_id')->where('is_deleted', 0);
    }
}
