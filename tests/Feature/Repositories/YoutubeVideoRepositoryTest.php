<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Repositories\YoutubeVideoRepository;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\Resource\Videos;
use Google\Service\YouTube\VideoListResponse;
use Google\Service\YouTube;
use Mockery;
use Mockery\Mock;

class YoutubeVideoRepositoryTest extends TestCase
{
    private Mock|YouTube $youtube;

    public function setUp(): void
    {
        parent::setUp();
        $youtube = Mockery::mock(YouTube::class);
        $youtube->videos = Mockery::mock(Videos::class);
        $this->youtube = $youtube;
    }

    /**
     * @test
     */
    public function listVideos()
    {
        $this->youtube->videos->shouldReceive('listVideos')
            ->with(['snippet', 'contentDetails'], ['id' => 'someYoutubeId'])
            ->andReturn(Mockery::mock(VideoListResponse::class));
        $video = new YoutubeVideoRepository($this->youtube);
        $result = $video->listVideos(['someYoutubeId']);
        $this->assertInstanceOf(VideoListResponse::class, $result);
    }

    /**
     * @test
     */
    public function getItems()
    {
        $videoListResponse = Mockery::mock(VideoListResponse::class);
        $videoListResponse->shouldReceive('getItems')
            ->andReturn(['mocked items']);
        $video = new YoutubeVideoRepository($this->youtube);
        $result = $video->getItems($videoListResponse);
        $this->assertEquals(['mocked items'], $result);
    }

    /**
     * @test
     */
    public function convertToVideoRecord()
    {
        $video = Mockery::mock(Video::class);
        $video->shouldReceive('toSimpleObject')
            ->andReturn((object) [
                'id' => 'someYoutubeId',
                'etag' => 'etag',
                'snippet' => (object) [
                    'title' => 'title',
                    'description' => 'description',
                    'publishedAt' => '2021-01-01T00:00:00Z',
                    'tags' => ['tag1', 'tag2'],
                ],
                'contentDetails' => (object) [
                    'duration' => 'PT1H1M1S',
                ],
            ]);
        $youtube = new YoutubeVideoRepository($this->youtube);
        $result = $youtube->convertToVideoRecord($video, 1);
        $this->assertEquals([
            'channel_id' => 1,
            'youtube_id' => 'someYoutubeId',
            'etag' => 'etag',
            'title' => 'title',
            'description' => 'description',
            'duration' => 'PT1H1M1S',
            'published_at' => new \Datetime('2021-01-01T00:00:00Z'),
            'tags' => ['tag1', 'tag2'],
        ], $result);
    }

}