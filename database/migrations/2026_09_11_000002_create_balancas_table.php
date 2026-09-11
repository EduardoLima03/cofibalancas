<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balancas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('serial', 100)->nullable();
            $table->decimal('capacidade_kg', 10, 2)->default(0);
            $table->decimal('tolerancia_kg', 10, 3)->default(0.500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balancas');
    }
};