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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('token')->nullable();
            $table->integer('rating');
            $table->string('ease_of_use');
            /* $table->string('waiting_time_satisfaction'); */
            $table->integer('waiting_time_satisfaction');
            $table->text('suggestions')->nullable();
            $table->unsignedBigInteger('branch_id')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
