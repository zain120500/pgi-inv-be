<?php

use Illuminate\Support\Facades\Auth;
// use App\Model\Cart;


function ModelApp()
{

    //Contohh select : $results = DB::select('select * from users where id = :id', ['id' => 1]);
    //Contoh Native Query : DB::statement('drop table users');

    function getkodeinvoice3($tanggal) //oke
    {
        $query = DB::select('SELECT max(no_invoice) as max_code, tanggal FROM pemakaian WHERE tanggal = :tanggal', ['tanggal' => $tanggal]);
        $max_id = $query->max_code;
        $max_fix = (int) substr($max_id, 9, 4);

        $max_nik = $max_fix + 1;

        $tgl = date('d', strtotime($tanggal));
        $bln = date('m', strtotime($tanggal));
        $thn = date('Y', strtotime($tanggal));

        $nik = "P".$thn.$bln.$tgl.sprintf("%04s", $max_nik);
        return $nik;
    }


    function getnomer_barang($tanggal) //oke
    {

        $query = $this->db->query(
        "SELECT max(A.nomer_barang) as max_code, B.tanggal
        FROM pembelian_detail A
        LEFT JOIN pembelian B ON B.id = A.id_pembelian
        WHERE B.tanggal='$tanggal' AND SUBSTRING(A.nomer_barang,1,2) not in('FA','CA')");
        $row = $query->row_array();

        $max_id = $row['max_code'];
        $max_fix = (int) substr($max_id, 7, 3);

        $max_nik = $max_fix + 1;

        $tgl = date('d', strtotime($tanggal));
        $bulan = date('m', strtotime($tanggal));
        $tahun = date('y', strtotime($tanggal));

        $nik = $tahun.$bulan.$tgl.sprintf("%03s", $max_nik);
        return $nik;
    }

    function getkodebarang($id_jenis) //oke
    {
        $sql = $this->db->query("SELECT A.id_kategori, B.kode FROM barang_jenis A
        LEFT JOIN kategori B ON B.id = A.id_kategori WHERE A.id ='$id_jenis'");
        $rows = $sql->row();

        $id_kategori = $rows->id_kategori;
        $kode = $rows->kode;

        $query = $this->db->query("SELECT max(A.kode_barang) as max_code FROM barang_tipe A
                LEFT JOIN barang_merk B ON B.id = A.id_merk
                LEFT JOIN barang_jenis C ON C.id = B.id_jenis
                WHERE C.id_kategori='$id_kategori'");

        $row = $query->row_array();

        $max_id = $row['max_code'];
        $max_fix = (int) substr($max_id, 3, 7);

        $max_nik = $max_fix + 1;

        $nik = $kode.sprintf("%07s", $max_nik);
        return $nik;
    }

    function getkodeinvoice() //oke
    {

        $query = $this->db->query("select max(no_invoice) as max_code FROM pembelian WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");

        $row = $query->row_array();

        $max_id = $row['max_code'];
        $max_fix = (int) substr($max_id, 9, 4);

        $max_nik = $max_fix + 1;

        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "B".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }

    function getkodeinvoice4() //oke
    {

        $query = $this->db->query("SELECT max(no_invoice) as max_code FROM dropshipper WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");

        $row = $query->row_array();

        $max_id = $row['max_code'];
        $max_fix = (int) substr($max_id, 9, 4);

        $max_nik = $max_fix + 1;

        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "D".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }

    function getkodeinvoice2()
    {
        $query = DB::select("select max(no_invoice) as max_code FROM pengiriman WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");

        $max_id = $query->max_code;
        $max_fix = (int) substr($max_id, 9, 4);

        $max_nik = $max_fix + 1;

        $tanggal = $time = date("d");
        $bulan = $time = date("m");
        $tahun = $time = date("Y");

        $nik = "K".$tahun.$bulan.$tanggal.sprintf("%04s", $max_nik);
        return $nik;
    }

    function datalist_pembelian_detail($id) //oke
    {
        $query = DB::select("SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.keterangan, A.detail_barang,
        A.jumlah, A.harga, A.total_harga, E.tanggal, A.satuan, E.flag, A.nomer_barang, A.status, E.user_input
        FROM pembelian_detail A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN pembelian E ON E.id = A.id_pembelian
        WHERE A.id_pembelian =:id ", ['id' => $id] );

        return $query;
    }

    function cetak_pembelian_detail($id) //oke
    {
        $query = DB::select("SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.keterangan, A.detail_barang,
        A.jumlah, A.harga, A.total_harga, E.tanggal, A.satuan, E.flag, A.nomer_barang, A.status
        FROM pembelian_detail A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN pembelian E ON E.id = A.id_pembelian
        WHERE A.id_pembelian =:id AND A.status in(0,4)", ['id' => $id] );

        return $query;
    }

    function datalist_barang_void($start_date, $end_date) //oke
    {

        if($start_date > 0){
            $start_dates = date('Y-m-d', $start_date);
            $end_dates = date('Y-m-d', $end_date);

            $where = "AND E.tanggal >='".$start_dates."' AND E.tanggal <= '".$end_dates."'";
        } else {
            $where = "AND MONTH(E.tanggal) = MONTH(CURDATE())";
        }

        $query = DB::select("SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.keterangan, A.detail_barang,
        A.jumlah, A.harga, A.total_harga, E.tanggal, A.satuan, E.flag, A.nomer_barang, A.status, F.nama as supplier
        FROM pembelian_detail A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN pembelian E ON E.id = A.id_pembelian
        LEFT JOIN supplier F ON F.id = E.id_supplier
        WHERE A.status in(2,4) $where");

        return $query;

    }

    function datalist_void_pembelian($start_date, $end_date) //oke
    {

        if($start_date > 0){
            $start_dates = date('Y-m-d', $start_date);
            $end_dates = date('Y-m-d', $end_date);

            $where = "AND A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."'";
        } else {
            $where = "AND MONTH(A.tanggal) = MONTH(CURDATE())";
        }

        $query = DB::select("SELECT A.tanggal, A.id, E.no_invoice , COUNT(B.id_tipe) as unit, SUM(B.total_harga) as total_pembelian,
        F.nama as supplier, A.previous_flag
        FROM tbl_void_pembelian A
        LEFT JOIN pembelian E ON E.no_invoice = A.no_invoice
        LEFT JOIN pembelian_detail B ON B.id_pembelian = E.id
        LEFT JOIN supplier F ON F.id = E.id_supplier
        WHERE A.status in(0) $where");

        return $query;

    }

    function datalist_pemakaian($start_date, $end_date, $id_cabang)
    {

        $id_user = $this->session->userdata('kode_cabang');
        // $id_user = 'P00003';
        if($start_date > 0){
            $start_dates = date('Y-m-d', $start_date);
            $end_dates = date('Y-m-d', $end_date);

            $where = "WHERE A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."' AND A.pic in($id_cabang)";
        } else {
            $where = "WHERE MONTH(A.tanggal) = MONTH(CURDATE()) AND A.pic in($id_cabang)";
        }

        $query = DB::select(
        "SELECT A.id, A.tanggal, A.pic, A.nomer_barang, A.id_tipe, A.jumlah, A.total_harga,
        A.keperluan, A.pemakai, D.jenis, C.merk, B.tipe, E.nama_cabang
        FROM pemakaian A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        $where
        ORDER BY A.tanggal DESC");

        return $query;
    }

    function datalist_pembelian($start_date, $end_date, $id_cabang, $level) //oke
    {
    if($level == 'Finance'){
        $where_status = "AND A.flag in(1,2)";
    } else {
        $where_status = "";
    }
    if($start_date > 0){
        $start_dates = date('Y-m-d', $start_date);
        $end_dates = date('Y-m-d', $end_date);

        $where = "WHERE A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."' AND A.pic in($id_cabang)";
    } else {
        $where = "WHERE MONTH(A.tanggal) = MONTH(CURDATE()) AND A.pic in($id_cabang)";
    }

    $query = $this->db->query("SELECT A.flag, A.id, A.no_invoice, A.tanggal, B.nama as supplier, SUM(C.total_harga) as total_pembelian,
    COUNT(C.id_tipe) as unit
    FROM pembelian A
    LEFT JOIN pembelian_detail C ON C.id_pembelian = A.id
    LEFT JOIN supplier B ON B.id = A.id_supplier
    $where $where_status
    GROUP BY A.id ORDER BY A.flag, A.id ASC");

    return $query->result();

    }

    function datalist_pembelian_dropshipper($start_date, $end_date) //oke
    {
    $level = $this->session->userdata('level');

    if($level == 'Finance'){
        $where_status = "AND A.flag in(1) AND A.status in(1)";
    } else {
        $where_status = "";
    }

    if($start_date > 0){
        $start_dates = date('Y-m-d', $start_date);
        $end_dates = date('Y-m-d', $end_date);

        $where = "WHERE A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."'";
    } else {
        $where = "WHERE MONTH(A.tanggal) = MONTH(CURDATE())";
    }

    $query = $this->db->query("SELECT A.status, A.flag, A.id, A.no_invoice, A.tanggal, B.nama as supplier, SUM(C.total_harga) as total_pembelian,
    COUNT(C.id_tipe) as unit
    FROM dropshipper A
    LEFT JOIN dropshipper_detail C ON C.id_dropshipper = A.id
    LEFT JOIN supplier B ON B.id = A.id_supplier
    $where $where_status
    GROUP BY A.id ORDER BY A.status ASC");

    return $query->result();

    }

    function datalist_dropshipper_detail($id) //oke
    {

        $level = $this->session->userdata('level');
        $kode_cabang = $this->session->userdata('kode_cabang');

        $query = $this->db->query("SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.keterangan, A.detail_barang,
        A.jumlah, A.harga, A.total_harga, E.tanggal, A.satuan, E.flag, A.nomer_barang, A.id_gudang, F.nama_cabang, A.status, E.user_input
        FROM dropshipper_detail A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN dropshipper E ON E.id = A.id_dropshipper
        LEFT JOIN tbl_cabang F ON F.kode_cabang = A.id_gudang
        WHERE A.id_dropshipper ='$id'");

        return $query->result();

    }

    function datalist_masuk_dropshipper($start_date, $end_date, $id_cabang, $status) //oke
    {



    if($start_date > 0){
        $start_dates = date('Y-m-d', $start_date);
        $end_dates = date('Y-m-d', $end_date);

        $where = "WHERE A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."' AND C.id_gudang in ($id_cabang)";
    } else {
        $where = "WHERE MONTH(A.tanggal) = MONTH(CURDATE()) AND C.id_gudang in ($id_cabang) AND C.status not in (1)";
    }

    if($status !==""){
        $having = "HAVING id_status =".$status;
    } else {
        $having ="";
    }
    $query = $this->db->query("SELECT CEILING(AVG(C.status)) as id_status, D.nama_cabang, A.flag, A.id, A.no_invoice, A.tanggal, B.nama as supplier, SUM(C.total_harga) as total_pembelian,
    COUNT(C.id_tipe) as unit
    FROM dropshipper A
    LEFT JOIN dropshipper_detail C ON C.id_dropshipper = A.id
    LEFT JOIN supplier B ON B.id = A.id_supplier
    LEFT JOIN tbl_cabang D ON D.kode_cabang = C.id_gudang
    $where AND A.flag = 1 AND A.status not in (1)
    GROUP BY A.id $having ORDER BY A.tanggal DESC");

    return $query->result();

    }

    function datalist_pengiriman_detail($id) //oke
    {

        $query = $this->db->query("SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.keterangan, A.detail_barang,
        A.jumlah, A.harga, A.total_harga, E.tanggal, A.satuan, E.flag, E.user_input, A.status, A.nomer_barang
        FROM pengiriman_detail A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN pengiriman E ON E.id = A.id_pengiriman
        WHERE A.id_pengiriman ='$id'");

        return $query->result();

    }

    function datalist_pengiriman($start_date, $end_date, $id_cabang)
    {
        $id_user = $this->session->userdata('kode_cabang');

        if($start_date > 0){
        $start_dates = date('Y-m-d', $start_date);
        $end_dates = date('Y-m-d', $end_date);

        $where = "WHERE A.pengirim in($id_cabang)";
        } else {
        $where = "WHERE  A.pengirim in($id_cabang)";
        }

        $query = $this->db->query(
        "SELECT A.status, A.id, A.no_invoice, A.kategori, D.nama_cabang as pengirim, B.nama_cabang as penerima,
        A.tanggal, A.kurir, A.flag, COUNT(C.id_tipe) as item, SUM(C.jumlah) as qty

        FROM pengiriman A
        LEFT JOIN pengiriman_detail C ON C.id_pengiriman = A.id
        LEFT JOIN tbl_cabang B ON B.kode_cabang = A.penerima
        LEFT JOIN tbl_cabang D ON D.kode_cabang = A.pengirim
        $where
        GROUP BY A.id
        ORDER BY A.id DESC");

        return $query->result();

    }

    function datalist_inventaris($id_jenis, $id_divisi)
    {
        if($id_jenis >0){
        $where_jenis = "AND C.id_jenis ='$id_jenis'";
        } else {
        $where_jenis = "";
        }

        if($id_divisi >0){
        $where_divisi = "AND E.id_divisi ='$id_divisi'";
        } else {
        $where_divisi = "";
        }

        $query = $this->db->query(
        "SELECT A.id, A.tanggal, A.flag, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, E.nama_karyawan, A.jumlah_stok, A.satuan, F.nama_jabatan

        FROM stok_inventaris A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_karyawan E ON E.nik = A.pemakai
        LEFT JOIN tbl_jabatan F ON F.id_jabatan = E.id_jabatan
        WHERE jumlah_stok >= 1 $where_jenis $where_divisi
        ORDER BY A.tanggal DESC");

        return $query->result();

    }

    function datalist_stok_asset_tetap($lokasi, $penerima, $id_cabang)
    {

    $where_cabang = "";
    if($lokasi=="" && $penerima==""){
        $where_cabang = "AND A.pic in($id_cabang)";
        $where_pic = "";
        $where_lokasi = "";
    } else {

        if($lokasi!==""){
        $where_lokasi = "AND E.lokasi ='$lokasi'";
        } else {
        $where_lokasi = "";
        }

        if($penerima!==""){
        $where_pic = "AND A.pic ='$penerima'";
        } else {
        $where_pic = "";
        }
    }

        $query = $this->db->query(
        "SELECT A.id, A.last_update, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, E.nama_cabang, A.jumlah_stok, A.satuan
        , A.total_asset

        FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        WHERE A.jumlah_stok >= 1 AND D.id_kategori = 1 $where_cabang $where_lokasi $where_pic
        ORDER BY A.pic DESC");

        return $query->result();

    }

    function datalist_laporan_stok_barang($lokasi, $penerima, $id_cabang, $id_jenis, $id_kategori)
    {

    $where_cabang = "";
    $where_jenis= "";
    $where_kategori = "";
    if($id_jenis >0){
        $where_jenis = "AND D.id = '$id_jenis'";
    }
    if($id_kategori >0){
        $where_kategori = "AND D.id_kategori = '$id_kategori'";
    }

    if($lokasi=="" && $penerima==""){
        $where_cabang = "AND A.pic in($id_cabang)";
        $where_pic = "";
        $where_lokasi = "";

    } else {

        if($lokasi!==""){
        $where_lokasi = "AND E.lokasi ='$lokasi'";
        } else {
        $where_lokasi = "";
        }

        if($penerima!==""){
        $where_pic = "AND A.pic ='$penerima'";
        } else {
        $where_pic = "";
        }
    }


        $query = $this->db->query(
        "SELECT A.id, A.last_update, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, E.nama_cabang, A.jumlah_stok, A.satuan
        , A.total_asset, B.tipe_kode

        FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        WHERE A.jumlah_stok >= 1 $where_cabang $where_kategori $where_jenis $where_lokasi $where_pic
        ORDER BY A.pic DESC");

        return $query->result();

    }


    function datalist_laporan_stok_inventari($id_divisi, $id_karyawan, $id_jenis)
    {

    $where_jenis= "";
    $where_lokasi = "";
    $where_pic = "";

    if($id_jenis >0){
    $where_jenis = "AND D.id = '$id_jenis'";
    }

    if($id_divisi >0){
    $where_lokasi = "AND E.id_divisi ='$id_divisi'";
    }

    if($id_karyawan >0){
    $where_pic = "AND A.pemakai ='$id_karyawan'";
    }

    $query = $this->db->query(
    "SELECT A.id, A.tanggal, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, E.nama_karyawan, A.jumlah_stok, A.satuan
    , A.total_asset

    FROM stok_inventaris A
    LEFT JOIN barang_tipe B ON B.id = A.id_tipe
    LEFT JOIN barang_merk C ON C.id = B.id_merk
    LEFT JOIN barang_jenis D ON D.id = C.id_jenis
    LEFT JOIN tbl_karyawan E ON E.nik = A.pemakai
    LEFT JOIN tbl_jabatan F ON F.id_jabatan = E.id_jabatan
    WHERE A.jumlah_stok >= 1 $where_jenis $where_lokasi $where_pic
    ORDER BY A.pemakai DESC");

    return $query->result();

    }

    function datalist_stok_asset_lancar($lokasi, $penerima, $id_cabang)
    {

    $where_cabang = "";
    if($lokasi=="" && $penerima==""){
        $where_cabang = "AND A.pic in($id_cabang)";
        $where_pic = "";
        $where_lokasi = "";
    } else {

        if($lokasi!==""){
        $where_lokasi = "AND E.lokasi ='$lokasi'";
        } else {
        $where_lokasi = "";
        }

        if($penerima!==""){
        $where_pic = "AND A.pic ='$penerima'";
        } else {
        $where_pic = "";
        }
    }

        $query = $this->db->query(
        "SELECT A.id, A.last_update, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, E.nama_cabang, A.jumlah_stok, A.satuan
        , A.total_asset

        FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        WHERE A.jumlah_stok >= 1 AND D.id_kategori = 2 $where_cabang $where_lokasi $where_pic

        ORDER BY A.pic DESC");

        return $query->result();

    }

    function datalist_inventaris_cetak($id)
    {

        $query = $this->db->query(
        "SELECT A.id, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, A.nomer_barang, A.jumlah_stok, A.satuan

        FROM stok_inventaris A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        WHERE A.id ='$id'
        ORDER BY A.last_update DESC");

        return $query->result();

    }

    function datalist_barang_masuk($start_date, $end_date, $kode_cabang, $status)
    {

        if($start_date > 0){
        $start_dates = date('Y-m-d', $start_date);
        $end_dates = date('Y-m-d', $end_date);

        $where = "WHERE A.tanggal >='".$start_dates."' AND A.tanggal <= '".$end_dates."' AND A.penerima in($kode_cabang) AND A.flag=1";
        } else {
        $where = "WHERE MONTH(A.tanggal) = MONTH(CURDATE()) AND A.penerima in($kode_cabang) AND A.flag=1";
        }

        if($status !==""){
        $where_status = "AND A.status =".$status;
        } else {
        $where_status ="";
        }

        $query = $this->db->query(
        "SELECT A.id, A.no_invoice, A.kategori, D.nama_cabang as pengirim, B.nama_cabang as penerima,
        A.tanggal, A.kurir, A.flag, COUNT(C.id_tipe) as item, SUM(C.jumlah) as qty, A.status

        FROM pengiriman A
        LEFT JOIN pengiriman_detail C ON C.id_pengiriman = A.id
        LEFT JOIN tbl_cabang B ON B.kode_cabang = A.penerima
        LEFT JOIN tbl_cabang D ON D.kode_cabang = A.pengirim
        $where $where_status
        GROUP BY A.id
        ORDER BY A.status ASC");

        return $query->result();

    }

    function datalist_history_barang($nomer_barang)
    {

        $query = $this->db->query(
        "SELECT A.tanggal as tanggal, A.last_update as last, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, E.nama_cabang, 'Terima' as keterangan

        FROM barang_masuk A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        WHERE A.nomer_barang = '$nomer_barang'

        UNION

        SELECT A.tanggal as tanggal, A.last_update as last, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, E.nama_cabang, 'Kirim' as keterangan

        FROM barang_keluar A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_cabang E ON E.kode_cabang = A.pic
        WHERE A.nomer_barang = '$nomer_barang'

        UNION

        SELECT A.tanggal as tanggal, A.last_update as last, D.jenis, C.merk, B.tipe, A.imei, A.detail_barang, E.nama_karyawan as nama_cabang, 'Terima' as keterangan

        FROM stok_inventaris A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        LEFT JOIN tbl_karyawan E ON E.nik = A.pemakai
        WHERE A.nomer_barang = '$nomer_barang'

        ORDER BY last DESC");

        return $query->result();

    }

    function datasupplier() //oke
    {
    $query = $this->db->query('SELECT * FROM supplier');
    return $query->result();
    }

    function datakategori() //oke
    {
    $query = $this->db->query('SELECT * FROM kategori');
    return $query->result();
    }


    function datajenis_barang() //oke
    {
    $query = $this->db->query('SELECT A.*, B.nama, B.kode FROM barang_jenis A
    LEFT JOIN kategori B ON B.id = A.id_kategori
    ORDER BY A.jenis ASC ');
    return $query->result();
    }

    function datamerk_barang() //oke
    {
    $query = $this->db->query('SELECT A.*, B.jenis, B.golongan, C.nama FROM barang_merk A
    LEFT JOIN barang_jenis B ON B.id = A.id_jenis
    LEFT JOIN kategori C ON C.id = B.id_kategori
    ORDER BY A.merk ASC ');
    return $query->result();
    }

    function datatipe_barang() //oke
    {
    $query = $this->db->query('SELECT A.*, B.merk, C.jenis, B.id_jenis FROM barang_tipe A
    LEFT JOIN barang_merk B ON B.id = A.id_merk
    LEFT JOIN barang_jenis C ON C.id = B.id_jenis
    ORDER BY C.jenis ASC ');
    return $query->result();
    }

    function dropdown_supplier() //oke
    {
        $sql = "SELECT * FROM supplier ORDER BY nama";
        $query = $this->db->query($sql);

            $value[''] = '-- PILIH --';
            foreach ($query->result() as $row){
                $value[$row->id] = $row->nama;
            }
            return $value;
    }


    function dropdown_kategori() //oke
    {
        $sql = "SELECT * FROM kategori ORDER BY nama";
        $query = $this->db->query($sql);

            $value[''] = '-- PILIH --';
            foreach ($query->result() as $row){
                $value[$row->id] = $row->nama;
            }
            return $value;
    }

    function dropdown_jenisbarang() //oke
    {
        $sql = "SELECT * FROM barang_jenis ORDER BY jenis";
        $query = $this->db->query($sql);

        return $query;
    }

    function select_jenis_barang($id_kategori) //oke
    {
        $sql = "SELECT * FROM barang_jenis WHERE id_kategori ='$id_kategori' ORDER BY jenis";
        $query = $this->db->query($sql);

        return $query;
    }


    function dropdown_merk($id_jenis) //oke
    {
        $sql = "SELECT * FROM barang_merk WHERE id_jenis ='$id_jenis' ORDER BY merk";
        $query = $this->db->query($sql);

        $value[''] = '';
        foreach ($query->result() as $row){
        $value[$row->id] = $row->merk;
        }
        return $value;
    }

    function dropdown_tipe($id_merk) //oke
    {
        $sql = "SELECT * FROM barang_tipe WHERE id_merk ='$id_merk' ORDER BY tipe";
        $query = $this->db->query($sql);

        $value[''] = '';
        foreach ($query->result() as $row){
        $value[$row->id] = $row->tipe;
        }
        return $value;
    }

    function dropdown_cabang($lokasi) //oke
    {
        $sql = "SELECT * FROM tbl_cabang WHERE lokasi ='$lokasi' ORDER BY kode_cabang";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function dropdown_kcu() //oke
    {
        $sql = "SELECT * FROM tbl_cabang WHERE is_kcu =1 ORDER BY kode_cabang ASC";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function dropdown_pusat() //oke
    {
        $sql = "SELECT * FROM tbl_cabang WHERE lokasi ='2' ORDER BY nama_cabang";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function dropdown_gudang_lama() //oke
    {
        $sql = "SELECT * FROM tbl_cabang WHERE kode_cabang='P00003' ORDER BY nama_cabang";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function get_cabang($id_user) //oke
    {

    $this->load->library('curl');

    $ch = curl_init();
    // set url
    $url = "https://program.pusatgadai.id/hris/api_internal/dropdown_cabang?nik=".$id_user;
    curl_setopt($ch, CURLOPT_URL, $url);

    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // tutup curl
    curl_close($ch);

    $profile = json_decode($output, TRUE);
    $elements = array();
    foreach ($profile as $v_user) {

        $elements[] = "'".$v_user['kode_cabang']."'";

    }

        $hasil = implode(',', $elements);

        return $hasil;
    }

    function get_pusat() //oke
    {
        $sql = "SELECT * FROM tbl_cabang WHERE lokasi ='2' ORDER BY nama_cabang";
        $query = $this->db->query($sql)->result_array();
        $elements = array();
        foreach ($query as $v_user) {

        $elements[] = "'".$v_user['kode_cabang']."'";

    }

        $hasil = implode(',', $elements);

        return $hasil;

    }

    function dropdown_pengirim() //oke
    {
        $kode_cabang = $this->session->userdata('kode_cabang');
        $sql = "SELECT * FROM tbl_cabang WHERE kode_cabang ='$kode_cabang' ORDER BY nama_cabang";
        $query = $this->db->query($sql);

        return $query->result_array();
    }

    function datakategori_kirim() //oke
    {
    $query = $this->db->query('SELECT * FROM pengiriman_kategori');
    return $query->result();
    }

    function dropdown_karyawan($id_divisi) //oke
    {
        $sql = "SELECT A.nik, A.nama_karyawan FROM tbl_karyawan A
        WHERE A.flag_aktif ='A' AND A.id_divisi ='$id_divisi'";
        $query = $this->db->query($sql);

        return $query->result();
    }

    function dropdown_kirimbarang($pengirim, $penerima, $search) //oke
    {
        if($search !==""){
        $like = "AND A.nomer_barang LIKE '%$search%' OR D.jenis LIKE '%$search%' OR C.merk LIKE '%$search%' OR B.tipe LIKE '%$search%'";
        } else {
        $like ="";
        }
        if($this->session->userdata('level') !=='User'){
            $where ='';
        } else {
        if($penerima == 'P00003'){
            $where ='';
        } else {
            $where ='AND D.id_kategori = 2';
        }
        }
        $sql = "SELECT A.nomer_barang, D.jenis, C.merk, B.tipe, SUM(A.jumlah_stok) as jumlah,
        (SELECT IFNULL(SUM(y.jumlah),0) FROM pengiriman_detail y
        LEFT JOIN pengiriman x on x.id = y.id_pengiriman
        WHERE y.nomer_barang in(A.nomer_barang) AND x.flag=0 AND x.pengirim='$pengirim') as jumlah_gantung
        FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        WHERE A.pic = '$pengirim' $where $like
        GROUP by A.nomer_barang
        HAVING (jumlah-jumlah_gantung) > 0";
        $query = $this->db->query($sql);

        return $query->result();
    }

    function dropdown_pakaibarang($pengirim, $search) //oke
    {

        if($search !==""){
        $like = "AND A.nomer_barang LIKE '%$search%' OR D.jenis LIKE '%$search%' OR C.merk LIKE '%$search%' OR B.tipe LIKE '%$search%'";
        } else {
        $like ="";
        }

        $sql = "SELECT A.nomer_barang, D.jenis, C.merk, B.tipe, SUM(A.jumlah_stok) as jumlah,
        (SELECT IFNULL(SUM(y.jumlah),0) FROM pengiriman_detail y
        LEFT JOIN pengiriman x on x.id = y.id_pengiriman
        WHERE y.nomer_barang in(A.nomer_barang) AND x.flag=0 AND x.pengirim='$pengirim') as jumlah_gantung
        FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        WHERE A.pic = '$pengirim' AND D.id_kategori = 2 $like
        GROUP by A.nomer_barang
        HAVING (jumlah-jumlah_gantung) > 0";
        $query = $this->db->query($sql);

        return $query->result();
    }

    function dropdown_barang_inventaris($pengirim) //oke
    {

    $search = $this->input->post('query');
    if(isset($search) && $search !==""){
        $like = "AND A.nomer_barang LIKE '%$search%' OR D.jenis LIKE '%$search%' OR C.merk LIKE '%$search%' OR B.tipe LIKE '%$search%'";
    }

    $sql = "SELECT A.nomer_barang, D.jenis, C.merk, B.tipe, SUM(A.jumlah_stok) as jumlah,
    (SELECT IFNULL(SUM(y.jumlah),0) FROM pengiriman_detail y
    LEFT JOIN pengiriman x on x.id = y.id_pengiriman
    WHERE y.nomer_barang in(A.nomer_barang) AND x.flag=0 AND x.pengirim='$pengirim') as jumlah_gantung
    FROM stok_barang A
        LEFT JOIN barang_tipe B ON B.id = A.id_tipe
        LEFT JOIN barang_merk C ON C.id = B.id_merk
        LEFT JOIN barang_jenis D ON D.id = C.id_jenis
        WHERE A.pic = '$pengirim' AND D.id_kategori = 1 $like
        GROUP by A.nomer_barang
        HAVING (jumlah-jumlah_gantung) > 0";
        $query = $this->db->query($sql);

    return $query->result();
    }

    function dropdown_divisi() //oke
    {
    $sql = "SELECT * FROM tbl_divisi ORDER BY nm_Divisi";
    $query = $this->db->query($sql);

    return $query->result();
    }


    function dropdown_jenisbarang_invent() //oke
    {
    $sql = "SELECT * FROM barang_jenis WHERE id_kategori=1 ORDER BY jenis";
    $query = $this->db->query($sql);

    return $query->result();
    }

    function upload() //oke
    {

    // File upload configuration
    $config['upload_path']    = './upload/invoice/';
    $config['allowed_types']  = 'gif|jpg|jpeg|png|pdf|doc|docx';
    $config['max_size']       = '5000';
    $config['max_width']      = '5000';
    $config['max_height'] 		= '5000';

    // Load and initialize upload library
    $this->load->library('upload', $config);
    $this->upload->initialize($config);

    // Upload file to server
    if ($this->upload->do_upload('foto_invoice')) {
        $return = $this->upload->data();
        return $return;
        }

    }


    function hapus_data($where,$table){ //oke
    $this->db->where($where);
    $this->db->delete($table);
    }

    function stok_from_pemakaian($no_invoice){ //oke

    $select = $this->db->query("SELECT
    A.id as id_detail, A.pic, A.total_harga, A.id_tipe, A.jumlah, A.no_invoice,
    A.tanggal, A.user_input, A.nomer_barang
    FROM pemakaian A
    WHERE A.no_invoice ='$no_invoice'");

    $result = $select->result();
    $data=array();
    $no=0;
    foreach ($result as $key) {
    //barang keluar
    $data[]=array(
        'id_tipe'=>$key->id_tipe,
        'nomer_barang'=>$key->nomer_barang,
        'pic'=>$key->pic,
        'jumlah'=>$key->jumlah,
        'total_harga'=>$key->total_harga,
        'tanggal'=>$key->tanggal,
        'no_invoice'=>$key->no_invoice,
        'id_detail'=>$key->id_detail,
        'flag' =>0,
        'user_input'=>$key->user_input
    );

    $no++;
    }

    $this->db->trans_start();
    $this->db->insert_batch('barang_keluar', $data);
    $this->db->trans_complete();
    }

    function dropdown_id_user($username) //oke
    {

    $sql = "SELECT
            nik as id_user, Nm_karyawan as nama from tbl_InformasiKaryawan
            WHERE nik = '$username'

            UNION
            SELECT B.kdCabang as id_user,
            CONCAT('Cabang ',B.Nm_Cabang) as nama
            from tbl_karyawan A
            LEFT JOIN tbl_cabang B ON B.kdCabang = A.id_cabang
            WHERE A.nik = '$username'";

            $query = $this->db->query($sql);

    if($this->session->userdata('kode_cabang') !== "999"){
        foreach ($query->result() as $row){
            $value[$row->id_user] = $row->nama;
        }
    } else {
        $value[$this->session->userdata('id_user')] = $this->session->userdata('nama_user');
    }

        return $value;
    }

    function datalist_fixed_asset($id_user) //oke
    {

    $query = $this->db->query("SELECT
    A.nomer_barang, D.jenis, C.merk, B.tipe, A.pic,
    SUM(A.jumlah_stok) as jumlah_stok, sum(A.total_asset) as total_asset

    FROM stok_barang A
    LEFT JOIN barang_tipe B ON B.id = A.id_tipe
    LEFT JOIN barang_merk C ON C.id = B.id_merk
    LEFT JOIN barang_jenis D ON D.id = C.id_jenis
    WHERE A.pic = '$id_user' AND D.id_kategori=1
    GROUP by A.nomer_barang HAVING jumlah_stok > 0");

    return $query->result();

    }

    function datalist_current_asset($id_user) //oke
    {

    $query = $this->db->query("SELECT
    A.nomer_barang, D.jenis, C.merk, B.tipe, A.pic,
    SUM(A.jumlah_stok) as jumlah_stok, sum(A.total_asset) as total_asset

    FROM stok_barang A
    LEFT JOIN barang_tipe B ON B.id = A.id_tipe
    LEFT JOIN barang_merk C ON C.id = B.id_merk
    LEFT JOIN barang_jenis D ON D.id = C.id_jenis
    WHERE A.pic = '$id_user' AND D.id_kategori=2
    GROUP by A.nomer_barang HAVING jumlah_stok > 0");

    return $query->result();

    }


    function datalist_jenis_barang($id_kategori) //oke
    {
    if($id_kategori > 0){

    $where = "WHERE A.id_kategori =".$id_kategori;

    } else {
    $where = "";
    }
    $query = $this->db->query("SELECT A.*, B.nama, B.kode FROM barang_jenis A
    LEFT JOIN kategori B ON B.id = A.id_kategori
    $where
    ORDER BY A.jenis ASC");
    return $query->result();
    }


    function datalist_tipe_barang($id_jenis_barang) //oke
    {
    if($id_jenis_barang > 0){

    $where = "WHERE C.id =".$id_jenis_barang;

    } else {
    $where = "";
    }

    $query = $this->db->query("SELECT A.*, B.merk, C.jenis FROM barang_tipe A
    LEFT JOIN barang_merk B ON B.id = A.id_merk
    LEFT JOIN barang_jenis C ON C.id = B.id_jenis
    $where
    ORDER BY C.jenis ASC");
    return $query->result();
    }

}