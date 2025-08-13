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
        Schema::create('kling_user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('image_to_video_id')->nullable()
                ->constrained('image_to_videos')->onDelete('cascade');

            $table->foreignId('virtual_try_on_id')->nullable()
                ->constrained('virtual_try_ons')->onDelete('cascade');

            $table->foreignId('virtual_model_id')->nullable()
                ->constrained('virtual_models')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kling_user_logs');
    }
};
