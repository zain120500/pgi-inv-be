<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserStaffCabang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('_user_staff_cabang', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_staff_id');
            $table->bigInteger('cabang_id');
            $table->bigInteger('role_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_user_staff_cabang');
    }
}
