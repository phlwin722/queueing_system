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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('First_name');
            $table->string('Lastname');
            $table->string('Username');
            $table->string('Password');
            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');      
            $table->string('types');
            $table->string('Image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};