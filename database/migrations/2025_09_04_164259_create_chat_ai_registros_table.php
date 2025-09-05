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
        Schema::create('chat_ai_registros', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->text('user_message');
            $table->text('ai_response');
            $table->float('tokens_prompt')->nullable();
            $table->float('tokens_thought')->nullable();
            $table->float('tokens_response')->nullable();
            $table->float('tokens_total')->nullable();
            $table->float('price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_ai_registros');
    }
};
