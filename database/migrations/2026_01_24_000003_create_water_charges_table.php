<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Option A - Simple monthly water charge per tenancy.
     */
    public function up(): void
    {
        Schema::create('water_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained()->cascadeOnDelete();
            $table->string('period_month', 7); // YYYY-MM format
            $table->decimal('amount', 10, 2);
            $table->enum('source', ['manual', 'auto'])->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenancy_id', 'period_month']);
            $table->index('period_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_charges');
    }
};
