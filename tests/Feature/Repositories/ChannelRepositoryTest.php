<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\ChannelRepository;
use App\Models\Channel;

class ChannelRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    public function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
    }

    /**
     * @test
     */
    public function store_新しいチャンネルを登録する()
    {
        $channelRepository = new ChannelRepository();
        $channelId = $channelRepository->store([
            'youtube_id' => 'new_youtube_id',
            'title' => 'new_title',
        ]);
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => 'new_youtube_id',
            'title' => 'new_title',
        ]);
    }

    /**
     * @test
     */
    public function findByYoutubeId_存在する場合は配列で返す()
    {
        $channelRepository = new ChannelRepository();
        $channel = $channelRepository->findByYoutubeId($this->channel->youtube_id);
        $this->assertEquals($this->channel->toArray(), $channel);
    }

    /**
     * @test
     */
    public function findByYoutubeId_存在しない場合は空の配列を返す()
    {
        $channelRepository = new ChannelRepository();
        $channel = $channelRepository->findByYoutubeId('not_exist_youtube_id');
        $this->assertEquals([], $channel);
    }

    /**
     * @test
     */
    public function updateByYoutubeId_チャンネルを更新する()
    {
        $channelRepository = new ChannelRepository();
        $channelId = $channelRepository->updateByYoutubeId($this->channel->youtube_id, [
            'title' => 'updated_title',
        ]);
        $this->assertDatabaseHas('channels', [
            'id' => $channelId,
            'youtube_id' => $this->channel->youtube_id,
            'title' => 'updated_title',
        ]);
        $this->assertEquals($this->channel->id, $channelId);
    }

}