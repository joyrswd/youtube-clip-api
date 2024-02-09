<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Repositories\YoutubeChannelRepository;
use Google\Service\YouTube\ChannelListResponse;
use Google\Service\YouTube\Channel;
use Google\Service\YouTube\ChannelSnippet;
use Google\Service\YouTube\Resource\Channels;
use Google\Service\YouTube;
use Mockery;
use Mockery\Mock;

class YoutubeChannelRepositoryTest extends TestCase
{
    private Mock|YouTube $youtube;

    public function setUp(): void
    {
        parent::setUp();
        $youtube = Mockery::mock(YouTube::class);
        $youtube->channels = Mockery::mock(Channels::class);
        $this->youtube = $youtube;
    }

    /**
     * @test
     */
    public function getChannelById()
    {
        $this->youtube->channels->shouldReceive('listChannels')
            ->with('snippet, contentDetails', ['id' => 'someYoutubeId'])
            ->andReturn(Mockery::mock(ChannelListResponse::class));
        $youtube = new YoutubeChannelRepository($this->youtube);
        $result = $youtube->getChannelById('someYoutubeId');
        $this->assertInstanceOf(ChannelListResponse::class, $result);
    }

    /**
     * @test
     */
    public function getItems()
    {
        $channelListResponse = Mockery::mock(ChannelListResponse::class);
        $channelListResponse->shouldReceive('getItems')
            ->andReturn(['mocked items']);
        $youtube = new YoutubeChannelRepository($this->youtube);
        $result = $youtube->getItems($channelListResponse);
        $this->assertEquals(['mocked items'], $result);
    }

    /**
     * @test
     */
    public function getSnippet()
    {
        $channel = Mockery::mock(Channel::class);
        $channel->shouldReceive('getSnippet')
            ->andReturn(Mockery::mock(ChannelSnippet::class));
        $channelListResponse = [ $channel ];
        $youtube = new YoutubeChannelRepository($this->youtube);
        $result = $youtube->getSnippet($channelListResponse);
        $this->assertInstanceOf(ChannelSnippet::class, $result);
    }

    /**
     * @test
     */
    public function getSnippetTitle()
    {
        $channelSnippet = Mockery::mock(ChannelSnippet::class);
        $channelSnippet->shouldReceive('getTitle')
            ->andReturn('mocked title');
        $youtube = new YoutubeChannelRepository($this->youtube);
        $result = $youtube->getSnippetTitle($channelSnippet);
        $this->assertEquals('mocked title', $result);
    }

}