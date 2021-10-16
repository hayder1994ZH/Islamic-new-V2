<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialComment extends Model
{
    protected $guarded =[];
    
    protected $hidden =[
        'is_deleted'
    ];
        
	public function socials()
	{
		return $this->belongsTo(Social::class, 'social_id');
    }
    
	public function users()
	{
		return $this->belongsTo(User::class, 'user_id')->selectRaw('id, full_name, image');
    }

    public function replies()
    {
        return $this->hasMany(SocialComment::class, 'comment_id', 'id')->where('comment_id', '!=', null);
    } 
}
