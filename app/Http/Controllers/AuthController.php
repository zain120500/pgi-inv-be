<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\ValidateUserRegistration;
use App\Http\Requests\ValidateUserLogin;
use App\User;
use App\Model\RoleMenu;
use App\Model\Menu;

use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }
    public function register(ValidateUserRegistration $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => 1
        ]); 
        return new UserResource($user); 
    }

    public function login(ValidateUserLogin $request){
      
        $user = "";
        $access_menu = "";

        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return  response()->json([ 
                'errors' => [
                    'msg' => ['Incorrect username or password.']
                ]  
            ], 401);

        } else {
            $user = auth()->user();

            $access_menu = $user->role->roleMenu;
            $access_menu = $access_menu->map(function ($query) {
                $query['menu'] = Menu::select(['id','code','name'])->where('id', $query->menu_id)->first();

                return $query;
            });
        }
    
        return response()->json([
            'type' =>'success',
            'message' => 'Logged in.',
            'token' => $token,
            'user' => $user
        ]);
    }
 
    public function user()
    { 
        $user = JWTAuth::parseToken()->authenticate();
        
        $roleMenu = $user->role->roleMenu;
        $roleMenu = $roleMenu->map(function ($query) {
            $query['menu'] = Menu::select(['id','code','name'])->where('id', $query->menu_id)->first();

            return $query;
        });
        
        return $user;
        // return auth()->user();
       //return new UserResource(auth()->user());
    }
}