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
            $table->enum('email_status',['sending_customer','pending_alert','sending_alert','thankyou_sending']);
            $table->integer('queue_number');
            $table->enum('status', ['waiting', 'serving', 'cancelled','finished'])->default('waiting');
            $table->string('waiting_customer')->nullable();
            $table->unsignedBigInteger('currency_selected')->nullable();
            $table->string('priority_service')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
