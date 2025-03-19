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
        Schema::create('window_archives', function (Blueprint $table) {
            $table->id();
            $table->string('window_name');
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('teller_id')->nullable();
            $table->timestamp('archived_at')->useCurrent(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('window_archives');
    }
};
