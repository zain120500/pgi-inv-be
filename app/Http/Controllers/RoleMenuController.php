<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\Role;
use App\Model\Menu;
use App\Model\RoleMenu;
use App\Helpers\Constants;


class RoleMenuController extends Controller
{

    public function index()
    {
        $query = RoleMenu::paginate(15);

        // return response()->json([
        //     'status' =>'success',
        //     'data' => $query
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function all()
    {
        $getquery = Menu::select(['id', 'code', 'name'])->get();

        $collect = $getquery->map(function ($query) {
            $query['role'] = RoleMenu::where('menu_id', $query->id)->pluck('role_id')->toArray();
            return $query;
        });

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $getquery
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $getquery
        );
    }

    public function store(Request $request)
    {
        $datas = $request->data;
        $query = [];
        foreach ($datas as $key => $data) {

            $getData = RoleMenu::where([
                "menu_id" => $data['menuIds']
            ])->delete();

            foreach ($data['roleIds'] as $keys => $role_id) {
                $query[] = RoleMenu::create([
                    "role_id" => $role_id,
                    "menu_id" => $data['menuIds']
                ]);
            }
        }

        if ($query) {
            // return $this->successResponse($query, 'Success', 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $query
            );
        } else {
            // return $this->errorResponse('Process failed', 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $query
            );
        }
    }

    public function show($id)
    {
        //
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
