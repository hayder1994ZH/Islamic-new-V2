<?php
namespace App\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $guarded =[];
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    
    protected $hidden =[
        'is_deleted','file_id'
    ];
    
    //files
	public function files()
	{
		return $this->belongsTo(Files::class, 'file_id');
    }
    
    //files
	public function users()
	{
		return $this->belongsTo(User::class, 'user_id')->selectRaw('id, full_name, image');
    }

    public function replies()
    {
        return $this->hasMany(Comments::class, 'comment_id', 'id')->where('comment_id', '!=', null);
    }
}
