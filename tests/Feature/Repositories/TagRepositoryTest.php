<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\TagRepository;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Tag;
use App\Repositories\MeilisSearchRepository;

class TagRepositoryTest extends TestCase
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
        $this->tag = Tag::factory()->create();
    }

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function store_タグが存在しない場合は新規登録される()
    {
        $tagRepository = new TagRepository();
        $tagId = $tagRepository->store('新しいタグ', $this->video->id);
        $this->assertDatabaseHas('tags', [
            'id' => $tagId,
            'name' => '新しいタグ',
        ]);
    }

    /**
     * @test
     */
    public function store_タグが存在する場合は新規登録されない()
    {
        $tagRepository = new TagRepository();
        $tagId = $tagRepository->store($this->tag->name, $this->video->id);
        $this->assertEquals($this->tag->id, $tagId);
    }

    /**
     * @test
     */
    public function clear_動画に紐づくタグが全て削除される()
    {
        $tagRepository = new TagRepository();
        $tagRepository->clear($this->video->id);
        $this->assertDatabaseMissing('tag_video', [
            'tag_id' => $this->tag->id,
            'video_id' => $this->video->id,
        ]);
    }

}