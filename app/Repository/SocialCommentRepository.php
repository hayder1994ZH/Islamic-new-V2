<?php
namespace App\Repository;

use App\Models\Social;
use App\Models\SocialComment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

//                        <----------- Welcome To SocialCommentRepository Page ----------->

class SocialCommentRepository extends BaseRepository {

    //Repo to Get all SocialComment socials
    public function getSocialComment($skip, $take, $id)
    {
        $result =  SocialComment::where('socials_id', $id)->with('users')
                            ->orderBy("id", "desc");
        $totalCount = SocialComment::get();
        return  $resp = [
                    'items' => $result->skip($skip)->take($take)->get(),
                    'totalCount' => $totalCount->count()
                ];

    }

    //Base repo to get list item by id
   public function getList($take, $skip = 0, $relation){
    $model = QueryBuilder::for(SocialComment::class)
    ->with($relation)
    ->allowedFilters(['comment'])
    ->where('is_deleted', 0)
    ->allowedSorts(['id']);
    $modelCount = SocialComment::with($relation)->where('is_deleted', 0);
    return [
        'totlaCount' => $modelCount->get()->count(),
        'items' => $model->take($take)->skip($skip)->get()
        ];
    }

    public function SocialCommentById($id, $take, $skip = 0, $domain)
    {    
        $fail = Social::where('id', $id)->where('is_deleted',0)->first();
        if(!$fail){
            return [
                'totlaCount' => 0,
                'items' =>[]
            ];
        }
        $socials = QueryBuilder::for(SocialComment::where('social_id', $id))
            ->allowedSorts(['id'])
            ->allowedIncludes(['users', 'socials', 'replies'])
            ->allowedFilters(['commnet', 'user_id', 'social_id'])
            ->where('is_deleted',0);
        return [
            'totlaCount' => $socials->count(),
            'items' => $socials->take($take)->skip($skip)->get()
            ->map(function ($item) use ($domain) {
                $data['id'] =$item->id  ;
                $data['comment'] =$item->comment  ;
                $data['user_id'] =$item->user_id  ;
                $data['comment_id'] =$item->comment_id  ;
                $date = strtotime($item->created_at);
                $data['created_at'] = date('Y-m-d h:i', $date);
                $date = strtotime($item->updated_at);
                $data['updated_at'] = date('Y-m-d h:i', $date);
                $data['social_title'] =$item->socials->title;
                $data['social_description'] =$item->socials->description;
                $data['social_file'] =($item->socials->post_file != null)? $domain . $this->socialBuket . $item->socials->post_file:null;
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
