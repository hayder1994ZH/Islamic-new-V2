<?php

namespace App\Http\Controllers;

use App\Models\Vocalist;
use App\Helpers\Utilities;
use App\Models\Temp_files;
use App\Models\SliderVocalist;
use App\Models\TempRemove;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repository\TempRepository;
use App\Repository\VocalistRepository;
use App\Repository\TempRemoveRepository;

class VocalistController extends Controller
{
    private $VocalistRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->VocalistRepository = new VocalistRepository(new Vocalist());
        $this->middleware('role:Admin,owner', ['only' => ['update', 'store']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'search' => 'string',
            'take' => 'required|Integer'
        ]);
        $search = $request->search;
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->VocalistRepository->getAllVocalist($take, $skip, $domain,$search);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id)
    {
        $domain = $request->get('host');
        $vocalist = Vocalist::where('id', $id)->with('user')->get()->map(function ($item) use ($domain) {
            $data['id'] =$item->id;
            $data['name'] =$item->name  ;
            $data['category_id'] =  null  ;
            $data['image'] = ($item->key != null)? $domain . $this->imageBuket .$item->key:null  ;
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
        return $vocalist[0];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);
        $data['user_id'] = auth()->user()->id;
        $random = Str::random(32);
        if ($request->hasFile('image')) { //upload image
            $image['size'] = $request->file('image')->getSize();
            $image_icon = $request->file('image');
            $imageUrl2 = $random ."__vocalistImage.jpg";
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $imageUrl2);
            $data['key'] = $imageUrl2;
            $data['buket'] = 'islamic_images';
            $this->TempRepository->create(['key' => $data['key'], 'buket' => $data['buket'], 'table' => 'vocalists' ]);
        }
        $this->VocalistRepository->create($data);
        return Utilities::wrap(['message' => 'vocalist Created'], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'string',
        ]);
        $table = Vocalist::where('id', $id)->FirstOrFail();
        $random = Str::random(32);
        if ($request->hasFile('image')) { //upload image
            $image_icon = $request->file('image');
            $imageUrl2 = $random ."__vocalistImage.jpg";
            $destinationPath = storage_path('app/public');
            $image_icon->move($destinationPath, $imageUrl2);
            $data['key'] = $imageUrl2;
            $data['buket'] = 'islamic_images';
            if($table->key != null){
                $this->TempRemoveRepository->create(['key' => $table->key, 'buket' => $data['buket'], 'table' => 'vocalists']);
            }
            $this->TempRepository->create(['key' => $data['key'], 'buket' => $data['buket'], 'table' => 'vocalists']);
        }
        $response = $this->VocalistRepository->update($id, $data);
        return Utilities::wrap(['message' => 'vocalist updted successfully'],$response['code']);
    }    
   
    public function destroy($id)
    {
        $vocalist = Vocalist::where('id', $id)->firstOrFail();
        if($vocalist->key != null){
            $this->TempRemoveRepository->create(['key' => $vocalist->key, 'buket' => $vocalist->buket, 'table' => 'vocalists']);
        }
        SliderVocalist::where('vocalist_id', $id)->where('is_deleted', 0)->delete();
        $vocalist->delete();
        return Utilities::wrap(['message' => 'deleted successfully'], 200);
    } 
}
