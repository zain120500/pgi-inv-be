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
        $kategori = Kategori::create([
            "nama"=> $request->nama,
            "kode"=> $request->kode
        ]);

        return response()->json([
            'type' =>'success',
            'data' => $kategori
        ]);
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
