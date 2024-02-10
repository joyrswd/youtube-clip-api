<?php

namespace App\Repositories;

use Google\Service\YouTube;
use Google\Service\YouTube\Resource\Search;
use Google\Service\YouTube\SearchListResponse;
use Google\Service\YouTube\SearchResult;
use Google\Service\YouTube\ResourceId;
use Illuminate\Support\Facades\Log;

class YoutubeSearchRepository
{
    private Search $search;

    private $params = [
        'channelId' => '',
        'order' => 'date',
        'type' => 'video',
        'maxResults' => 50,
    ];

    public function __construct(YouTube $youtube)
    {
        $this->search = $youtube->search;
    }

    public function listSearch(string $id, ?array $conditions = [], ?string $token = null) : ?SearchListResponse
    {
        $this->params['channelId'] = $id;
        foreach ($conditions as $key => $value) {
            $this->params[$key] = $value;
        }
        if ($token) {
            $this->params['pageToken'] = $token;
        }
        try {
            return $this->search->listSearch('id', $this->params);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    public function getPageToken(SearchListResponse $response) : ?string
    {
        return $response->getNextPageToken();
    }

    public function getItems(SearchListResponse $response) : array
    {
        return $response->getItems();
    }

    public function getId(SearchResult $item) : ResourceId
    {
        return $item->getId();
    }

    public function getVideoId(ResourceId $resource) : string
    {
        return $resource->getVideoId();
    }

}
