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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key'); // Unique key for the template (e.g., rent_due, late_payment)
            $table->json('channel'); // Channel(s) for notification (in_app, email, sms)
            $table->string('subject')->nullable(); // Subject of the notification
            $table->text('body'); // Body/content of the notification
            $table->boolean('is_active')->default(true); // Whether the template is active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
