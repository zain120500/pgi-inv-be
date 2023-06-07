<?php

namespace App\Http\Controllers;

use App\Helpers\Constants;
use App\Http\Resources\User as UserResource;
use App\Model\KategoriPicFpp;
use App\Model\KategoriProsesPic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\ValidateUserRegistration;
use App\Http\Requests\ValidateUserLogin;
use App\User;
use App\Model\UserStaffCabang;
use App\Model\RoleMenu;
use App\Model\Role;
use App\Model\Menu;
use App\Model\TopMenu;
use App\Model\Cabang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'submitForgetPasswordForm', 'submitResetPasswordForm']]);
    }

    public function register(ValidateUserRegistration $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'role_id' => 1
        ]);
        return new UserResource($user);
    }

    public function login(ValidateUserLogin $request)
    {
        $user = "";
        $id_top_menu = [];

        $credentials = request(['username', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return $this->errorResponse('Incorrect username or password.', 401);
        } else if (auth()->user()->is_active == 1) {
            $user = auth()->user();
            $access_menu = $user->role->roleMenu;
            $access_menu = $access_menu->map(function ($query) use ($id_top_menu) {
                $query['menu'] = Menu::select(['id', 'code', 'name', 'parent_id'])->where('id', $query->menu_id)->first();
                return $query;
            });

            //get Top Menu from Menu Model
            foreach ($user->role->roleMenu as $key => $val_menu) {
                if (!empty($val_menu->menu->parent_id)) {
                    if (!in_array($val_menu->menu->parent_id, $id_top_menu)) {
                        $id_top_menu[] = $val_menu->menu->parent_id;
                    }
                }
            }
            // $cabang = "";
            // if ($user->role_id == 3) {         //Jika Kepala Unit (3)
            //     $cabang = Cabang::select('id','name')->where('kepala_unit_id', $user->id)->get();
            // } elseif ($user->role_id == 4) {         //Jika Kepala Cabang (4)
            //     $cabang = Cabang::select('id','name')->where('kepala_cabang_id', $user->id)->get();
            // } elseif ($user->role_id == 5) {        //Jika Kepala Cabang Senior (5)
            //     $cabang = Cabang::select('id','name')->where('kepala_cabang_senior_id', $user->id)->get();
            // }

            $cabang = UserStaffCabang::select('cabang.id', 'cabang.name', 'cabang.kode')
                ->where('user_staff_id', auth()->user()->id)
                ->join('cabang', 'cabang.id', '=', '_user_staff_cabang.cabang_id')
                ->get();

            $top_menu = TopMenu::whereIn('id', $id_top_menu)->pluck('code');

            $kategoriProses = KategoriPicFpp::where('user_id', auth()->user()->id)->get();

            return response()->json([
                'type' => 'success',
                'message' => 'Logged in.',
                'token' => $token,
                'user' => $user,
                'top_menu' => $top_menu,
                'cabang' => $cabang,
                'kategori_proses' => $kategoriProses
            ]);
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_9007, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                Constants::ERROR_MESSAGE_9007,
                null
            );
        }
    }

    public function user()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $roleMenu = $user->role->roleMenu;
        $roleMenu = $roleMenu->map(function ($query) {
            $query['menu'] = Menu::select(['id', 'code', 'name'])->where('id', $query->menu_id)->first();

            return $query;
        });

        return $user;
        // return auth()->user();
        //return new UserResource(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        auth()->invalidate(true);

        return response()->json([
            'code' => 200,
            'status' => Constants::HTTP_MESSAGE_200
        ]);
    }

    public function refreshToken()
    {
        try {
            $token = auth()->refresh(true, true);
        } catch (TokenInvalidException $e) {
            throw new AccessDeniedHttpException('The token is invalid');
        }

        return response()->json([
            'code' => 200,
            'status' => Constants::HTTP_MESSAGE_200,
            'token' => $token
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $user = User::where('id', auth()->user()->id)->first();

        $user->update([
            'password' => bcrypt($request->password),
            'is_new_user' => 1
        ]);

        if ($user) {
            // return $this->successResponse($user, Constants::ERROR_MESSAGE_9005, 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                Constants::ERROR_MESSAGE_9005,
                $user
            );
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                Constants::ERROR_MESSAGE_403,
                null
            );
        }
    }

    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        if ($request->email) {
            // return $this->successResponse($request->email, Constants::ERROR_MESSAGE_9004, 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                Constants::ERROR_MESSAGE_9004,
                $request->email
            );
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                Constants::ERROR_MESSAGE_403,
                null
            );
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $updatePassword = DB::table('password_resets')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$updatePassword) {
            return back()->withInput()->with('error', 'Invalid token!');
        }

        $user = User::where('email', $request->email)
            ->update(['password' => bcrypt($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        if ($user) {
            // return $this->successResponse($user, Constants::ERROR_MESSAGE_9005, 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                Constants::ERROR_MESSAGE_9005,
                $user
            );
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                Constants::ERROR_MESSAGE_403,
                null
            );
        }
    }
}
