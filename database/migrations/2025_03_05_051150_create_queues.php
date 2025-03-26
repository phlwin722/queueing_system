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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('name');
            $table->string('email');
            $table->string('email_status');
            $table->integer('queue_number');
            $table->enum('status', ['waiting', 'serving', 'cancelled','finished'])->default('waiting');
            $table->string('waiting_customer')->nullable();
            $table->unsignedBigInteger('currency_selected')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues'); 
    }
};
