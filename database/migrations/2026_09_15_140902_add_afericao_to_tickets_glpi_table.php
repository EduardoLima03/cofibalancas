<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets_glpi', function (Blueprint $table) {
            $table->dropForeign(['conferencia_id']);
            $table->unsignedBigInteger('conferencia_id')->nullable()->change();
            $table->foreignId('afericao_temperatura_id')->nullable()
                ->after('conferencia_item_id')
                ->constrained('afericoes_temperatura')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets_glpi', function (Blueprint $table) {
            $table->dropForeign(['afericao_temperatura_id']);
            $table->dropColumn('afericao_temperatura_id');
            $table->unsignedBigInteger('conferencia_id')->nullable(false)->change();
            $table->foreign('conferencia_id')->references('id')->on('conferencias')->cascadeOnDelete();
        });
    }
};