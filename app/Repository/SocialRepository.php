<?php
namespace App\Repository;

use App\Models\Social;
use App\Models\SocialLike;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class SocialRepository extends BaseRepository {

    public function getAllSocial($take, $skip, $domain, $type){

        if($type == 1){
            $dataType = [1];
        }else{
            $dataType = [0,2];
        }
        $result = Social::with('user','comments', 'like_social.user')->withCount('likes')
        // ->where('approve', 1)
        ->where('s3', 1)
        ->where('is_deleted', 0)
        ->whereIn('is_video', $dataType)
        ->orderBy("id", "desc");
        $totalCount = Social::with('user','comments')->where('is_video', $type)->withCount('likes')
                            // ->where('approve', 1)
                            // ->where('s3', 1)
                            ->where('is_deleted', 0)->get();

        return [
            'totalCount' => $totalCount->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain){
                    $data['id'] =$item->id;
                    $data['title'] =$item->title;
                    $data['views'] =$item->views;
                    $data['likes_count'] = $item->likes_count;
                    
                    if (Auth::guard('api')->check()) {
                        $like = SocialLike::where('social_id', $item->id)
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
                    $data['comments_count'] = count($item->comments);
                    $data['description'] =$item->description;
                    $data['post_file'] = ($item->post_file != null)? $domain . $this->socialBuket .$item->post_file:null  ;
                    $data['user_id'] =$item->user->id;
                    $data['user_full_name'] =$item->user->full_name  ;
                    $data['user_image'] = ($item->user->image != null)?  $domain . $this->imageBuket . $item->user->image:null  ;
                    $data['created_at'] =$item->created_at;
                    $data['updated_at'] =$item->updated_at;
                    $data['likes'] =$item->like_social->map(function($item) use($domain){
                        $like['id'] = $item->user->id;
                        $like['full_name'] = $item->user->full_name;
                        $like['image'] = ($item->user->image != null)? $domain . $this->imageBuket.$item->user->image:null;
                        return $like;
                    });
                    $data['comments'] =$item->comments->map(function($item) use($domain){
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
                    $data['is_video'] =$item->is_video  ;
                    $data['s3'] =$item->s3  ;

                    return $data;
            })
        ];
    }
    
    public function getAllSocialView($take, $skip, $domain, $type){

        if($type == 1){
            $dataType = [1];
        }else{
            $dataType = [0,2];
        }
        $result = Social::with('user','comments', 'like_social.user')->withCount('likes')
        // ->where('approve', 1)
        ->where('s3', 1)
        ->where('is_deleted', 0)
        ->orderby('views', 'desc')
        ->whereIn('is_video', $dataType)
        ->orderBy("id", "desc");
        $totalCount = Social::with('user','comments')->orderBy("id", "desc")->where('is_video', $type)->withCount('likes')
                            // ->where('approve', 1)
                            // ->where('s3', 1)
                            ->where('is_deleted', 0)->get();
        return [
            'totalCount' => $totalCount->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain){
                    $data['id'] =$item->id;
                    $data['title'] =$item->title;
                    $data['views'] =$item->views;
                    $data['likes_count'] = $item->likes_count;
                    $data['comments_count'] = count($item->comments);
                    $data['description'] =$item->description;
                    $data['post_file'] = ($item->post_file != null)? $domain . $this->socialBuket .$item->post_file:null  ;
                    $data['user_id'] =$item->user->id;
                    $data['user_full_name'] =$item->user->full_name  ;
                    $data['user_image'] = ($item->user->image != null)?  $domain . $this->imageBuket . $item->user->image:null  ;
                    $data['created_at'] =$item->created_at;
                    $data['updated_at'] =$item->updated_at;
                    $data['likes'] =$item->like_social->map(function($item) use($domain){
                        $like['id'] = $item->user->id;
                        $like['full_name'] = $item->user->full_name;
                        $like['image'] = ($item->user->image != null)? $domain . $this->imageBuket.$item->user->image:null;
                        return $like;
                    });
                    $data['comments'] =$item->comments->map(function($item) use($domain){
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
                    $data['is_video'] =$item->is_video  ;
                    $data['s3'] =$item->s3  ;

                    return $data;
            })
        ];
    }
    
    public function getAllUnApproveSocial($take, $skip, $domain){

        $result = Social::with('user', 'comments', 'like_social.user')->withCount('likes')
        ->where('is_deleted', 0)
        ->orderBy("id", "desc");
        $totalCount = Social::with('user','comments')->withCount('likes')->withCount('comments')
                            ->where('is_deleted', 0)->get();
        return [
            'totalCount' => $totalCount->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain){
                    $data['id'] =$item->id;
                    $data['title'] =$item->title  ;
                    $data['views'] =$item->views;
                    $data['likes_count'] = $item->likes_count;
                    $data['comments_count'] = count($item->comments);
                    $data['description'] =$item->description  ;
                    $data['post_file'] = ($item->post_file != null)? $domain . $this->socialBuket .$item->post_file:null  ;
                    $data['user_id'] =$item->user->id;
                    $data['user_full_name'] =$item->user->full_name  ;
                    $data['user_image'] = ($item->user->image != null)?  $domain . $this->imageBuket . $item->user->image:null  ;
                    $data['created_at'] =$item->created_at;
                    $data['updated_at'] =$item->updated_at;
                    $data['likes'] =$item->like_social->map(function($item) use($domain){
                        $like['id'] = $item->user->id;
                        $like['full_name'] = $item->user->full_name;
                        $like['image'] = ($item->user->image != null)? $domain . $this->imageBuket.$item->user->image:null;
                        return $like;
                    });
                    $data['comments'] =$item->comments->map(function($item) use($domain){
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
                    $data['is_video'] =$item->is_video  ;
                    $data['s3'] =$item->s3  ;
                    return $data;
            })
        ];
    }
    public function getAllSocialFile($take, $skip, $domain){

        $result = Social::with('user', 'comments', 'like_social.user')->withCount('likes')
        ->where('is_deleted', 0)
        ->where('s3', 1)
        ->orderBy("id", "desc");
        $totalCount = Social::with('user','comments')->withCount('likes')->withCount('comments')
                            ->where('is_deleted', 0)->get();
        return [
            'totalCount' => $totalCount->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain){
                    $data['id'] =$item->id;
                    $data['title'] =$item->title  ;
                    $data['views'] =$item->views;
                    $data['likes_count'] = $item->likes_count;
                    $data['comments_count'] = count($item->comments);
                    $data['description'] =$item->description  ;
                    $data['post_file'] = ($item->post_file != null)? $domain . $this->socialBuket .$item->post_file:null  ;
                    $data['user_id'] =$item->user->id;
                    $data['user_full_name'] =$item->user->full_name  ;
                    $data['user_image'] = ($item->user->image != null)?  $domain . $this->imageBuket . $item->user->image:null  ;
                    $data['created_at'] =$item->created_at;
                    $data['updated_at'] =$item->updated_at;
                    $data['likes'] =$item->like_social->map(function($item) use($domain){
                        $like['id'] = $item->user->id;
                        $like['full_name'] = $item->user->full_name;
                        $like['image'] = ($item->user->image != null)? $domain . $this->imageBuket.$item->user->image:null;
                        return $like;
                    });
                    $data['comments'] =$item->comments->map(function($item) use($domain){
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
                    $data['is_video'] =$item->is_video  ;
                    $data['s3'] =$item->s3  ;
                    return $data;
            })
        ];
    }
}