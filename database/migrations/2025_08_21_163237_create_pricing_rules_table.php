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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            // Campos originales
            $table->string('model_name');
            $table->string('mode')->nullable();
            $table->string('duration')->nullable();
            $table->float('tokens')->nullable();
            $table->float('price')->nullable();
            // Nuevos campos
            $table->string('billing_type')->nullable();
            // per_second, per_request, per_image, per_5_seconds
            $table->string('feature')->nullable();
            // native_audio, no_native_audio, motion_control,
            // text_to_image, image_to_image, etc.
            $table->string('resolution')->nullable();
            // 720P, 1080P, 4K, 1K, 2K
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
