<?php

namespace App\Models;

use App\Models\User;
use App\Models\Likes;
use App\Models\Rating;
use App\Models\Comments;
use App\Models\Downloads;
use App\Models\File_objects;
use App\Models\Subcategories;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
	protected $guarded =[];

	protected $hidden = [
        'user_id',
        'aproved',
	];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];
    
    //Relations
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id')->selectRaw('id, full_name, image');
    }
	 
    public function rating()
    {
        return $this->hasMany(Rating::class, 'file_id')->selectRaw('ratings.file_id,avg(ratings.stars) as total')->groupBy('ratings.file_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class,  'file_id');
    }
	 	 
    public function objectFiles()
    {
        return $this->hasMany(File_objects::class, 'file_id')->where('is_deleted', 0);
    }
	  	 
    public function object()
    {
        return $this->hasMany(File_objects::class, 'file_id')->where('is_deleted', 0);
    }
	 
    public function number_files()
    {
        return $this->hasMany(File_objects::class, 'file_id');
        // return $this->hasMany(File_objects::class, 'file_id')->selectRaw('file_id, count(key) as total ')->groupBy('file_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'tags_files', 'file_id', 'tag_id')->selectRaw('tag_id as id,name');
    }
	 
    public function likes()
    {
        return $this->hasMany(Likes::class, 'file_id')->selectRaw('file_id, count(user_id) as total ')->groupBy('file_id');
    }
		 
    public function downloads()
    {
        return $this->hasMany(Downloads::class, 'file_id')->selectRaw('file_id, count(user_id) as total ')->groupBy('file_id');
    }
		 
    public function comment()
    {
        return $this->hasMany(Comments::class, 'file_id')->selectRaw('file_id, count(user_id) as total ')->groupBy('file_id');
    }
				 
    public function comments()
    {
        return $this->hasMany(Comments::class, 'file_id')->orderBy('id', 'DESC')->whereNull('comment_id')->with('users')->where('is_deleted', 0);
    }
		 
    public function categories()
    {
		return $this->belongsTo(Categories::class, 'category_id')->selectRaw('id, name, icon, icon_mobile')->where('is_deleted', 0);
    }
    public function vocalist()
    {
		return $this->belongsTo(Vocalist::class, 'vocalist_id')->where('is_deleted', 0);
    }
    public function collection()
    {
		return $this->belongsTo(Collection::class, 'collection_id')->where('is_deleted', 0);
    }


}
