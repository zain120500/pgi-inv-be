<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KategoriPICFpp extends Migration
{

    public function up()
    {
        Schema::create('kategori_pic_fpp', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('devisi_id');
            $table->integer('id_kategori_fpp');
            $table->integer('created_by');
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
        Schema::dropIfExists('kategori_pic_fpp');

    }
}
