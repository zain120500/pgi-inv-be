<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Kategori;
use App\Helpers\Constants;

class KategoriController extends Controller
{

    public function index()
    {
        $kategori = Kategori::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query['barangJenis'] = $query->barangJenis;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $kategori->setCollection($collect)
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = Kategori::create([
            "nama" => $request->nama,
            "kode" => $request->kode
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {
        $query = Kategori::find($id);

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
        $query = Kategori::where('id', $id)
            ->update([
                "nama" => $request->nama,
                "kode" => $request->kode
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = Kategori::find($id)->delete();


        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
