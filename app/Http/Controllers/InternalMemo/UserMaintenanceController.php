<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\InternalMemo;
use App\Model\UserMaintenance;
use Illuminate\Http\Request;

class UserMaintenanceController extends Controller
{
    public function all(Request $request)
    {
        $record = UserMaintenance::orderBy('id', 'DESC')->get();
        if($request->nama){
            $record = UserMaintenance::where('nama', 'LIKE', $request->nama)->get();
        }elseif ($request->no_telp){
            $record = UserMaintenance::where('no_telp', 'LIKE', $request->no_telp)->get();
        }elseif ($request->pekerjaan){
            $record = UserMaintenance::where('pekerjaan', 'LIKE', $request->pekerjaan)->get();
        }

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function paginate()
    {
        $record = UserMaintenance::orderBy('id', 'DESC')->paginate(15);

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function show($id)
    {
        $record = UserMaintenance::find($id);

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function store(Request $request)
    {
        $record = UserMaintenance::create([
            'nama' => $request->nama,
            'pekerjaan' => $request->pekerjaan,
            'no_telp' => $request->no_telp,
            'keterangan' => $request->keterangan,
            'created_by' => auth()->user()->id
        ]);

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function update(Request $request, $id)
    {
        $record = UserMaintenance::find($id);

        $update = UserMaintenance::where('id', $record->id)->update([
            'nama' => $request->nama,
            'pekerjaan' => $request->pekerjaan,
            'no_telp' => $request->no_telp,
            'keterangan' => $request->keterangan,
        ]);

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function destroy($id)
    {
        $record = UserMaintenance::find($id)->delete();

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function getInternalMemo()
    {
        $record = InternalMemo::orderBy('id', 'DESC')->get();

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }
}
