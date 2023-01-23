<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KategoriFpp extends Migration
{

    public function up()
    {
        Schema::create('kategori_fpp', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kategori_jenis_fpp');        
            $table->string('name');
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
        Schema::dropIfExists('kategori_fpp');

    }
}
