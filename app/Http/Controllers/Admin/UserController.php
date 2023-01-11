<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Paginator;
use App\Model\Menu;


class UserController extends Controller
{

    public function index()
    {
        $users = User::paginate(15);

        $collect_user = $users->getCollection()->map(function ($query) {
            $query->role;
            $query->admin;

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $users->setCollection($collect_user)

        ], 200);
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
            $q['menu'] = Menu::select(['id','code','name'])->where('id', $q->menu_id)->first();

            return $q;
        });
        
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
        //
    }

    public function destroy($id)
    {
        //
    }
}
