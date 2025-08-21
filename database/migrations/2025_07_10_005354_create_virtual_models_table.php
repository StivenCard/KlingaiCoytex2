<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_models', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('task_id')->unique();
            $table->string('model_name'); // kling-v1, kling-v1-5, kling-v2
            $table->text('prompt')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->enum('age_group', ['children', 'youth', 'elderly'])->default('youth');
            $table->enum('skin_tone', ['light', 'medium', 'dark','olive'])->default('medium');
            $table->enum('aspect_ratio', ['1:1', '9:16', '2:3',  '3:4'])->default('3:4');
            $table->float('tokens')->nullable();
            $table->float('price')->nullable();
            $table->integer('output_count')->default(1);
            $table->json('result_image_paths')->nullable();
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_models');
    }
};
