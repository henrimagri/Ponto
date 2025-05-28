<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário admin padrão
        User::create([
            'name' => 'Administrador',
            'cpf' => '11111111111',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'dob' => '1990-01-01',
            'cep' => '01310-100',
            'address' => 'Avenida Paulista',
            'numero' => '1000',
            'complemento' => 'Conjunto 1',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'manager_id' => null,
        ]);

        // Criar um gestor exemplo
        $gestor = User::create([
            'name' => 'João Gestor',
            'cpf' => '22222222222',
            'email' => 'gestor@teste.com',
            'password' => Hash::make('123456'),
            'role' => 'gestor',
            'dob' => '1985-05-15',
            'cep' => '04038-001',
            'address' => 'Rua Vergueiro',
            'numero' => '2000',
            'complemento' => null,
            'bairro' => 'Vila Mariana',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'manager_id' => null,
        ]);

        // Criar um funcionário exemplo subordinado ao gestor
        User::create([
            'name' => 'Maria Funcionária',
            'cpf' => '33333333333',
            'email' => 'funcionario@teste.com',
            'password' => Hash::make('123456'),
            'role' => 'funcionario',
            'dob' => '1992-08-20',
            'cep' => '04567-890',
            'address' => 'Rua das Flores',
            'numero' => '123',
            'complemento' => 'Apto 45',
            'bairro' => 'Jardim São Paulo',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'manager_id' => $gestor->id,
        ]);
    }
}
