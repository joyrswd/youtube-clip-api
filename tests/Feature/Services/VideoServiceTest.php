<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\VideoService;
use App\Models\Channel;
use App\Models\Video;
use App\Repositories\MeilisSearchRepository;

class VideoServiceTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;

    public function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
    }

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function create_動画が新規登録される()
    {
        $videoService = app(VideoService::class);
        $videoId = $videoService->create([
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '新しい動画',
            'description' => '新しい動画の説明',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '新しい動画',
            'description' => '新しい動画の説明',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
    }

    /**
     * @test
     */
    public function update_動画が更新される()
    {
        $videoService = app(VideoService::class);
        $videoId = $videoService->create([
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'title' => '新しい動画',
            'etag' => 'new_etag',
            'description' => '新しい動画の説明',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $videoService->update([
            'title' => '更新された動画',
            'description' => '更新された動画の説明',
            'youtube_id' => 'new_video_id',
            'channel_id' => $this->channel->id,
            'etag' => 'new_etag',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '更新された動画',
            'description' => '更新された動画の説明',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
    }

    /**
     * @test
     */
    public function upsert_動画が新規登録される()
    {
        $videoService = app(VideoService::class);
        $videoId = $videoService->upsert([
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '新しい動画',
            'description' => '新しい動画の説明',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '新しい動画',
            'description' => '新しい動画の説明',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
    }

    /**
     * @test
     */
    public function upsert_動画が更新される()
    {
        $videoService = app(VideoService::class);
        $videoId = $videoService->create([
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'title' => '新しい動画',
            'etag' => 'new_etag',
            'description' => '新しい動画の説明',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $videoService->upsert([
            'title' => '更新された動画',
            'description' => '更新された動画の説明',
            'youtube_id' => 'new_video_id',
            'channel_id' => $this->channel->id,
            'etag' => 'new_etag',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'channel_id' => $this->channel->id,
            'youtube_id' => 'new_video_id',
            'etag' => 'new_etag',
            'title' => '更新された動画',
            'description' => '更新された動画の説明',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
    }


}