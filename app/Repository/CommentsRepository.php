<?php
namespace App\Repository;

use App\Models\Comments;
use App\Models\Files;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

//                        <----------- Welcome To CommentsRepository Page ----------->

class CommentsRepository extends BaseRepository {

    //Repo to Get all Comments files
    public function getComments($skip, $take, $id)
    {
        $result =  Comments::where('files_id', $id)->with('users')
                            ->orderBy("id", "desc");
        $totalCount = Comments::get();
        return  $resp = [
                    'items' => $result->skip($skip)->take($take)->get(),
                    'totalCount' => $totalCount->count()
                ];

    }

    //Base repo to get list item by id
   public function getList($take, $skip = 0, $relation){
    $model = QueryBuilder::for(Comments::class)
    ->with($relation)
    ->allowedFilters(['comment'])
    ->where('is_deleted', 0)
    ->allowedSorts(['id']);
    $modelCount = Comments::with($relation)->where('is_deleted', 0);

    return [
        'totlaCount' => $modelCount->get()->count(),
        'items' => $model->take($take)->skip($skip)->get()
        ];
    }


    public function commentsById($id, $take, $skip = 0, $domain)
    {    
        $fail = Files::where('id', $id)->where('is_deleted',  0)->first();
        if(!$fail){
            return [
                'totlaCount' => 0,
                'items' =>[]
            ];
        }
        $files = QueryBuilder::for(Comments::where('file_id', $id))
            ->allowedSorts(['id'])
            ->allowedIncludes(['users', 'files.object', 'replies'])
            ->allowedFilters(['comments', 'user_id', 'file_id'])
            ->where('comment_id',  null)
            ->where('is_deleted',  0);
        return [
            'totlaCount' => $files->count(),
            'items' => $files->take($take)->skip($skip)->get()
            ->map(function ($item) use ($domain) {
                $data['id'] =$item->id  ;
                $data['comment'] =$item->comment  ;
                $data['user_id'] =$item->user_id  ;
                $data['comment_id'] =$item->comment_id  ;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i', $date);
                $date = strtotime($item->updated_at);
                $data['updated_at'] = date('Y-m-d h:i', $date);
                $data['file_title'] =$item->files->title;
                $data['file_description'] =$item->files->description;
                $aa['filesObject'] =$item->files->object->map(function($item) use ($domain){
                    if($item->type_audio == 0){
                        return $ss['ss'] = ($item->key != null)? $domain . $this->imageBuket . $item->key . $this->imageSize:null;
                    }
                     
                });
                $data['file_image'] =($aa['filesObject'][0] == null)?$aa['filesObject'][0]:$aa['filesObject'][1];
                $data['created_at'] =$item->created_at  ;
                $data['updated_at'] =$item->updated_at  ;
                $data['users_id'] =$item->users['id'];
                $data['users_full_name'] =$item->users['full_name'];
                $data['users_image'] =($item->users['image'] != null)? $domain . $this->imageBuket . $item->users['image']:null;
                $data['replies'] =$item->replies  ;
                return $data;
            } )
        ];
    }

}

//                                   <----------- Thank You ----------->
