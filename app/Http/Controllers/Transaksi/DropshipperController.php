<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Model\Dropshipper;
use App\Model\DropshipperDetail;

class DropshipperController extends Controller
{

    public function index()
    {
        $query = Dropshipper::paginate(15);

        return response()->json([
            'status' =>'success',
            'data' => $query
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
        $query = Dropshipper::find($id);
        $query['detail'] = DropshipperDetail::where('id_dropshipper', $query->id)->get();

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
