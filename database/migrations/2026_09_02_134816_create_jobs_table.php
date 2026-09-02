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
        Schema::create('recruitment_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('location')->nullable();
            $table->string('workplace_type')->default('hybrid');
            $table->string('employment_type')->default('full_time');
            $table->string('status')->default('draft');
            $table->text('description');
            $table->jsonb('required_skills')->nullable();
            $table->unsignedSmallInteger('openings')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_jobs');
    }
};
