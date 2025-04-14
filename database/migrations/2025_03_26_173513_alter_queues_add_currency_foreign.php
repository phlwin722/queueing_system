<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {


        // Add foreign key constraint
        $table->foreign('currency_selected')
                ->references('id')
                ->on('currencies')
                ->onUpdate('cascade')
                ->onDelete('set null');  // Adjust as needed: cascade, restrict, set null, etc.
    });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['currency_selected']);
        });
    }
};
