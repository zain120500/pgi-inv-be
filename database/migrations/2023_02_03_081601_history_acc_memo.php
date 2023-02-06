<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HistoryAccMemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_memo', function (Blueprint $table) {
            $table->id();
            $table->integer('id_internal_memo');
            $table->integer('id_pic');
            $table->integer('user_id');
            $table->integer('devisi_id');
            $table->integer('flag');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_memo');
    }
}
