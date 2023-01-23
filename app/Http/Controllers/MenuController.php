<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\RoleMenu;
use App\Model\Menu;
use App\Model\TopMenu;


class MenuController extends Controller
{

    public function index()
    {
        $getquery = Menu::get();

        $collect = $getquery->map(function ($query) {
            $query['top_menu'] = TopMenu::where('id', $query->parent_id)->get();

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $getquery
        ], 200);  
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Menu::create([
            "id"=> $request->id,
            "code"=> $request->code,
            "name"=> $request->name,
            "parent_id"=> $request->parent_id,
            "user_specific_menu"=> $request->user_specific_menu

        ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function show($id)
    {
        $query = Menu::find($id);

        if(!empty($query)){
            $query['top_menu'] = TopMenu::where('id', $query->parent_id)->get();

            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = Menu::where('id', $id)
            ->update([
                "code"=> $request->code,
                "name"=> $request->name,
                "parent_id"=> $request->parent_id,
                "user_specific_menu"=> $request->user_specific_menu
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function destroy($id)
    {
        $query = Menu::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
