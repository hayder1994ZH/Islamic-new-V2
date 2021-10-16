<?php
namespace App\Repository;

use App\Models\Ads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class AdsRepository extends BaseRepository {

    public function index($take, $skip = 0, $domain){
        $model = QueryBuilder::for(Ads::class)
                                ->where('is_deleted', 0)
                                ->allowedFilters('title', 'status')
                                ->take($take)
                                ->skip($skip)
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->map(function($item) use($domain){
                                    $data['id'] = $item->id;
                                    $data['title'] = $item->title;
                                    $data['description'] = $item->description;
                                    $data['url'] = $item->url;
                                    $data['image'] = ($item->image != null)? $domain . $this->imageBuket . $item->image:null;
                                    $data['status'] = $item->status;
                                    $data['created_at'] = $item->created_at;
                                    $data['updated_at'] = $item->updated_at;
                                    return $data;
                                })
                                    ;
        return $model;
    }

    
}