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
            $table->string('task_id')->unique();
            $table->string('model_name'); // kling-v1, kling-v1-5, kling-v2
            $table->text('prompt')->nullable();
            $table->string('gender')->nullable(); // male, female
            $table->string('age')->nullable(); // children, youth, elderly
            $table->string('skin_tone')->nullable(); // light, medium, dark, etc
            $table->string('aspect_ratio')->default('1:1'); // 16:9, 9:16, 1:1, etc
            $table->integer('output_count')->default(1);
            $table->json('result_image_paths')->nullable(); // URLs de imágenes guardadas
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed'])->default('submitted');
            $table->text('task_status_msg')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_models');
    }
};
