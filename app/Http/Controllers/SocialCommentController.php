<?php

namespace App\Http\Controllers;

use App\Models\Social;
use App\Models\SocialComment;
use App\Helpers\Utilities;
use Illuminate\Http\Request;
use App\Repository\SocialRepository;
use App\Repository\SocialCommentRepository;

class SocialCommentController extends Controller
{
    private $SocialRepository;
    private $SocialCommentRepository;
    public function __construct()
    {
        $this->SocialCommentRepository = new SocialCommentRepository(new SocialComment());
        $this->SocialRepository = new SocialRepository(new Social());
        $this->middleware('role:Admin,owner', ['only' => ['update', 'destroy']]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $domain = $request->get('host');
        $skip = $request->skip;
        $response = $this->SocialCommentRepository->getlist($take, $skip,['users','socials']);
        $response['items'] = $response['items']->map(function ($item) use ($domain) {
            $data['id'] = $item->id;
            $data['comment'] = $item->comment;
            $date = strtotime($item->created_at);
            $data['created_at'] = date('Y-m-d h:i:s', $date);
            $date = strtotime($item->updated_at);
            $data['updated_at'] = date('Y-m-d h:i:s', $date);
            $data['userId'] = $item->users->id;
            $data['nameUser'] = $item->users->full_name;
            $data['userImage'] =($item->users->image != null)? $domain.$this->imageBuket.$item->users->image:null;
            $data['socialId'] = $item->socials->id;
            $data['socialTitle'] = $item->socials->title;
            $data['social_file'] =($item->socials->post_file != null)? $domain.$this->socialBuket.$item->socials->post_file:null;
            $data['social_size'] = $item->socials->size;
            $data['socialCreated_at'] = $item->socials->created_at;
            return $data;
        });
        return Utilities::wrap($response, 200);
    }

    //Add SocialComment
    public function store(Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string',
            'social_id' => 'required|integer|exists:socials,id',
            'comment_id' => 'nullable'
        ]);
        $data['user_id'] = auth()->user()->id;
        $this->SocialCommentRepository->create($data);
        return Utilities::wrap(['message' => 'Comment Created'], 200);
    }

    //update SocialComment
    public function update(Request $request, $id)
    {
        $SocialComment = $request->validate([
            'comment' => 'required|string'
        ]);
        SocialComment::where('id', $id)->where('is_deleted', 0)->firstOrFail();
        $response = $this->SocialCommentRepository->update($id, $SocialComment);
        return Utilities::wrap(['message' => 'comment updted successfully'],$response['code']);
    }

    //Delete SocialComment
    public function destroy($id)
    {
        $SocialModel = SocialComment::where('id', $id)->firstOrFail();
        $response = $this->SocialCommentRepository->delete($SocialModel);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    } 

    public function show(Request $request, $id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->SocialCommentRepository->SocialCommentById($id, $take, $skip, $domain);
        return $response;
    }



    
}
