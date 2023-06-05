<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Kabupaten;
use App\Model\Kecamatan;
use App\Model\Kelurahan;
use App\Helpers\Constants;
use App\Model\Provinsi;

class WilayahController extends Controller
{
    public function getKabupatenAll(Request $request)
    {
        if (!empty($request->id)) {
            $query = Kabupaten::where('id', $request->id)->get();
        } else {
            $query = Kabupaten::get();
        }

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

    public function getKecamatanAll(Request $request)
    {
        if (!empty($request->id)) {
            $query = Kecamatan::where('id', $request->id)->get();
        } else {
            $query = Kecamatan::get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $query
        ], 200);
    }

    public function getKelurahanAll(Request $request)
    {
        if (!empty($request->id)) {
            $query = Kelurahan::where('id', $request->id)->get();
        } else {
            $query = Kelurahan::get();
        }

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

    public function getProvinsiAll(Request $request)
    {
        if (!empty($request->id)) {
            $query = Provinsi::where('id', $request->id)->get();
        } else {
            $query = Provinsi::get();
        }

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
