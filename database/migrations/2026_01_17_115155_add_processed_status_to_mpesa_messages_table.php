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
        Schema::table('mpesa_messages', function (Blueprint $table) {
            DB::statement("ALTER TABLE mpesa_messages MODIFY COLUMN status ENUM('new', 'parsed', 'matched', 'rejected', 'processed') NOT NULL DEFAULT 'new'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mpesa_messages', function (Blueprint $table) {
            DB::statement("ALTER TABLE mpesa_messages MODIFY COLUMN status ENUM('new', 'parsed', 'matched', 'rejected') NOT NULL DEFAULT 'new'");
        });
    }
};
