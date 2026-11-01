<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('category', 20)->default('akademik');
            $table->string('title');
            $table->string('recipient')->nullable();
            $table->string('level')->nullable();
            $table->date('achieved_at')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('curriculum_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('subjects')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 20)->default('kegiatan');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('alumni_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('university');
            $table->string('major')->nullable();
            $table->smallInteger('graduation_year');
            $table->string('type', 30)->default('ptn');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_destinations');
        Schema::dropIfExists('academic_events');
        Schema::dropIfExists('curriculum_groups');
        Schema::dropIfExists('achievements');
    }
};
