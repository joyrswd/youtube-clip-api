<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\VideoRepository;
use App\Models\Channel;
use App\Models\Video;
use App\Repositories\MeilisSearchRepository;


class VideoRepositoryTest extends TestCase
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

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function store_新しい動画を登録する()
    {
        $videoRepository = new VideoRepository();
        $videoId = $videoRepository->store([
            'youtube_id' => 'new_youtube_id',
            'channel_id' => $this->channel->id,
            'etag' => 'new_etag',
            'title' => 'new_title',
            'description' => 'new_description',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'youtube_id' => 'new_youtube_id',
            'channel_id' => $this->channel->id,
            'etag' => 'new_etag',
            'title' => 'new_title',
            'description' => 'new_description',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
    }

    /**
     * @test
     */
    public function convertToTime_ISO8601形式の文字列を秒数に変換する()
    {
        $class = new \ReflectionClass(VideoRepository::class);
        $method = $class->getMethod('convertToTime');
        $method->setAccessible(true);
        $result = $method->invoke(new VideoRepository(), 'PT1H1M1S');
        $this->assertEquals(3661, $result);
    }

    /**
     * @test
     */
    public function convertToTime_ISO8601形式の文字列を秒数に変換する_日付が含まれる場合()
    {
        $class = new \ReflectionClass(VideoRepository::class);
        $method = $class->getMethod('convertToTime');
        $method->setAccessible(true);
        $result = $method->invoke(new VideoRepository(), 'P1DT1H1M1S');
        $this->assertEquals(90061, $result);
    }

    /**
     * @test
     */
    public function convertToTime_ISO8601形式の文字列を秒数に変換する_時間が含まれない場合()
    {
        $class = new \ReflectionClass(VideoRepository::class);
        $method = $class->getMethod('convertToTime');
        $method->setAccessible(true);
        $result = $method->invoke(new VideoRepository(), 'P1D');
        $this->assertEquals(86400, $result);
    }
    
    /**
     * @test
     */
    public function findByYoutubeId_YouTubeの動画IDから動画を取得する()
    {
        $videoRepository = new VideoRepository();
        $result = $videoRepository->findByYoutubeId($this->video->youtube_id);
        $this->assertEquals($this->video->id, $result['id']);
    }

    /**
     * @test
     */
    public function findByYoutubeId_YouTubeの動画IDから動画を取得する_存在しない場合()
    {
        $videoRepository = new VideoRepository();
        $result = $videoRepository->findByYoutubeId('not_exist_youtube_id');
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function updateByYoutubeId_動画を更新する()
    {
        $videoRepository = new VideoRepository();
        $videoId = $videoRepository->updateByYoutubeId($this->video->youtube_id, [
            'channel_id' => $this->channel->id,
            'title' => 'updated_title',
            'etag' => 'updated_etag',
            'description' => 'updated_description',
            'duration' => 'PT1H1M1S',
            'published_at' => new \DateTime('2021-01-01 00:00:00'),
        ]);
        $this->assertDatabaseHas('videos', [
            'id' => $videoId,
            'channel_id' => $this->channel->id,
            'youtube_id' => $this->video->youtube_id,
            'etag' => 'updated_etag',
            'title' => 'updated_title',
            'description' => 'updated_description',
            'duration' => 3661,
            'published_at' => '2021-01-01 00:00:00',
        ]);
        $this->assertEquals($this->video->id, $videoId);
    }

}