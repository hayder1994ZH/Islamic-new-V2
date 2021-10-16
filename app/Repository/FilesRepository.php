<?php

namespace App\Repository;

use App\Models\Favorite;
use App\Models\File_objects;
use App\Models\Files;
use App\Models\User;
use App\Models\Tags;
use App\Models\Categories;
use App\Models\Rating;
use App\Models\Likes;
use App\Models\PlayList;
use App\Models\Vocalist;
use Illuminate\Http\Request;
use App\Models\Tags_files;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Boolean;

class FilesRepository extends BaseRepository
{

    //Repo to Get all files
    public function getFile($id, $domain, $domain2, $type)
    {
        $arr = [];
        $objectModel = File_objects::where('is_deleted', 0);
       
        if (Auth::guard('api')->check()) {
            $favorite = Favorite::where('file_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();
            if ($favorite) {
                $favorites = true;
            } else {
                $favorites = false;
            }
            $liked = Likes::where('file_id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();
            if ($liked) {
                $Likes = true;
            } else {
                $Likes = false;
            }
        }else{
            $favorites = 'unauthorized';
            $Likes = 'unauthorized';
        }
        $objectData = [];
        return $result = Files::where('id', $id)->with('ratings', 'collection','vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
            ->where('is_deleted', 0)
            ->where('aproved', 0)
            ->withCount('likes')
            ->whereHas('object',function ($objectModel) use($type) {
                $objectModel->where('type_audio', $type);
            })
            // ->firstOrFail()
            ->get()
            ->map(function ($item) use ($domain, $arr, $domain2,$favorites, $Likes, $type, $objectData) {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $dd['ImageFile'] =  $item->object;
                foreach($item->object as $image){
                    if(str_contains($image->key, 'largImage')){
                        $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                    }
                }
                $data['favorite'] = $favorites;
                $data['like'] = $Likes;
                $data['likes_count'] = $item->likes_count;
                $data['totale_size'] = $item->totale_size;
                $data['views'] = $item->views;
                $data['trailer_url'] = $item->trailer_url;
                $data['rating'] = $item->rating;
                $data['total_downloads'] = $item->total_downloads;
                $data['description'] = $item->description;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                $data['category_id'] = $item->category_id;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                $data['created_at'] = $item->created_at;
                $data['updated_at'] = $item->updated_at;
                $data['categories'] = $item->categories;
                $data['user'] =[
                    'id' =>  $item->user->id,
                    'full_name' =>  $item->user->full_name,
                    'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $data['downloads'] = $item->downloads;
                $data['likes'] = $item->likes;
                $data['comments'] = $item->comments->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    $comment['replies'] = $item->replies->map(function($item) use($domain){
                        $comment['id'] = $item->id;
                        $comment['comment'] = $item->comment;
                        $comment['comment_id'] = $item->comment_id;
                        $comment['user_id'] = $item->user_id;
                        $comment['created_at'] = $item->created_at;
                        $comment['updated_at'] = $item->updated_at;
                        $comment['users_id'] = $item->users->id;
                        $comment['users_full_name'] = $item->users->full_name;
                        $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                        return $comment;
                    });
                    return $comment;
                });
                $data['tags'] = $item->tags;
                $ss['objectFiles'] = $item->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type && $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type && $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }

                $data['objectFiles'] = $objectData;
                if(!empty($item->vocalist)){
                    $data['vocalistId'] = $item->vocalist->id;
                    $data['vocalistName'] = $item->vocalist->name;
                    $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
                }else{
                    $data['vocalistId'] = null;
                    $data['vocalistName'] = null;
                    $data['vocalistImage'] = null;
                }
                if(!empty($item->collection)){
                    $data['collectionId'] = $item->collection->id;
                    $data['collectionName'] = $item->collection->name;
                    $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
                }else{
                    $data['collectionId'] = null;
                    $data['collectionName'] = null;
                    $data['collectionImage'] = null;
                }
                
                return $data;
            });
            
       
    }

    //Repo to Get all files
    public function getPlaylist($domain, $take, $skip, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = PlayList::where('user_id', auth()->user()->id)->with('user', 'file', 'file.object', 'file.vocalist','file.collection')
        ->whereHas('file.object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
            ->where('is_deleted', 0);
            $objectData = [];
            return   [
              'total_count' => $result->get()->count(),
              'items' => $result->take($take)->skip($skip)->get()
              ->map(function ($item) use ($domain, $objectData, $type) {
                $data['id'] = $item->file_id;
                $data['user_id'] = $item->user_id;
                $data['title'] = $item->file->title;
                $dd['ImageFile'] =  $item->file->object;
                foreach($item->file->object as $image){
                    if(str_contains($image->key, 'largImage')){
                        $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                    }
                }
                $data['totale_size'] = $item->file->totale_size;
                $data['views'] = $item->file->views;
                $data['trailer_url'] = $item->trailer_url;
                $data['rating'] = $item->file->rating;
                $data['description'] = $item->file->description;
                $data['created_at'] = $item->file->created_at;
                $data['updated_at'] = $item->file->updated_at;
                $data['category_id'] = $item->file->category_id;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                // $data['ratings'] = $item->ratings;
                $data['categories'] = $item->file->categories;
                $data['user'] =[
                    'id' =>  $item->user->id,
                    'full_name' =>  $item->user->full_name,
                    'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $ss['objectFiles'] = $item->file->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type && $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type && $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }
                $data['objectFiles'] = $objectData;
                 if(!empty($item->vocalist)){
                    $data['vocalistId'] = $item->vocalist->id;
                    $data['vocalistName'] = $item->vocalist->name;
                    $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
                }else{
                    $data['vocalistId'] = null;
                    $data['vocalistName'] = null;
                    $data['vocalistImage'] = null;
                }
                if(!empty($item->collection)){
                    $data['collectionId'] = $item->collection->id;
                    $data['collectionName'] = $item->collection->name;
                    $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
                }else{
                    $data['collectionId'] = null;
                    $data['collectionName'] = null;
                    $data['collectionImage'] = null;
                }
                return $data;
            })
            ];
    }

public function getFilebyId($id, $domain, $domain2, $type)
{
    $arr = [];
    $objectModel = File_objects::where('is_deleted', 0);
    if (Auth::guard('api')->check()) {
        $favorite = Favorite::where('file_id', $id)
        ->where('user_id', auth()->user()->id)
        ->first();
        if ($favorite) {
            $favorites = true;
        } else {
            $favorites = false;
        }
        $liked = Likes::where('file_id', $id)
        ->where('user_id', auth()->user()->id)
        ->first();
        if ($liked) {
            $Likes = true;
        } else {
            $Likes = false;
        }
    }else{
        $favorites = 'unauthorized';
        $Likes = 'unauthorized';
    }
    $objectData = [];
    return $result = Files::where('id', $id)->with('ratings', 'categories', 'user', 'collection', 'vocalist', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes')
        ->get()
        ->map(function ($item) use ($domain, $arr, $domain2,$favorites, $Likes, $objectData, $type) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $data['favorite'] = $favorites;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['like'] = $Likes;
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] =  $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            // $data['number_files'] = ['total' => count($arr)];
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == $type && $type == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == $type && $type == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            return $data;
        });
        return $result;
    }

    //Repo for file vocalist id
    public function getByVocalistId($id, $domain, $take, $skip, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('vocalist_id', $id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes');
        $objectData =[];
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
          ->map(function ($item) use ($domain, $type, $objectData) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] =  $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            // $data['number_files'] = ['total' => count($arr)];
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == $type && $type == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == $type && $type == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            return $data;
        })
        ];
        
    }
    
    //Repo for file vocalist id
    public function getByCategoryId($id, $domain)
    {
        
        $arr = [];
        $ids = [];
        $result = Files::where('category_id', $id)->with('vocalist', 'categories')
        ->where('is_deleted', 0)
        ->where('aproved', 0)->get();
        foreach($result as $val){
            if(!empty($val->vocalist)){
                if(!in_array($val->vocalist->id, $ids)){
                    $ss['id'] = $val->vocalist->id;
                    $ss['name'] = $val->vocalist->name;
                    $ss['category_id'] = $val->categories->id;
                    $ss['image'] = ($val->vocalist->key != null)? $domain . $this->imageBuket . $val->vocalist->key:null;
                    array_push($arr, $ss);
                    array_push($ids, $ss['id']);
                }
            }
        }
      return  $arr;
        
    }

    //Repo for get random files by category id
    public function getRandomFilesByCategoryId($domain, $type,$category_id, $vocalist_id)
    {
        $dataFile = [];
        $objectModel = File_objects::where('is_deleted', 0);
        $resultByVocalist = Files::where('vocalist_id', $vocalist_id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes');
        $objectData =[];
        $totalRowsV =  $resultByVocalist->count() - 1;
        $take = 5;
        $skip = $totalRowsV > 5 ? mt_rand(0, $totalRowsV - 5) : 0;
        $fileVoca = $resultByVocalist->take($take)->skip($skip)->get();
        $funFileVoca = $this->getMaping($objectData, $type, $domain, $fileVoca);
        foreach($funFileVoca as $VFile){
          array_push($dataFile, $VFile);
        }
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('category_id', $category_id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes');
        $objectData =[];
        $take = 20;
        $totalRows =  $result->count() - 1;
        $skip = $totalRows > 20 ? mt_rand(0, $totalRows - 20) : 0;
        $randFiles = $result->take($take)->skip($skip)->get();
        $funRandFiles = $this->getMaping($objectData, $type, $domain, $randFiles);
      
        foreach($funRandFiles as $VFile){
            array_push($dataFile, $VFile);
        }
        return $dataFile;
        
    }

    //Repo for file vocalist id and category id
    public function getByVocalistIdAndCategoryId($id, $domain, $take, $skip, $type,$category_id)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('vocalist_id', $id)->where('category_id', $category_id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes');
        $objectData =[];
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
          ->map(function ($item) use ($domain, $type, $objectData) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] =  $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            // $data['number_files'] = ['total' => count($arr)];
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == $type && $type == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == $type && $type == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            return $data;
        })
        ];
        
    }

    //Repo for vocalist id
    public function getListByVocalistId($id, $domain, $take, $skip)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('vocalist_id', $id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->withCount('likes');
        $objectData =[];
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
          ->map(function ($item) use ($domain, $objectData) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] =  $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            // $data['number_files'] = ['total' => count($arr)];
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            return $data;
        })
        ];
        
    }
    //Repo for vocalist id
    public function getByCollectionId($id, $domain, $take, $skip, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('collection_id', $id)->with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->where('aproved', 0)
        ->withCount('likes');
        $objectData = [];
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
          ->map(function ($item) use ($domain,$type, $objectData) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] = $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            // $data['number_files'] = ['total' => count($arr)];
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == $type && $type == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == $type && $type == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            
            return $data;
        })
        ];
        
    }  

    public function rates($id)
    {
        $rate = Rating::where('file_id', $id)->get();
        $sum = ($rate->count() == 0) ? 1 : $rate->count();
        $rate1 = $rate->where('stars', 1)->count();
        $rate2 = $rate->where('stars', 2)->count();
        $rate3 = $rate->where('stars', 3)->count();
        $rate4 = $rate->where('stars', 4)->count();
        $rate5 = $rate->where('stars', 5)->count();
        $rates = [
            'star1' => [
                'count' => $rate1,
                'average' => $rate1 * 100 / $sum
            ],
            'star2' => [
                'count' => $rate2,
                'average' => $rate2 * 100 / $sum
            ],
            'star3' => [
                'count' => $rate3,
                'average' => $rate3 * 100 / $sum
            ],
            'star4' => [
                'count' => $rate4,
                'average' => $rate4 * 100 / $sum
            ],
            'star5' => [
                'count' => $rate5,
                'average' => $rate5 * 100 / $sum
            ],
        ];
        return $rates;
    }

    //Repo for dashboard
    public function dashboard()
    {
        $user = User::where('is_deleted', 0)->where('role_id','!=', 1)->get()->count();
        $total_downloads = DB::table("files")
	    ->select(DB::raw("SUM(total_downloads) as total_download"))
	    ->get();
        $total_views = DB::table("files")
	    ->select(DB::raw("SUM(views) as total_views"))
	    ->get();
        $total_size = DB::table("files")
	    ->select(DB::raw("SUM(totale_size) as total_size"))
	    ->get();
        $categories = Categories::where('is_deleted', 0)->get()->count();
        $file = Files::where('is_deleted', 0)->get();
        $file1 = $file->count();
        $file2 = $file->where('aproved', 1)->count();
        $files = [
            'files' => $file1,
            'files_unaproved' => $file2,
            'users' => $user,
            'total_downloads_file' => $total_downloads[0]->total_download,
            'total_views_file' => $total_views[0]->total_views,
            'total_size' => $total_size[0]->total_size,
            'categories' => $categories,
        ];
        return $files;
    }

    //Repo for update Files 
    public function updateFiles($request, $id)
    {
        $Files = Files::where('id', $id)->firstOrFail();
        $Files->update($request);
        $response = array('message' =>  'Update Files successfully', 'code' => 200);
        return  $response;
    }

    //Repo for getAllData
    public function getAllData($skip = 0, $take, $tags, $domain,$category_id, $search, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        if (!is_null($search)) {
            $tags = Tags::where('name', 'like', '%'.$search.'%')->get()->map(function ($item){
                 return  $data['id']= $item->id;
            });
        }
        if (!is_null($search)) {
            $vocalist = Vocalist::where('name', 'like', '%'.$search.'%')->get()->map(function ($item){
                 return  $data['id']= $item->id;
            });
        }
        if (!is_null($tags)) {
            $check = QueryBuilder::for(Files::class)->join('tags_files', 'files.id', '=', 'tags_files.file_id')
                ->whereIn('tags_files.tag_id', $tags)
                ->distinct();
                if(!$check->exists()){
                    $query = QueryBuilder::for(Files::class)->orWhere('title', 'like', '%'.$search.'%');
                }else{
                    $query = $query->join('tags_files', 'files.id', '=', 'tags_files.file_id')
                             ->whereIn('tags_files.tag_id', $tags);
                }
        } 
        if (!is_null($search)) {
            $categories = Categories::where('name', 'like', '%'.$search.'%')->get()->map(function ($item){
                 return  $data['id']= $item->id;
            });
        }
        if (!is_null($search)) {
            $query->orWhere('title', 'like', '%'.$search.'%');
        }
        if (!is_null($search)) {
           $query->orwhereIn('vocalist_id', $vocalist);
        }
        if (!is_null($search)) {
           $query->orwhereIn('category_id', $categories);
        }
        if($category_id != null){
         $query = $query->whereHas('categories', function ($q) use ($category_id) {
            $q->where('category_id', $category_id)->where('is_deleted', 0);
          });
        }
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
            ->allowedFilters(['category_id', 'title', 'description'])
            ->where('files.aproved', 0)
            ->where('files.is_deleted', 0)
            ->with('user', 'vocalist', 'collection', 'object', 'rating',  'likes', 'comment', 'number_files', 'categories')
            ->whereHas('object',function ($objectModel) use($type) {
               return $objectModel->where('type_audio', $type);
            })
            ->orderBy('files.id', 'desc')
            ->select('files.*')
            ->withCount('likes');
            $objectData =[];
            if($search == null){
                return [
                    'totlaCount' => $query->count(),
                    'items' => $query->take($take)->skip($skip)->get()
                    ->map(function ($item) use ($domain, $type, $objectData) {
                        $data['id'] = $item->id;
                        $data['title'] = $item->title;
                        $dd['ImageFile'] =  $item->object;
                        foreach($item->object as $image){
                            if(str_contains($image->key, 'largImage')){
                                $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                            }
                        }
                        if (Auth::guard('api')->check()) {
                            $favorite = Favorite::where('file_id', $item->id)
                            ->where('user_id', auth()->user()->id)
                            ->first();
                            if(!$favorite){
                                $data['favorited'] = false;
                            }else{
                                $data['favorited'] = true;
                            }
                        }else{
                            $data['favorited'] = false;
                        }
                        if (Auth::guard('api')->check()) {
                                $like = Likes::where('file_id', $item->id)
                                ->where('user_id', auth()->user()->id)
                                ->first();
                                if(!$like){
                                    $data['liked'] = false;
                                }else{
                                    $data['liked'] = true;
                                }
                            }else{
                                $data['liked'] = false;
                        }
                        $data['likes_count'] = $item->likes_count;
                        $data['totale_size'] = $item->totale_size;
                        $data['trailer_url'] = $item->trailer_url;
                        $data['views'] = $item->views;
                        $data['rating'] = $item->rating;
                        $data['total_downloads'] = $item->total_downloads;
                        $data['description'] = $item->description;
                        $data['created_at'] = $item->created_at;
                        $data['collection_id'] = $item->collection_id;
                        $data['vocalist_id'] = $item->vocalist_id;
                        $data['updated_at'] = $item->updated_at;
                        $data['categories'] = $item->categories;
                        $data['user'] =[
                                    'id' =>  $item->user->id,
                                    'full_name' =>  $item->user->full_name,
                                    'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                        ];
                        $ss['objectFiles'] = $item->object;
                        foreach($ss['objectFiles'] as $obj){
                            if($obj->type_audio == $type && $type == 1){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                            if($obj->type_audio == $type && $type == 2){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                        }
                        $data['objectFiles'] = $objectData;
                        if(!empty($item->vocalist)){
                           $data['vocalistId'] = $item->vocalist->id;
                           $data['vocalistName'] = $item->vocalist->name;
                           $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
                       }else{
                           $data['vocalistId'] = null;
                           $data['vocalistName'] = null;
                           $data['vocalistImage'] = null;
                       }
                       if(!empty($item->collection)){
                           $data['collectionId'] = $item->collection->id;
                           $data['collectionName'] = $item->collection->name;
                           $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
                       }else{
                           $data['collectionId'] = null;
                           $data['collectionName'] = null;
                           $data['collectionImage'] = null;
                       }
                        
                        return $data;
                    }),
                ];
            }else{
               $files = $query->get()->map(function ($item) use ($domain, $type, $objectData) {
                        $data['id'] = $item->id;
                        $data['title'] = $item->title;
                        $dd['ImageFile'] =  $item->object;
                        foreach($item->object as $image){
                            if(str_contains($image->key, 'largImage')){
                                $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                            }
                        }
                        if (Auth::guard('api')->check()) {
                            $favorite = Favorite::where('file_id', $item->id)
                            ->where('user_id', auth()->user()->id)
                            ->first();
                            if(!$favorite){
                                $data['favorited'] = false;
                            }else{
                                $data['favorited'] = true;
                            }
                        }else{
                            $data['favorited'] = false;
                        }
                            if (Auth::guard('api')->check()) {
                                $like = Likes::where('file_id', $item->id)
                                ->where('user_id', auth()->user()->id)
                                ->first();
                                if(!$like){
                                    $data['liked'] = false;
                                }else{
                                    $data['liked'] = true;
                                }
                            }else{
                                $data['liked'] = false;
                            }
                        $data['likes_count'] = $item->likes_count;
                        $data['totale_size'] = $item->totale_size;
                        $data['trailer_url'] = $item->trailer_url;
                        $data['views'] = $item->views;
                        $data['rating'] = $item->rating;
                        $data['total_downloads'] = $item->total_downloads;
                        $data['description'] = $item->description;
                        $data['created_at'] = $item->created_at;
                        $data['updated_at'] = $item->updated_at;
                        $data['categories'] = $item->categories;
                        $data['collection_id'] = $item->collection_id;
                        $data['vocalist_id'] = $item->vocalist_id;
                        $data['user'] =[
                                    'id' =>  $item->user->id,
                                    'full_name' =>  $item->user->full_name,
                                    'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                        ];
                        $ss['objectFiles'] = $item->object;
                        foreach($ss['objectFiles'] as $obj){
                            if($obj->type_audio == $type && $type == 1){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                            if($obj->type_audio == $type && $type == 2){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                        }
                        $data['objectFiles'] = $objectData;
                        if(!empty($item->vocalist)){
                           $data['vocalistId'] = $item->vocalist->id;
                           $data['vocalistName'] = $item->vocalist->name;
                           $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
                       }else{
                           $data['vocalistId'] = null;
                           $data['vocalistName'] = null;
                           $data['vocalistImage'] = null;
                       }
                       if(!empty($item->collection)){
                           $data['collectionId'] = $item->collection->id;
                           $data['collectionName'] = $item->collection->name;
                           $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
                       }else{
                           $data['collectionId'] = null;
                           $data['collectionName'] = null;
                           $data['collectionImage'] = null;
                       }
                        return $data;
                    });
                $newArr = [];
                foreach($files as $file){
                    if(!empty($file["objectFiles"])){
                       array_push($newArr, $file);
                    }
                }
                return [
                    'totlaCount' => count($newArr),
                    'items' => $newArr
                 ];

            }
    }

    public function getListFiles($skip = 0, $take, $domain, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['user', 'vocalist', 'collection', 'object', 'rating', 'total_downloads', 'likes', 'comment', 'number_files', 'categories'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes');
                $objectData = [];
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
            ->map(function ($item) use ($domain, $type, $objectData) {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $data['ImageFile'] =  ($item->object[0]->key != null)? $domain . $this->imageBuket . $item->object[0]->key:null;;
                $data['aproved'] = $item->aproved;
                $data['totale_size'] = $item->totale_size;
                $data['likes_count'] = $item->likes_count;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                $data['trailer_url'] = $item->trailer_url;
                $data['views'] = $item->views;
                $data['rating'] = $item->rating;
                $data['total_downloads'] = $item->total_downloads;
                $data['description'] = $item->description;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i:s', $date);
                $data['updated_at'] =  substr(substr($item->updated_at, strpos($item->updated_at, 'T')), 0, strpos(substr($item->updated_at, strpos($item->updated_at, 'T')), " "));
                $data['categories'] = $item->categories;
                $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $ss['objectFiles'] = $item->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type&& $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type&& $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }
                $data['objectFiles'] = $objectData;
                if(!empty($item->vocalist)){
                   $data['vocalistId'] = $item->vocalist->id;
                   $data['vocalistName'] = $item->vocalist->name;
                   $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
               }else{
                   $data['vocalistId'] = null;
                   $data['vocalistName'] = null;
                   $data['vocalistImage'] = null;
               }
               if(!empty($item->collection)){
                   $data['collectionId'] = $item->collection->id;
                   $data['collectionName'] = $item->collection->name;
                   $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
               }else{
                   $data['collectionId'] = null;
                   $data['collectionName'] = null;
                   $data['collectionImage'] = null;
               }
                return $data;
            }),

        ];
    }

    // Filter 
    public function filter($id, $skip = 0, $take, $tags = null, $domain, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = Files::whereHas('categories', function ($q) use ($id) {
            $q->where('id', $id)->where('is_deleted', 0);
        });
        // $tags = [5,6,92];
        if (!is_null($tags)) {
            $query = $query->join('tags_files', 'file.id', '=', 'tags_files.file_id')
                ->whereIn('tags_files.tag_id', $tags)
                ->distinct();
        }

        $files = QueryBuilder::for($query)
            ->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
            ->allowedIncludes(['user', 'vocalist', 'collection', 'object', 'rating', 'total_downloads', 'likes', 'comment', 'number_files', 'categories'])
            ->allowedFilters(['category_id', 'aproved', 'title'])
            ->where('files.aproved', 0)
            ->whereHas('object',function ($objectModel) use($type) {
                $objectModel->where('type_audio', $type);
            })->where('files.is_deleted', 0)
            ->orderBy('files.id', 'desc')
            ->withCount('likes');
            $objectData = [];
            return [
            'totalCount' => $files->count(),
            'items' => $files->take($take)->skip($skip)->get()
                    ->map(function ($item) use ($domain, $tags, $objectData, $type) {
                        if ($tags == null) {
                            $data['id'] = $item->id;
                        } else {
                            $data['id'] = $item->file_id;
                        }
                        $data['title'] = $item->title;
                        $data['ImageFile'] =  ($item->object[0]->key != null)? $domain . $this->imageBuket . $item->object[0]->key:null;;
                        $data['totale_size'] = $item->totale_size;
                        $data['likes_count'] = $item->likes_count;
                        $data['trailer_url'] = $item->trailer_url;
                        $data['views'] = $item->views;
                        $data['rating'] = $item->rating;
                        $data['type'] = $item->type;
                        $data['file_id'] = $item->file_id;
                        $data['tag_id'] = $item->tag_id;
                        $data['collection_id'] = $item->collection_id;
                        $data['vocalist_id'] = $item->vocalist_id;
                        $data['total_downloads'] = $item->total_downloads;
                        $data['description'] = $item->description;
                        $data['created_at'] = $item->created_at;
                        $data['updated_at'] = $item->updated_at;
                        $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>   ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                        ];
                        $data['categories'] = $item->categories;
                        $ss['objectFiles'] = $item->object;
                        foreach($ss['objectFiles'] as $obj){
                            if($obj->type_audio == $type && $type == 1){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                            if($obj->type_audio == $type && $type == 2){
                                $ob['id'] = $obj->id;
                                $ob['name'] = $obj->name;
                                $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                                $ob['size'] = $obj->size;
                                $ob['type_audio'] = $obj->type_audio;
                                array_push($objectData, $ob);
                                $ob =[];
                            }
                        }
                        $data['objectFiles'] = $objectData;
                        if(!empty($item->vocalist)){
                           $data['vocalistId'] = $item->vocalist->id;
                           $data['vocalistName'] = $item->vocalist->name;
                           $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
                       }else{
                           $data['vocalistId'] = null;
                           $data['vocalistName'] = null;
                           $data['vocalistImage'] = null;
                       }
                       if(!empty($item->collection)){
                           $data['collectionId'] = $item->collection->id;
                           $data['collectionName'] = $item->collection->name;
                           $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
                       }else{
                           $data['collectionId'] = null;
                           $data['collectionName'] = null;
                           $data['collectionImage'] = null;
                       }
                        return  $data;
                    }),
        ];
    }

    //Repo to get all file sorting by views
    public function getListFilesSortView($skip = 0, $take, $domain, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['user', 'vocalist', 'collection', 'object', 'rating', 'total_downloads', 'likes', 'comment', 'number_files', 'categories'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('views', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes');
                $objectData = [];
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
            
            ->map(function ($item) use ($domain, $type, $objectData) {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $data['ImageFile'] =  ($item->object[0]->key != null)? $domain . $this->imageBuket . $item->object[0]->key:null;;
                $data['aproved'] = $item->aproved;
                $data['totale_size'] = $item->totale_size;
                $data['likes_count'] = $item->likes_count;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                $data['trailer_url'] = $item->trailer_url;
                $data['views'] = $item->views;
                $data['rating'] = $item->rating;
                $data['total_downloads'] = $item->total_downloads;
                $data['description'] = $item->description;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i:s', $date);
                $data['updated_at'] =  substr(substr($item->updated_at, strpos($item->updated_at, 'T')), 0, strpos(substr($item->updated_at, strpos($item->updated_at, 'T')), " "));
                $data['categories'] = $item->categories;
                $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $ss['objectFiles'] = $item->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type&& $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type&& $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }
                $data['objectFiles'] = $objectData;
                if(!empty($item->vocalist)){
                   $data['vocalistId'] = $item->vocalist->id;
                   $data['vocalistName'] = $item->vocalist->name;
                   $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
               }else{
                   $data['vocalistId'] = null;
                   $data['vocalistName'] = null;
                   $data['vocalistImage'] = null;
               }
               if(!empty($item->collection)){
                   $data['collectionId'] = $item->collection->id;
                   $data['collectionName'] = $item->collection->name;
                   $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
               }else{
                   $data['collectionId'] = null;
                   $data['collectionName'] = null;
                   $data['collectionImage'] = null;
               }
                
                return $data;
            }),

        ];
    }
    
    //Repo to get all file sorting by Ratings
    public function getListFilesSortRating($skip = 0, $take, $domain, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['user', 'vocalist', 'collection', 'object', 'rating', 'total_downloads', 'likes', 'comment', 'number_files', 'categories'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('rating', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes');
                $objectData = [];
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
            
            ->map(function ($item) use ($domain, $type, $objectData) {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $data['ImageFile'] =  ($item->object[0]->key != null)? $domain . $this->imageBuket . $item->object[0]->key:null;;
                $data['aproved'] = $item->aproved;
                $data['totale_size'] = $item->totale_size;
                $data['likes_count'] = $item->likes_count;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                $data['trailer_url'] = $item->trailer_url;
                $data['views'] = $item->views;
                $data['rating'] = $item->rating;
                $data['total_downloads'] = $item->total_downloads;
                $data['description'] = $item->description;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i:s', $date);
                $data['updated_at'] =  substr(substr($item->updated_at, strpos($item->updated_at, 'T')), 0, strpos(substr($item->updated_at, strpos($item->updated_at, 'T')), " "));
                $data['categories'] = $item->categories;
                $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $ss['objectFiles'] = $item->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type&& $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type&& $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }
                $data['objectFiles'] = $objectData;
                if(!empty($item->vocalist)){
                   $data['vocalistId'] = $item->vocalist->id;
                   $data['vocalistName'] = $item->vocalist->name;
                   $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
               }else{
                   $data['vocalistId'] = null;
                   $data['vocalistName'] = null;
                   $data['vocalistImage'] = null;
               }
               if(!empty($item->collection)){
                   $data['collectionId'] = $item->collection->id;
                   $data['collectionName'] = $item->collection->name;
                   $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
               }else{
                   $data['collectionId'] = null;
                   $data['collectionName'] = null;
                   $data['collectionImage'] = null;
               }
                return $data;
            }),

        ];
    }
    
    //Repo to get all file sorting by Downloads
    public function getListFilesSortDownload($skip = 0, $take, $domain, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['user', 'vocalist', 'collection', 'object', 'rating', 'total_downloads', 'likes', 'comment', 'number_files', 'categories'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('total_downloads', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes');
                $objectData = [];
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
            
            ->map(function ($item) use ($domain, $type, $objectData) {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $data['ImageFile'] =  ($item->object[0]->key != null)? $domain . $this->imageBuket . $item->object[0]->key:null;;
                $data['aproved'] = $item->aproved;
                $data['totale_size'] = $item->totale_size;
                $data['likes_count'] = $item->likes_count;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
                $data['trailer_url'] = $item->trailer_url;
                $data['views'] = $item->views;
                $data['rating'] = $item->rating;
                $data['total_downloads'] = $item->total_downloads;
                $data['description'] = $item->description;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i:s', $date);
                $data['updated_at'] =  substr(substr($item->updated_at, strpos($item->updated_at, 'T')), 0, strpos(substr($item->updated_at, strpos($item->updated_at, 'T')), " "));
                $data['categories'] = $item->categories;
                $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                ];
                $ss['objectFiles'] = $item->object;
                foreach($ss['objectFiles'] as $obj){
                    if($obj->type_audio == $type&& $type == 1){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                    if($obj->type_audio == $type&& $type == 2){
                        $ob['id'] = $obj->id;
                        $ob['name'] = $obj->name;
                        $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                        $ob['size'] = $obj->size;
                        $ob['type_audio'] = $obj->type_audio;
                        array_push($objectData, $ob);
                        $ob =[];
                    }
                }
                $data['objectFiles'] = $objectData;
                if(!empty($item->vocalist)){
                   $data['vocalistId'] = $item->vocalist->id;
                   $data['vocalistName'] = $item->vocalist->name;
                   $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
               }else{
                   $data['vocalistId'] = null;
                   $data['vocalistName'] = null;
                   $data['vocalistImage'] = null;
               }
               if(!empty($item->collection)){
                   $data['collectionId'] = $item->collection->id;
                   $data['collectionName'] = $item->collection->name;
                   $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
               }else{
                   $data['collectionId'] = null;
                   $data['collectionName'] = null;
                   $data['collectionImage'] = null;
               }
                return $data;
            }),

        ];
    }

    // public function ()
    
    //Repo for get random files by category id
    public function advanceSearch($skip, $take, $domain, $type, $search, $vocalist_id, $collection_id, $category_id)
    {
        $dataFile = [];
        $objectModel = File_objects::where('is_deleted', 0);
        $response = Files::with('ratings', 'collection', 'vocalist', 'categories', 'user', 'object', 'objectFiles',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'number_files', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes');
        if(!is_null($search))
            $response->where('title', 'like', '%'.$search.'%');
            
        if(!is_null($vocalist_id))
        $response->where('vocalist_id', $vocalist_id);


        if(!is_null($collection_id))
            $response->where('collection_id', $collection_id);
            
        if(!is_null($category_id))
            $response->where('category_id', $category_id);

        $objectData =[];
        return[
            'total_count' => $response->get()->count(),
            'items' => $this->getMaping($objectData, $type, $domain, $response->take($take)->skip($skip)->get())
        ];
    }
    public function getMaping($objectData, $type, $domain, $datas)
    {
      return  $datas->map(function ($item) use ($domain, $type, $objectData) {
            $data['id'] = $item->id;
            $data['title'] = $item->title;
            $dd['ImageFile'] =  $item->object;
            foreach($item->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
            $data['likes_count'] = $item->likes_count;
            $data['totale_size'] = $item->totale_size;
            $data['trailer_url'] = $item->trailer_url;
            $data['views'] = $item->views;
            $data['rating'] = $item->rating;
            $data['total_downloads'] = $item->total_downloads;
            $data['description'] = $item->description;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['category_id'] = $item->category_id;
            $data['collection_id'] = $item->collection_id;
            $data['vocalist_id'] = $item->vocalist_id;
            $data['created_at'] = $item->created_at;
            $data['updated_at'] = $item->updated_at;
            $data['categories'] = $item->categories;
            $data['user'] =[
                'id' =>  $item->user->id,
                'full_name' =>  $item->user->full_name,
                'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
            ];
            $data['downloads'] = $item->downloads;
            $data['likes'] = $item->likes;
            $data['comments'] =  $item->comments->map(function($item) use($domain){
                $comment['id'] = $item->id;
                $comment['comment'] = $item->comment;
                $comment['comment_id'] = $item->comment_id;
                $comment['user_id'] = $item->user_id;
                $comment['created_at'] = $item->created_at;
                $comment['updated_at'] = $item->updated_at;
                $comment['users_id'] = $item->users->id;
                $comment['users_full_name'] = $item->users->full_name;
                $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                $comment['replies'] = $item->replies->map(function($item) use($domain){
                    $comment['id'] = $item->id;
                    $comment['comment'] = $item->comment;
                    $comment['comment_id'] = $item->comment_id;
                    $comment['user_id'] = $item->user_id;
                    $comment['created_at'] = $item->created_at;
                    $comment['updated_at'] = $item->updated_at;
                    $comment['users_id'] = $item->users->id;
                    $comment['users_full_name'] = $item->users->full_name;
                    $comment['users_image'] = ($item->users->image != null)? $domain . $this->imageBuket.$item->users->image:null;
                    return $comment;
                });
                return $comment;
            });
            $data['tags'] = $item->tags;
            $ss['objectFiles'] = $item->object;
            foreach($ss['objectFiles'] as $obj){
                if($obj->type_audio == $type && $type == 1){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
                if($obj->type_audio == $type && $type == 2){
                    $ob['id'] = $obj->id;
                    $ob['name'] = $obj->name;
                    $ob['key'] = ($obj->key != null)? $domain . $this->audioBuket . $obj->key:null;
                    $ob['size'] = $obj->size;
                    $ob['type_audio'] = $obj->type_audio;
                    array_push($objectData, $ob);
                    $ob =[];
                }
            }
            $data['objectFiles'] = $objectData;
            if(!empty($item->vocalist)){
               $data['vocalistId'] = $item->vocalist->id;
               $data['vocalistName'] = $item->vocalist->name;
               $data['vocalistImage'] = ($item->vocalist->key != null)? $domain . $this->imageBuket . $item->vocalist->key:null;
           }else{
               $data['vocalistId'] = null;
               $data['vocalistName'] = null;
               $data['vocalistImage'] = null;
           }
           if(!empty($item->collection)){
               $data['collectionId'] = $item->collection->id;
               $data['collectionName'] = $item->collection->name;
               $data['collectionImage'] = ($item->collection->image != null)? $domain . $this->imageBuket . $item->collection->image:null;
           }else{
               $data['collectionId'] = null;
               $data['collectionName'] = null;
               $data['collectionImage'] = null;
           }
            return $data;
        }); 

    }
    
} 