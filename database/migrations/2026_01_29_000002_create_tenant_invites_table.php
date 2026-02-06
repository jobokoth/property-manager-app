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
        Schema::create('tenant_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users');
            $table->string('email');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('token', 64)->unique();
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->date('start_date');
            $table->enum('status', ['pending', 'accepted', 'expired', 'cancelled'])->default('pending');
            $table->datetime('expires_at');
            $table->datetime('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invites');
    }
};
