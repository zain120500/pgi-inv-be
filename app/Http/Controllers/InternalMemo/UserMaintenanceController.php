<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\InternalMemo;
use App\Model\UserMaintenance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function paginate(Request $request)
    {
        $record = UserMaintenance::orderBy('id', 'DESC')->paginate(15);

        if($request->nama){
            $record = UserMaintenance::where('nama', 'like', '%' . $request->nama . '%')->orderBy('id', 'DESC')->paginate(15);
        }else if($request->no_telp){
            $record = UserMaintenance::where('no_telp', 'like', '%' . $request->no_telp . '%')->orderBy('id', 'DESC')->paginate(15);
        }
        else if($request->flag){
            $record = UserMaintenance::where('flag', 'like', '%' . $request->flag . '%')->orderBy('id', 'DESC')->paginate(15);
        }

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
        $files = $request['files'];

        if(!empty($files)) {
            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $record = UserMaintenance::create([
                    'nama' => $request->nama,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'foto' => $imageName,
                    'keterangan' => $request->keterangan,
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);

                $user = User::create([
                    'name' => $request->nama,
                    'email' => strtolower($request->nama).'@gmail.com',
                    'password' => bcrypt(123456789),
                    'role_id' => 1
                ]);

                $uM = UserMaintenance::where('id', $record->id)->first();
                $uM->update([
                    'user_id' => $user->id
                ]);
            }
        }else{
            $record = UserMaintenance::create([
                'nama' => $request->nama,
                'wilayah' => $request->wilayah,
                'pekerjaan' => $request->pekerjaan,
                'status' => $request->status,
                'no_telp' => $request->no_telp,
                'keterangan' => $request->keterangan,
                'flag' => 0,
                'created_by' => auth()->user()->id
            ]);
        }

        if($record){
            return $this->successResponse([$record, $user],Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function update(Request $request, $id)
    {
        $files = $request['files'];
        $record = UserMaintenance::find($id);

        if(empty($files)){
            $update = UserMaintenance::where('id', $record->id)->update([
                'nama' => $request->nama,
                'pekerjaan' => $request->pekerjaan,
                'no_telp' => $request->no_telp,
                'keterangan' => $request->keterangan,
            ]);
        }else{
            foreach ($files as $key => $file) {
                $image_64 = $file; //your base64 encoded data
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
                $replace = substr($image_64, 0, strpos($image_64, ',')+1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10).'.'.$extension;
                Storage::disk('sftp')->put($imageName, base64_decode(($image), 'r+'));

                $update = UserMaintenance::where('id', $record->id)->update([
                    'nama' => $request->nama,
                    'pekerjaan' => $request->pekerjaan,
                    'foto' => $imageName,
                    'no_telp' => $request->no_telp,
                    'keterangan' => $request->keterangan,
                ]);
            }
        }

        if($update){
            return $this->successResponse($update,Constants::HTTP_MESSAGE_200, 200);
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
