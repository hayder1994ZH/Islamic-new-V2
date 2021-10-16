<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Downloads extends Model
{
    protected $guarded =[];
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    
    protected $hidden =[
        'is_deleted','files_id'
    ];
    
    //files
	public function files()
	{
		return $this->belongsTo(Files::class)->count();
    }

	public function fileer()
	{
		return $this->belongsTo(Files::class, 'files_id', 'id')->with('subcategories');
    }
    
	public function file()
	{
		return $this->belongsTo(Files::class, 'files_id', 'id');
    }
	public function downloaded()
	{
		return $this->belongsTo(Files::class, 'files_id', 'id')->where('is_deleted', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
