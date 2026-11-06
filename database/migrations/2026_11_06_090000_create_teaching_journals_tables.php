<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('topic', 150);
            $table->text('activity');
            $table->text('assignment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'date']);
            $table->index(['teaching_assignment_id', 'date']);
        });

        Schema::create('teaching_journal_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('teaching_journals')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status', 10)->default('hadir');
            $table->string('note', 150)->nullable();
            $table->timestamps();

            $table->unique(['journal_id', 'student_id']);
        });

        Schema::create('teaching_journal_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('teaching_journals')->cascadeOnDelete();
            $table->string('type', 10);
            $table->string('path');
            $table->string('original_name', 150)->nullable();
            $table->timestamps();

            $table->index('journal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journal_attachments');
        Schema::dropIfExists('teaching_journal_attendances');
        Schema::dropIfExists('teaching_journals');
    }
};
