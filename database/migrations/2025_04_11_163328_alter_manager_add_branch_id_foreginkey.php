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
        Schema::table('managers', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('restrict'); // Adjust as needed: cascade, restrict, set null, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
    }
};
