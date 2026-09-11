<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets_glpi', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id', 50)->nullable();
            $table->foreignId('conferencia_id')->constrained('conferencias')->cascadeOnDelete();
            $table->unsignedBigInteger('conferencia_item_id')->nullable();
            $table->string('titulo');
            $table->text('descricao');
            $table->string('status', 50)->default('enviado');
            $table->text('resposta_glpi')->nullable();
            $table->decimal('diferenca', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets_glpi');
    }
};