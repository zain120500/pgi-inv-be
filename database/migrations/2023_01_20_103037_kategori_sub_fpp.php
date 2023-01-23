<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KategoriSubFpp extends Migration
{

    public function up()
    {
        Schema::create('kategori_sub_fpp', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('id_kategori_fpp');
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
        Schema::dropIfExists('kategori_sub_fpp');

    }
}
