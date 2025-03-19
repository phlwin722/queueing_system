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
        Schema::create('reset_window_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_reset')->default(false);
            $table->integer('reset_minutes')->default(0);
            $table->integer('reset_days')->default(0);
            $table->integer('reset_weeks')->default(0);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reset_window_settings');
    }
};
