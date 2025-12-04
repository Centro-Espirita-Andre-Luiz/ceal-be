<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Healer;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Criar ou atualizar gestor
        $manager = User::updateOrCreate(
            ['email' => 'gestor@ceal.com'],
            [
                'name' => 'Gestor Principal',
                'password' => Hash::make('password'),
                'type' => User::TYPE_MANAGER,
                'phone' => '(11) 99999-9999',
            ]
        );

        // Criar ou atualizar magnetizador
        $healerUser = User::updateOrCreate(
            ['email' => 'magnetizador@ceal.com'],
            [
                'name' => 'Magnetizador Exemplo',
                'password' => Hash::make('password'),
                'type' => User::TYPE_HEALER,
                'birth_date' => '1980-05-15',
                'phone' => '(11) 97777-7777',
            ]
        );

        // Criar ou atualizar informações do magnetizador
        $healer = Healer::updateOrCreate(
            ['user_id' => $healerUser->id],
            [
                'specialty' => 'Magnetoterapia Holística',
                'license_number' => 'MT12345',
                'active' => true,
            ]
        );

        // Criar ou atualizar paciente de exemplo
        Patient::updateOrCreate(
            ['email' => 'paciente@ceal.com'],
            [
                'name' => 'Paciente Exemplo',
                'birth_date' => '1955-10-20',
                'phone' => '(11) 96666-6666',
                'emergency_contact' => '(11) 95555-5555',
                'preferred_healer_id' => $healer->id,
                'access_code' => '123456',
                'manager_id' => $manager->id,
            ]
        );

        $this->command->info('✅ Seeder executado com sucesso!');
    }
}
