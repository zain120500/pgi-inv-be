<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Cabang;

class CabangController extends Controller
{
    public function index()
    {
        $cabang = Cabang::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $cabang
        ], 200); 
    }

    public function all()
    {
        $cabang = Cabang::select('id','name','alamat','hp','kode')->get();

        return response()->json([
            'status' =>'success',
            'data' => $cabang
        ], 200); 
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $cabang = Cabang::create([
            "is_active"=> $request->is_active,
            "old_id"=> $request->old_id,
            "name"=> $request->name,
            "alamat"=> $request->alamat,
            "cash_in_hand_berjalan"=> $request->cash_in_hand_berjalan,
            "counter_anggota"=> $request->counter_anggota,
            "counter_faktur"=> $request->counter_faktur,
            "hp"=> $request->hp,
            "kode"=> $request->kode,
            "skema_hitung"=> $request->skema_hitung,
            "telepon"=> $request->telepon,
            "created_by_id"=> $request->created_by_id,
            "updated_by_id"=> $request->updated_by_id,
            "kabupaten_kota_id"=> $request->kabupaten_kota_id,
            "kepala_cabang_id"=> $request->kepala_cabang_id,
            "kepala_cabang_senior_id"=> $request->kepala_cabang_senior_id,
            "kepala_unit_id"=> $request->kepala_unit_id,
            "kas_awal"=> $request->kas_awal,
            "ip_address"=> "",
            "latitude"=> $request->latitude,
            "longitude"=> $request->longitude
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $cabang
        ]);

    }

    public function show($id)
    {
        $cabang = Cabang::find($id);

        if(!empty($cabang)){
            return $this->successResponse($cabang,'Success', 200);
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
        $cabang = Cabang::where('id', $id)
            ->update([
            "is_active"=> $request->is_active,
            "old_id"=> $request->old_id,
            "name"=> $request->name,
            "alamat"=> $request->alamat,
            "cash_in_hand_berjalan"=> $request->cash_in_hand_berjalan,
            "counter_anggota"=> $request->counter_anggota,
            "counter_faktur"=> $request->counter_faktur,
            "hp"=> $request->hp,
            "kode"=> $request->kode,
            "skema_hitung"=> $request->skema_hitung,
            "telepon"=> $request->telepon,
            "created_by_id"=> $request->created_by_id,
            "updated_by_id"=> $request->updated_by_id,
            "kabupaten_kota_id"=> $request->kabupaten_kota_id,
            "kepala_cabang_id"=> $request->kepala_cabang_id,
            "kepala_cabang_senior_id"=> $request->kepala_cabang_senior_id,
            "kepala_unit_id"=> $request->kepala_unit_id,
            "kas_awal"=> $request->kas_awal,
            "ip_address"=> "",
            "latitude"=> $request->latitude,
            "longitude"=> $request->longitude
        ]);

        return response()->json([
            'status' =>'success',
            'data' => $cabang
        ], 200);

    }

    public function destroy($id)
    {
        $query = Cabang::find($id)->delete();

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200); 
    }
}
