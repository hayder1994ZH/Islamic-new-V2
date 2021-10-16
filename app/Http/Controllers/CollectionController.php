<?php

namespace App\Http\Controllers;

use App\Models\TempRemove;
use App\Models\Collection;
use App\Helpers\Utilities;
use App\Models\Temp_files;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repository\TempRemoveRepository;
use App\Repository\TempRepository;
use App\Repository\CollectionRepository;

class CollectionController extends Controller
{
    private $TempRemoveRepository;
    private $CollectionRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->CollectionRepository = new CollectionRepository(new Collection());
        $this->middleware('role:Admin,owner', ['only' => ['update', 'store']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $domain = $request->get('host');
        $take = $request->take;
        $skip = $request->skip;
        $response = $this->CollectionRepository->getAllCollection($take, $skip,$domain);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id)
    {
        $domain = $request->get('host');
        $Collection = Collection::where('id', $id)->with('user')->get()->map(function ($item) use ($domain) {
            $data['id'] =$item->id;
            $data['name'] =$item->name  ;
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
        } );
        return $Collection[0];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'image' => 'required|file',
        ]);
        $data['user_id'] = auth()->user()->id;
        if (array_key_exists("image", $data)) { //check image
            $image['image'] = $request->file('image')->store('');            
            $temp['key'] = $image['image'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['image'];
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'collections']);
        }
        $this->CollectionRepository->create($data);
        return Utilities::wrap(['message' => 'Collection Created'], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'string',
            'image' => 'file',
        ]);
        $table = Collection::where('id', $id)->firstOrFail();
        if (array_key_exists("image", $data)) { //check image
            $image['image'] = $request->file('image')->store('');            
            $temp['key'] = $image['image'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['image'];
            if($table->image != null){
                $this->TempRemoveRepository->create(['key' => $table->image, 'buket' => $temp['buket'], 'table' => 'collections']);
            }
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'collections']);
        }
        $response = $this->CollectionRepository->update($id, $data);
        return Utilities::wrap(['message' => 'Collection updted successfully'],$response['code']);
    }    
   
    public function destroy($id)
    {
        $Collection = Collection::where('id', $id)->firstOrFail();
        if($Collection->image != null){
            $this->TempRemoveRepository->create(['key' => $Collection->image, 'buket' => 'islamic_images', 'table' => 'collections']);
        }
        $response = $this->CollectionRepository->delete($Collection);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
        return Utilities::wrap(['message' => 'deleted successfully'], 200);
    } 

}
