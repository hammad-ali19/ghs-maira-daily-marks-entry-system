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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_section_subject_id')->constrained('class_section_subject')->cascadeOnDelete();
            $table->string('title');
            $table->date('test_date');
            $table->unsignedInteger('max_marks');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['class_section_subject_id', 'test_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
