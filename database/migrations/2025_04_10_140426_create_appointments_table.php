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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('referenceNumber');      
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->string('email');
            $table->unsignedBigInteger('type_id');
            $table->date('appointment_date');
            $table->enum('status', ['Booked', 'Arrived', 'Completed'])->default('Booked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
