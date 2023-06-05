<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Devisi;
use App\Model\DevisiAccessFpp;
use App\User;
use App\Model\KategoriPicFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriFpp;
use App\Helpers\Constants;


class KategoriPicController extends Controller
{
    public function index()
    {
        $kategori = KategoriPicFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->user->makeHidden(['created_at', 'updated_at']);
            $query->kategori->kategoriJenis;
            $query->devisi->makeHidden(['created_at', 'updated_at']);
            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $kategori
        );
    }

    public function all()
    {
        $kategori = KategoriPicFpp::get();

        $collect = $kategori->map(function ($query) {
            $query->user->makeHidden(['created_at', 'updated_at']);
            $query->kategori->kategoriJenis;
            $query->devisi->makeHidden(['created_at', 'updated_at']);
            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $kategori
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $kategori = $request->id_kategori_jenis_fpp;
        $kProses = $request->kategori_proses;

        try {
            foreach ($kProses as $keys => $kP) {
                $query = KategoriPicFpp::create([
                    "user_id" => $request->user_id,
                    "devisi_id" => $request->devisi_id,
                    "id_kategori_jenis_fpp" => $kategori,
                    "kategori_proses" => $kP,
                    "created_by" => auth()->user()->id
                ]);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {
        $query = KategoriPicFpp::find($id);

        $query->barangJenis;
        $query->user->makeHidden(['created_at', 'updated_at']);
        $query->kategori->kategoriJenis;
        $query->devisi->makeHidden(['created_at', 'updated_at']);

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
        $query = KategoriPicFpp::where('id', $id)
            ->update([
                "user_id" => $request->user_id,
                "devisi_id" => $request->devisi_id,
                "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
                "kategori_proses" => $request->kategori_proses,
                "created_by" => auth()->user()->id
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = KategoriPicFpp::find($id);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
