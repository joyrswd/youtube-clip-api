<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Repositories\YoutubeSearchRepository;
use Google\Service\YouTube\Resource\Search;
use Google\Service\YouTube\SearchListResponse;
use Google\Service\YouTube\SearchResult;
use Google\Service\YouTube\ResourceId;
use Google\Service\YouTube;
use Mockery;
use Mockery\Mock;

class YoutubeSearchRepositoryTest extends TestCase
{
    private Mock|YouTube $youtube;

    public function setUp(): void
    {
        parent::setUp();
        $youtube = Mockery::mock(YouTube::class);
        $youtube->search = Mockery::mock(Search::class);
        $this->youtube = $youtube;
    }

    /**
     * @test
     */
    public function listSearch()
    {
        $this->youtube->search->shouldReceive('listSearch')
            ->with('id', [
                'channelId' => 'someChannelId',
                'order' => 'date',
                'type' => 'video',
                'maxResults' => 50,
            ])
            ->andReturn(Mockery::mock(SearchListResponse::class));
        $search = new YoutubeSearchRepository($this->youtube);
        $result = $search->listSearch('someChannelId');
        $this->assertInstanceOf(SearchListResponse::class, $result);
    }

    /**
     * @test
     */
    public function getPageToken()
    {
        $searchListResponse = Mockery::mock(SearchListResponse::class);
        $searchListResponse->shouldReceive('getNextPageToken')->andReturn('token');
        $search = new YoutubeSearchRepository($this->youtube);
        $result = $search->getPageToken($searchListResponse);
        $this->assertEquals('token', $result);
    }

    /**
     * @test
     */
    public function getItems()
    {
        $searchListResponse = Mockery::mock(SearchListResponse::class);
        $searchListResponse->shouldReceive('getItems')->andReturn(['mocked items']);
        $search = new YoutubeSearchRepository($this->youtube);
        $result = $search->getItems($searchListResponse);
        $this->assertEquals(['mocked items'], $result);
    }

    /**
     * @test
     */
    public function getId()
    {
        $searchResult = Mockery::mock(SearchResult::class);
        $searchResult->shouldReceive('getId')->andReturn(Mockery::mock(ResourceId::class));
        $search = new YoutubeSearchRepository($this->youtube);
        $result = $search->getId($searchResult);
        $this->assertInstanceOf(ResourceId::class, $result);
    }

    /**
     * @test
     */
    public function getVideoId()
    {
        $resourceId = Mockery::mock(ResourceId::class);
        $resourceId->shouldReceive('getVideoId')->andReturn('mocked video id');
        $search = new YoutubeSearchRepository($this->youtube);
        $result = $search->getVideoId($resourceId);
        $this->assertEquals('mocked video id', $result);
    }
}