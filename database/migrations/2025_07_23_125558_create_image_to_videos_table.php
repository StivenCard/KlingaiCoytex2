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
        Schema::create('image_to_videos', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('model_name');
            $table->text('prompt');
            $table->text('negative_prompt')->nullable();
            $table->string('mode')->default('std');
            $table->string('duration')->default('5');
            $table->string('aspect_ratio')->default('16:9');
            $table->json('input_image_paths');
            $table->json('result_video_paths')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_to_videos');
    }
};
