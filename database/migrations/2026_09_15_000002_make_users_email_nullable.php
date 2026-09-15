<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
        });

        DB::table('users')->where('email', '')->update(['email' => null]);
    }

    public function down(): void
    {
        DB::table('users')->whereNull('email')->update(['email' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 255)->change();
        });
    }
};