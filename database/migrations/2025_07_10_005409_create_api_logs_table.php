<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('operation_type', ['virtual_model', 'virtual_try_on']);
            $table->string('task_id')->nullable();
            $table->string('model_name')->nullable();
            $table->text('prompt')->nullable(); // Solo para virtual_model
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed']);
            $table->string('task_status')->nullable();

            // 🔥 MANTENER request_data PARA virtual_model (metadatos)
            $table->json('request_data')->nullable();

            // 🔥 AGREGAR rutas específicas PARA virtual_try_on
            $table->string('human_image_path')->nullable(); // Solo try-on
            $table->string('cloth_image_path')->nullable(); // Solo try-on

            $table->json('response_data')->nullable();
            $table->json('error_details')->nullable();
            $table->string('endpoint');
            $table->string('http_method');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
