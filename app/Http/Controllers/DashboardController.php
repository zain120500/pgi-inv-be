<?php

namespace App\Http\Controllers;

use App\Model\Pembelian;
use App\Model\StokBarang;
use App\Model\BarangJenis;
use App\Model\BarangMerk;
use App\Model\BarangTipe;
use Illuminate\Http\Request;
use Carbon\Carbon;


class DashboardController extends Controller
{
  public function pembelianTrack(){
    \Carbon\Carbon::setLocale('id');
    $date = Carbon::now();
 
    $bulan = [ 0,1,2,3,4,5,6];

    $data = [];
    
    $months = [];
    foreach($bulan as $b){
        if($b == 0){
            $month = $date->month;
            $year = $date->year;
            $day = [];

            for( $i=1 ; $i <= $date->daysInMonth;  $i++ ){
                $pembelian = Pembelian::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where('flag', 3)->whereMonth('tanggal','=',$month)->whereDay('tanggal','=',$i)->get();
                array_unshift($day ,(object)['hari' => $pembelian , 'tanggal'=>$i]);
            }
            $pembelian2 = Pembelian::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where('flag', 3)->whereMonth('tanggal','=',$month)->get();
            
            array_unshift( $data, (object)['data' => $pembelian2 , 'bulan' => Carbon::create()->month($month)->isoFormat('MMMM') .' '. $year, 'hari' => $day]);
        }else{
            $datetime = Carbon::parse($date->subMonth(1)->format('M-Y'));
            $month = $datetime->month;
            $year =  $datetime->year;
            $day = [];
            
            for( $i=1 ; $i <= $datetime->daysInMonth;  $i++ ){
                $pembelian = Pembelian::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where('flag', 3)->whereMonth('tanggal','=',$month)->whereDay('tanggal','=',$i)->get();
                array_push($day ,(object)['hari' => $pembelian , 'tanggal'=>$i]);
            }
            $pembelian2 = Pembelian::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->where('flag', 3)->whereMonth('tanggal','=',$month)->get();
           // array_unshift( $data, (object)['data' => $pembelian2 , 'bulan' => Carbon::create()->month($month)->isoFormat('MMMM') .' '. $year, 'hari' => $day]);
            //return $day;
        
            //array_unshift($months , (object)['month'.$month => $day]);
            // return $day;
            // $days=[];
            array_unshift( $data, (object)[ 'data' => $pembelian2 ,'bulan' => Carbon::create()->month($month)->isoFormat('MMMM') .' '. $year , 'hari' => $day]);
            //return $months;
        }
       //return $months;

    }
  
    //return $data;
    foreach($data as $d){
        //$d->data;
        $total_pembelian_bulanan = [];
        $total_pembelian_bulan = 0;

        foreach($d->data as $query){

                $total_harga_barang = [];
                $query->detail;
                $total_pembelian = 0;
    
                foreach($query->detail as $detail){
                    array_push( $total_harga_barang, $detail->total_harga);
                }
                foreach($total_harga_barang as $harga_barang){
                    $total_pembelian += $harga_barang;
                }
         
                $query->total_pembelian = $total_pembelian += $query->ongkir;
                array_push( $total_pembelian_bulanan, $total_pembelian);
        }
        foreach($total_pembelian_bulanan as $total){
            $total_pembelian_bulan += $total;
        }
        $d->total_Pembelian_bulan =   $total_pembelian_bulan;

        foreach($d->hari as $day){
            $day->total_pem_hari = 0;
            foreach($day->hari as $d){
                
                $total_harga_barang = [];
                $d->detail;
                $total_pembelian = 0;
    
                foreach($d->detail as $detail){
                    array_push( $total_harga_barang, $detail->total_harga);
                }
                foreach($total_harga_barang as $harga_barang){
                    $total_pembelian += $harga_barang;
                }
         
                $d->total_pembelian = $total_pembelian += $d->ongkir;
                $day->total_pem_hari += $d->total_pembelian; 
            }
            //return $day->hari;
        }
      
    }
    return $data;
  }
  public function stokTersedia(){

    $bJenis = BarangJenis::where('id_kategori', '=', 2)->get(); //Fixed Asset
    $bMerek = BarangMerk::whereIn('id_jenis', $bJenis->pluck('id'))->get();
    $bTipe = BarangTipe::whereIn('id_merk', $bMerek->pluck('id'))->get();
    $bStok = StokBarang::whereIn('id_tipe', $bTipe->pluck('id'))
        ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))->with('barangTipe')->where('jumlah_stok','>',0 )->get();
    //$data = StokBarang::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->with('barangTipe.barangMerk.barangJeniss')->where('jumlah_stok',0)->get();
    return $bStok;
  }
  public function stokHabis(){

    $bJenis = BarangJenis::where('id_kategori', '=', 2)->get(); //Fixed Asset
    $bMerek = BarangMerk::whereIn('id_jenis', $bJenis->pluck('id'))->get();
    $bTipe = BarangTipe::whereIn('id_merk', $bMerek->pluck('id'))->get();
    $bStok = StokBarang::whereIn('id_tipe', $bTipe->pluck('id'))
        ->whereIn('pic', $this->cabangGlobal()->pluck('kode'))->with('barangTipe')->where('jumlah_stok',0)->get();
    //$data = StokBarang::whereIn('pic', $this->cabangGlobal()->pluck('kode'))->with('barangTipe.barangMerk.barangJeniss')->where('jumlah_stok',0)->get();
    return $bStok;
  }

}
