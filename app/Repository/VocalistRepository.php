<?php
namespace App\Repository;

use App\Models\Vocalist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;


class VocalistRepository extends BaseRepository {

public function getAllVocalist($take, $skip, $domain, $search){

    $result = QueryBuilder::for(Vocalist::class)
                            ->with('user')
                            ->withCount('files')
                            ->where('is_deleted', 0)
                            ->orderBy("id", "desc");
    if(!is_null($search)){
        $result->where('name', 'like', '%'.$search.'%');
    }

    return [
        'totalCount' => $result->get()->count(),
        'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain) {
                    $data['id'] =$item->id;
                    $data['name'] =$item->name  ;
                    $data['category_id'] =  null  ;
                    $data['image'] = ($item->key != null)? $domain .$this->imageBuket.$item->key:null  ;
                    $data['files_count'] =$item->files_count  ;
                    $data['created_at'] =$item->created_at;
                    $data['updated_at'] =$item->updated_at;
                    if(!empty($item->user)){
                        $data['user'] =[
                            'id' =>  $item->user->id,
                            'full_name' =>  $item->user->full_name,
                            'image' =>  ($item->user->image != null)? $domain . $this->imageBuket . $item->user->image . $this->imageSize:null,
                        ];
                    } 
                    return $data;
        })
    ];
}
    
}