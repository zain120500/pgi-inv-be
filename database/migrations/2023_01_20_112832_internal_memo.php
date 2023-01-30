<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InternalMemo extends Migration
{

    public function up()
    {
        Schema::create('internal_memo', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kategori_fpp');
            $table->integer('id_kategori_jenis_fpp');
            $table->integer('id_kategori_sub_fpp');
            $table->integer('id_devisi');
            $table->integer('qty');
            $table->integer('created_by');
            $table->string('catatan');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('internal_memo');

    }
}
