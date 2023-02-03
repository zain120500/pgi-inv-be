<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Paginator;
use App\Model\Devisi;


class DevisiController extends Controller
{

    public function index()
    {
        $getData = Devisi::paginate(15);
        return $this->successResponse($getData,'Success', 200);
    }

    public function all(Request $request)
    {
        if(!empty($request->is_fpp)){
            $getData = Devisi::where('isFpp', $request->is_fpp)->get()->makeHidden(['created_at','updated_at']);
        } else {
            $getData = Devisi::all()->makeHidden(['created_at','updated_at']);
        }

        return $this->successResponse($getData,'Success', 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Devisi::create([
            "kd_Divisi"=> $request->kd_Divisi,
            "nm_Divisi"=> $request->nm_Divisi,
            "UserInput"=> $request->UserInput
        ]);

        return $this->successResponse($query,'Success', 200);
    }

    public function show($id)
    {
        $query = Devisi::find($id);

        if(!empty($query)){
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
        $query = Devisi::where('DivisiID', $id)
            ->update([
                "kd_Divisi"=> $request->kd_Divisi,
                "nm_Divisi"=> $request->nm_Divisi,
                "UserInput"=> $request->UserInput
            ]);
            
        return $this->successResponse($query,'Success', 200);
    }

    public function destroy($id)
    {
        $query = Devisi::find($id)->delete();

        return $this->successResponse($query,'Success', 200);

    }
}
