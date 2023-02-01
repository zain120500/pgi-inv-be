<?php

use Illuminate\Support\Facades\Auth;
// use App\Model\Cart;

function Helpers()
{
    function getkodebarang($id_jenis) //oke
    {
        $rows = DB::statement("SELECT A.id_kategori, B.kode FROM barang_jenis A
        LEFT JOIN kategori B ON B.id = A.id_kategori WHERE A.id ='$id_jenis'");
        // $rows = $sql->row();

        $id_kategori = $rows->id_kategori;
        $kode = $rows->kode;

        $row = DB::statement("SELECT max(A.kode_barang) as max_code FROM barang_tipe A
                LEFT JOIN barang_merk B ON B.id = A.id_merk
                LEFT JOIN barang_jenis C ON C.id = B.id_jenis
                WHERE C.id_kategori='$id_kategori'");

        // $row = $query->row_array();

        $max_id = $row['max_code'];
        $max_fix = (int) substr($max_id, 3, 7);

        $max_nik = $max_fix + 1;

        $nik = $kode.sprintf("%07s", $max_nik);
        return $nik;
    }

}