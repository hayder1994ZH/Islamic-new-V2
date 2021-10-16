<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use App\Helpers\Utilities;
use Illuminate\Http\Request;
use App\Models\Permissions_roles;
use App\Repository\RolesRepository;

class RolesController extends Controller
{
       
    private $RolesRepository;
    private $id;
    public function __construct()
    {
        $this->RolesRepository = new RolesRepository(new Roles());
        $this->middleware('role:owner', ['only' => ['store', 'index']]);
    }

    
    //All Roles data
    public function index(Request $request)
    {
       $request->validate([
           'skip' => 'Integer',
           'take' => 'required|Integer'
       ]);
       $take = $request->take;
       $skip = $request->skip;       
       $response = $this->RolesRepository->getListRoles($skip, $take);
       return Utilities::wrap($response, 200);
    }
     
    //Get Single Roles
    public function show($id)
    {
        // $response = $this->RolesRepository->getById($id);
        // return $response;
    }

    //Add Roles
    public function store(Request $request)
    {
        // Validations
        $Roles = $request->validate([
            'name' => 'required|string',
        ]);
        $response = $this->RolesRepository->create($Roles);
        return Utilities::wrap(['message' => 'Add Roles successfully'],$response['code']);
    }

    //Update Roles
    public function update(Request $request, $id)
    {
        // $Roles = $request->validate([
        //     'name' => 'nullable|string',
        // ]);
        // $response = $this->RolesRepository->update($id, $Roles);
        // return Utilities::wrap(['message' => 'updated Roles successfully'],$response['code']);
    }
   

    // Delete Roles
    public function destroy($id)
    {
        // $RolesModel = Roles::where('id', $id)->firstOrFail();
        // $response = $this->RolesRepository->softDelete($RolesModel);
        // return Utilities::wrap(['message' => $response['message']], $response['code']);
    }


    //Add Roles
    // public function addPermissions_roles(Request $request)
    // {
    //     //Validations
    //     $Roles = $request->validate([
    //         'roles_id' => 'required|string',
    //         'permissions_id' => 'required|string',
    //     ]);

    //     //Processing
    //     $response = Permissions_roles::create($Roles);

    //     //Response
    //     return Utilities::wrap(['message' => 'Add permissions roles successfully'],$response['code']);
    // }

}
