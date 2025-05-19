<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdukTable extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->bigIncrements('id_produk');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('nama_produk');
            $table->string('kategori_produk');
            $table->text('deskripsi');
            $table->integer('harga');
            $table->string('gambar')->nullable();
            $table->enum('kualitas', ['tinggi', 'sedang', 'rendah']);
            $table->enum('size', ['S', 'M', 'L', 'XL']);
            $table->integer('stok');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
}
