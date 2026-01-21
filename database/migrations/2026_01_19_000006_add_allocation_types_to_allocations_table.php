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
        // For SQLite, we need to recreate the table to modify the enum
        // For MySQL, we can use ALTER TABLE
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ENUM, it uses CHECK constraints
            // The existing column should work as-is since SQLite uses text for enums
            // Just ensure the application logic handles the new types
        } else {
            // MySQL: Modify the enum to include new types
            DB::statement("ALTER TABLE allocations MODIFY allocation_type ENUM('rent', 'water', 'other', 'rent_arrears', 'water_arrears', 'advance', 'overpayment') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE allocations MODIFY allocation_type ENUM('rent', 'water', 'other', 'overpayment') NOT NULL");
        }
    }
};
