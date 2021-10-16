<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Models\TempRemove;
use App\Models\Temp_files;
use Illuminate\Http\Request;
use App\Repository\AdsRepository;
use App\Repository\TempRemoveRepository;
use App\Repository\TempRepository;
use App\Helpers\Utilities;

class AdsController extends Controller
{
    private $AdsRepository;
    private $TempRemoveRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->AdsRepository = new AdsRepository(new Ads());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->middleware('role:Admin,owner', ['only' => ['update','store']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
    }

    public function getlist(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $domain = $request->get('host');
        $take = $request->take;
        $skip = $request->skip;
        $response = $this->AdsRepository->index($take, $skip, $domain);
        return Utilities::wrap($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'url'=>'nullable|string',
            'image'=>'file',
            'status'=>'integer'
        ]);
        
        if (array_key_exists("image", $data)) { //check image
            $image['key'] = $request->file('image')->store('');            
            $temp['key'] = $image['key'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['key'];
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'ads']);
        }
        // return $data;
        $response = $this->AdsRepository->create($data);
        return Utilities::wrap(['message' => 'Ads Created Successfully'],$response['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ads  $ads
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->AdsRepository->getById($id);
        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ads  $ads
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'url'=>'nullable|string',
            'image'=>'file',
            'status'=>'integer'
        ]);
        $table = Ads::where('id', $id)->firstOrFail();
        if (array_key_exists("image", $data)) { //check image
            $image['key'] = $request->file('image')->store('');            
            $temp['key'] = $image['key'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['key'];
            if($table->image != null){
                $this->TempRemoveRepository->create(['key' => $table->image, 'buket' => $temp['buket'], 'table' => 'ads' ]);
            }
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'ads']);
        }
        
        $response = $this->AdsRepository->update($id, $data);
        return Utilities::wrap(['message' => 'Ads Updated Successfully'],$response['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ads  $ads
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ads = Ads::findOrFail($id);
        if($ads->image != null){
            $this->TempRemoveRepository->create(['key' => $ads->image, 'buket' => 'islamic_images', 'table' => 'ads' ]);
        }
            $response = $this->AdsRepository->softDelete($ads);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }
}
