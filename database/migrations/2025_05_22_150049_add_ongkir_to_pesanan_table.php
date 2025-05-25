<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('pesanan', function (Blueprint $table) {
        $table->integer('ongkir')->default(0)->after('status_pesanan');
    });
}

public function down()
{
    Schema::table('pesanan', function (Blueprint $table) {
        $table->dropColumn('ongkir');
    });
}

};
