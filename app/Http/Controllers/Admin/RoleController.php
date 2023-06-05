<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Role;
use App\Model\Menu;
use App\Model\RoleMenu;
use App\Helpers\Constants;

class RoleController extends Controller
{
    public function index()
    {
        $query = Role::all();

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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Role::create([
            "name" => $request->name,
            "level" => $request->level,
            "is_active" => $request->is_active
        ]);

        // return response()->json([
        //     'type' => 'success',
        //     'data' => $query
        // ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {
        $query = Role::find($id);

        if (!empty($query)) {
            // return $this->successResponse($query, 'Success', 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $query
            );
        } else {
            // return $this->errorResponse('Data is Null', 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $query
            );
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = Role::where('id', $id)
            ->update([
                "name" => $request->name,
                "level" => $request->level,
                "is_active" => $request->is_active
            ]);

        // return response()->json([
        //     'type' => 'success',
        //     'data' => $query
        // ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = Role::find($id)->delete();

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $query
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
