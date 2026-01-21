<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            DB::statement("ALTER TABLE allocations MODIFY COLUMN allocation_type ENUM('rent', 'water', 'other', 'overpayment') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allocations', function (Blueprint $table) {
            DB::statement("ALTER TABLE allocations MODIFY COLUMN allocation_type ENUM('rent', 'water', 'other') NOT NULL");
        });
    }
};
