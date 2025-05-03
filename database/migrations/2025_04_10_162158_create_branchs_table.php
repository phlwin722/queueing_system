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
        Schema::create('branchs', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('managers')
                ->onUpdate('cascade')
                ->onDelete('set null');  // Adjust as needed: cascade, restrict, set null, etc.
            $table->string('region') ->nullable();
            $table->string('province') ->nullable();
            $table->string('city') ->nullable();
            $table->string('Barangay') ->nullable();
            $table->string('address') ->nullable();
            $table->string('status') ->nullable();
            $table->string('opening_date') ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branchs');
    }
};
