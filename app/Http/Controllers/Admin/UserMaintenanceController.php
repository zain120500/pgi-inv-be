<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\UserMaintenance;
use Illuminate\Http\Request;

class UserMaintenanceController extends Controller
{
    public function index()
    {
        $record = UserMaintenance::orderBy('id', 'DESC')->get();

        if($record){
            return $this->successResponse($record,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }

    public function create(Request $request)
    {
        $record = UserMaintenance::create([
            'user_id' => auth()->user()->id,
            'nama' => $request->nama,
            'pekerjaan' => $request->pekerjaan,
            'keterangan' => $request->keterangan,
            'created_by' => auth()->user()->id
        ]);

        if($record){
            return $this->successResponse($record,'Success', 200);
        } else {
            return $this->errorResponse('Process Data error', 403);
        }
    }
}
