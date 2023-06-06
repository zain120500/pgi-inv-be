<?php

namespace App\Console\Commands;

use App\Model\InternalMemo;
use App\Model\InternalMemoMaintenance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        \Log::info("Testing Cron is Running ... !");

        /*
           Write your database logic we bellow:
           User::create(['email'=>'send mail']);
        */
//        dd("Testing Cron is Running ... ");

        $imMaintenance = InternalMemoMaintenance::get();

        $arr = [];
        $arrs = [];
        foreach ($imMaintenance as $keys => $value){

            if($value->date < Carbon::createFromFormat('Y-m-d H:i:s', $value->created_at)->addDays(1)->format('Y-m-d'))
            {
                $value->update([
                    'flag' => 2,
                ]);
                $arr[] = $imMaintenance->first();
            }else {
                $arr[] = "Gagal";
            }

            $iMemo = InternalMemo::where('id', $value->id_internal_memo)->get();
            if(!empty($iMemo)) {
                foreach ($iMemo as $key => $memo) {
                    $memo->update([
                        'flag' => 11,
                    ]);
                    $arrs[] = $memo->first();
                }
            }
        }

        $this->info('testing:cron Command Run Successfully !');
    }
}
