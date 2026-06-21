<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 🔥 Teacher hanya dibuat melalui seeder (tidak bisa register)
        User::create([
            'full_name' => 'Pak Budi Guru',
            'username'  => 'budi_guru',
            'email'     => 'budi@teacher.com',
            'password'  => Hash::make('password123'),
            'role'      => 'teacher',
        ]);

        // Student bisa register, tapi kita buat contoh juga
        User::create([
            'full_name' => 'Rian Siswa',
            'username'  => 'rian_siswa',
            'email'     => 'rian@student.com',
            'password'  => Hash::make('password123'),
            'role'      => 'student',
        ]);

        $this->command->info('✅ User Seeder berhasil!');
        $this->command->info('📌 Teacher: budi_guru / password123');
        $this->command->info('📌 Student: rian_siswa / password123');
    }
}