<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loja_user', function (Blueprint $table) {
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->primary(['loja_id', 'user_id']);
            $table->foreign('loja_id')->references('id')->on('lojas')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        DB::table('loja_user')->insertUsing(
            ['loja_id', 'user_id', 'created_at', 'updated_at'],
            DB::table('users')
                ->whereNotNull('loja_id')
                ->selectRaw('loja_id, id, NOW(), NOW()')
        );

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('loja_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('loja_id')->nullable()->after('role');
        });

        Schema::dropIfExists('loja_user');
    }
};