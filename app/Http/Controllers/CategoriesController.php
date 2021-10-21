<?php

namespace App\Http\Controllers;

use App\Helpers\Utilities;
use App\Models\Categories;
use App\Models\TempRemove;
use App\Models\Temp_files;
use Illuminate\Http\Request;
use App\Repository\TempRemoveRepository;
use App\Repository\CategoriesRepository;
use App\Repository\TempRepository;

class CategoriesController extends Controller
{
        
    private $TempRemoveRepository;
    private $CategoriesRepository;
    private $TempRepository;
    private $id;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->CategoriesRepository = new CategoriesRepository(new Categories());
        $this->middleware('role:Admin,owner', ['only' => ['store', 'update']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
    }

    
    //All Categories data
    public function index(Request $request) // anyone
    {
       //validations
       $request->validate([
           'skip' => 'Integer',
           'take' => 'required|Integer'
       ]);

       //parameters
       $domain = $request->get('host');
       $take = $request->take;
       $skip = $request->skip;
       
       //Processing
       $response = $this->CategoriesRepository->getListCategories($skip, $take, $domain);

       //Response
       return Utilities::wrap($response, 200);
    }

    
    //All vocalist By Category id 
    public function getVocalistByCategory(Request $request, $id) // anyone
    {
       //validations
       $request->validate([
           'skip' => 'Integer',
           'take' => 'required|Integer'
       ]);

       //parameters
       $domain = $request->get('host');
       $take = $request->take;
       $skip = $request->skip;
       
       //Processing
       $response = $this->CategoriesRepository->getListVocalistCategories($skip, $take, $domain, $id);

       //Response
       return Utilities::wrap($response, 200);
    }

    //Get Single Categories
    public function show($id)
    {

       return Categories::where('id', $id)->firstOrFail();
    }
     
    //Add Categories
    public function store(Request $request) // Admin 
    {
        $Categories = $request->validate([
            'name' => 'required|string|unique:categories',
            'icon' => 'required|string',
            'icon_mobile' => 'required|file|mimes:png',
        ]);
            
        if (array_key_exists("icon_mobile", $Categories)) { //check image
            $image['icon_mobile'] = $request->file('icon_mobile')->store('');            
            $temp['key'] = $image['icon_mobile'];
            $temp['buket'] = 'islamic_images';
            $Categories['icon_mobile'] = $image['icon_mobile'];
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'categories' ]);
        }

        $response = $this->CategoriesRepository->create($Categories);
        return Utilities::wrap(['message' => 'Add Categories successfully'],$response['code']);
    }
   
    //Update Categories
    public function update(Request $request, $id) // Admin
    {
        $Categories = $request->validate([
            'name' => 'string',
            'icon' => 'string',
            'icon_mobile' => 'file|mimes:png',
        ]);
        $table = Categories::where('id', $id)->firstOrFail();    
        if (array_key_exists("icon_mobile", $Categories)) { //check image
            $image['icon_mobile'] = $request->file('icon_mobile')->store('');            
            $temp['key'] = $image['icon_mobile'];
            $temp['buket'] = 'islamic_images';
            $Categories['icon_mobile'] = $image['icon_mobile'];
            if($table->icon_mobile != null){
                $this->TempRemoveRepository->create(['key' => $table->icon_mobile, 'buket' => $temp['buket'], 'table' => 'categories']);
            }
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'categories']);
        }
        $response = $this->CategoriesRepository->update($id, $Categories);
        return Utilities::wrap(['message' => 'updated Categories successfully'],$response['code']);
    }

    //Delete Categories
    // public function destroy($id) // Admin
    // {
    //     $CategoriesModel = Categories::where('id', $id)->firstOrFail();
    //     $response = $this->CategoriesRepository->softDelete($CategoriesModel);
    //     return Utilities::wrap(['message' => $response['message']], $response['code']);
    // } 
}
