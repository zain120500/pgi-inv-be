<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Cabang;
use App\Model\RoleMenu;
use App\Model\Role;
use App\User;
use App\Model\UserStaffCabang;

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

    public function getCabangUser(Request $request)
    {
        $query = UserStaffCabang::query();
        if(!empty($request->user)){
            $query =  $query->where('user_staff_id', $request->user);
        }if(!empty($request->cabang)){
            $query =  $query->where('cabang_id', $request->cabang);
        }if(!empty($request->role)){
            $query =  $query->where('role_id', $request->role);
        }

        $result = $query->get();

        $result->map(function ($q) {
            $q->user;
            $q->role;
            $q->cabang;
            return $q;
        });

        return $result;
    }

    public function update(Request $request)
    {
        $cabangs = $request->cabang_id;

        $querys = UserStaffCabang::where('user_staff_id', $request->user_staff_id)->delete();

        $user = User::find($request->user_staff_id);

        foreach ($cabangs as $key => $cabang) {
            $query = UserStaffCabang::create([
                "user_staff_id"=> $request->user_staff_id,
                "cabang_id"=> $cabang,
                "role_id"=> $user->role_id,
            ]);
        }

        if(!empty($query)){
            return $this->successResponse($query,'Success', 200);
        } else {
            return $this->errorResponse('Data update Error', 403);
        }
    }

    public function userCabangUpdate(){
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
