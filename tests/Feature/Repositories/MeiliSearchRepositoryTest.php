<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\MeilisSearchRepository;
use App\Models\Channel;
use App\Models\Video;


class MeiliSearchRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private Channel $channel;
    private Video $video;

    public function setUp(): void
    {
        parent::setUp();
        $this->channel = Channel::factory()->create();
        $this->video = Video::factory()->create([
            'channel_id' => $this->channel->id,
        ]);
    }

    /**
     * @test
     */
    public function storeIndex_インデックス名の取得()
    {
        $meilisSearchRepository = new MeilisSearchRepository(Video::class);
        $indexName = $meilisSearchRepository->getIndexName();
        $tableName = $this->video->getTable();
        $prefix = config('scout.prefix');
        $this->assertEquals($prefix . $tableName, $indexName);
    }

    /**
     * @test
     */
    public function storeIndex_インデックスの情報取得()
    {
        $meilisSearchRepository = new MeilisSearchRepository(Video::class);
        $indexInfo = $meilisSearchRepository->getIndexInfo();
        $this->assertArrayHasKey('isIndexing', $indexInfo);
        
    }


    /**
     * @test
     */
    public function storeIndex_インデックスの中身を空にする()
    {
        $meilisSearchRepository = new MeilisSearchRepository(Video::class);
        $meilisSearchRepository->truncate();
        sleep(1);//反映に時間がかかるため
        $indexInfo = $meilisSearchRepository->getIndexInfo();
        $this->assertEquals(0, $indexInfo['numberOfDocuments']);
    }

    /**
     * @test
     */
    public function storeIndex_インデックスの削除()
    {
        $meilisSearchRepository = new MeilisSearchRepository(Video::class);
        $meilisSearchRepository->deleteIndex();
        sleep(1);//反映に時間がかかるため
        $indexInfo = $meilisSearchRepository->getIndexInfo();
        $this->assertNull($indexInfo);
    }


}