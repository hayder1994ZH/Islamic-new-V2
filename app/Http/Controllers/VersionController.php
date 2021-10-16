<?php

namespace App\Http\Controllers;

use App\Models\Version;
use App\Helpers\Utilities;
use App\Models\Temp_files;
use App\Models\TempRemove;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repository\TempRepository;
use App\Repository\VersionRepository;
use App\Repository\TempRemoveRepository;

class VersionController extends Controller
{
    private $VersionRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->VersionRepository = new VersionRepository(new Version());
        $this->middleware('role:Admin,owner', ['only' => ['destroy', 'update', 'store']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
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
        $response = $this->VersionRepository->getAllVersion($take, $skip, $domain);
        return Utilities::wrap($response, 200);
    }

    public function show(Request $request, $id)
    {
        $Version = Version::where('id', $id)->firstOrFail();
        return $Version;
    }

    public function showByVersion(Request $request, $version)
    {
        $Version = Version::where('version', $version)->firstOrFail();
        return $Version;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'version' => 'required|string',
            'url' => 'required|string',
        ]);
        
        $this->VersionRepository->create($data);
        return Utilities::wrap(['message' => 'Version Created'], 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'version' => 'string',
            'public' => 'integer|in:0,1',
            'active' => 'integer|in:0,1',
            'url' => 'string',
        ]);
        
        $response = $this->VersionRepository->update($id, $data);
        return Utilities::wrap(['message' => 'Version updted successfully'],$response['code']);
    }   
   
    public function destroy($id)
    {
        $Version = Version::where('id', $id)->firstOrFail();
        $response = $this->VersionRepository->softDelete($Version);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    } 

}
