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
        Schema::create('license_table', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->index('date');
            $table->unsignedBigInteger('user_id');
            $table->integer('order')->default(0);
            $table->unique(['date', 'order']);
            $table->index('order');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_table');
    }
};
