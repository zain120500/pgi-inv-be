<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\BarangTipe;
use App\Model\BarangMerk;
use App\Helpers\Constants;

class BarangMerkController extends Controller
{

    public function index(Request $request)
    {
        if (!empty($request->merk)) {
            $barang = BarangMerk::where('merk', 'like', '%' . $request->merk . '%')->paginate(15);
        } else if (!empty($request->id_jenis)) {
            $barang = BarangMerk::where('id_jenis', $request->id_jenis)->paginate(15);
        } else {
            $barang = BarangMerk::paginate(15);
        }

        $collect = $barang->getCollection()->map(function ($query) {
            $query->barangJenis;
            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barang->setCollection($collect)
        );
    }


    public function all(Request $request)
    {
        if (!empty($request->merk)) {
            $query = BarangMerk::where('merk', 'like', '%' . $request->merk . '%')->get();
        } else if (!empty($request->id_jenis)) {
            $query = BarangMerk::where('id_jenis', $request->id_jenis)->get();
        } else {
            $query = BarangMerk::get();
        }

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }


    public function store(Request $request)
    {
        $merk = BarangMerk::create([
            "merk" => $request->merk,
            "id_jenis" => $request->id_jenis
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $merk
        );
    }

    public function show($id)
    {
        $query = BarangMerk::find($id);

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
        $query = BarangMerk::where('id', $id)
            ->update([
                "merk" => $request->merk,
                "id_jenis" => $request->id_jenis
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = BarangMerk::find($id)->delete();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
