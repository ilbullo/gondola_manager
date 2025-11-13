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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->onDelete('set null');            
            $table->unsignedInteger('slot'); // 0 to numColumns-1
            $table->string('value', 1)->nullable(); // 'N', 'X', 'A', 'C', 'P'
            $table->string('voucher')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->unsignedInteger('slots_occupied')->default(1);
            $table->timestamps();
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
