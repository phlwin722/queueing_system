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
        Schema::create('reset_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_reset')->default(false);
            $table->enum('reset_type', ['Daily', 'Weekly', 'Monthly'])->nullable();
            $table->time('reset_time')->nullable(); // Ensures HH:MM:SS format
            $table->string('reset_day')->nullable();
            $table->date('reset_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reset_settings');
    }
};
