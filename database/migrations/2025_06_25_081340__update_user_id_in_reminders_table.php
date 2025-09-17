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
        Schema::table('reminders', function (Blueprint $table) {
            // 1. Drop the existing foreign key constraint
            $table->dropForeign(['user_id']);
            
            // 2. Change the column type to a regular unsigned big integer
            $table->unsignedBigInteger('user_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminders', function (Blueprint $table) {
            // Revert back to foreign key (if needed)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });
    }
};