<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agency_works', function (Blueprint $table) {
            $table->id();

            // Data del lavoro
            $table->date('date');

            // Relazione con l'utente
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Relazione con l'agenzia
            $table->foreignId('agency_id')
                  ->constrained('agencies')
                  ->onDelete('cascade');

            // Codice voucher (facoltativo)
            $table->string('voucher')->nullable();

            // Importo del lavoro
            $table->double('amount')->default(90);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_works');
    }
};
