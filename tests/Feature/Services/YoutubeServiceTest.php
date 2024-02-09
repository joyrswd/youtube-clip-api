<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\YoutubeService;
use App\Repositories\YoutubeChannelRepository;
use App\Repositories\YoutubeSearchRepository;
use App\Repositories\YoutubeVideoRepository;
use Mockery;
use Mockery\Mock;

class YoutubeServiceTest extends TestCase
{
    use RefreshDatabase;

    private YoutubeChannelRepository|Mock $channel;
    private YoutubeSearchRepository|Mock $search;
    private YoutubeVideoRepository|Mock $video;

    public function setUp(): void
    {
        parent::setUp();
        $this->channel = Mockery::mock(YoutubeChannelRepository::class);
        $this->search = Mockery::mock(YoutubeSearchRepository::class);
        $this->video = Mockery::mock(YoutubeVideoRepository::class);
    }

    /**
     * @test
     */
    public function getChannelTitleById()
    {
        $this->channel->shouldReceive('getChannelById')
            ->with('someChannelId')
            ->andReturn(Mockery::mock(\Google\Service\YouTube\ChannelListResponse::class));
        $this->channel->shouldReceive('getItems')
            ->andReturn(['mocked items']);
        $this->channel->shouldReceive('getSnippet')
            ->andReturn(Mockery::mock(\Google\Service\YouTube\ChannelSnippet::class));
        $this->channel->shouldReceive('getSnippetTitle')
            ->andReturn('channelTitle');
        $youtube = new YoutubeService($this->channel, $this->search, $this->video);
        $result = $youtube->getChannelTitleById('someChannelId');
        $this->assertEquals('channelTitle', $result);
    }

    /**
     * @test
     */
    public function findVideoIds()
    {
        $this->search->shouldReceive('listSearch')
            ->with('someChannelId', 'token')
            ->andReturn(Mockery::mock(\Google\Service\YouTube\SearchListResponse::class));
        $this->search->shouldReceive('getPageToken')
            ->andReturn('token2');
        $this->search->shouldReceive('getItems')
            ->andReturn([Mockery::mock(\Google\Service\YouTube\SearchResult::class)]);
        $this->search->shouldReceive('getId')
            ->andReturn(Mockery::mock(\Google\Service\YouTube\ResourceId::class));
        $this->search->shouldReceive('getVideoId')
            ->andReturn('someVideoId');
        $youtube = new YoutubeService($this->channel, $this->search, $this->video);
        $result = $youtube->findVideoIds('someChannelId', 'token');
        $this->assertEquals([['someVideoId'], 'token2'], $result);
    }

    /**
     * @test
     */
    public function findVideInfoByIds()
    {
        $this->video->shouldReceive('listVideos')
            ->with(['someVideoId'])
            ->andReturn(Mockery::mock(\Google\Service\YouTube\VideoListResponse::class));
        $this->video->shouldReceive('getItems')
            ->andReturn([Mockery::mock(\Google\Service\YouTube\Video::class)]);
        $this->video->shouldReceive('convertToVideoRecord')
            ->with(Mockery::type(\Google\Service\YouTube\Video::class), 2)
            ->andReturn(['mocked video']);
        $youtube = new YoutubeService($this->channel, $this->search, $this->video);
        $result = $youtube->findVideInfoByIds(['someVideoId'], 2);
        $this->assertEquals([['mocked video']], $result);
    }


}
