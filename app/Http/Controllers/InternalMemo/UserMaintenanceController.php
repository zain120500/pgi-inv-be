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
        $record = UserMaintenance::with('user')->orderBy('id', 'DESC')->paginate(15);

        if($request->nama){
            $record = UserMaintenance::where('nama', 'like', '%' . $request->nama . '%')->with('user')->orderBy('id', 'DESC')->paginate(15);
        }else if($request->no_telp){
            $record = UserMaintenance::where('no_telp', 'like', '%' . $request->no_telp . '%')->with('user')->orderBy('id', 'DESC')->paginate(15);
        }
        else if($request->flag){
            $record = UserMaintenance::where('flag', 'like', '%' . $request->flag . '%')->with('user')->orderBy('id', 'DESC')->paginate(15);
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
        $record->user;

        if($record){
            return $this->successResponse($record,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function store(Request $request)
    {
        $files = $request['foto'];
        $ktp = $request['ktp'];

        if(!empty($files) && !empty($ktp)) {
            $image_64 = $files; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',')+1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $foto = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($foto, base64_decode(($image), 'r+'));

            $image = $ktp; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image, 0, strpos($image, ',')+1);
            $images = str_replace($replace, '', $image);
            $images = str_replace(' ', '+', $images);
            $ktp = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($ktp, base64_decode(($images), 'r+'));

            try {
                $user = User::create([
                    'name' => $request->nama,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role_id' => $request->role_id,
                    'devisi_id' => 5
                ]);

                $record = UserMaintenance::create([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'user_id' => $user->id,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'foto' => $foto,
                    'ktp' => $ktp,
                    'keterangan' => $request->keterangan,
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(!empty($files) && empty($ktp)) {
            $image_64 = $files; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',')+1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $foto = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($foto, base64_decode(($image), 'r+'));

            try {
                $user = User::create([
                    'name' => $request->nama,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role_id' => $request->role_id,
                    'devisi_id' => 5
                ]);

                $record = UserMaintenance::create([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'user_id' => $user->id,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'foto' => $foto,
                    'keterangan' => $request->keterangan,
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(empty($files) && !empty($ktp)) {
            $image = $ktp; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image, 0, strpos($image, ',')+1);
            $images = str_replace($replace, '', $image);
            $images = str_replace(' ', '+', $images);
            $ktp = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($ktp, base64_decode(($images), 'r+'));

            try {
                $user = User::create([
                    'name' => $request->nama,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role_id' => $request->role_id,
                    'devisi_id' => 5
                ]);

                $record = UserMaintenance::create([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'user_id' => $user->id,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'ktp' => $ktp,
                    'keterangan' => $request->keterangan,
                    'flag' => 0,
                    'created_by' => auth()->user()->id
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(empty($files) && empty($ktp)){
            $user = User::create([
                'name' => $request->nama,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
                'devisi_id' => 5
            ]);

            $record = UserMaintenance::create([
                'nama' => $request->nama,
                'username' => $request->username,
                'user_id' => $user->id,
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
        $files = $request['foto'];
        $ktp = $request['ktp'];
        $record = UserMaintenance::find($id);
        $users = User::where('id', $record->user_id)->first();

        if(!empty($files) && !empty($ktp)) {
            $image_64 = $files; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',')+1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $foto = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($foto, base64_decode(($image), 'r+'));

            $image = $ktp; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image, 0, strpos($image, ',')+1);
            $images = str_replace($replace, '', $image);
            $images = str_replace(' ', '+', $images);
            $ktp = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($ktp, base64_decode(($images), 'r+'));

            try {
                $users->update([
                    'name' => $request->nama,
                    'username' => $request->username,
//                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);

                Storage::disk('sftp')->delete(basename($record->foto));
                Storage::disk('sftp')->delete(basename($record->ktp));

                $update = UserMaintenance::where('id', $record->id)->update([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'foto' => $foto,
                    'ktp' => $ktp,
                    'keterangan' => $request->keterangan,
                    'is_active' => $request->is_active
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(!empty($files) && empty($ktp)) {
            $image_64 = $files; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image_64, 0, strpos($image_64, ',')+1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $foto = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($foto, base64_decode(($image), 'r+'));

            try {
                $users->update([
                    'name' => $request->nama,
                    'username' => $request->username,
//                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);

                Storage::disk('sftp')->delete(basename($record->foto));

                $update = UserMaintenance::where('id', $record->id)->update([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'foto' => $foto,
                    'keterangan' => $request->keterangan,
                    'is_active' => $request->is_active
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(!empty($ktp) && empty($files)) {
            $image = $ktp; //your base64 encoded data
            $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
            $replace = substr($image, 0, strpos($image, ',')+1);
            $images = str_replace($replace, '', $image);
            $images = str_replace(' ', '+', $images);
            $ktp = Str::random(10).'.'.$extension;
            Storage::disk('sftp')->put($ktp, base64_decode(($images), 'r+'));

            try {
                $users->update([
                    'name' => $request->nama,
                    'username' => $request->username,
//                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);

                Storage::disk('sftp')->delete(basename($record->ktp));

                $update = UserMaintenance::where('id', $record->id)->update([
                    'nama' => $request->nama,
                    'username' => $request->username,
                    'wilayah' => $request->wilayah,
                    'pekerjaan' => $request->pekerjaan,
                    'status' => $request->status,
                    'no_telp' => $request->no_telp,
                    'ktp' => $ktp,
                    'keterangan' => $request->keterangan,
                    'is_active' => $request->is_active
                ]);
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }else if(empty($files) || empty($ktp)){
            $users->update([
                'name' => $request->nama,
                'username' => $request->username,
//                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            $update = UserMaintenance::where('id', $record->id)->update([
                'nama' => $request->nama,
                'username' => $request->username,
                'wilayah' => $request->wilayah,
                'pekerjaan' => $request->pekerjaan,
                'status' => $request->status,
                'no_telp' => $request->no_telp,
                'keterangan' => $request->keterangan,
                'is_active' => $request->is_active
            ]);
        }

        if($update){
            return $this->successResponse($update,Constants::HTTP_MESSAGE_200, 200);
        } else {
            return $this->errorResponse(Constants::ERROR_MESSAGE_403, 403);
        }
    }

    public function destroy($id)
    {
        try {
            $user = UserMaintenance::where('id', $id)->first();
            Storage::disk('sftp')->delete(basename($user->foto));
            Storage::disk('sftp')->delete(basename($user->ktp));
            $record = UserMaintenance::find($id)->delete();
            $uDel = User::where('id', $user->user_id)->delete();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        if($record && $uDel){
            return $this->successResponse([$record, $uDel],Constants::HTTP_MESSAGE_200, 200);
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
