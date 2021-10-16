<?php

namespace App\Http\Controllers;

use App\Helpers\Utilities;
use App\Models\Companies;
use App\Models\Temp_files;
use App\Repository\CompaniesRepository;
use App\Repository\TempRepository;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    private $CompaniesRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->CompaniesRepository = new CompaniesRepository(new Companies());
        $this->middleware('role:Admin,owner', ['only' => ['index', 'show', 'destroy', 'update', 'store']]);
        // $this->middleware('role:Company', ['only' => ['updateMyCompany', 'getMyCompany']]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->CompaniesRepository->getAllCompanies($take, $skip, $domain);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id)
    {
        $domain = $request->get('host');
        $company = Companies::where('id', $id)->with('user')->get()->map(function ($item) use ($domain) {
            // $data['id'] =$item->id  ;
            $data['name'] =$item->name  ;
            $data['details'] =$item->details  ;
            $data['logo'] = ($item->logo != null)?  $domain .'/images/'.$item->logo:null  ;
            $data['color'] =$item->color  ;
            $data['http_host'] =$item->http_host  ;
            $data['http_protocol'] =$item->http_protocol  ;
            // $data['storage_port'] =$item->storage_port  ;
            // $data['remote_ip'] =$item->remote_ip  ;
            $data['email'] =$item->email  ;
            $data['email2'] =$item->email2  ;
            $data['facebook_link'] =$item->facebook_link  ;
            $data['instegram_link'] =$item->instegram_link  ;
            $data['twitter_link'] =$item->twitter_link  ;
            $data['address'] =$item->address  ;
            $data['phone'] =$item->phone  ;
            $data['phone2'] =$item->phone2  ;
            $data['aproved'] =$item->aproved  ;
            // $data['user_id'] =$item->user_id  ;
            // $data['created_at'] =$item->created_at  ;
            // $data['updated_at'] =$item->updated_at  ;
            // $data['expired_date'] =$item->expired_date  ;
             $data['user'] =[
                            'id' => $item->user->id  ,
                            'full_name' => $item->user->full_name  ,
                            'email' => $item->user->email  ,
                            'phone' => $item->user->phone  ,
                            'image' => ($item->user->image != null)? $domain .'/images/'.$item->user->image:null  , 
                           ];
            return $data;
        } );
        if(!$company){
            return Utilities::wrap(['message' => 'you don`t have company'],404);
        }
        return $company[0];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'details' => 'nullable',
            'logo' => 'required|mimes:jpg,bmp,png',
            'color' => 'required|string',
            'http_host' => 'required|string',
            'http_protocol' => 'required|string',
            'remote_ip' => 'required|string',
            'storage_port' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
            'email' => 'nullable',
            'email2' => 'nullable',
            'facebook_link' => 'nullable',
            'instegram_link' => 'nullable',
            'twitter_link' => 'nullable',
            'address' => 'nullable',
            'phone' => 'nullable',
            'phone2' => 'nullable',
        ]);
        
        $data['expired_date'] = $futureDate=date('Y-m-d', strtotime('+1 year'));
        
        if (array_key_exists("logo", $data)) { //check image
            $fullPath = uniqid('uploadImage_') ."__" . date('Y-m-d-H:i:s') ."__". rand(1000000,9999999); //new file path
            $image_icon = $request->file('logo');
            $name = $image_icon->getClientOriginalName();
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            // if(preg_match('/\p{Arabic}/u', $name) || $ext != 'jpg' ){
            $name = $fullPath . ".jpg";
            // }
            $ImageName = str_replace(':', '-', $name);
            $image['key'] = str_replace(' ', '-', $ImageName);
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $image['key']);
            
            $temp['file_path'] = $image['key'];
            $temp['buket'] = 'store-images';
            $data['logo'] = $image['key'];
            $this->TempRepository->create(['file_path' => $temp['file_path'], 'buket' => $temp['buket'] ]);
         
        }
        $this->CompaniesRepository->create($data);
        return Utilities::wrap(['message' => 'company Created'], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'details' => 'nullable',
            'logo' => 'nullable|string',
            'color' => 'required|string',
            'http_host' => 'required|string',
            'http_protocol' => 'required|string',
            'storage_port' => 'required|string',
            'remote_ip' => 'required|string',
            'user_id' => 'nullable|string',
            'email' => 'nullable|string',
            'email2' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            'instegram_link' => 'nullable|string',
            'twitter_link' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'phone2' => 'nullable|string'
        ]);
         
        if (array_key_exists("logo", $data)) { //check image
            $fullPath = uniqid('uploadImage_') ."__" . date('Y-m-d-H:i:s') ."__". rand(1000000,9999999); //new file path
            $image_icon = $request->file('logo');
            $name = $image_icon->getClientOriginalName();
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if(preg_match('/\p{Arabic}/u', $name) || $ext != 'jpg' ){
               $name = $fullPath . ".jpg";
            }
            $ImageName = str_replace(':', '-', $name);
            $image['key'] = str_replace(' ', '-', $ImageName);
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $image['key']);
            
            $temp['file_path'] = $image['key'];
            $temp['buket'] = 'store-images';
            $data['logo'] = $image['key'];
            $this->TempRepository->create(['file_path' => $temp['file_path'], 'buket' => $temp['buket'] ]);
        }

        $response = $this->CompaniesRepository->update($id, $data);
        return Utilities::wrap(['message' => 'company updted successfully'],$response['code']);
    }    
   
    public function destroy($id)
    {
        $comapny = Companies::where('id', $id)->firstOrFail();
        $response = $this->CompaniesRepository->delete($comapny);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    } 
    


    public function updateMyCompany(Request $request)
    {
        $id = auth()->user()->id;
        $company = Companies::where('user_id', $id)->with('user')->first();
        if(!$company){
            return Utilities::wrap(['message' => 'you don`t have company'],404);
        }
        $data = $request->validate([
            'name' => 'string',
            'details' => 'nullable',
            'email' => 'nullable',
            'email2' => 'nullable',
            'facebook_link' => 'nullable',
            'instegram_link' => 'nullable',
            'twitter_link' => 'nullable',
            'address' => 'nullable',
            'phone' => 'nullable',
            'phone2' => 'nullable',
            'logo' => 'nullable|file',
            'color' => 'string',
            'http_host' => 'string',
            'http_protocol' => 'string',
            'storage_port' => 'string',
            'remote_ip' => 'string'
        ]);
          
        if (array_key_exists("logo", $data)) { //check image
            $fullPath = uniqid('uploadImage_') ."__" . date('Y-m-d-H:i:s') ."__". rand(1000000,9999999); //new file path
            $image_icon = $request->file('logo');
            $name = $image_icon->getClientOriginalName();
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if(preg_match('/\p{Arabic}/u', $name) || $ext != 'jpg' ){
               $name = $fullPath . ".jpg";
            }
            $ImageName = str_replace(':', '-', $name);
            $image['key'] = str_replace(' ', '-', $ImageName);
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $image['key']);
            
            $temp['file_path'] = $image['key'];
            $temp['buket'] = 'store-images';
            $data['logo'] = $image['key'];
            $this->TempRepository->create(['file_path' => $temp['file_path'], 'buket' => $temp['buket'] ]);
         
        }
        $id = auth()->user()->id;
        $company = Companies::where('user_id', $id)->update($data);
        return Utilities::wrap(['message' => 'company updted successfully'],200);
    }

    public function getMyCompany(Request $request)
    {
        $id = auth()->user()->id;
        $domain = $request->get('host');
        $company = Companies::where('user_id', $id)->with('user')->get()->map(function ($item) use ($domain) {
            // $data['id'] =$item->id  ;
            $data['name'] =$item->name  ;
            $data['details'] =$item->details  ;
            $data['logo'] = ($item->logo != null)?  $domain .'/images/'.$item->logo:null  ;
            $data['color'] =$item->color  ;
            $data['http_host'] =$item->http_host  ;
            $data['http_protocol'] =$item->http_protocol  ;
            // $data['storage_port'] =$item->storage_port  ;
            // $data['remote_ip'] =$item->remote_ip  ;
            $data['email'] =$item->email  ;
            $data['email2'] =$item->email2  ;
            $data['facebook_link'] =$item->facebook_link  ;
            $data['instegram_link'] =$item->instegram_link  ;
            $data['twitter_link'] =$item->twitter_link  ;
            $data['address'] =$item->address  ;
            $data['phone'] =$item->phone  ;
            $data['phone2'] =$item->phone2  ;
            $data['aproved'] =$item->aproved  ;
            // $data['user_id'] =$item->user_id  ;
            // $data['created_at'] =$item->created_at  ;
            // $data['updated_at'] =$item->updated_at  ;
            // $data['expired_date'] =$item->expired_date  ;
             $data['user'] =[
                            'id' => $item->user->id  ,
                            'full_name' => $item->user->full_name  ,
                            'email' => $item->user->email  ,
                            'phone' => $item->user->phone  ,
                            'image' => ($item->user->image != null)? $domain .'/images/'.$item->user->image:null  , 
                           ];
            return $data;
        } );
        if(!$company){
            return Utilities::wrap(['message' => 'you don`t have company'],404);
        }
        return $company[0];
    }
    
    public function getMyCompanyClient(Request $request)
    {
        $domain = $request->get('host');
        $company = Companies::where('http_host', $request->get('domain'))->get()->map(function ($item) use ($domain) {
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
            return $data;
        } );
        return $company[0];
    }


}
