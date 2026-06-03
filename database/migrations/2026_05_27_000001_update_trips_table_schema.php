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
            // Change budget from string to decimal
            $table->decimal('budget', 10, 2)->change();
            
            // Add deleted_at for soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // Revert budget back to string
            $table->string('budget')->change();
            
            // Remove soft deletes
            $table->dropSoftDeletes();
        });
    }
};
