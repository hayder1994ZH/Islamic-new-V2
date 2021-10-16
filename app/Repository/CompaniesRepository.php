<?php
namespace App\Repository;

use App\Models\Companies;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CompaniesRepository extends BaseRepository {

public function getAllCompanies($take, $skip, $domain){

    $result = Companies::with('user')->where('is_deleted', 0)
    ->orderBy("id", "desc");
    $totalCount = Companies::where('is_deleted', 0)->get();

    return  $resp = [
        'totalCount' => $totalCount->count(),
        'items' => $result->skip($skip)->take($take)->get()
        ->map(function ($item) use ($domain) {
            $data['id'] =$item->id  ;
            $data['name'] =$item->name  ;
            $data['details'] =$item->details  ;
            $data['logo'] = ($item->logo != null)?  $domain .'/images/'.$item->logo:null  ;
            $data['color'] =$item->color  ;
            $data['http_host'] =$item->http_host  ;
            $data['http_protocol'] =$item->http_protocol  ;
            $data['storage_port'] =$item->total_downloads  ;
            $data['remote_ip'] =$item->remote_ip  ;
            $data['email'] =$item->email  ;
            $data['email2'] =$item->email2  ;
            $data['facebook_link'] =$item->facebook_link  ;
            $data['instegram_link'] =$item->instegram_link  ;
            $data['twitter_link'] =$item->twitter_link  ;
            $data['address'] =$item->address  ;
            $data['phone'] =$item->phone  ;
            $data['phone2'] =$item->phone2  ;
            $data['aproved'] =$item->aproved  ;
            $data['user_id'] =$item->user_id  ;
            $data['created_at'] =$item->created_at  ;
            $data['updated_at'] =$item->updated_at  ;
            $data['expired_date'] =$item->expired_date  ;
            $data['user'] =[
                            'id' => $item->user->id  ,
                            'full_name' => $item->user->full_name  ,
                            'email' => $item->user->email  ,
                            'phone' => $item->user->phone  ,
                            'image' => ($item->user->image != null)? $domain .'/images/'.$item->user->image:null  , 
                           ];
            return $data;
        } )
    ];
}
    
}