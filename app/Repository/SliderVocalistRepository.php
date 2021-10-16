<?php
namespace App\Repository;

use App\Models\SliderVocalist;
use App\Models\File_objects;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class SliderVocalistRepository extends BaseRepository {

    public function index($take, $skip = 0, $domain, $imageSize, $type)
    {
        $SliderVocalist = QueryBuilder::for(SliderVocalist::class)
        ->allowedSorts(['id'])
        ->with('vocalist')
        ->where('is_deleted', 0)
        ->take($take)
        ->skip($skip)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) use($domain, $imageSize, $type) {
            $data['SliderVocalist_id'] =$item->id  ;
            $data['id'] =$item->vocalist->id;
                    $data['name'] =$item->vocalist->name  ;
                    $data['image'] = ($item->vocalist->key != null)? $domain .$this->imageBuket.$item->vocalist->key:null  ;
                    $data['created_at'] = substr(substr($item->created_at, strpos($item->created_at, 'T')), 0, strpos(substr($item->created_at, strpos($item->created_at, 'T')), " "));
                    $data['updated_at'] = substr(substr($item->updated_at, strpos($item->updated_at, 'T')), 0, strpos(substr($item->updated_at, strpos($item->updated_at, 'T')), " "));
                    return $data;
            return $data;
        });
    return [
        'items' => $SliderVocalist,
    ];

}

    
}