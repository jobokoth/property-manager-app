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
        Schema::create('mpesa_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Foreign key to properties table
            $table->unsignedBigInteger('tenant_user_id')->nullable(); // Foreign key to users table
            $table->text('raw_text'); // Raw Mpesa message text
            $table->string('sender_msisdn')->nullable(); // Sender's phone number
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->string('trans_id')->nullable(); // Transaction ID from Mpesa
            $table->timestamp('trans_time')->nullable(); // Transaction timestamp
            $table->json('parsed_json')->nullable(); // Parsed transaction details
            $table->enum('status', ['new', 'parsed', 'matched', 'rejected'])->default('new');
            $table->unsignedBigInteger('uploaded_by_user_id'); // Foreign key to users table
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('tenant_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_messages');
    }
};
