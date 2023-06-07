<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Paginator;
use App\Model\Menu;


class UserController extends Controller
{
    public function all()
    {
        $users = User::orderBy('created_at', 'ASC')->get();

        $collect_user = $users->map(function ($query) {
            $query->role;
            $query->admin;

            return $query;
        });

        if ($users) {
            // return $this->successResponse($users, Constants::HTTP_MESSAGE_200, 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $users
            );
        } else {
            // return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $users
            );
        }
    }

    public function index()
    {
        $users = User::paginate(15);

        $collect_user = $users->getCollection()->map(function ($query) {
            $query->role;
            $query->admin;

            return $query;
        });

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $users->setCollection($collect_user)
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $users->setCollection($collect_user)
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
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

        return $this->successResponse($query, 'Success', 200);


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
        //
    }

    public function destroy($id)
    {
        //
    }
}
