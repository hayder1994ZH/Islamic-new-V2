<?php

namespace App\Http\Controllers;

use App\Models\Tags;
use App\Models\User;
use App\Models\Files;
use Illuminate\Support\Str;
use App\Models\Temp_files;
use App\Models\Downloads;
use App\Helpers\Utilities;
use App\Models\Categories;
use App\Models\Tags_files;
use App\Models\File_objects;
use App\Models\PlayList;
use App\Models\History;
use Illuminate\Http\Request;
use App\Models\TempRemove;
use App\Repository\PlayListRepository;
use App\Repository\TagsRepository;
use App\Repository\FilesRepository;
use App\Repository\ObjectRepository;
use App\Repository\TempRepository;
use App\Repository\TempRemoveRepository;
use App\Repository\DownloadsRepository;
use Carbon\Carbon;
use Facade\FlareClient\Stacktrace\File;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class FilesController extends Controller
{
    private $PlayListRepository;
    private $FilesRepository;
    private $ObjectRepository;
    private $DownloadsRepository;
    private $TagsRepository;
    private $TempRepository;
    private $id;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->PlayListRepository = new PlayListRepository(new PlayList());
        $this->DownloadsRepository = new DownloadsRepository(new Downloads());
        $this->TagsRepository = new TagsRepository(new Tags());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->ObjectRepository = new ObjectRepository(new File_objects());
        $this->FilesRepository = new FilesRepository(new Files());
        $this->middleware('role:Admin,owner', ['only' => ['deleteFileObject', 'destroy', 'store', 'upload', 'update', 'approve']]);
    }

    public function index(Request $request) // Anyone
    {
        $request->validate([
            'skip' => 'Integer',
            'category_id' => 'Integer',
            'take' => 'required|Integer',
            'tags' => 'nullable',
            'search' => 'nullable'
        ]);
          
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $tags = $request->tags;
        $search = $request->search;
        $category_id = $request->category_id;
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getAllData($skip, $take, $tags, $domain,$category_id, $search, $type['type']);
        return Utilities::wrap($response, 200);
    }
    public function getFromTemp(Request $request) // Anyone
    {
        
        $response = Temp_files::get();
        return Utilities::wrap($response, 200);
    }
    //advanceSearch
    public function getLists(Request $request) 
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
         
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getListFiles($skip, $take, $domain, $type['type']);
        return Utilities::wrap($response, 200);
    }
    //advanceSearch
    public function advanceSearch(Request $request) 
    {
        $data = $request->validate([
            'skip' => 'Integer',
            'search' => 'nullable|string',
            'vocalist_id' => 'nullable|Integer|exists:vocalists,id',
            'category_id' => 'nullable|Integer|exists:categories,id',
            'collection_id' => 'nullable|Integer|exists:collections,id',
            'take' => 'required|Integer',
        ]);
         
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $vocalist_id = $request->vocalist_id;
        $category_id = $request->category_id;
        $collection_id = $request->collection_id;
        $search = $request->search;
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->advanceSearch($skip, $take, $domain, $type['type'], $search, $vocalist_id, $collection_id, $category_id);
        return Utilities::wrap($response, 200);
    }
    
    public function getSortByView(Request $request) 
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
         
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getListFilesSortView($skip, $take, $domain, $type['type']);
        return Utilities::wrap($response, 200);
    }
    
    public function getSortByDownload(Request $request) 
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
         
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getListFilesSortDownload($skip, $take, $domain, $type['type']);
        return Utilities::wrap($response, 200);
    }
    
    public function getSortByRating(Request $request) 
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
         
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getListFilesSortRating($skip, $take, $domain, $type['type']);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id) // Anyone
    {
        
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        $domain = $request->get('host');
        $domain2 = $request->get('hostScheme');
        $response = $this->FilesRepository->getFile($id, $domain, $domain2, $type['type']);
        $getFile = Files::where('id', $id)->where('aproved', 0)->firstOrFail();
        $views = $this->FilesRepository->update($id, ['views' => $getFile->views+=1]);
        return  $response;
        

    }

    public function getMyPlaylist(Request $request) // Anyone
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
      
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;$domain = $request->get('host');
        $response = $this->FilesRepository->getPlaylist($domain, $take, $skip, $type['type']);
        return  $response;
    }
    
    public function dashboardAdmin(Request $request) // Admin  getRandomFilesByCategoryId
    {
        $response = $this->FilesRepository->dashboard();
        return  $response;
    }

    public function getAllByVocaId(Request $request, $id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getListByVocalistId($id, $domain, $take, $skip);
        return  $response;
    }

    public function getByVocaId(Request $request, $id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getByVocalistId($id, $domain, $take, $skip, $type['type']);
        return  $response;
    }

    public function getByCategorysId(Request $request, $id)
    {
        $domain = $request->get('host');
        $response = $this->FilesRepository->getByCategoryId($id, $domain);
        return  $response;
    }

    //
    public function getByVocaIdIdAndCategoryId(Request $request, $id, $category_id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
        
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getByVocalistIdAndCategoryId($id, $domain, $take, $skip, $type['type'], $category_id);
        return  $response;
    }

    //
    public function getRandomFileByCategoryId(Request $request, $category_id, $id)
    {
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $domain = $request->get('host');
        $response = $this->FilesRepository->getRandomFilesByCategoryId($domain, $type['type'], $category_id, $id);
        return  $response;
    }

    //
    public function getByCollectionId(Request $request, $id)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
        ]);
        
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);

        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->FilesRepository->getByCollectionId($id, $domain, $take, $skip, $type['type']);
        return  $response;
    }
    
    public function getFileById(Request $request, $id) // Anyone
    {
        
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);

        $domain = $request->get('host');
        $domain2 = $request->get('hostScheme');
        $response = $this->FilesRepository->getFilebyId($id, $domain, $domain2, $type['type']);
        $getFile = Files::where('id', $id)->firstOrFail();
        if($getFile->user_id != auth()->user()->id){
            return Utilities::wrap(['error' => 'permisson denid'], 403);
        }
        $views = $this->FilesRepository->update($id, ['views' => $getFile->views+=1]);
        
        return $response;
    }

    //create file
    public function store(Request $request) // Opration & Admin & Company
    {
        //Validation
        $request->all();
        $File = $request->validate([
            'title' => 'required|string',
            'trailer_url' => 'string',
            'description' => 'string',
            'vocalist_id' => 'required|integer|exists:vocalists,id',
            'category_id' => 'required|integer|exists:categories,id',
            'collection_id' => 'required|integer|exists:collections,id',
        ]);
        $imageAndroid = $request->validate([
            'imageAndroid' => 'nullable|image',
            'image' => 'required|image'
        ]);
        $tags = $request->validate([
            "tags"    => "nullable|array",
        ]);

        //Processing
        $random = Str::random(32);
        $fullPath = $random; //new file path
        $File['user_id'] = auth()->user()->id;
        $addFiles = $this->FilesRepository->create($File);
        if ($request->hasFile('image')) { //upload image
            $image['size'] = $request->file('image')->getSize();
            $image_icon = $request->file('image');
            $imageUrl2 = $fullPath ."__largImage.jpg";
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $imageUrl2);
            $image['key'] = $imageUrl2;
            $image['buket'] = 'islamic_images';
            $image['type'] = 1;
            $image['file_id'] = $addFiles['message']->id;
            $this->ObjectRepository->create($image);
            $this->TempRepository->create(['key' => $image['key'], 'buket' => $image['buket'], 'table' => 'file_objects']);
        }
        if ($request->hasFile('imageAndroid')) { //upload image
            $image_iconAndroid = $request->file('imageAndroid');
            $image2['size'] = $request->file('imageAndroid')->getSize();
            $imageAndroid = Str::random(32) ."__imageAndroid.jpg";
            $destinationPathAndroid = storage_path('app/public');
            $image_iconAndroid->move($destinationPathAndroid, $imageAndroid);
            $image2['key'] = $imageAndroid;
            $image2['buket'] = 'islamic_images';
            $image2['type'] = 4;
            $image2['file_id'] = $addFiles['message']->id;
            $this->ObjectRepository->create($image2);
            $this->TempRepository->create(['key' => $image2['key'], 'buket' => $image2['buket'], 'table' => 'file_objects']);
        }

        if (!empty($tags['tags'][0])) {
            foreach ($tags['tags'] as $data) {
                $tag['name'] = $data;
                $check = Tags::where('name', $tag['name'])->first();
                if ($check) {
                    $tagFiles['tag_id'] = $check['id'];
                    $tagFiles['file_id'] = $addFiles['message']->id;
                } else {
                    $fileTag =  $this->TagsRepository->create($tag);
                    $tagFiles['tag_id'] = $fileTag['message']->id;
                    $tagFiles['file_id'] = $addFiles['message']->id;
                }
                Tags_files::create($tagFiles);
            }
        }
        return Utilities::wrap(['file_id' => $addFiles['message']->id, 'user_id' => auth()->user()->id], 200);
    }

    public function geter($id)
    {
        $file = Files::where('id', $id)->with('object')->first();
        return $file;
    }

    //Update Files
    public function update(Request $request, $id) // Admin
    {
        $data = $request->validate([
            'title' => 'string',
            'trailer_url' => 'string',
            'description' => 'string',
            'category_id' => 'exists:categories,id',
            'collection_id' => 'integer|exists:collections,id',
            'vocalist_id' => 'integer|exists:vocalists,id',
        ]);
        Files::where('id', $id)->firstOrFail();
        if ($request->hasFile('image')) { //upload image
            $LargImage =  File_objects::where('file_id', $id)->where('type', 1)->first();
            if($LargImage){
                $this->TempRemoveRepository->create(['key' => $LargImage->key, 'buket' => $LargImage->buket, 'table' => 'file_objects']);
                $LargImage->delete();
            }
            $image_icon = $request->file('image');
            $imageUrl2 = Str::random(32) ."__largImage.jpg";
            $destinationPathlarg = storage_path('app/public');
            $image_icon->move($destinationPathlarg, $imageUrl2);
            $image['key'] = $imageUrl2;
            $image['buket'] = 'islamic_images';
            $image['type'] = 1;
            $image['size'] = $request->file('image')->getSize();
            $image['file_id'] = $id;
            $this->ObjectRepository->create($image);
            $this->TempRepository->create(['key' => $image['key'], 'buket' => $image['buket'], 'table' => 'file_objects']);

        }
        
        if ($request->hasFile('imageAndroid')) { //upload image
            $ImageAndroid =  File_objects::where('file_id', $id)->where('type', 4)->first();
              if($ImageAndroid){
                $this->TempRemoveRepository->create(['key' => $ImageAndroid->key, 'buket' => $ImageAndroid->buket, 'table' => 'file_objects']);
                $ImageAndroid->delete();
              }  
              $image_iconAndroid = $request->file('imageAndroid');
              $imageAndroid = Str::random(32) ."__imageAndroid.jpg";
              $destinationPathAndroid = storage_path('app/public');
              $image_iconAndroid->move($destinationPathAndroid, $imageAndroid);
              $image2['key'] = $imageAndroid;
              $image2['buket'] = 'islamic_images';
              $image2['type'] = 4;
              $image2['size'] = $request->file('imageAndroid')->getSize();
              $image2['file_id'] = $id;
              $this->ObjectRepository->create($image2);
              $this->TempRepository->create(['key' => $image2['key'], 'buket' => $image2['buket'], 'table' => 'file_objects' ]);
          }
        $response = $this->FilesRepository->update($id, $data);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }

    public function upload(Request $request, $id)
    {
        ini_set('upload_max_filesize', '10000M');
        ini_set('post_max_size', '10000M');
        ini_set('max_input_time', 300000);
        ini_set('max_execution_time', 300000);
        
        $file_path = $request->validate([
            'key' => 'file'
        ]);
        $type_audio = $request->validate([
            'type_audio' => 'required|integer|in:1,2'
        ]);
        if(!$_FILES["key"]["name"]){//update file name
            $name = $request->validate([
                'name' => 'string',
            ]);
            $this->ObjectRepository->update($id, $name);
            return Utilities::wrap(['message' => 'Update name file successfully'], 200);
        }
        $addFiles = $this->FilesRepository->getById($id);
         if($addFiles->user_id !=  auth()->user()->id){
            return Utilities::wrap(['message' => 'permisson denid'], 400);
         }
            $target_dir = '/var/www/islamicBack/storage/app/public/';
            $rand = rand();
            $_FILES["key"]["tmp_name"];
            $name = $_FILES["key"]["name"];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            
            $fullPath = Str::random(32)."." . $ext ;
            if(preg_match('/\p{Arabic}/u', $name)){
                $name =  $fullPath;
            }else{
                $name = str_replace('.'. $ext, '', $name);
                $name = preg_replace('/[^A-Za-z0-9_]/', '-', $name).'.'.$ext;
            }
            $checkFile = File_objects::where('key', $name)->where('is_deleted', 0)->first();
            if(!$checkFile){ //check file
                $nameFile = $request->validate([
                    'name' => 'string',
                ]);
                $fileName = $name;
                $target_file = $target_dir . $fileName;        
                move_uploaded_file($_FILES["key"]["tmp_name"], $target_file);
                $file_path = $fileName;
                $objFile['key'] = $file_path;
                $size = $_FILES["key"]["size"];
                $objFile['size'] = $size;
                if(!empty($nameFile)){
                    $objFile['name'] = $nameFile['name'];
                }
                $objFile['type_audio'] = $type_audio['type_audio'];
                if($type_audio['type_audio'] == 1){
                    $objFile['buket'] = 'islamic_videos';
                    $objFile['type'] = 2;
                }
                if($type_audio['type_audio'] == 2){
                    $objFile['buket'] = 'islamic_audio';
                    $objFile['type'] = 3;
                }
                $objFile['file_id'] = $id;
                $addObjectFile = $this->ObjectRepository->create($objFile);
                $this->TempRepository->create(['key' => $objFile['key'], 'buket' => $objFile['buket'], 'table' => 'file_objects']);
                $sizeFile['totale_size'] = $addFiles->totale_size + $size ;
                $this->FilesRepository->update($id, $sizeFile);
                return Utilities::wrap(['message' => 'Upload file successfully'], 200);
            }
            return Utilities::wrap(['message' => 'this file is exists ', 'file_id' => $checkFile['file_id'] ], 400);
    }

    //Delete Files 
    public function destroy($id) // Admin
    {
        $FilesModel = Files::where('id', $id)->where('is_deleted', 0)->firstOrFail();
        $response = $this->FilesRepository->softDelete($FilesModel);

        $objectFiles = File_objects::where('file_id', $id)->where('is_deleted', 0)->get();
        foreach($objectFiles as $object){
                $objectModel = File_objects::where('id', $object->id)->firstOrFail();
                $this->TempRemoveRepository->create(['key' => $objectModel->key, 'buket' => $objectModel->buket, 'table' => 'file_objects']);
                $this->ObjectRepository->softDelete($objectModel);
            }

        return Utilities::wrap(['message' => 'deleted successfully'], 200);
    }

    //Approved upload
    public function approve($id) // Admin
    {
        $file = Files::where('id', $id)->firstOrFail();
        if($file->aproved == 0){
            $this->FilesRepository->update($id, ['aproved' => 1]);
            return ['message' => 'Unaproved Successfully ', 'code' => 200];
        }
        $this->FilesRepository->update($id, ['aproved' => 0]);
        return ['message' => 'Aproved Successfully ', 'code' => 200];
    }

    // Filter Data
    public function filter($id, Request $request) // Anyone
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer',
            'tags' => 'array'
        ]);
        
        $type = $request->validate([
            'type' => 'required|Integer'
        ]);
        
        $tags = $request->tags;
        $skip = $request->skip;
        $take = $request->take;
        $domain = $request->get('host');
        $response = $this->FilesRepository->filter($id, $skip, $take, $tags, $domain, $type['type']);
        return response()->json($response);
    }

    //download files  advanceSearch
    public function download(Request $request, $id) // Anyone
    {
        $type = $request->validate([
            'type' => 'required|integer|in:1,2'
        ]);

        $objectFile = File_objects::with('file')
            ->where('id', $id)
            ->where('type_audio', $type['type'])
            ->where('is_deleted', 0)
            ->firstOrFail();
        $files = Files::where('id', $objectFile->file_id)
        ->where('is_deleted', 0)
        ->first();
        if( $type['type'] == 1){
            $url = $request->get('host') . $this->vedioBuket . $objectFile->key;
        }else if($type['type'] == 2){
            $url = $request->get('host') . $this->audioBuket . $objectFile->key;
        }
        $this->FilesRepository->update( $files->id,[
            'total_downloads' => $files->total_downloads + 1
        ]);
        if (Auth::guard('api')->check()) {
            $download['user_id'] = auth()->user()->id;
        } else {
            $download['user_id'] = null;
        }
        $download['file_id'] = $objectFile->file_id;
        $this->DownloadsRepository->create($download);
        return Utilities::wrap(['messge' => $url], 200);

    }

    //Get Rates By Id
    public function rates($id) // Anyone
    {
        $response = $this->FilesRepository->rates($id);
        return  $response;
    }

    //Delete file object By Id
    public function deleteFileObject($id) // Anyone
    {
        $objectFiles = File_objects::where('id', $id)->where('is_deleted', 0)->firstOrFail();
        $file = Files::where('id', $objectFiles->file_id)->where('is_deleted', 0)->firstOrFail();
        if($file->user_id != auth()->user()->id){
            return ['message' => 'permisson denied', 'code' => 403];
        }
        if($objectFiles->buket == 'islamic_audio' || $objectFiles->buket == 'islamic_video'  ){
            $newSize =  $file->totale_size - $objectFiles->size ;
            $this->FilesRepository->update($objectFiles->file_id,['totale_size' => $newSize] );
            $this->TempRemoveRepository->create(['key' => $objectFiles->key, 'buket' => $objectFiles->buket, 'table' => 'file_objects']);

        }
        $this->ObjectRepository->softDelete($objectFiles);
        return Utilities::wrap(['message' => 'deleted successfully'], 200);  
    }

    //add playlist
    public function playlist(Request $request) 
    {
       $file = $request->validate([
            'file_id' => 'integer|exists:files,id',
        ]);
        Files::where('id', $file['file_id'])->where('is_deleted', 0)->firstOrFail();
        $check = PlayList::where('file_id', $file['file_id'])->where('user_id', auth()->user()->id)->first();
        if($check){
            return Utilities::wrap(['message' => 'this file already  exsit in your playlist'], 400);  
        }
        $file['user_id'] = auth()->user()->id;
        $this->PlayListRepository->create($file);
        return Utilities::wrap(['message' => 'add to playlist successfully'], 200);  
    }
    
    //delete file playlist
    public function deleteFromPlaylist($id) 
    {
        $check = PlayList::where('file_id', $id)->where('user_id', auth()->user()->id)->firstOrFail();
        $this->PlayListRepository->delete($check);
        return Utilities::wrap(['message' => 'file deleted from your playlist successfully'], 200);  
    }

}
