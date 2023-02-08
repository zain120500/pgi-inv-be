<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Cabang;
use App\Model\RoleMenu;
use App\Model\Role;
use App\User;

class CabangUserController extends Controller
{

    public function index()
    {
        $cabang = Cabang::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $cabang
        ], 200); 
    }

    public function getUserKCS(){ //Jika Kepala Cabang Senior (5)
        $query = User::select('id','name')->where('role_id', 5)->get();

        if(!empty($query)){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function getUserKC(){ //Jika Kepala Cabang (4)
        $query = User::select('id','name')->where('role_id', 4)->get();

        if(!empty($query)){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
    }

    public function getUserKU(){ //Jika Kepala Unit (3)
        $query = User::select('id','name')->where('role_id', 3)->get();

        if(!empty($query)){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data is Null', 403);
        }
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
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {        
        $cabang = Cabang::where('id', $id)->update([
            "kepala_cabang_id"=> $request->kepala_cabang_id,
            "kepala_cabang_senior_id"=> $request->kepala_cabang_senior_id,
            "kepala_unit_id"=> $request->kepala_unit_id,
        ]);

        if(!empty($cabang)){
            return $this->successResponse($cabang,'Success', 200);
        } else {
            return $this->errorResponse('Data update Error', 403);
        }
    }

    public function destroy($id)
    {
        //
    }
}
