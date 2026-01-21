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
        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenancy_id'); // Foreign key to tenancies table
            $table->string('period_month'); // Month in YYYY-MM format
            $table->timestamp('generated_at'); // When the statement was generated
            $table->json('totals_json'); // JSON object containing statement totals
            $table->string('pdf_url')->nullable(); // Optional URL to PDF version
            $table->enum('status', ['draft', 'generated', 'sent'])->default('generated');
            $table->timestamps();

            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statements');
    }
};
