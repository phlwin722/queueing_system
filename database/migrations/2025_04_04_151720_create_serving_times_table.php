<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('serving_times', function (Blueprint $table) {
            $table->id();
            $table->integer('minutes');

            // Nullable foreign key to types table
            $table->unsignedBigInteger('type_id')->nullable();
            $table->foreign('type_id')->references('id')->on('types')->onDelete('set null');

            // Nullable foreign key to tellers table
            $table->unsignedBigInteger('teller_id')->nullable();
            $table->foreign('teller_id')->references('id')->on('tellers')->onDelete('set null');
            
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('serving_times');
    }
};
