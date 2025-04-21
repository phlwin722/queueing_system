<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

        // foreign the type the branch id on branchs
        Schema::table('types', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // 
        });

        // foreign the teller branch id on branchs
        Schema::table('tellers', function (Blueprint $table) {
            // add foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // foregin the window branch id on branchs 
        Schema::table('windows', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // foregin the currencie branch id on branchs
        Schema::table('currencies', function (Blueprint $table) {
            // add foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // foreign the survey_responses branch id on branchs
        Schema::table('survey_responses', function (Blueprint $table) {
            // add foregin key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // foreign the queues branch id on branchs
        Schema::table('queues', function (Blueprint $table) {
            // add foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        // foreign the window_archives branch id on branchs
        Schema::table('window_archives', function (Blueprint $table) {
            // add foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('appointments', function (Blueprint $table) {
            // add foreign key
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('appointments', function (Blueprint $table) {
            // add foreign key 
            $table->foreign('type_id')
                ->references('id')
                ->on('types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        Schema::table('waiting_times', function (Blueprint $table) {
            // add foreign key 
            $table->foreign('branch_id')
                ->references('id')
                ->on('branchs')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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

        Schema::table('types', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('tellers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('windows', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('window_archives', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('waiting_times', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });
    }
};
