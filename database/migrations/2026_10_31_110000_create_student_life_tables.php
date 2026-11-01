<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('advisor')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('programs')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('counseling_services', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->default('layanan');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('schedule')->nullable();
            $table->string('contact')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_services');
        Schema::dropIfExists('student_organizations');
    }
};
