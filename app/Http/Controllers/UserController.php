<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use App\Models\Agent;
use App\Models\TempRemove;
use App\Models\Roles;
use GuzzleHttp\Client;
use App\Helpers\Utilities;
use App\Models\Temp_files;
use Illuminate\Http\Request;
use App\Repository\TempRepository;
use App\Repository\TempRemoveRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
class UserController extends Controller
{
 

    private $UserRepository;
    private $TempRepository;
    public function __construct()
    {
        $this->TempRemoveRepository = new TempRemoveRepository(new TempRemove());
        $this->TempRepository = new TempRepository(new Temp_files());
        $this->UserRepository = new UserRepository(new User());
        $this->middleware('role:Admin,owner', ['only' => ['index', 'update', 'store']]);
        $this->middleware('role:owner', ['only' => ['destroy']]);
    }


    public function show(Request $request, $id) // Anyone
    {
        $domain = $request->get('host');
        $response = $this->UserRepository->find($id, $domain);
        return Utilities::wrap($response, 200);
    }

    public function register(Request $request) // Anyone
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);
        $data['role_id'] = 1;
        $response = $this->UserRepository->registerUser($data);
        return Utilities::wrap(['messge' => $response['message']], $response['code']);
    }

    public function login(Request $request) // Anyone
    {
        $valiation = $request->validate([
            'email' => 'required',
            'password' => 'required|min:6',
        ]);
        $response = $this->UserRepository->authenticate($valiation);
        return Utilities::wrap($response['message'], $response['code']);
    }

    public function logout() // Anyone
    {
        $response = $this->UserRepository->logoutUser();
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }

    public function me(Request $request) // Anyone
    {
        $domain = $request->get('host');
        $response = $this->UserRepository->me($domain);
        return $response[0];
    }


    public function updateProfile(Request $request) // Anyone
    {
        $data = $request->validate([
            'full_name' => 'nullable|string',
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'password' => 'string|min:6',
            'image' => 'nullable|file'
        ]);

        if (array_key_exists("password", $request->all())) {
            $data['password'] = Hash::make($data['password']);
        }
        $table = User::where('id', $id)->firstOrFail();
        if (array_key_exists("image", $data)) { //check image
            $image['key'] = $request->file('image')->store('');
            $temp['key'] = $image['key'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['key'];
            if($table->image != null){
                $this->TempRemoveRepository->create(['key' => $table->image, 'buket' => $temp['buket'], 'table' => 'users']);
            }
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'users' ]);
         
        }
        auth()->user()->update($data);
        return response()->json(["message" => "user updated"], 200);
    }


// ========================= Admin Functions =========================
    public function destroy($id) // Admin
    {
        $model = User::where('id', $id)->where('is_deleted', 0)->firstOrfail();
        $response = $this->UserRepository->softDelete($model);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }


    public function store(Request $request) // Admin
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|string|min:6',
            'role_id' => 'integer|exists:roles,id',
            'image' => 'nullable|file'
        ]);
        
        if (array_key_exists("image", $data)) { //check image
            $image['key'] = $request->file('image')->store('');
            $temp['key'] = $image['key'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['key'];
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'users' ]);
         
        }
        $response = $this->UserRepository->registerUser($data);
        return Utilities::wrap(['messge' => $response['message']], $response['code']);
    }

    //update user
    public function update(Request $request, $id) // Admin
    {
        $data = $request->validate([
            'full_name' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'role_id' => 'integer|exists:roles,id',
            'password' => 'string|min:6',
            'image' => 'file'
        ]);

        if (array_key_exists("password", $request->all())) {
            if (!is_null($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
        }
        $table = User::where('id', $id)->firstOrFail();
        if (array_key_exists("image", $data)) { //check image
            $image['key'] = $request->file('image')->store('');            
            $temp['key'] = $image['key'];
            $temp['buket'] = 'islamic_images';
            $data['image'] = $image['key'];
            if($table->image != null){
                $this->TempRemoveRepository->create(['key' => $table->image, 'buket' => $temp['buket'], 'table' => 'users']);
            }
            $this->TempRepository->create(['key' => $temp['key'], 'buket' => $temp['buket'], 'table' => 'users']);
        }
        $response = $this->UserRepository->update($id, $data);
        return Utilities::wrap(['message' => $response['message']], $response['code']);
    }


    public function index(Request $request) // Admin
    {
        
        //validations
        $request->validate([
            'skip' => 'Integer',
            'take' => 'required|Integer'
        ]);
        $take = $request->take;
        $skip = $request->skip;
        $domain = $request->get('host');
        $response = $this->UserRepository->getListUsers($skip, $take, $domain);
        return Utilities::wrap($response['message'], $response['code']);
    }

    public function getReport(Request $request)
    {
        $date = $request->validate([
            'fromDate' => 'required|string',
            'toDate' => 'required|string'
        ]);
        $sum = 0;
        // $fromDate= "2021-05-28";
        // $toDate = "2021-06-28";
        $domain = $request->get('host');
          $response = User::where('role_id', 1)->orwhere('role_id', 2)
                                ->withCount(['files' => function($query) use ($date)
                                    {
                                        $query->whereRaw(
                                            "(created_at >= ? AND created_at <= ?)", 
                                            [$date['fromDate']." 00:00:00", $date['toDate']." 23:59:59"]);
                                    }])
                                    ->with(['filesSize' => function($query) use ($date)
                                    {
                                       return $query->whereRaw(
                                            "(created_at >= ? AND created_at <= ?)", 
                                            [$date['fromDate']." 00:00:00", $date['toDate']." 23:59:59"])->sum('totale_size');
                                    }])
                                    ->get()
                                    ->map(function($item) use ($sum, $domain){
                                         $data['id'] = $item->id;
                                         $data['full_name'] = $item->full_name;
                                         $data['email'] = $item->email;
                                         $data['phone'] = $item->phone;
                                         $data['image'] = ($item->image != null)? $domain . $this->imageBuket . $item->image:null;
                                         $data['files_count'] = $item->files_count;
                                         foreach($item->filesSize as $value){
                                          $sum +=  $value->totale_size;
                                         }
                                         $data['files_size'] = $sum;
                                         $data['status'] = $item->status;
                                        return $data;
                                    });
    }


    public function loginAPi(Request $request)
    {
        //Validation
        $data = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);
        $response = Http::withHeaders([
            'Authorization' => 'Basic dm9kdTpBamhhSGcxZ0chR0dISko=',
        ])->post('http://192.168.98.254:8080/api2/api/v1/login', [
            "phone" => $request->phone,
            "password" => hash('sha256', sha1(md5($request->password))),
        ]);
         $res = json_decode($response->getBody(), true);
        if($response->status() != 200){
            return Utilities::wrap(['error' => 'invalid phone or pasword'], 400);
        }
        $user1 = User::where('phone', $res['account']['phone'])->where('email', $res['account']['email'])->first();
        if(!$user1){
            User::create([
                'full_name' => $res['account']['nicename'] ,
                'email' => $res['account']['email'] ,
                'phone' => $res['account']['phone'] ,
                'password' =>  Hash::make($request->password),
                'role_id' => 3
            ]);
        }
        if (!Hash::check($request->password, $user1->password)) { //check password
            $user1->update([
                'password' =>  Hash::make($request->password)
            ]);
        }
        $user = User::where('phone', $res['account']['phone'])->where('email', $res['account']['email'])->first();
        try {
            JWTAuth::factory()->setTTL(60 * 24 * 360 * 20);
            if (!$token = JWTAuth::fromUser($user)) {
                return Utilities::wrap(['error' => 'invalid credentials'], 400);
            }
        } catch (JWTException $e) {
            return Utilities::wrap(['error' => 'could not create_token'], 401);
        }
         $baseToken =  auth()->claims([
            'user_id' => $user->role_id,
            'full_name' => $user->full_name,
            'email' => $user->email,
         ])->fromUser($user);
        return Utilities::wrap(['token' => $baseToken], 200);

    }

    public function registerAPI(Request $request)
    {
        //Validation
        $data = $request->validate([
            'nicename' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email',
        ]);
        $response = Http::withHeaders([
            'Authorization' => 'Basic dm9kdTpBamhhSGcxZ0chR0dISko=',
        ])->post('http://192.168.98.254:8080/api2/api/v1/join', [
            "nicename" => $request->nicename,
            "phone" => $request->phone,
            "email" => $request->email,
        ]);
        $res = json_decode($response->getBody() , true);
        return Utilities::wrap($res, $response->status());
    }

    public function recoveryAPI(Request $request)
    {
        
        //Validation
        $data = $request->validate([
            'email' => 'required|string|email',
        ]);
        $response = Http::withHeaders([
            'Authorization' => 'Basic dm9kdTpBamhhSGcxZ0chR0dISko=',
        ])->post('http://192.168.98.254:8080/api2/api/v1/recovery', [
            "email" => $request->email,
        ]);
        $res = json_decode($response->getBody(), true);
        return Utilities::wrap($res, $response->status());

    }

    public function googleLogin(Request $request)
    {
        //Validation
        $data = $request->validate([
            'email' => 'required|string',
            'google_id' => 'required|string',
            'full_name' => 'required|string',
        ]);

        $fake = User::where('email', $data['email'])
                        ->with('roles')
                        ->first();
        
        
        if($fake) {
            $fuser = User::where('google_id', $data['google_id'])
                        ->where('email', $data['email'])
                        ->with('roles')
                        ->first();
            if(!$fuser){
                $fuser2 = User::where('google_id', null)
                ->where('email', $data['email'])
                ->with('roles')
                ->first();
                if(!$fuser2){
                    return Utilities::wrap(['erorr' => 'Unauthorized'], 401);
                }else{
                    $fuser2->update(['google_id'=> $data['google_id']]) ; 
                }
            }
        }
        if(!$fake) {
            User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'google_id'=> $data['google_id'],
                'password' => encrypt('hayderAlkobaryIsHere'),
                'role_id' => 3,
            ]); 
        } 
        $userData = User::where('email', $data['email'])
                            ->first();
        JWTAuth::factory()->setTTL(60*24*360*20);
            $token = JWTAuth::fromUser($userData);
            $token = auth()->claims([
            'user_id' => $userData->role_id,
            'email' => $userData->email,
            ])->fromUser($userData);
            $response = [
                'token' => $token
                ];
        return Utilities::wrap($response, 200);

    
    }
}
