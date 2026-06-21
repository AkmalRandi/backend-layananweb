<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('mata_pelajaran', function (Blueprint $table) {
        // id_mape Int(11) otomatis jadi Primary Key dan Auto Increment
        // Catatan: Mengikuti nama field di gambar 'id_mape'
        $table->integer('id_mape')->autoIncrement();
        
        // nama_mapel Varchar(50)
        $table->string('nama_mapel', 50);
        
        $table->timestamps();
    });
}

};
