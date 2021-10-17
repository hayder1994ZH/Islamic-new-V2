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
    public function getFile($id, $type)
    {
        return Files::where('id', $id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
                                        ->where('is_deleted', 0)
                                        ->where('aproved', 0)
                                        ->withCount('likes')
                                        ->whereHas('object',function ($objectModel) use($type) {
                                            $objectModel->where('type_audio', $type);
                                        })
                                        ->withCount('likes')
                                        ->withCount('objectFiles')
                                        ->firstOrFail();
    }
    //Repo to Get all files
    public function getPlaylist($domain, $take, $skip, $type)
    {
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
                
                $data['rating'] = $item->file->rating;
                $data['description'] = $item->file->description;
                $data['created_at'] = $item->file->created_at;
                $data['updated_at'] = $item->file->updated_at;
                $data['category_id'] = $item->file->category_id;
                $data['collection_id'] = $item->collection_id;
                $data['vocalist_id'] = $item->vocalist_id;
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
    public function getFilebyId($id, $type)
    {
        return  Files::where('id', $id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
            ->where('is_deleted', 0)
            ->where('aproved', 0)
            ->whereHas('object',function ($objectModel) use($type) {
                $objectModel->where('type_audio', $type);
            })
            ->withCount('likes')
            ->withCount('objectFiles')
            ->get();//Edited
        
    }
    //Repo for file vocalist id
    public function getByVocalistId($id, $take, $skip, $type)
    {
        $result = Files::where('vocalist_id', $id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        return[
            'total_count' => $result->count(),
            'items' => $result->take($take)->skip($skip)->get()
        ];//Edited
        
    }
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
    public function getRandomFilesByCategoryId($type,$category_id, $vocalist_id)
    {
        $dataFile = [];
        $resultByVocalist = Files::where('vocalist_id', $vocalist_id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($q) use($type) {
            $q->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        $totalRowsV =  $resultByVocalist->count() - 1;
        $take = 5;
        $skip = $totalRowsV > 5 ? mt_rand(0, $totalRowsV - 5) : 0;
        $fileVoca = $resultByVocalist->take($take)->skip($skip)->get();
        foreach($fileVoca as $VFile){
          array_push($dataFile, $VFile);
        }
        $result = Files::where('category_id', $category_id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($q2) use($type) {
            $q2->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        $take = 20;
        $totalRows =  $result->count() - 1;
        $skip = $totalRows > 20 ? mt_rand(0, $totalRows - 20) : 0;
        return $result->take($take)->skip($skip)->get();
    }
    //Repo for file vocalist id and category id
    public function getByVocalistIdAndCategoryId($id, $take, $skip, $type,$category_id)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $result = Files::where('vocalist_id', $id)->where('category_id', $category_id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        return[
            'total_count' => $result->count(),
            'items' => $result->take($take)->skip($skip)->get()
        ];//Edited
    }
    //Repo for vocalist id
    public function getListByVocalistId($id, $take, $skip)
    {
        $result = Files::where('vocalist_id', $id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->withCount('likes')
        ->withCount('objectFiles');
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
        ];//Edited
    }
    //Repo for vocalist id
    public function getByCollectionId($id, $take, $skip, $type)
    {
        $result = Files::where('collection_id', $id)->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        return[
          'total_count' => $result->count(),
          'items' => $result->take($take)->skip($skip)->get()
        ];//Edited
        
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
    public function getAllData($skip = 0, $take, $tags,$category_id, $search, $type)
    {
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
                if($check->exists()){
                    $query = $query->join('tags_files', 'files.id', '=', 'tags_files.file_id')
                             ->whereIn('tags_files.tag_id', $tags);
                }
        } 
        if (!is_null($search)) {
            $categories = Categories::where('name', 'like', '%'.$search.'%')->get()->map(function ($item){
                 return  $data['id']= $item->id;
            });
            $query->orWhere('title', 'like', '%'.$search.'%')
            ->orwhereIn('vocalist_id', $vocalist)
            ->orwhereIn('category_id', $categories);
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
            ->with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
            ->whereHas('object',function ($objectModel) use($type) {
               return $objectModel->where('type_audio', $type);
            })
            ->orderBy('files.id', 'desc')
            ->select('files.*')
            ->withCount('likes')
            ->withCount('objectFiles');
            return [
                'totlaCount' => $query->count(),
                'items' => $query->take($take)->skip($skip)->get()
            ];
    }
    public function getListFiles($skip = 0, $take, $type)
    {
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes')
                ->withCount('objectFiles');
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
        ];//Edited
    }
    // Filter 
    public function filter($id, $skip = 0, $take, $tags = null, $type)
    {
        $query = Files::whereHas('categories', function ($q) use ($id) {
            $q->where('id', $id)->where('is_deleted', 0);
        });
        if (!is_null($tags)) {
            $query = $query->join('tags_files', 'file.id', '=', 'tags_files.file_id')
                ->whereIn('tags_files.tag_id', $tags)
                ->distinct();
        }
        $files = QueryBuilder::for($query)
            ->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
            ->allowedIncludes(['ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags'])
            ->allowedFilters(['category_id', 'aproved', 'title'])
            ->where('files.aproved', 0)
            ->whereHas('object',function ($objectModel) use($type) {
                $objectModel->where('type_audio', $type);
            })->where('files.is_deleted', 0)
            ->orderBy('files.id', 'desc')
            ->withCount('likes')
            ->withCount('objectFiles');
            return [
            'totalCount' => $files->count(),
            'items' => $files->take($take)->skip($skip)->get()
            ];//Edited
    }

    //Repo to get all file sorting by views
    public function getListFilesSortView($skip = 0, $take, $type)
    {
        $objectModel = File_objects::where('is_deleted', 0);
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('views', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes')
                ->withCount('objectFiles');
                $objectData = [];
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
        ]; //Edited
    }
    
    //Repo to get all file sorting by Ratings
    public function getListFilesSortRating($skip = 0, $take, $type)
    {
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('rating', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes')
                ->withCount('objectFiles');
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
        ]; //Edited       
        
    }
    
    //Repo to get all file sorting by Downloads
    public function getListFilesSortDownload($skip = 0, $take, $type)
    {
        $query = QueryBuilder::for(Files::class);
        $query->allowedSorts(['total_downloads', 'views', 'created_at', 'id', 'rating'])
                ->allowedIncludes(['ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags'])
                ->allowedFilters(['category_id', 'title', 'description'])
                ->orderBy('total_downloads', 'desc')
                ->whereHas('object',function ($objectModel) use($type) {
                    $objectModel->where('type_audio', $type);
                })
                ->where('files.is_deleted', 0)
                ->orderBy('files.id', 'desc')
                ->select('files.*')
                ->withCount('likes')
                ->withCount('objectFiles');
        return [
            'totlaCount' => $query->count(),
            'items' => $query->take($take)->skip($skip)->get()
        ];//Edited        
        
    }

    // public function ()
    
    //Repo for get random files by category id
    public function advanceSearch($skip, $take, $type, $search, $vocalist_id, $collection_id, $category_id)
    {
        $response = Files::with('ratings', 'user', 'object',  'downloads', 'likes', 'comments.users', 'comments.replies.users', 'tags')
        ->where('is_deleted', 0)
        ->where('aproved', 0)
        ->whereHas('object',function ($objectModel) use($type) {
            $objectModel->where('type_audio', $type);
        })
        ->withCount('likes')
        ->withCount('objectFiles');
        if(!is_null($search))
            $response->where('title', 'like', '%'.$search.'%');
            
        if(!is_null($vocalist_id))
        $response->where('vocalist_id', $vocalist_id);


        if(!is_null($collection_id))
            $response->where('collection_id', $collection_id);
            
        if(!is_null($category_id))
            $response->where('category_id', $category_id);
        return[
            'total_count' => $response->get()->count(),
            'items' => $response->take($take)->skip($skip)->get()
        ];//Edited
    }
} 