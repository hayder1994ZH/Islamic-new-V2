<?php

namespace App\Http\Controllers;

use App\Helpers\Utilities;
use App\Models\Tags;
use App\Repository\TagsRepository;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private $TagsRepository;
    public function __construct()
    {
        $this->TagsRepository = new TagsRepository(new Tags());
        $this->middleware('role:Admin,owner', ['except' => ['index']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $response = $this->TagsRepository->getAll($take, $skip);
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
            'name' => 'required|string',
        ]);
        $response = $this->TagsRepository->create($data);
        return Utilities::wrap(['message' => 'Tag created successfully'],$response['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->TagsRepository->getById($id);
        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tag = $request->validate([
            'name' => 'required|string',
        ]);
        $response = $this->TagsRepository->update($id, $tag);
        return Utilities::wrap(['message' => 'tag updated'],$response['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tag = Tags::findOrFail($id);
        $response = $this->TagsRepository->softDelete($tag);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }
}
