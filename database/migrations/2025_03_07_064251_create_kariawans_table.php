<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kariawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik')->unique();
            $table->string('nama');
            $table->enum('kelamin',['Laki-laki','Perempuan']);
            $table->string('jabatan');
            $table->boolean('delete')->default(0);
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
        Schema::dropIfExists('kariawans');
    }
};
