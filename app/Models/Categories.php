<?php

namespace App\Models;

use App\Helpers\Utilities;
use App\Models\Subcategories;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted', 'icon_mobile'
    ]; 
    protected $appends = ['imageCategory'];
    public function getImageCategoryAttribute(){
      return ($this->icon_mobile != null)? request()->get('host') . Utilities::$imageBuket . $this->icon_mobile:null;
    }

    public function vocalist()
    {
      return $this->hasMany(Vocalist::class, 'category_id')->where('is_deleted', 0);
    }
}
