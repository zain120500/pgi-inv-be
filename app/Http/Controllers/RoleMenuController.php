<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\Role;
use App\Model\Menu;
use App\Model\RoleMenu;


class RoleMenuController extends Controller
{

    public function index()
    {
        $query = RoleMenu::paginate(15);
        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }

    public function all()
    {
        $query = RoleMenu::all();
        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200);
    }

    public function store(Request $request)
    {
        $datas = $request->data;
        $query = [];
        foreach ($datas as $key => $data) {

            $getData = RoleMenu::where([ 
                "menu_id"=> $data['menuIds']
            ])->delete();

            foreach ($data['roleIds'] as $keys => $role_id) {
                $query[] = RoleMenu::create([
                    "role_id"=> $role_id,
                    "menu_id"=> $data['menuIds']
                ]);
            }
        }

        if($query){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Process failed', 403);
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
