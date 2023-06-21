<?php

namespace App\Http\Controllers\InternalMemo;

use App\Helpers\Constants;
use App\Http\Controllers\Controller;
use App\Model\Cabang;
use App\Model\InternalMemo;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanInternalMemo extends Controller
{
    public function laporanPerbaikan(Request $request)
    {
        $internal = InternalMemo::query();

        $internal = $internal->get();

        $internal->map(function ($query) {
//            $query['flag_status'] = $this->getFlagStatus($query->flag);
            $query['cabang'] = Cabang::where('id', $query->id_cabang)->with('kabupatenKota')->first();
            $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
            $query->devisi;
            $query->kategori;
            $query->kategoriJenis;
            $query->kategoriSub;

            return $query;
        });

        return self::buildResponse(
            Constants::HTTP_CODE_200,
            Constants::HTTP_MESSAGE_200,
            $internal
        );
    }

    public function printMemoDetail($id)
    {
        $query = InternalMemo::find($id);

        $query['cabang'] = Cabang::where('id', $query->id_cabang)->with('kabupatenKota')->first();
        $query['kepala_cabang'] = User::where('id', $query->created_by)->first();
        $query->devisi;
        $query->kategori;
        $query->kategoriJenis;
        $query->kategoriSub;

        return view('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo]);

//        $customPaper = array(360, 360, 360, 360);
//        $pdf = PDF::loadView('InternalMemo.internalMemoPdf', ['query' => $query, 'memo' => $query->MemoFile, 'history' => $query->listHistoryMemo])->setPaper('a4');
//        return $pdf->download('internal-memo.pdf');
    }
}
