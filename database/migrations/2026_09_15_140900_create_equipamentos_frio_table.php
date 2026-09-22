<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipamentos_frio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->string('nome');
            $table->enum('tipo', [
                'camara_congelada',
                'camara_refrigerada',
                'freezer_domestico',
                'balcao_refrigerado',
                'freezer_supermercado',
            ])->default('camara_congelada');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->decimal('temp_min', 10, 2)->nullable();
            $table->decimal('temp_max', 10, 2);
            $table->decimal('tolerancia_c', 10, 2)->default(0.50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipamentos_frio');
    }
};