
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
        Schema::create('tellers', function (Blueprint $table) {
            $table->id();
            $table->string('teller_firstname');
            $table->string('teller_lastname');
            $table->string('teller_username')->unique();
            $table->string('teller_password');
            $table->foreignId('type_id')
                    ->nullable()
                    ->constrained('types')
                    ->onDelete('cascade');
            $table->string('type_ids_selected')->nullable();
            $table->string('Image')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tellers');
    }
};