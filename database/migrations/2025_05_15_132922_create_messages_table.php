<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('chat_room_id');
    $table->unsignedBigInteger('sender_id');
    $table->text('message');
    $table->timestamps();

    $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
    $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
});

    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};