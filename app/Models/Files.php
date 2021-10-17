<?php

namespace App\Models;

use App\Models\User;
use App\Models\Likes;
use App\Models\Rating;
use App\Models\Comments;
use App\Models\Downloads;
use App\Helpers\Utilities;
use App\Models\File_objects;
use App\Models\Subcategories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
	protected $guarded =[];

	protected $hidden = [
        'user_id',
        'ImageFileObj',
        'categories',
        'vocalist',
        'collection',
        'aproved',
        'is_deleted'
	];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];
    protected $appends = ['faviorate', 'liked', 'imageFile', 'vocalistName','vocalistImage', 'collectionName','collectionImage', 'categoryName','categoryImage'
];

    // get if user faviorate file
    public function getFaviorateAttribute(){
        if (Auth::guard('api')->check()) {
            $favorite = Favorite::where('file_id', $this->id)
            ->where('user_id', auth()->user()->id)
            ->first();
            if(!$favorite){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }

    // get if user liked file
    public function getLikedAttribute(){
        if (Auth::guard('api')->check()) {
            $like = Likes::where('file_id', $this->id)
            ->where('user_id', auth()->user()->id)
            ->first();
            if(!$like){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
    // image file
    public function getImageFileAttribute(){
        foreach($this->ImageFileObj as $fielobj){
            if($fielobj->buket == 'islamic_images'){
                return $fielobj->imageFile;
            }else{
                return null;
            }
        }
    }
    // Vocalist file
    public function getVocalistNameAttribute(){
        if($this->vocalist != null){
            return $this->vocalist->name;
        }else{
            return null;
        }
    }
    // Vocalist file
    public function getVocalistImageAttribute(){
        if($this->vocalist != null){
            return $this->vocalist->imageVocalist;
        }else{
            return null;
        }
    }
    // Collection file
    public function getCollectionNameAttribute(){
        if($this->vocalist != null){
            return $this->vocalist->name;
        }else{
            return null;
        }
    }
    // Collection file
    public function getCollectionImageAttribute(){
        if($this->collection != null){
            return $this->collection->imageCollection;
        }else{
            return null;
        }
    }
    // Category file
    public function getCategoryNameAttribute(){
        if($this->categories != null){
            return $this->categories->name;
        }else{
            return null;
        }
    }
    // Category file
    public function getCategoryImageAttribute(){
        if($this->categories != null){
            return $this->categories->imageCategory;
        }else{
            return null;
        }
    }
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
        return $this->hasMany(File_objects::class, 'file_id')->where('type_audio', '!=', 0)->where('is_deleted', 0);
    }	

    public function ImageFileObj()
    {
        return $this->hasMany(File_objects::class, 'file_id')->where('type_audio', 0)->where('is_deleted', 0);
    }
	  	 
    public function object()
    {
        return $this->hasMany(File_objects::class, 'file_id')->where('type_audio', '!=', 0)->where('is_deleted', 0);
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
