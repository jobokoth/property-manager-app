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
        Schema::create('service_request_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id'); // Foreign key to service_requests table
            $table->enum('type', ['image', 'video']); // Type of media
            $table->string('cloudinary_public_id'); // Public ID in Cloudinary
            $table->string('url'); // URL to the media file
            $table->integer('bytes')->nullable(); // File size in bytes
            $table->string('format')->nullable(); // File format
            $table->integer('duration')->nullable(); // Duration in seconds for videos
            $table->timestamps();

            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_media');
    }
};
