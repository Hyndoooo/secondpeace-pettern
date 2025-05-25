<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->unsignedBigInteger('id_ref')->nullable()->after('id_user');
    });
}

public function down()
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->dropColumn('id_ref');
    });
}

};
