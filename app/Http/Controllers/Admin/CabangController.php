<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Cabang;
use App\Model\UserStaffCabang;
use Illuminate\Support\Facades\DB;


class CabangController extends Controller
{
    public function index()
    {
        $cabang = Cabang::paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $cabang
        );
    }

    public function all()
    {
        $cabang = Cabang::select('id', 'name', 'alamat', 'hp', 'kode')->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $cabang
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $cabang = Cabang::create([
            "is_active" => $request->is_active,
            "old_id" => $request->old_id,
            "name" => $request->name,
            "alamat" => $request->alamat,
            "cash_in_hand_berjalan" => $request->cash_in_hand_berjalan,
            "counter_anggota" => $request->counter_anggota,
            "counter_faktur" => $request->counter_faktur,
            "hp" => $request->hp,
            "kode" => $request->kode,
            "skema_hitung" => $request->skema_hitung,
            "telepon" => $request->telepon,
            "created_by_id" => $request->created_by_id,
            "updated_by_id" => $request->updated_by_id,
            "kabupaten_kota_id" => $request->kabupaten_kota_id,
            "kepala_cabang_id" => $request->kepala_cabang_id,
            "kepala_cabang_senior_id" => $request->kepala_cabang_senior_id,
            "kepala_unit_id" => $request->kepala_unit_id,
            "kas_awal" => $request->kas_awal,
            "ip_address" => "",
            "latitude" => $request->latitude,
            "longitude" => $request->longitude
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $cabang
        );
    }

    public function show($id)
    {
        $query = Cabang::find($id);
        $query['user_cabang'] = DB::table('_user_staff_cabang')
            ->where('cabang_id', $query->id)
            ->join('users', 'users.id', '=', '_user_staff_cabang.user_staff_id')
            ->join('role', 'role.id', '=', '_user_staff_cabang.role_id')
            ->join('tbl_divisi', 'tbl_divisi.DivisiID', '=', 'users.devisi_id')
            ->select('_user_staff_cabang.*', 'users.name', 'users.username', 'role.name as role_name', 'tbl_divisi.nm_Divisi as nama_divisi')
            ->get();

        //        $query = Cabang::where('id', $id)->with('userStaffCabang.user.devisi', 'userStaffCabang.user.role')->first();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $cabang = Cabang::where('id', $id)
            ->update([
                "is_active" => $request->is_active,
                "old_id" => $request->old_id,
                "name" => $request->name,
                "alamat" => $request->alamat,
                "cash_in_hand_berjalan" => $request->cash_in_hand_berjalan,
                "counter_anggota" => $request->counter_anggota,
                "counter_faktur" => $request->counter_faktur,
                "hp" => $request->hp,
                "kode" => $request->kode,
                "skema_hitung" => $request->skema_hitung,
                "telepon" => $request->telepon,
                "created_by_id" => $request->created_by_id,
                "updated_by_id" => $request->updated_by_id,
                "kabupaten_kota_id" => $request->kabupaten_kota_id,
                "kepala_cabang_id" => $request->kepala_cabang_id,
                "kepala_cabang_senior_id" => $request->kepala_cabang_senior_id,
                "kepala_unit_id" => $request->kepala_unit_id,
                "kas_awal" => $request->kas_awal,
                "ip_address" => "",
                "latitude" => $request->latitude,
                "longitude" => $request->longitude
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $cabang
        );
    }

    public function destroy($id)
    {
        $query = Cabang::find($id)->delete();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
