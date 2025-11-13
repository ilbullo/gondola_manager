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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Agency name (e.g., "Agency A", "Custom Agency")
            $table->string('code',4)->unique(); // Agency short name (e.g. Bucintoro -> BUCI)
            $table->timestamps();
            $table->softDeletes(); // Aggiunge la colonna deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
