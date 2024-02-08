<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Channel;
use App\Models\Video;
use App\Repositories\MeilisSearchRepository;

class ChannelModelTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function 作成()
    {
        $channel = Channel::factory()->create();

        $this->assertInstanceOf(Channel::class, $channel);
        $this->assertDatabaseHas('channels', ['id' => $channel->id]);
    }

    /**
     * @test
     */
    public function 更新()
    {
        $channel = Channel::factory()->create();

        $updatedChannelData = [
            'title' => 'Updated Channel Name',
        ];

        $channel->update($updatedChannelData);

        $this->assertEquals($updatedChannelData['title'], $channel->title);
    }

    /**
     * @test
     */
    public function 削除()
    {
        $channel = Channel::factory()->create();

        $channel->forceDelete();

        $this->assertModelMissing($channel);
    }

    /**
     * @test
     */
    public function リレーション()
    {
        $channel = Channel::factory()->create();
        $video = $channel->videos()->create([
            'title' => 'Video Title',
            'description' => 'Video Description',
            'youtube_id' => 'Video Youtube ID',
            'etag' => 'Video Etag',
            'duration' => 100,
            'published_at' => now(),
        ]);

        $this->assertInstanceOf(Channel::class, $video->channel);
    }

    /**
     * @test
     */
    public function タイムスタンプ()
    {
        $channel = Channel::factory()->create();

        $this->assertNotNull($channel->created_at);
        $this->assertNotNull($channel->updated_at);
    }
    
    /**
     * @test
     */
    public function ソフトデリート()
    {
        $channel = Channel::factory()->create();

        $channel->delete();

        $this->assertSoftDeleted($channel);
    }

    /**
     * @test
     */
    public function リストア()
    {
        $channel = Channel::factory()->create();

        $channel->delete();
        $channel->restore();

        $this->assertDatabaseHas('channels', ['id' => $channel->id]);
    }

}