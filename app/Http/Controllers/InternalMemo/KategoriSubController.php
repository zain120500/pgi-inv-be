<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Model\KategoriSubFpp;
use App\Helpers\Constants;


class KategoriSubController extends Controller
{

    public function index(Request $request)
    {
        $value = $request->search;
        $query = KategoriSubFpp::where('name', 'like', '%' . $value . '%')
            ->orWhere('id_kategori_fpp', 'like', '%' . $value . '%')
            ->orWhere('id_kategori_jenis', 'like', '%' . $value . '%')
            ->orWhere('sla', 'like', '%' . $value . '%')
            ->paginate(15);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function all()
    {
        $query = KategoriSubFpp::with('kategori')->get();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function store(Request $request)
    {
        $query = KategoriSubFpp::create([
            "name" => $request->name,
            "id_kategori_jenis" => $request->id_kategori_jenis,
            "sla" => $request->sla
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {
        $query = KategoriSubFpp::find($id);
        $query->kategori;

        if (!empty($query)) {
            $query->kategori;
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
        $query = KategoriSubFpp::where('id', $id)->update([
            "name" => $request->name,
            "id_kategori_jenis" => $request->id_kategori_jenis,
            "sla" => $request->sla
        ]);

        if ($query) {
            // return $this->successResponse($query, 'Success', 200);

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $query
            );
        } else {
            // return $this->errorResponse('Process Data error', 403);

            return self::buildResponse(
                Constants::HTTP_CODE_403,
                Constants::HTTP_MESSAGE_403,
                $query
            );
        }
    }


    public function destroy($id)
    {
        $query = KategoriSubFpp::find($id);

        if (!empty($query)) {
            $query->delete();
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
}
