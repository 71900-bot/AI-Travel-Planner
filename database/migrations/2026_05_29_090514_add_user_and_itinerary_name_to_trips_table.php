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
        Schema::table('trips', function (Blueprint $table) {
            // Ownership: link trip to the user who generated it
            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Friendly itinerary name for history list/detail
            $table->string('itinerary_name')->after('budget');

            // Keep the DB aligned with what the UI expects
            // (optional if you want to incorporate travelers into the name reliably)
            // If you don't want this column, we can omit it.
            $table->unsignedInteger('travelers')->default(1)->after('duration');

            // Store itinerary as text (OpenAI returns a string)
            // If you already have a text column, no change required.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'itinerary_name', 'travelers']);
        });
    }
};

