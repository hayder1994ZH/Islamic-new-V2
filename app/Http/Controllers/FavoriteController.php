<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Helpers\Utilities;
use App\Repository\FavoriteRepository;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    private $FavoriteRepository;
    public function __construct()
    {
        $this->FavoriteRepository = new FavoriteRepository(new Favorite());
        $this->middleware('role:owner', ['only' => [ 'destroy']]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file_id' => 'required|exists:files,id' 
        ]);
        $data['user_id'] = auth()->user()->id;
        $check = Favorite::where('user_id', $data['user_id'])
                            ->where('file_id', $data['file_id'])
                            ->first();
        if($check) {
            $check->delete();
            return Utilities::wrap(['message' => 'file deleted from list'], 200);
        } else {
            $this->FavoriteRepository->create($data);
            return Utilities::wrap(['message' => 'file inserted to list'], 200);
        }
    }

    public function myFavorite(Request $request)
    {
        $type = $request->validate([
            'type' => 'required|in:1,2' 
        ]);
        // $favorite = auth()->user()->load('favorite.file.user', 'favorite.file.categories')->favorite;
        $response = $this->FavoriteRepository->getList($request->get('host'), $type['type']);
        return $response;
    }

    public function destroy()
    {
        $id = auth()->user()->id;
        Favorite::where('user_id', $id)->delete();
        return response()->json([
            "message" => "Deleted"
        ]);
    }

}
