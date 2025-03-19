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
        Schema::table('queues', function (Blueprint $table) {
            $table->foreignId('teller_id')
                ->nullable()
                ->after('type_id') // Place it after email_status
                ->constrained('tellers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['teller_id']); // Drop foreign key first
            $table->dropColumn('teller_id'); // Then drop the column
        });
    }
};
