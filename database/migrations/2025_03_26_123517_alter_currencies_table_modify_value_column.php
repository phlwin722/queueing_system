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
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('flag')->after('currency_symbol');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('value', 'buy_value'); // Step 1: Rename 'value' to 'buy_value'
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->decimal('sell_value', 10, 2)->after('buy_value'); // Step 2: Add 'sell_value' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('flag'); // Drop 'sell_value' column
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('sell_value'); // Drop 'sell_value' column
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('buy_value', 'value'); // Rename 'buy_value' back to 'value'
        });
    }
};
