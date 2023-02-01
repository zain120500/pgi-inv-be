<?php

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\ValidateUserRegistration;
use App\Http\Requests\ValidateUserLogin;
use App\User;
use App\Model\RoleMenu;
use App\Model\Role;
use App\Model\Menu;
use App\Model\TopMenu;
use App\Model\Cabang;
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
        $id_top_menu = [];

        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return $this->errorResponse('Incorrect username or password.', 401);
        } else {
            $user = auth()->user();
            $access_menu = $user->role->roleMenu;
            $access_menu = $access_menu->map(function ($query) use ($id_top_menu) {
                $query['menu'] = Menu::select(['id','code','name','parent_id'])->where('id', $query->menu_id)->first();
                return $query;
            });
        }

        //get Top Menu from Menu Model
        foreach ($user->role->roleMenu as $key => $val_menu) {
            if(!empty($val_menu->menu->parent_id)){
                if( !in_array( $val_menu->menu->parent_id ,$id_top_menu ) ){
                    $id_top_menu[] = $val_menu->menu->parent_id;
                }
            }
        }

        $cabang = "";
        if ($user->role_id == 3) {         //Jika Kepala Unit (3)
            $cabang = Cabang::select('id','name')->where('kepala_unit_id', $user->id)->get();
        } elseif ($user->role_id == 4) {         //Jika Kepala Cabang (4)
            $cabang = Cabang::select('id','name')->where('kepala_cabang_id', $user->id)->get();
        } elseif ($user->role_id == 5) {        //Jika Kepala Cabang Senior (5)
            $cabang = Cabang::select('id','name')->where('kepala_cabang_senior_id', $user->id)->get();
        }

        $top_menu = TopMenu::whereIn('id', $id_top_menu)->pluck('code');

        return response()->json([
            'type' =>'success',
            'message' => 'Logged in.',
            'token' => $token,
            'user' => $user,
            'top_menu'=> $top_menu,
            'cabang' => $cabang
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