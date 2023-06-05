<?php

namespace App\Http\Controllers\Admin\Barang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\BarangJenis;
use App\Helpers\Constants;

class BarangJenisController extends Controller
{

    public function index(Request $request)
    {
        if (!empty($request->jenis)) {
            $barang = BarangJenis::where('jenis', 'like', '%' . $request->jenis . '%')->paginate(15);
        } else if (!empty($request->id_kategori)) {
            $barang = BarangJenis::where('id_kategori', 'like', '%' . $request->id_kategori . '%')->paginate(15);
        } else {
            $barang = BarangJenis::paginate(15);
        }

        $collect = $barang->getCollection()->map(function ($query) {
            $query->barangKategori;
            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $barang->setCollection($collect)
        );
    }

    public function all()
    {
        $query = BarangJenis::all();

        if (!empty($query)) {
            // return $this->successResponse($query, 'Success', 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $query
            );
        } else {
            // return $this->errorResponse('Data is Null', 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $query
            );
        }
    }

    public function store(Request $request)
    {
        $jenis = BarangJenis::create([
            "jenis" => $request->jenis,
            "id_kategori" => $request->id_kategori,
            "golongan" => $request->golongan
        ]);

        // return response()->json([
        //     'type' => 'success',
        //     'data' => $jenis
        // ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $jenis
        );
    }


    public function show($id)
    {
        $query = BarangJenis::find($id);
        if (!empty($query)) {
            $query->barangKategori;
            // return $this->successResponse($query, 'Success', 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $query
            );
        } else {
            // return $this->errorResponse('Data is Null', 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $query
            );
        }
    }

    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $query = BarangJenis::where('id', $id)
            ->update([
                "jenis" => $request->jenis,
                "id_kategori" => $request->id_kategori,
                "golongan" => $request->golongan
            ]);

        // return response()->json([
        //     'type' => 'success',
        //     'data' => $query
        // ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = BarangJenis::find($id)->delete();

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $query
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
