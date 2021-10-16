<?php

namespace App\Http\Controllers;

use App\Models\Files;
use App\Models\Likes;
use App\Helpers\Utilities;
use Illuminate\Http\Request;
use App\Repository\FilesRepository;
use App\Repository\LikesRepository;

class LikesController extends Controller
{
    private $LikesRepository;
    private $id;
    public function __construct()
    {
        $this->LikesRepository = new LikesRepository(new Likes());
        $this->id = auth()->user()->id;
    }

    //Add Likes
    public function like(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'exists:files,id'
        ]);
        $check = Likes::where('user_id', $this->id)
                        ->where('file_id', $data['file_id'])
                        ->first();
        if($check) {
            $check->delete();
            $message = 'unliked';
        } else {
            $data['user_id'] = $this->id;
            $this->LikesRepository->create($data);
            $message = 'liked';
        }
        return Utilities::wrap(['message' => $message], 200);
    }
 
}
