<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('balanca_id')->nullable()->constrained('balancas')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('data_conferencia');
            $table->enum('status', ['aprovado', 'reprovado', 'aprovado_com_ressalva'])->default('aprovado');
            $table->decimal('peso_esperado', 10, 3)->nullable();
            $table->decimal('peso_real', 10, 3)->nullable();
            $table->decimal('diferenca', 10, 3)->nullable();
            $table->decimal('tolerancia_usada', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferencias');
    }
};