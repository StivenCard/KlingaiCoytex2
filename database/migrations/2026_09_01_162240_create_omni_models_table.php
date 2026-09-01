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
        Schema::create('omni_models', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('task_id')->unique();
            $table->string('model_name');
            $table->enum('model_type',['virtual','default','upload']);
            $table->text('prompt')->nullable();
            $table->string('human_image_path')->nullable();
            $table->enum('garments_type',['single','multiple'])->default('single');
            $table->string('cloth_image_path')->nullable();
            $table->string('output_count')->default('1');
            $table->enum('aspect_ratio',['16:9','9:16','1:1','4:3','3:4','3:2','2:3','21:9','auto'])->default('auto');
            $table->enum('resolution',['1k','2k','4k'])->default('1k');
            $table->float('tokens')->nullable();
            $table->float('price')->nullable();
            $table->json('result_image_paths')->nullable();
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed'])->default('submitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('omni_models');
    }
};
