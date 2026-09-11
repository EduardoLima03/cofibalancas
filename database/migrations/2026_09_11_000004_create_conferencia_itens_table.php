<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conferencia_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conferencia_id')->constrained('conferencias')->cascadeOnDelete();
            $table->string('descricao_item')->nullable();
            $table->decimal('peso_esperado', 10, 3);
            $table->decimal('peso_real', 10, 3);
            $table->decimal('diferenca', 10, 3);
            $table->boolean('dentro_tolerancia')->default(true);
            $table->string('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conferencia_itens');
    }
};