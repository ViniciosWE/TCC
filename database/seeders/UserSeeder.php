<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'Super Administrador',
            'email' => 'superadministrador@gmail.com',
            'tipo' => 'SUPER_ADMINISTRADOR',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'name' => 'Administrador',
            'email' => 'administrador@gmail.com',
            'tipo' => 'ADMINISTRADOR',
            'password' => Hash::make('12345678'),
        ]);
    }
}
