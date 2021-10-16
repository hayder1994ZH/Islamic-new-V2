<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayList extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ]; 
     
    //Relations
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id')->selectRaw('id, full_name, image');
    }
    
	public function file()
	{
		return $this->belongsTo(Files::class, 'file_id')->where('is_deleted', 0);
    }
    
}
