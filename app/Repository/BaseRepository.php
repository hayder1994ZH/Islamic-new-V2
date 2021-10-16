<?php
namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

//                        <----------- Welcome To BaseRepository Page ----------->

abstract class BaseRepository {
   public $imageSize = '';
    // public $imageSize = '?h=256';
    public $imageBuket = '/images/';
    public $vedioBuket = ':7777/islamic_videos/';
    public $audioBuket = ':7777/islamic_audio/';
    public $socialBuket = ':7777/islamic_social/';
    // public $imageSize = '';
    // public $imageSize = '?h=256';
    // public $imageBuket = '/storage/';
    // public $vedioBuket = '/storage/';
    // public $audioBuket = '/storage/';
    // public $socialBuket = '/storage/';
    
    public $table;
    public function __construct(Model $model){
        $this->table = $model;
    }
    
    //Base repo to get item by id
    public function getAll($take, $skip = 0){
        $model = $this->table->where('is_deleted', 0)
                                    ->take($take)
                                    ->skip($skip)
                                    ->get();
        return $model;
    }

        //Base repo to get item by id
    public function getById($id){
        return $model = $this->table->where('is_deleted', 0)->findOrFail($id);
    }

    public function check($id){
        return $model = $this->table->where('is_deleted', 0)->find($id);
    }

    //Base repo to create item
    public function create($data){
        
        $model = $this->table->create($data);
        return  $response = ['message' => $model,'code' => 200];
    }

    //Base repo to update item 
    public function update($id, $values){
        $item = $this->table->where('is_deleted', 0)->where('id',$id)->firstOrFail();
        $item->update($values);
        return  $response = ['message' => 'Updated successfuly','code' => 200];
    }

    //base repo to soft delete item
    public function softDelete($model)
    {
        $model->update(['is_deleted' => 1]);
        return  $response = ['message' => 'deleted successfuly','code' => 200];
    }
    
    //base repo to delete item
    public function delete($model)
    {
        $model->delete();
        return  $response = ['message' => 'deleted successfuly','code' => 200];
    }



    

}
