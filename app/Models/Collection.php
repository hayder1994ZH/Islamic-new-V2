<?php

namespace App\Models;

use App\Helpers\Utilities;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted', 'image', 'updated_at'
    ]; 
    protected $fillable = ['id', 'name', 'image', 'user_id', 'created_at', 'updated_at'];
    protected $appends = ['imageCollection'];
    public function getImageCollectionAttribute(){
        return ($this->image != null)? request()->get('host') . Utilities::$imageBuket . $this->image:null;
    }
            
    public function files()
    {
		return $this->hasMany(Files::class, 'collection_id')->whereHas('object',function ($objectModel) {
            $objectModel->where('type_audio', 1)->orWhere('type_audio', 2);
     })->where('is_deleted', 0);
    }
    
    //user
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
    }
}
