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
            $table->text('hints_used')->nullable(); // Hints aplicados
            $table->enum('status', ['submitted', 'processing', 'completed', 'failed']);
            $table->string('task_status')->nullable(); // Estado de la API
            $table->text('task_status_msg')->nullable();
            $table->json('request_data')->nullable(); // Payload enviado (sin imágenes base64)
            $table->json('response_data')->nullable(); // Respuesta de la API
            $table->json('error_details')->nullable(); // Detalles de errores
            $table->string('endpoint'); // URL del endpoint usado
            $table->string('http_method'); // GET/POST
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
