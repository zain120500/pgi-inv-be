<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Kategori;

class KategoriController extends Controller
{

    public function index()
    {
        $kategori = Kategori::paginate(15);

        $collect = $kategori->getCollection()->map(function ($query) {
            $query['barangJenis'] = $query->barangJenis;

            return $query;
        });

        return response()->json([
            'status' =>'success',
            'data' => $kategori->setCollection($collect)
        ], 200);  
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
        $query = Kategori::find($id);
        $query->barangJenis;
        
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
