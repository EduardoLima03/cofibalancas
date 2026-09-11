<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conferencias', function (Blueprint $table) {
            $table->datetime('data_conferencia')->change();
        });
    }

    public function down(): void
    {
        Schema::table('conferencias', function (Blueprint $table) {
            $table->date('data_conferencia')->change();
        });
    }
};