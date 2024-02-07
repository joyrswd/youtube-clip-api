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
        Schema::create('videos', function (Blueprint $table) {
            $table->id()->comment('動画ID');
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete()->comment('チャンネルID');
            $table->string('youtube_id')->unique()->comment('Youtube ID');
            $table->string('etag')->unique()->comment('Etag');
            $table->string('title')->comment('動画タイトル');
            $table->text('description')->nullable()->comment('動画説明');
            $table->integer('duration')->comment('動画時間');
            $table->timestamp('published_at')->comment('動画公開日');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
