<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_try_ons', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('task_id')->unique();
            $table->string('model_name'); // kolors-virtual-try-on-v1, kolors-virtual-try-on-v1-5
            $table->enum('model_type', ['virtual', 'default', 'upload']); // Tipo de modelo usado
            $table->string('human_image_path')->nullable(); // Ruta del modelo guardado
            $table->enum('garments_type', ['single', 'multiple']); // Tipo de prendas
            $table->string('cloth_image_path')->nullable(); // Ruta de prenda guardada
            $table->integer('output_count')->default(1);
            $table->json('result_image_paths')->nullable(); // URLs de resultados guardados
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed'])->default('submitted');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_try_ons');
    }
};
