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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenancy_id'); // Foreign key to tenancies table
            $table->string('period_month'); // Month in YYYY-MM format
            $table->decimal('rent_due', 10, 2)->default(0); // Rent amount due
            $table->decimal('rent_paid', 10, 2)->default(0); // Rent amount paid
            $table->decimal('water_due', 10, 2)->default(0); // Water amount due
            $table->decimal('water_paid', 10, 2)->default(0); // Water amount paid
            $table->decimal('carried_forward', 10, 2)->default(0); // Amount carried forward
            $table->timestamps();

            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
