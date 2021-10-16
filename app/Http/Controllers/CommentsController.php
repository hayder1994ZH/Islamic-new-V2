<?php

namespace App\Http\Controllers;

use App\Models\Files;
use App\Models\Comments;
use App\Helpers\Utilities;
use Illuminate\Http\Request;
use App\Repository\FilesRepository;
use App\Repository\CommentsRepository;

class CommentsController extends Controller
{
    private $FilesRepository;
    private $CommentsRepository;
    public function __construct()
    {
        $this->CommentsRepository = new CommentsRepository(new Comments());
        $this->FilesRepository = new FilesRepository(new Files());
        $this->middleware('role:Admin,owner', ['only' => ['update', 'destroy', 'index']]);
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
        $response = $this->CommentsRepository->getlist($take, $skip,['users','files.object']);
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
            $data['filesId'] = $item->files->id;
            $data['filesTitle'] = $item->files->title;
            $data['filetTotale_size'] = $item->files->totale_size;
            $data['filetTotal_downloads'] = $item->files->total_downloads;
            $data['filetCreated_at'] = $item->files->created_at;
            $data['objectFiles'] = $item->files->object->map(function ($item) use($domain){
                if($item->type_audio == 1){
                    $object['id'] = $item->id;
                    $object['name'] = $item->name;
                    $object['key'] = ($item->key != null)? $domain . $this->vedioBuket . $item->key:null;
                    $object['size'] = $item->size;
                    $object['type_audio'] = $item->type_audio;
                }
                if( $item->type_audio == 2){
                    $object['id'] = $item->id;
                    $object['name'] = $item->name;
                    $object['key'] = ($item->key != null)? $domain . $this->audioBuket . $item->key:null;
                    $object['size'] = $item->size;
                    $object['type_audio'] = $item->type_audio;
                }
                return $object;
            });
            return $data;
        });
        return Utilities::wrap($response, 200);
    }

    //Add Comments
    public function store(Request $request)
    {
        $data = $request->validate([
            'comment' => 'required|string',
            'file_id' => 'required|integer|exists:files,id',
            'comment_id' => 'nullable'
        ]);
        $data['user_id'] = auth()->user()->id;
        $this->CommentsRepository->create($data);
        return Utilities::wrap(['message' => 'Comment Created'], 200);
    }

    //Add Comments
    public function update(Request $request, $id)
    {
        $comments = $request->validate([
            'comment' => 'required|string'
        ]);
        Comments::where('id', $id)->where('is_deleted', 0)->firstOrFail();
        $response = $this->CommentsRepository->update($id, $comments);
        return Utilities::wrap(['message' => 'comment updted successfully'],$response['code']);
    }
   

    //Delete Comments
    public function destroy($id)
    {
        $FilesModel = Comments::where('id', $id)->firstOrFail();
        $response = $this->CommentsRepository->delete($FilesModel);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    } 

    public function commentsById(Request $request, $id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->CommentsRepository->commentsById($id, $take, $skip, $domain);
        return $response;
    }



    
}
