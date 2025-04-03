<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->time('break_from'); // Stores start time
            $table->time('break_to');   // Stores end time
            $table->timestamps();       // Created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
};

