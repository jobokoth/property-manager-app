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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Foreign key to properties table
            $table->unsignedBigInteger('unit_id'); // Foreign key to units table
            $table->unsignedBigInteger('tenancy_id'); // Foreign key to tenancies table
            $table->unsignedBigInteger('tenant_user_id'); // Foreign key to users table
            $table->enum('category', ['plumbing', 'electrical', 'carpentry', 'painting', 'cleaning', 'appliance', 'security', 'other']);
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'in_review', 'quoted', 'scheduled', 'in_progress', 'completed', 'closed'])->default('open');
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('tenancy_id')->references('id')->on('tenancies')->onDelete('cascade');
            $table->foreign('tenant_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
