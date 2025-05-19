<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id'); // pelanggan
    $table->unsignedBigInteger('admin_id')->nullable(); // admin/cs
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
});

    }

    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};