<?php

namespace App\Models;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Model;

class Subcategories extends Model
{
    
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ];
    
    //files
    public function files()
    {
        return $this->hasMany(Files::class);
    }
    		 
	//subcategories
    public function categories()
    {
		return $this->belongsTo(Categories::class);
    }
}
