<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\ChannelService;

class ChannelServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function create_チャンネルが新規登録される()
    {
        $channelService = app(ChannelService::class);
        $channelId = $channelService->create('new_channel_id', '新しいチャンネル');
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => 'new_channel_id',
            'title' => '新しいチャンネル',
        ]);
    }

    /**
     * @test
     */
    public function findByYoutubeId_チャンネルが取得できる()
    {
        $channelService = app(ChannelService::class);
        $channelService->create('new_channel_id', '新しいチャンネル');
        $channel = $channelService->findByYoutubeId('new_channel_id');
        $this->assertEquals('新しいチャンネル', $channel['title']);
    }

    /**
     * @test
     */
    public function findByYoutubeId_チャンネルが取得できない()
    {
        $channelService = app(ChannelService::class);
        $channel = $channelService->findByYoutubeId('not_exists_channel_id');
        $this->assertEmpty($channel);
    }
    
    /**
     * @test
     */
    public function update_チャンネルが更新される()
    {
        $channelService = app(ChannelService::class);
        $channelId = $channelService->create('new_channel_id', '新しいチャンネル');
        $channelService->update('new_channel_id', 'updated_title');
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => 'new_channel_id',
            'title' => 'updated_title',
        ]);
    }

    /**
     * @test
     */
    public function upsert_チャンネルが新規登録される()
    {
        $channelService = app(ChannelService::class);
        $channelId = $channelService->upsert('new_channel_id', '新しいチャンネル');
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => 'new_channel_id',
            'title' => '新しいチャンネル',
        ]);
    }

    /**
     * @test
     */
    public function upsert_チャンネルが更新される()
    {
        $channelService = app(ChannelService::class);
        $channelId = $channelService->create('new_channel_id', '新しいチャンネル');
        $channelService->upsert('new_channel_id', 'updated_title');
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => 'new_channel_id',
            'title' => 'updated_title',
        ]);
    }

}