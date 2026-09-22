<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('afericoes_temperatura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->foreignId('equipamento_id')->nullable()->constrained('equipamentos_frio')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('data_afericao');
            $table->enum('status', ['aprovado', 'reprovado'])->default('aprovado');
            $table->decimal('temperatura_lida', 10, 2);
            $table->decimal('temp_min_usada', 10, 2)->nullable();
            $table->decimal('temp_max_usada', 10, 2)->nullable();
            $table->decimal('desvio', 10, 2)->default(0);
            $table->boolean('dentro_tolerancia')->default(true);
            $table->decimal('tolerancia_usada', 10, 2)->default(0.50);
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afericoes_temperatura');
    }
};