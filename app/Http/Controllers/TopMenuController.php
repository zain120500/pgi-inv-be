<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\RoleMenu;
use App\Model\Menu;
use App\Model\TopMenu;

class TopMenuController extends Controller
{
    public function index()
    {
        $getquery = TopMenu::paginate(15);

        $collect = $getquery->getCollection()->map(function ($query) {
            $query['menu'] = Menu::where('parent_id', $query->id)->get();

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $getquery->setCollection($collect)
        ], 200);  
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = TopMenu::create([
            "id"=> $request->id,
            "code"=> $request->code,
            "name"=> $request->name
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function show($id)
    {
        $query = TopMenu::find($id);
        $query['menu'] = Menu::where('parent_id', $query->id)->get();

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
        $query = TopMenu::where('id', $id)
            ->update([
                "code"=> $request->code,
                "name"=> $request->name,
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function destroy($id)
    {
        $query = TopMenu::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
