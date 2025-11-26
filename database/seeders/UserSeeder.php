<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Healer;
use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Criar gestor
        $manager = User::firstOrCreate([
            'name' => 'Gestor Principal',
            'email' => 'gestor@ceal.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_MANAGER,
            'phone' => '(11) 99999-9999',
        ]);

        // Criar magnetizador
        $healerUser = User::firstOrCreate([
            'name' => 'Magnetizador Exemplo',
            'email' => 'magnetizador@ceal.com',
            'password' => Hash::make('password'),
            'type' => User::TYPE_HEALER,
            'birth_date' => '1980-05-15',
            'phone' => '(11) 97777-7777',
        ]);

        // Criar informações do magnetizador
        $healer = Healer::firstOrCreate([
            'user_id' => $healerUser->id,
            'specialty' => 'Magnetoterapia Holística',
            'license_number' => 'MT12345',
            'active' => true,
        ]);

        // Criar paciente de exemplo
        Patient::firstOrCreate([
            'name' => 'Paciente Exemplo',
            'email' => 'paciente@ceal.com',
            'birth_date' => '1955-10-20',
            'phone' => '(11) 96666-6666',
            'emergency_contact' => '(11) 95555-5555',
            'preferred_healer_id' => $healer->id,
            'access_code' => '123456',
            'manager_id' => $manager->id,
        ]);
    }
}
