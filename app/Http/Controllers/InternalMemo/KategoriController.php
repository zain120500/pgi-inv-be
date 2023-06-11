<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Helpers\Constants;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = KategoriFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->kategoriJenis;
            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $kategori->setCollection($collect)
        );
    }

    public function all()
    {
        $query = KategoriFpp::all();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function store(Request $request)
    {
        $query = KategoriFpp::create([
            "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
            "name" => $request->name,
            "sla" => $request->sla,
            "created_by" => auth()->user()->id,
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {

        $query = KategoriFpp::find($id);

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
        $query = KategoriFpp::where('id', $id)
            ->update([
                "id_kategori_jenis_fpp" => $request->id_kategori_jenis_fpp,
                "name" => $request->name,
                "sla" => $request->sla,
                "created_by" => auth()->user()->id,
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = KategoriFpp::find($id);

        $query->delete();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
