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
    Schema::create('guru', function (Blueprint $table) {
        // id_guru Int(11) otomatis jadi Primary Key dan Auto Increment
        $table->integer('id_guru')->autoIncrement();
        
        // nama siswa Varchar(100) -> Sesuai gambar kolom tertulis 'nama siswa' tapi keterangan 'Nama guru'
        // Disarankan menggunakan nama_guru agar konsisten, namun berikut jika mengikuti nama field gambar:
        $table->string('nama_siswa', 100); 
        
        // username Varchar(50)
        $table->string('username', 50)->unique();
        
        // password Varchar(100)
        $table->string('password', 100);
        
        $table->timestamps();
    });
}

};
