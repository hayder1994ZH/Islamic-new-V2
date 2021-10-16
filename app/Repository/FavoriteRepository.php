<?php
namespace App\Repository;

use App\Models\Favorite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class FavoriteRepository extends BaseRepository {

    public function getList($domain, $type)
    {
        $user_id = auth()->user()->id;
        $objectData = [];
        $favorite = Favorite::where('user_id', $user_id)
                                ->with('file.categories', 'file.user','file.object' )
                                ->whereHas('file.object',function ($objectModel) use($type) {
                                    $objectModel->where('type_audio', $type);
                                })
                                ->get()
                                ->map(function ($item) use ($domain, $type, $objectData) {
                                    $data['favorite_id'] = $item->id;
                                    $data['favorite_date'] =   substr(substr($item->created_at, strpos($item->created_at, 'T')), 0, strpos(substr($item->created_at, strpos($item->created_at, 'T')), " "));
                                    $data['id'] =$item->file_id;
                                    $data['title'] =  $item->file->title ;
                                    $data['ImageFile'] =  ($item->file->object[0]->key != null)? $domain . $this->imageBuket . $item->file->object[0]->key:null;
                                    $data['totale_size'] =  $item->file->totale_size ;
                                    $data['views'] =  $item->file->views ;
                                    $data['rating'] =  $item->file->rating ;
                                    $data['total_downloads'] =  $item->file->total_downloads ;
                                    $data['description'] =  $item->file->description ;
                                    $data['category_id'] =  $item->file->category_id ;
                                    $data['created_at'] =  $item->file->created_at ;
                                    $data['updated_at'] =  $item->file->updated_at ;
                                    $data['categories'] = $item->file->categories;
                                    $data['user'] = [
                                                        'id' =>   $item->file->user->id ,
                                                        'image' =>  ($item->file->user->image != null)? $domain.$this->imageBuket.$item->file->user->image:null ,
                                                        'full_name' =>   $item->file->user->full_name ,
                                                    ];
                                    $ss['objectFiles'] = $item->file->object;
                                    foreach($ss['objectFiles'] as $obj){
                                        if($obj->type_audio == $type){
                                            $ob['id'] = $obj->id;
                                            $ob['name'] = $obj->name;
                                            $ob['key'] = ($obj->key != null)? $domain . $this->vedioBuket . $obj->key:null;
                                            $ob['size'] = $obj->size;
                                            $ob['type_audio'] = $obj->type_audio;
                                            array_push($objectData, $ob);
                                            $ob =[];
                                        }
                                    }
                                    $data['objectFiles'] = $objectData;
                                    return $data;
            });

        return[

            'total_count' => $favorite->count(),
            'items' =>  $favorite
        ];
    }

    
}