<?php

namespace App\Http\Controllers;

use App\Model\StokPusat;
use App\Model\UserStaffCabang;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\ApiResponser;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponser;

    public function cabangGlobal()
    {
        $loginId = auth()->user()->id;
        $cabang = UserStaffCabang::select('cabang.id','cabang.name', 'cabang.kode')
            ->where('user_staff_id', $loginId)
            ->join('cabang', 'cabang.id', '=', '_user_staff_cabang.cabang_id')
            ->get();

        foreach ($cabang as $cab){
            if($cab->kode === "00001"){
                $query = StokPusat::where('cabang_id', $cab->id)->get();
            }else{
                $query = $cabang;
            }
        }

        return $query;
    }
}
