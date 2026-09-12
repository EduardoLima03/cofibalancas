<?php

namespace Database\Seeders;

use App\Models\Balanca;
use App\Models\Loja;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $loja1 = Loja::create([
            'nome' => 'Loja Centro',
            'codigo' => '001',
            'cnpj' => '00.000.000/0001-01',
        ]);

        $loja2 = Loja::create([
            'nome' => 'Loja Norte',
            'codigo' => '002',
            'cnpj' => '00.000.000/0001-02',
        ]);

        Balanca::create([
            'loja_id' => $loja1->id,
            'nome' => 'Balança Balança 1',
            'marca' => 'Toledo',
            'modelo' => 'MGV-600',
            'capacidade_kg' => 600,
            'tolerancia_kg' => 0.500,
        ]);

        Balanca::create([
            'loja_id' => $loja1->id,
            'nome' => 'Balança Balança 2',
            'marca' => 'Filizola',
            'modelo' => 'ID-1500',
            'capacidade_kg' => 1500,
            'tolerancia_kg' => 1.000,
        ]);

        Balanca::create([
            'loja_id' => $loja2->id,
            'nome' => 'Balança Principal',
            'marca' => 'Toledo',
            'modelo' => 'MGV-300',
            'capacidade_kg' => 300,
            'tolerancia_kg' => 0.300,
        ]);

        User::create([
            'name' => 'Gerente Centro',
            'username' => 'gerente',
            'email' => 'gerente@gerente.com',
            'password' => 'gerente123',
            'role' => 'gerente',
            'loja_id' => $loja1->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Coletor Centro',
            'username' => 'coletor',
            'email' => 'coletor@coletor.com',
            'password' => 'coletor123',
            'role' => 'coletor',
            'loja_id' => $loja1->id,
            'is_active' => true,
        ]);
    }
}