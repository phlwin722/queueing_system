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
            $table->string('region');
            $table->string('province');
            $table->string('city');
            $table->string('Barangay');
            $table->string('address');
            $table->string('status');
            $table->string('opening_date');
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
