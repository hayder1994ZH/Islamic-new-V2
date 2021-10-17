<?php

namespace App\Models;

use App\Helpers\Utilities;
use Illuminate\Database\Eloquent\Model;

class Vocalist extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted', 'key', 'buket'
    ]; 
    protected $appends = ['imageVocalist'];
    public function getImageVocalistAttribute(){
        return ($this->key != null)? request()->get('host') . Utilities::$imageBuket . $this->key:null;
    }
    //user
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
    }

    //user
	public function files()
	{
		return $this->hasMany(Files::class, 'vocalist_id');
    }
}
