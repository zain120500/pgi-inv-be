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
{{--    <script src="https://cdn.tailwindcss.com"></script>--}}
</head>
<body>

<div class="container container-pdf">
    <div class="row">
        <div class="col-sm-12">
            <h4>Detail Persetujuan Perbaikan</h4>
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
                            <td class="col-sm-4">
                                <h6>No. Pengajuan</h6>
                                <span>{{$query->im_number ?? "-"}}</span>
                            </td>
                            <td class="col-sm-4">
                                <h6>Cabang</h6>
                                <span>{{$query->cabang->name ?? "-"}}</span>
                            </td>
                            <td class="col-sm-4">
                                <h6>Divisi</h6>
                                <span>{{$query->devisi->nm_Divisi ?? "-"}}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-4">
                                <h6>Kategori</h6>
                                <span>{{$query->kategori_jenis->name ?? "-"}}</span>
                            </td>
                            <td class="col-sm-4">
                                <h6>Pengajuan</h6>
                                <span>{{$query->kategori_sub->name ?? "-"}}</span>
                            </td>
                            <td class="col-sm-4">
                                <h6>Kuantitas</h6>
                                <span>{{$query->qty ?? "-"}}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-4">
                                <h6>Maintenance</h6>
                                <span>{{$query->memo_maintenance->id_user_maintenance ?? "-"}}</span>
                            </td>
                            <td class="col-sm-4">
                                <h6>Catatan</h6>
                                <span>{{$query->catatan ?? "-"}}</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Bukti Foto</h5>
                            <br />
                            @foreach($memo as $memos)
                                @if($memos->flag == "foto")
                                    <img src="{{$memos->path}}" class="img-thumbnail" alt="Cinque Terre" width="120" height="50">
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <h5>Riwayat Pengajuan</h5>
                    <table class="table table-bordered">
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
                                <td class="col-sm-4">
                                    <button type="button" class="btn btn-secondary btn-sm">Di Buat</button>
                                </td>
                                @elseif($status->status == 1)
                                <td class="col-sm-4"><button type="button" class="btn btn-primary btn-sm">Di Setujui</button></td>
                                @elseif($status->status == 2)
                                <td class="col-sm-4"><button type="button" class="btn btn-primary btn-sm">Di Setujui</button></td>
                                @elseif($status->status == 3)
                                <td class="col-sm-4"><button type="button" class="btn btn-primary btn-sm">Di Proses</button></td>
                                @elseif($status->status == 4)
                                <td class="col-sm-4"><button type="button" class="btn btn-success btn-sm">Di Selesaikan</button></td>
                                @endif
                                <td class="col-sm-4">{{$status->keterangan}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{--<div class="container mx-auto container-pdf">--}}
{{--<div>--}}
{{--    <div class="px-4 sm:px-0">--}}
{{--        <h3 class="text-base font-semibold leading-7 text-gray-900">Applicant Information</h3>--}}
{{--        <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Personal details and application.</p>--}}
{{--    </div>--}}
{{--    <div class="mt-6 border-t border-gray-100">--}}
{{--        <dl class="divide-y divide-gray-100">--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">Full name</dt>--}}
{{--                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">Margot Foster</dd>--}}
{{--            </div>--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">Application for</dt>--}}
{{--                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">Backend Developer</dd>--}}
{{--            </div>--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">Email address</dt>--}}
{{--                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">margotfoster@example.com</dd>--}}
{{--            </div>--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">Salary expectation</dt>--}}
{{--                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">$120,000</dd>--}}
{{--            </div>--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">About</dt>--}}
{{--                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">Fugiat ipsum ipsum deserunt culpa aute sint do nostrud anim incididunt cillum culpa consequat. Excepteur qui ipsum aliquip consequat sint. Sit id mollit nulla mollit nostrud in ea officia proident. Irure nostrud pariatur mollit ad adipisicing reprehenderit deserunt qui eu.</dd>--}}
{{--            </div>--}}
{{--            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">--}}
{{--                <dt class="text-sm font-medium leading-6 text-gray-900">Attachments</dt>--}}
{{--                <dd class="mt-2 text-sm text-gray-900 sm:col-span-2 sm:mt-0">--}}
{{--                    <ul role="list" class="divide-y divide-gray-100 rounded-md border border-gray-200">--}}
{{--                        <li class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">--}}
{{--                            <div class="flex w-0 flex-1 items-center">--}}
{{--                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">--}}
{{--                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />--}}
{{--                                </svg>--}}
{{--                                <div class="ml-4 flex min-w-0 flex-1 gap-2">--}}
{{--                                    <span class="truncate font-medium">resume_back_end_developer.pdf</span>--}}
{{--                                    <span class="flex-shrink-0 text-gray-400">2.4mb</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="ml-4 flex-shrink-0">--}}
{{--                                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Download</a>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">--}}
{{--                            <div class="flex w-0 flex-1 items-center">--}}
{{--                                <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">--}}
{{--                                    <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd" />--}}
{{--                                </svg>--}}
{{--                                <div class="ml-4 flex min-w-0 flex-1 gap-2">--}}
{{--                                    <span class="truncate font-medium">coverletter_back_end_developer.pdf</span>--}}
{{--                                    <span class="flex-shrink-0 text-gray-400">4.5mb</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="ml-4 flex-shrink-0">--}}
{{--                                <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">Download</a>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </dd>--}}
{{--            </div>--}}
{{--        </dl>--}}
{{--    </div>--}}
{{--</div>--}}
{{--</div>--}}

</body>

<style>
    .container-pdf{
        max-width: 640px
    }

    .text {
        display: flex;
        justify-content: center;
    }
</style>
</html>
