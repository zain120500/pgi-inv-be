<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h4>Detail Persetujuan</h4>
            <div class="row">
{{--                <div class="col-sm-6">--}}
{{--                    <h6>Internal Memo Number</h6>--}}
{{--                </div>--}}
{{--                <div class="col-sm-6">--}}
{{--                    <h6>123456789</h6>--}}
{{--                </div>--}}
                <div class="col-sm-12">
                    <table class="table table-borderless">
                        <tbody>
                        <tr>
                            <td>No. Pengajuan</td>
                            <td>{{$query->im_number ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Cabang</td>
                            <td>{{$query->cabang->name ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Divisi</td>
                            <td>{{$query->devisi->nm_Divisi ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>{{$query->kategori_jenis->name ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Pengajuan</td>
                            <td>{{$query->kategori_sub->name ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Kuantitas</td>
                            <td>{{$query->qty ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Catatan</td>
                            <td>{{$query->catatan ?? "-"}}</td>
                        </tr>
                        <tr>
                            <td>Maintenance</td>
                            <td>{{$query->memo_maintenance->id_user_maintenance ?? "-"}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Bukti Foto</h5>
                            <br />
                            @foreach($memo as $memos)
                                @if($memos->flag == "foto")
                                    <img src="{{$memos->path}}" class="img-thumbnail" alt="Cinque Terre" width="150" height="60">
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <h5>Riwayat Pengajuan</h5>
                    <table class="table table-borderless">
                        <thead>
                        <tr>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($history as $status)
                            <tr>
                                @if($status->status == 0)
                                <td><button type="button" class="btn btn-secondary">Di Buat</button></td>
                                @elseif($status->status == 1)
                                <td><button type="button" class="btn btn-primary">Di Setujui</button></td>
                                @elseif($status->status == 2)
                                <td><button type="button" class="btn btn-primary">Di Setujui</button></td>
                                @elseif($status->status == 3)
                                <td><button type="button" class="btn btn-primary">Di Proses</button></td>
                                @elseif($status->status == 4)
                                <td><button type="button" class="btn btn-success">Di Selesaikan</button></td>
                                @endif
                                <td>{{$status->keterangan}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
{{--        <div class="col-sm-4">--}}
{{--            <h3>Column 3</h3>--}}
{{--            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>--}}
{{--            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>--}}
{{--        </div>--}}
    </div>
</div>

</body>
</html>
