<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
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
    
	public function comments()
	{
		return $this->hasMany(SocialComment::class, 'social_id')->with('replies')->where('is_deleted', 0);
    }
     
	public function likes()
	{
		return $this->hasMany(SocialLike::class, 'social_id')->selectRaw('social_id, count(user_id) as total ')->groupBy('social_id')->where('is_deleted', 0);
    }
	
	public function like_social()
	{
		return $this->hasMany(SocialLike::class, 'social_id')->where('is_deleted', 0);
    }
}
