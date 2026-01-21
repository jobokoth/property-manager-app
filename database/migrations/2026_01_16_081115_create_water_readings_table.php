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
        Schema::create('water_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('water_meter_id'); // Foreign key to water_meters table
            $table->date('reading_date'); // Date of the reading
            $table->integer('reading_value'); // Actual reading value
            $table->unsignedBigInteger('captured_by_user_id'); // Foreign key to users table
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            $table->foreign('water_meter_id')->references('id')->on('water_meters')->onDelete('cascade');
            $table->foreign('captured_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_readings');
    }
};
