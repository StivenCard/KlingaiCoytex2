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
            $table->string('model_name');         // kling-v1-6, kolors-v1-5, kolors-virtual-try-on-v1-5
            $table->string('mode')->nullable();   // std, pro, text-to-image, default
            $table->string('duration')->nullable(); // 5, 10, default
            $table->float('tokens')->nullable();
            $table->float('price')->nullable();
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
