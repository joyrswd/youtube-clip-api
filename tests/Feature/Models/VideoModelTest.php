<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Tag;
use App\Repositories\MeilisSearchRepository;
use DateTime;

class VideoModelTest extends TestCase
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
    public function 作成()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $this->assertInstanceOf(Video::class, $video);
        $this->assertDatabaseHas('videos', ['id' => $video->id]);
    }

    /**
     * @test
     */
    public function 更新()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $updatedChannelData = [
            'title' => 'Updated Channel Name',
            'description' => 'Updated Channel Description',
            'youtube_id' => 'Updated Channel Youtube ID',
            'etag' => 'Updated Channel Etag',
            'duration' => 100,
            'published_at' => new \DateTime(),
        ];

        $video->update($updatedChannelData);

        $this->assertEquals($updatedChannelData['title'], $video->title);
        $this->assertEquals($updatedChannelData['description'], $video->description);
        $this->assertEquals($updatedChannelData['youtube_id'], $video->youtube_id);
        $this->assertEquals($updatedChannelData['etag'], $video->etag);
        $this->assertEquals($updatedChannelData['duration'], $video->duration);
        $this->assertEquals($updatedChannelData['published_at'], $video->published_at);
    }

    /**
     * @test
     */
    public function 削除()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $video->forceDelete();

        $this->assertModelMissing($video);
    }

    /**
     * @test
     */
    public function リレーション()
    {
        $channel = Channel::factory()->create();
        $video = Video::factory()->create(['channel_id' => $channel->id]);

        $this->assertInstanceOf(Channel::class, $video->channel);
    }

    /**
     * @test
     */
    public function タグリレーション()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);
        $tag = Tag::factory()->create();

        $video->tags()->attach($tag->id);

        $this->assertInstanceOf(Tag::class, $video->tags->first());
    }

    /**
     * @test
     */
    public function タイムスタンプ()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $this->assertNotNull($video->created_at);
        $this->assertNotNull($video->updated_at);
    }
    
    /**
     * @test
     */
    public function ソフトデリート()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $video->delete();

        $this->assertSoftDeleted($video);
    }

    /**
     * @test
     */
    public function リストア()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $video->delete();
        $video->restore();

        $this->assertDatabaseHas('videos', ['id' => $video->id]);
    }

    /**
     * @test
     */
    public function Meilisearch()
    {
        $video = Video::factory()->create(['channel_id' => $this->channel->id]);

        $this->assertNotNull($video->toSearchableArray());
    }

    /**
     * @test
     */
    public function MeilisearchArrangedValues()
    {
        $data = [
            'title' => 'Video Title',
            'description' => 'Video Description',
            'youtube_id' => 'Video Youtube ID',
            'etag' => 'Video Etag',
            'duration' => 100,
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
            'channel_id' => $this->channel->id,
        ];
        $video = Video::factory()->create($data);
        //　タグを追加
        $tag = Tag::factory()->create();
        $video->tags()->attach($tag->id);

        // Meilisearchの各値が整形されているか確認
        $searchable = $video->toSearchableArray();
        $this->assertEquals('https://www.youtube.com/watch?v=' . $data['youtube_id'], $searchable['url']);
        $this->assertEquals('https://i.ytimg.com/vi/' . $data['youtube_id'] . '/default.jpg', $searchable['thumbnail']);
        $this->assertEquals($data['title'], $searchable['title']);
        $this->assertEquals($data['description'], $searchable['description']);
        $this->assertEquals($this->channel->title, $searchable['channel']);
        $this->assertEquals($data['duration'], $searchable['duration']);
        $this->assertEquals('01:40', $searchable['time']);
        $this->assertEquals($data['published_at']->format('Y-m-d H:i:s'), $searchable['published_at']);
        $this->assertEquals($data['published_at']->getTimeStamp(), $searchable['timesatmp']);
        $this->assertEquals(collect([$tag->name]), $searchable['tags']);
    }

}