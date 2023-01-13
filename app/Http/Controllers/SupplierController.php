<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Supplier;


class SupplierController extends Controller
{

    public function index()
    {
        $supplier = Supplier::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $supplier
        ], 200); 
    }

    
    public function store(Request $request)
    {
        $query = Supplier::create([
            "nama"=> $request->nama,
            "alamat"=> $request->alamat,
            "no_hp"=> $request->no_hp,
            "link_web"=> $request->link_web,
            "keterangan	"=> $request->keterangan
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $query
        ]);
    }

    public function show($id)
    {
        $query = Supplier::find($id);

        return response()->json([
            'status' =>'success',
            'data' => $query
        ], 200);  
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
