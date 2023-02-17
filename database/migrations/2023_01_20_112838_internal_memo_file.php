<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InternalMemoFile extends Migration
{
    public function up()
    {
        Schema::create('internal_memo_files', function (Blueprint $table) {
            $table->id();
            $table->integer('id_internal_memo');
            $table->string('path')->nullable();
            $table->string('path_video')->nullable();
            $table->string('keterangan');
            $table->bigInteger('flag');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internal_memo_files');

    }
}
