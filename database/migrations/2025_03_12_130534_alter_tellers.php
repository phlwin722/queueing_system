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
        Schema::table('tellers', function (Blueprint $table) {
            $table->foreignId("types_id")
            ->nullable()
            ->constrained("types");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tellers', function (Blueprint $table) {
            $table->dropForeign(["types_id"]);
            $table->dropColumn("types");
        });
    }
};