<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tag;
use App\Models\Video;
use App\Models\Channel;
use App\Repositories\MeilisSearchRepository;

//use Laravel\Scout\ScoutServiceProvider;

class TagModelTest extends TestCase
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
        $tag = Tag::factory()->create();

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    /**
     * @test
     */
    public function 更新()
    {
        $tag = Tag::factory()->create();

        $updatedTagData = [
            'name' => 'Updated Tag Name',
        ];

        $tag->update($updatedTagData);

        $this->assertEquals($updatedTagData['name'], $tag->name);
    }

    /**
     * @test
     */
    public function 削除()
    {
        $tag = Tag::factory()->create();

        $tag->forceDelete();

        $this->assertModelMissing($tag);
    }

    /**
     * @test
     */
    public function リレーション()
    {
        $tag = Tag::factory()->create();
        $channel = Channel::factory()->create();
        $video1 = Video::factory()->create(['channel_id' => $channel->id]);
        $video2 = Video::factory()->create(['channel_id' => $channel->id]);

        $tag->videos()->attach([$video1->id, $video2->id]);

        $this->assertInstanceOf(Video::class, $tag->videos->first());
        $this->assertCount(2, $tag->videos);
    }

    /**
     * @test
     */
    public function タイムスタンプ()
    {
        $tag = Tag::factory()->create();

        $this->assertNotNull($tag->created_at);
        $this->assertNotNull($tag->updated_at);
    }
    

}