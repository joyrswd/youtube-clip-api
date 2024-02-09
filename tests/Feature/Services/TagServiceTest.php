<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\TagService;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Tag;
use App\Repositories\MeilisSearchRepository;

class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;
    private Video $video;
    private Tag $tag;

    public function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
        $this->video = Video::factory()->create([
            'channel_id' => $this->channel->id,
        ]);
    }

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function addTags_タグが新規登録される()
    {
        $tagService = app(TagService::class);
        $tagService->addTags(['新しいタグ'], $this->video->id);
        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    /**
     * @test
     */
    public function addTags_タグが既に登録されている場合は新規登録されない()
    {
        $tagService = app(TagService::class);
        $tagService->addTags(['新しいタグ'], $this->video->id);
        $tagService->addTags(['新しいタグ'], $this->video->id);
        $this->assertEquals(1, Tag::where('name', '新しいタグ')->count());
    }

}