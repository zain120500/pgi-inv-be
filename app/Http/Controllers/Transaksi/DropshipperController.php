<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Dropshipper;
use App\Model\DropshipperDetail;

use App\Helpers\Constants;

class DropshipperController extends Controller
{

    public function index()
    {
        $query = Dropshipper::orderBy('tanggal', 'DESC')->paginate(15);

        // return response()->json([
        //     'status' =>'success',
        //     'data' => $query
        // ], 200);

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
        //
    }

    public function show($id)
    {
        $query = Dropshipper::find($id);
        $query['detail'] = DropshipperDetail::where('id_dropshipper', $query->id)->get();

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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
