<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Supplier;


class SupplierController extends Controller
{

    public function index()
    {
        $supplier = Supplier::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $supplier
        ], 200); 
    }

    public function all()
    {
        $supplier = Supplier::all()->makeHidden(['created_at','updated_at']);

        return response()->json([
            'status' =>'success',
            'data' => $supplier
        ], 200); 
    }

    
    public function store(Request $request)
    {
        $query = Supplier::create([
            "nama"=> $request->nama,
            "alamat"=> $request->alamat,
            "no_hp"=> $request->no_hp,
            "link_web"=> $request->link_web,
            "keterangan"=> $request->keterangan
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function show($id)
    {
        $query = Supplier::find($id);

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
        $query = Supplier::where('id', $id)
            ->update([
                "nama"=> $request->nama,
                "alamat"=> $request->alamat,
                "no_hp"=> $request->no_hp,
                "link_web"=> $request->link_web,
                "keterangan"=> $request->keterangan
            ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }


    public function destroy($id)
    {
        $query = Supplier::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
