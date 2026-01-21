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
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id'); // Foreign key to units table
            $table->unsignedBigInteger('tenant_user_id'); // Foreign key to users table
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Nullable for ongoing tenancies
            $table->decimal('rent_amount', 10, 2); // Snapshot of rent amount at tenancy start
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->enum('status', ['active', 'terminated', 'expired'])->default('active');
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('tenant_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenancies');
    }
};
