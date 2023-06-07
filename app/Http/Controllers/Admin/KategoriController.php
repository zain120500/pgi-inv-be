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

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $kategori->setCollection($collect)
        // ], 200);

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

        if ($query) {
            return $this->successResponse($query, 'Success', 200);

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

    public function show($id)
    {
        $query = Kategori::find($id);

        if (!empty($query)) {
            $query->barangJenis;

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
        $query = Kategori::where('id', $id)
            ->update([
                "nama" => $request->nama,
                "kode" => $request->kode
            ]);

        // return $this->successResponse($query, 'Success', 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function destroy($id)
    {
        $query = Kategori::find($id)->delete();
        // return $this->successResponse($query, 'Success', 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
