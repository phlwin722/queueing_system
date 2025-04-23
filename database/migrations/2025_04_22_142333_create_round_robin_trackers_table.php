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
        Schema::create('round_robin_trackers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('last_teller_id');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Optional: Add foreign key constraints if your branch, type, or teller IDs come from other tables
            $table->foreign('branch_id')->references('id')->on('branchs');
            $table->foreign('type_id')->references('id')->on('types');
            $table->foreign('last_teller_id')->references('id')->on('tellers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_robin_trackers');
    }
};
