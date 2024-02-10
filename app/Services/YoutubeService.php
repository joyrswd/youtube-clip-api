<?php

namespace App\Services;

use App\Repositories\YoutubeChannelRepository;
use App\Repositories\YoutubeSearchRepository;
use App\Repositories\YoutubeVideoRepository;

class YoutubeService
{

    private YoutubeChannelRepository $channels;
    private YoutubeSearchRepository $search;
    private YoutubeVideoRepository $videos;

    public function __construct(YoutubeChannelRepository $channels, YoutubeSearchRepository $search, YoutubeVideoRepository $video)
    {
        $this->channels = $channels;
        $this->search = $search;
        $this->videos = $video;
    }

    public function getChannelTitleById(string $youtubeId): ?string
    {
        $channel = $this->channels->getChannelById($youtubeId);
        $items = $this->channels->getItems($channel);
        if (empty($items)) {
            return null;
        }
        $snippet = $this->channels->getSnippet($items);
        return $this->channels->getSnippetTitle($snippet);
    }

    public function findVideoIds(string $id, ?array $conditions = [], ?string $token = null) : array
    {
        $ids = [];
        $response = $this->search->listSearch($id, $conditions, $token);
        if (empty($response)) {
            return [$ids, null];
        }
        $items = $this->search->getItems($response);
        foreach ($items as $item) {
            $resource = $this->search->getId($item);
            $ids[] = $this->search->getVideoId($resource);
        }
        $token = $this->search->getPageToken($response);
        return [$ids, $token];
    }

    public function findVideoInfoByIds(array $ids, string $channelId): array
    {
        $videos = [];
        $response = $this->videos->listVideos($ids);
        $items = $this->videos->getItems($response);
        foreach ($items as $item) {
            $videos[] = $this->videos->convertToVideoRecord($item, $channelId);
        }
        return $videos;
    }

}