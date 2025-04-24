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
        Schema::table('queue_numbers', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('teller_id') 
                ->constrained('branchs')
                ->cascadeOnDelete();
        });

        Schema::table('break_times', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('break_to') 
                ->constrained('branchs')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_numbers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']); // Drop foreign key first
            $table->dropColumn('branch_id'); // Then drop the column
        });

        Schema::table('break_times', function (Blueprint $table) {
            $table->dropForeign(['branch_id']); // Drop foreign key first
            $table->dropColumn('branch_id'); // Then drop the column
        });
    }
};
