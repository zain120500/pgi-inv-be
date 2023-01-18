<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Role;
use App\Model\Menu;
use App\Model\RoleMenu;

class RoleController extends Controller
{
    public function index()
    {
        $query = Role::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Role::create([
                "name"=> $request->name,
                "level"=> $request->level,
                "is_active" => $request->is_active
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function show($id)
    {
        $query = Role::find($id);

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200);  
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $query = Role::where('id', $id)
            ->update([
                "name"=> $request->name,
                "level"=> $request->level,
                "is_active" => $request->is_active
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function destroy($id)
    {
        $query = Role::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
