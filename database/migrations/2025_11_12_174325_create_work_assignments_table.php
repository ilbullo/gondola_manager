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
        Schema::create('work_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_table_id');
            $table->unsignedBigInteger('agency_id')->nullable();           
            $table->unsignedInteger('slot'); // 0 to numColumns-1
            $table->string('value', 1)->nullable(); // 'N', 'X', 'A', 'C', 'P'
            $table->string('voucher')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->unsignedInteger('slots_occupied')->default(1);
            $table->timestamps();
            $table->foreign('license_table_id')->references('id')->on('license_table')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('set null');
            $table->index(['license_table_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_assignments');
    }
};
