<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Supplier;
use App\Helpers\Constants;


class SupplierController extends Controller
{

    public function index()
    {
        $supplier = Supplier::paginate(15);

        // return response()->json([
        //     'status' =>'success',
        //     'data' => $supplier
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $supplier
        );
    }

    public function all()
    {
        $supplier = Supplier::all()->makeHidden(['created_at', 'updated_at']);

        // return response()->json([
        //     'status' => 'success',
        //     'data' => $supplier
        // ], 200);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $supplier
        );
    }


    public function store(Request $request)
    {
        $query = Supplier::create([
            "nama" => $request->nama,
            "alamat" => $request->alamat,
            "no_hp" => $request->no_hp,
            "link_web" => $request->link_web,
            "keterangan" => $request->keterangan
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

    public function show($id)
    {
        $query = Supplier::find($id);

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
        $query = Supplier::where('id', $id)
            ->update([
                "nama" => $request->nama,
                "alamat" => $request->alamat,
                "no_hp" => $request->no_hp,
                "link_web" => $request->link_web,
                "keterangan" => $request->keterangan
            ]);

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $query
        );
    }


    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();

            return self::buildResponse(
                Constants::HTTP_CODE_200,
                Constants::HTTP_MESSAGE_200,
                $supplier
            );
        } catch (ModelNotFoundException $e) {
            return self::buildResponse(
                Constants::HTTP_CODE_404,
                Constants::HTTP_MESSAGE_404,
                null
            );
        }
    }
}
