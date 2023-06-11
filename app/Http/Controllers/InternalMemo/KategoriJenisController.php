<?php

namespace App\Http\Controllers\InternalMemo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\KategoriFpp;
use App\Model\KategoriJenisFpp;
use App\Helpers\Constants;


class KategoriJenisController extends Controller
{

    public function index()
    {
        $kategori = KategoriJenisFpp::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query->kategori;
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
        $query = KategoriJenisFpp::all();


        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $query = KategoriJenisFpp::create([
            "name" => $request->name
        ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }

    public function show($id)
    {
        $query = KategoriJenisFpp::find($id);

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
        $query = KategoriJenisFpp::where('id', $id)
            ->update([
                "name" => $request->name
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }


    public function destroy($id)
    {
        $query = KategoriJenisFpp::find($id);
        $query->delete();

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }
}
