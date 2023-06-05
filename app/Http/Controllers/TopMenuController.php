<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\RoleMenu;
use App\Model\Menu;
use App\Model\TopMenu;
use App\Helpers\Constants;

class TopMenuController extends Controller
{
    public function index()
    {
        $getquery = TopMenu::get();

        $collect = $getquery->map(function ($query) {
            $query['menu'] = Menu::where('parent_id', $query->id)->get();

            return $query;
        });

        // return response()->json([
        //     'status' =>'success',
        //     'data' => $getquery
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $collect
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = TopMenu::create([
            "id" => $request->id,
            "code" => $request->code,
            "name" => $request->name
        ]);

        // return response()->json([
        //     'type' =>'success',
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
        $query = TopMenu::find($id);

        if (!empty($query)) {
            $query['menu'] = Menu::where('parent_id', $query->id)->get();

            // return $this->successResponse($query,'Success', 200);

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
                'Data is Null'
            );
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = TopMenu::where('id', $id)
            ->update([
                "code" => $request->code,
                "name" => $request->name,
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
        $query = TopMenu::find($id)->delete();

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
