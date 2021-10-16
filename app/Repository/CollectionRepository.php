<?php
namespace App\Repository;

use App\Models\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;


class CollectionRepository extends BaseRepository {

    public function getAllCollection($take, $skip, $domain){

        $result =  QueryBuilder::for(Collection::class)->where('is_deleted', 0)
        ->withCount('files')
        ->with('user')
        ->allowedFilters(['name'])
        ->orderBy("id", "desc");

        return [
            'totalCount' => $result->count(),
            'items' => $result->skip($skip)->take($take)->get()
                ->map(function ($item) use ($domain){
                    $data['id'] = $item->id;
                    $data['name'] = $item->name;
                    $data['image'] = ($item->image != null)? $domain .$this->imageBuket . $item->image:null;
                    $data['files_count'] = $item->files_count;
                    $data['created_at'] = $item->created_at;
                    $data['updated_at'] = $item->updated_at;
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