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
        Schema::create('tag_video', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete()->comment('タグID');
            $table->foreignId('video_id')->constrained()->cascadeOnDelete()->comment('動画ID');
            $table->primary(['tag_id', 'video_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_video');
    }
};
