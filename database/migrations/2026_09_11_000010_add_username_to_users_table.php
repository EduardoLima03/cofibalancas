<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->nullable()->unique()->after('name');
        });

        $users = DB::table('users')->orderBy('id')->get();

        $usados = [];

        foreach ($users as $user) {
            $base = $user->email ? explode('@', $user->email)[0] : 'usuario' . $user->id;
            $base = preg_replace('/[^a-zA-Z0-9_.-]/', '', $base) ?: 'usuario' . $user->id;

            $username = $base;
            $i = 1;

            while (in_array($username, $usados, true)) {
                $username = $base . $i;
                $i++;
            }

            $usados[] = $username;

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};