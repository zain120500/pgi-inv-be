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

class GodmodController extends Controller
{
    public function godmod($id)
    {
        $user = "";
        $id_top_menu = [];
        $user = User::find($id);
        if (!$token = JWTAuth::fromUser($user)) {
            return $this->errorResponse('Incorrect username or password.', 401);
        } else if ($user->is_active == 1) {
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

            $cabang = UserStaffCabang::select('cabang.id', 'cabang.name', 'cabang.kode')
                ->where('user_staff_id', $user->id)
                ->join('cabang', 'cabang.id', '=', '_user_staff_cabang.cabang_id')
                ->get();

            $top_menu = TopMenu::whereIn('id', $id_top_menu)->pluck('code');

            $kategoriProses = KategoriPicFpp::where('user_id', $user->id)->get();

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
                $user
            );
        }
    }
}
