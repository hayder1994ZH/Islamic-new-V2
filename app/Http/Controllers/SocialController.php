<?php

namespace App\Http\Controllers;

use App\Models\Social;
use App\Helpers\Utilities;
use App\Models\SocialLike;
use App\Models\TempRemove;
use App\Models\Temp_files;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repository\TempRepository;
use App\Repository\TempRemoveRepository;
use App\Repository\SocialRepository;
use Illuminate\Support\Facades\Auth;
use App\Repository\SocialLikeRepository;

class SocialController extends Controller
{
    private $SocialRepository;
    private $SocialLikeRepository;
    private $TempRepository;
    private $TempRemoveRepository;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->SocialLikeRepository = new SocialLikeRepository(new SocialLike());
        $this->SocialRepository = new SocialRepository(new Social());
        $this->middleware('role:Admin,owner', ['only' => ['getUnApproveSocial']]);
        $this->middleware('role:owner', ['only' => ['approve', 'destroy']]);
    }

    public function getAllSocialViews(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'type' => 'required|Integer',
            'take' => 'required|Integer'
        ]);
        $type = $request->type;
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->SocialRepository->getAllSocialView($take, $skip, $domain, $type);
        return Utilities::wrap($response, 200);
    }
    
    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'type' => 'required|Integer',
            'take' => 'required|Integer'
        ]);
        $type = $request->type;
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->SocialRepository->getAllSocial($take, $skip, $domain, $type);
        return Utilities::wrap($response, 200);
    }

    public function getUnApproveSocial(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->SocialRepository->getAllUnApproveSocial($take, $skip, $domain);
        return Utilities::wrap($response, 200);
    }

    public function getAllSocialFiles(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->SocialRepository->getAllSocialFile($take, $skip, $domain);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id)
    {
        $SocialViews = Social::with('user','comments')->withCount('likes')->where('id', $id)
        // ->where('approve', 1)
        ->firstOrFail();
        $domain = $request->get('host');
        $Social = Social::with('user','comments', 'like_social.user')->withCount('likes')->where('id', $id)
                        //   ->where('approve', 1)
                          ->where('is_deleted', 0)->get()
        ->map(function ($item) use ($domain){
            $data['id'] =$item->id;
            $data['title'] =$item->title  ;
            $data['views'] =$item->views  ;
            $data['description'] =$item->description  ;
            $data['post_file'] = ($item->post_file != null)? $domain . $this->socialBuket .$item->post_file:null  ;
            $data['user_id'] =$item->user->id  ;
            $data['user_full_name'] =$item->user->full_name  ;
            $data['user_image'] = ($item->user->image != null)?  $domain . $this->imageBuket . $item->user->image:null  ;
            $data['created_at'] =$item->created_at;
            $data['updated_at'] =$item->updated_at;
            $data['likes_count'] = $item->likes_count;
            $data['comments_count'] = count($item->comments);
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
        });
        $this->SocialRepository->update($id, ['views' => $SocialViews->views + 1]);
        return $Social[0];
    }

    public function store(Request $request)
    {
        ini_set('upload_max_filesize', '100000M');
        ini_set('post_max_size', '100000M');
        ini_set('max_input_time', 30000000);
        ini_set('max_execution_time', 30000000);
        $data = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'post_file' => 'nullable|file',
            'is_video' => 'required|integer|in:0,1,2',
        ]);
        $data['user_id'] = auth()->user()->id;

        if(array_key_exists('post_file', $data)){
            $target_dir = storage_path('app/public/');
            $random = Str::random(32);
            $_FILES["post_file"]["tmp_name"];
            $name = $_FILES["post_file"]["name"];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            
            $fullPath = Str::random(32)."." . $ext ;
            if(preg_match('/\p{Arabic}/u', $name)){
                $name =  $fullPath;
            }else{
                $name = str_replace('.'. $ext, '', $name);
                $name = preg_replace('/[^A-Za-z0-9_]/', '-', $name).'.'.$ext;
            }
            $fileName = $name;
            $target_file = $target_dir . $random . $fileName;        
            move_uploaded_file($_FILES["post_file"]["tmp_name"], $target_file);
            $file_path =  $random . $fileName;
            $data['post_file'] = $file_path;
            $size = $_FILES["post_file"]["size"];
            $data['size'] = $size;
            $data['buket'] = 'islamic_social';
            $this->TempRepository->create(['key' => $data['post_file'], 'buket' => $data['buket'],'table' => 'social' ]);
        }else{
            $data['s3'] = 1;  
        }
            
        $res = $this->SocialRepository->create($data);
        return Utilities::wrap($res['message'], 200);
    }

    public function update(Request $request, $id)
    {
        // $data = $request->validate([
        //     'title' => 'required|string',
        //     'description' => 'string',
        //     'post_file' => 'file',
        // ]);
        
        // $response = $this->SocialRepository->update($id, $data);
        // return Utilities::wrap(['message' => 'Social updted successfully'],$response['code']);
    }    
   
    public function destroy($id)
    {
        $Social = Social::where('id',$id)->firstOrFail();
        
            if($Social->post_file != null){
                $this->TempRemoveRepository->create(['key' => $Social->post_file, 'buket' => $Social->buket,'table' => 'socials' ]);
            }
            $response = $this->SocialRepository->delete($Social);
        return Utilities::wrap(['message' => $response['message']],  $response['code']);
    } 

    public function like(Request $request)
    {
        $data = $request->validate([
            'social_id' => 'required|exists:socials,id'
        ]);
        if(!Auth::check()){
            return Utilities::wrap(['message' => 'permission denid'], 200);
        }
        $check = SocialLike::where('user_id',auth()->user()->id)
                        ->where('social_id', $data['social_id'])
                        ->first();
        if($check) {
            $check->delete();
            $message = 'unliked';
        } else {
            $data['user_id'] = auth()->user()->id;
            $this->SocialLikeRepository->create($data);
            $message = 'liked';
        }
        return Utilities::wrap(['message' => $message], 200);
    }
    public function approve($id)
    {
        $this->SocialRepository->update($id, ['approve' => 1]);
        return Utilities::wrap(['message' => 'successfully Aproved Social'], 200);
    }

}


