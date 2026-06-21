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
    Schema::create('siswa', function (Blueprint $table) {
        // id_siswa Int(11) otomatis jadi Primary Key dan Auto Increment
        $table->integer('id_siswa')->autoIncrement(); 
        
        // nama_siswa Varchar(100)
        $table->string('nama_siswa', 100);
        
        // kelas Varchar(10)
        $table->string('kelas', 10);
        
        // username Varchar(50)
        $table->string('username', 50)->unique(); // Ditambahkan unique untuk keamanan login
        
        // password Varchar(100)
        $table->string('password', 100);
        
        // Opsional: Menambahkan kolom created_at dan updated_at bawaan laravel
        $table->timestamps(); 
    });
}

};
