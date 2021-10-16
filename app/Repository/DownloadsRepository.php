<?php
namespace App\Repository;

use App\Models\Downloads;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

//                        <----------- Welcome To DownloadsRepository Page ----------->

class DownloadsRepository extends BaseRepository {

    public function index($domain)
    {
        $user_id = auth()->user()->id;
        $downloads = Downloads::where('user_id', $user_id)
                                ->with('file.user', 'file.categories')
                                ->get()
                                ->map(function ($item) use ($domain) {
                                    $data['Downloads_id'] = $item->id;
                                    $data['Downloads_date'] =   substr(substr($item->created_at, strpos($item->created_at, 'T')), 0, strpos(substr($item->created_at, strpos($item->created_at, 'T')), " "));
                                    $Downloads['Downloads'] = $item->file;
                                    if(!empty($Downloads['Downloads'])){
                                        if($item->file->categories->name != null){
                                        $data['file'] = [
                                            'id' => $item->file->id,
                                            'title' => $item->file->title,
                                            'totale_size' => $item->file->totale_size,
                                            'views' => $item->file->views,
                                            'rating' => $item->file->rating,
                                            'total_downloads' => $item->file->total_downloads,
                                            'description' => $item->file->description,
                                            'category_id' => $item->file->category_id,
                                            'created_at' =>  substr(substr($item->file->created_at, strpos($item->file->created_at, 'T')), 0, strpos(substr($item->file->created_at, strpos($item->file->created_at, 'T')), " ")) ,
                                            'updated_at' => substr(substr($item->file->updated_at, strpos($item->file->updated_at, 'T')), 0, strpos(substr($item->file->updated_at, strpos($item->file->updated_at, 'T')), " ")) ,
                                            'categoriesId' => $item->file->categories->id,
                                            'categoriesName' => $item->file->categories->name,
                                            'categoriesDescription' => $item->file->categories->icon,
                                            'user_upload_id' => $item->file->user->id,
                                            'user_upload_full_name' => $item->file->user->full_name,
                                            'user_upload_image' => ($item->file->user->image != null)? $domain .$this->imageBuket. $item->file->user->image:null,
                                        ];
                                    }else{
                                        $data['file'] = [];
                                    }
                                    }else{
                                        $data['file'] = []; 
                                    }
                                    
                                    return $data;
            });

        return $downloads;

















    }


    public function destroy($id)
    {
        Downloads::findOrFail($id)->delete();
        return ['message' => 'deleted successfuly','code' => 200];
    }

    
    public function destroyAll()
    {
        $user_id = auth()->user()->id;
        Downloads::where('user_id', $user_id)->delete();
        return ['message' => 'deleted successfuly','code' => 200];
    }


}

//                                   <----------- Thank You ----------->
