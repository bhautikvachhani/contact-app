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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('profile_image')->nullable();
            $table->string('additional_file')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('is_merged')->default(false);
            $table->unsignedBigInteger('merged_into')->nullable();
            $table->json('merged_data')->nullable();
            $table->timestamps();
            
            $table->foreign('merged_into')->references('id')->on('contacts')->onDelete('set null');
            $table->index(['name', 'email', 'gender']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
