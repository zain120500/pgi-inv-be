<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserRequest;
use Illuminate\Http\Request;
use App\User;
use Paginator;
use App\Model\Menu;


class UserController extends Controller
{
    public function all(Request $request)
    {
        $users = User::query();

        if ($request->search) {
            $users = $users->where('name', 'like', '%' . $request->search . '%')->orWhere('username', 'like', '%' . $request->search . '%');
        }else if($request->sort){
            $users = $users->orderBy('id', $request->sort);
        }

        $result = $users->orderBy('id', 'DESC')->get();

        $result->map(function ($query) {
            $query->role;
            $query->admin;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $result
        );
    }

    public function index(Request $request)
    {
        $users = User::query();

        if ($request->search) {
            $users = $users->where('name', 'like', '%' . $request->search . '%')->orWhere('username', 'like', '%' . $request->search . '%');
        }else if($request->sort){
            $users = $users->orderBy('id', $request->sort);
        }

        $result = $users->orderBy('id', 'DESC')->paginate(15);

        $result->getCollection()->map(function ($query) {
            $query->role;
            $query->admin;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $result
        );
    }

    public function create()
    {
        //
    }

    public function store(UserRequest $request)
    {
        $record = User::create([
            "name" => $request->name,
            "username" => $request->username,
            "email" => $request->email,
            "role_id" => $request->role_id,
            "devisi_id" => $request->devisi_id,
            "password" => bcrypt($request->password),
            "is_active" => 1,
            "is_new_user" => 0,
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function show($id)
    {
        $query = User::find($id);
        $access_menu = $query->role->roleMenu;
        $query->admin;

        $access_menu = $access_menu->map(function ($q) {
            $q['menu'] = Menu::select(['id', 'code', 'name'])->where('id', $q->menu_id)->first();

            return $q;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $record = User::where('id', $id)->first();

        if($request->password != null){
            $record->update([
                "name" => $request->name,
                "username" => $request->username,
                "email" => $request->email,
                "role_id" => $request->role_id,
                "devisi_id" => $request->devisi_id,
                "password" => bcrypt($request->password),
                "is_active" => $request->is_active
            ]);
        }else{
            $record->update([
                "name" => $request->name,
                "username" => $request->username,
                "email" => $request->email,
                "role_id" => $request->role_id,
                "devisi_id" => $request->devisi_id,
                "is_active" => $request->is_active
            ]);
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }

    public function destroy($id)
    {
        //
    }

    public function usersByRole()
    {
        $filter = ['Kepala Cabang', 'Kepala Cabang Senior'];

        $user = User::query();
        $record = $user->with('roleIm')
            ->whereHas('roleIm', function ($q) use ($filter) {
                $q->whereIn('name', $filter);
            })
            ->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $record
        );
    }
}
