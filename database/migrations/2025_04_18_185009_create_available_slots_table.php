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
        Schema::create('available_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('prepared_time')->nullable();
            $table->integer('is_available')->default(20); // Default to 20 available slots
            $table->unsignedBigInteger('branch_id');

             // Foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Adjust this to your branch table name
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_slots');
    }
};
