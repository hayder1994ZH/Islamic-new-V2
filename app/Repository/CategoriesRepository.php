<?php
namespace App\Repository;

use App\Models\Vocalist;
use App\Models\Categories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

//                        <----------- Welcome To CategoriesRepository Page ----------->

class CategoriesRepository extends BaseRepository {

     
    //Repo to Get all users
    public function getListCategories($skip, $take, $domain)
    {
        $result = Categories::where('is_deleted', 0)
                            // ->orderBy("id", "desc");
                            ->with('vocalist');
        $totalCount = Categories::where('is_deleted', 0)->get();

        return  $resp = [
                    'totalCount' => $totalCount->count(),
                    'items' => $result->skip($skip)->take($take)->get()
                    ->map(function ($item) use ($domain) {
                        $data['id'] = $item->id;
                        $data['name'] = $item->name;
                        $data['icon'] = $item->icon;
                        $data['icon_mobile'] = ($item->icon_mobile != null)? $domain .$this->imageBuket . $item->icon_mobile:null;
                        $data['created_at'] = $item->created_at;
                        $data['updated_at'] = $item->updated_at;
                       
                        return $data;
                    })
                ];
    }

    //Repo to Get all users
    public function getListVocalistCategories($skip, $take, $domain, $id)
    {
        $result = Vocalist::where('is_deleted', 0)
                            ->where('category_id', $id);
                            

        return  $resp = [
                    'totalCount' => $result->get()->count(),
                    'items' => $result->skip($skip)->take($take)->get()
                    ->map(function ($item) use ($domain) {
                        $data['id'] =$item->id;
                        $data['name'] =$item->name  ;
                        $data['category_id'] =$item->category_id  ;
                        $data['image'] = ($item->key != null)? $domain .$this->imageBuket.$item->key:null  ;
                        $data['created_at'] =$item->created_at;
                        $data['updated_at'] =$item->updated_at;
                        return $data;
                    })
                ];
    }
    
}