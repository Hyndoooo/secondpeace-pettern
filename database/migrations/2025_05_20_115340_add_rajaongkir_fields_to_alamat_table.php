<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRajaongkirFieldsToAlamatTable extends Migration
{
    public function up()
    {
        Schema::table('alamat', function (Blueprint $table) {
            $table->string('provinsi_id')->nullable();
            $table->string('provinsi_nama')->nullable();
            $table->string('kota_id')->nullable();
            $table->string('kota_nama')->nullable();
        });
    }

    public function down()
    {
        Schema::table('alamat', function (Blueprint $table) {
            $table->dropColumn(['provinsi_id', 'provinsi_nama', 'kota_id', 'kota_nama']);
        });
    }
}
