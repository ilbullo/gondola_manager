<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aggiunge la colonna senza vincoli inizialmente
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });

        // Popola i dati esistenti con la logica SLUG
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $parts = explode(' ', trim($user->name));
            if (count($parts) >= 2) {
                $iniziale = Str::lower(substr($parts[1], 0, 1));
                $cognome = Str::slug($parts[0]); 
                $username = $user->license_number;

                $user->update(['username' => $username]);
            } else {
                $user->update(['username' => Str::slug($user->license_number.'_'.Str::slug($user->name))]);
            }
        }

        // Ora rende la colonna obbligatoria e UNICA
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
