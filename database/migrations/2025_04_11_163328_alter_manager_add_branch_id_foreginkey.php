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
        // Add foreign key on 'managers' table
        Schema::table('managers', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('restrict'); // Adjust as needed: cascade, restrict, set null, etc.
        });

        // foregin the type branch id on branhs
        Schema::table('types', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Adjust as needed: cascade, restrict, set null, etc.
        });

        // foregin the teller branch id on branhs
        Schema::table('tellers', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Adjust as needed: cascade, restrict, set null, etc.
        });

        // foregin the type branch id on branhs
        Schema::table('windows', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Adjust as needed: cascade, restrict, set null, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key on 'managers' table
        Schema::table('managers', function (Blueprint $table) {
            $table->dropForeign('managers_branch_id_foreign');
        });

        // Drop foreign key on 'tellers' table
        Schema::table('tellers', function (Blueprint $table) {
            $table->dropForeign('tellers_branch_id_foreign');
        });

        // Drop foreign key on 'types' table
        Schema::table('types', function (Blueprint $table) {
            $table->dropForeign('types_branch_id_foreign');
        });

        // Drop foreign key on 'windows' table
        Schema::table('windows', function (Blueprint $table) {
            $table->dropForeign('windows_branch_id_foreign');
        });
    }
};
