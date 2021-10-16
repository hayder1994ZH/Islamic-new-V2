<?php
namespace App\Repository;

use App\Models\Slider;
use App\Models\File_objects;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class SliderRepository extends BaseRepository {

    public function index($take, $skip = 0, $domain, $imageSize, $type)
    {
        
        $objectModel = File_objects::where('is_deleted', 0);
        $objectData = [];
        $slider = QueryBuilder::for(Slider::class)
        ->allowedSorts(['id'])
        ->with('file.categories', 'file.user','file.object','file.vocalist')
        ->whereHas('file.object',function ($objectModel) use($type) {
                $objectModel->where('type_audio', $type);
            })
        ->where('is_deleted', 0)
        ->take($take)
        ->skip($skip)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($item) use($domain, $imageSize, $objectData, $type) {
            $data['slider_id'] =$item->id  ;
            $data['id'] =$item->file_id  ;
            $data['title'] =  $item->file->title ;
            $dd['ImageFile'] =  $item->file->object;
            foreach($item->file->object as $image){
                if(str_contains($image->key, 'largImage')){
                    $data['ImageFile'] = ($image->key != null)? $domain . $this->imageBuket . $image->key:null;
                }
            }
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
            $data['vocalistId'] = $item->file->vocalist->id;
            $data['vocalistName'] = $item->file->vocalist->name;
            $data['vocalistImage'] = ($item->file->vocalist->key != null)? $domain . $this->imageBuket . $item->file->vocalist->key:null;
            return $data;
        });
    return [
        'items' => $slider,
    ];

}

    
}