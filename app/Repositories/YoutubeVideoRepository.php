<?php

namespace App\Repositories;

use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\Resource\Videos;
use Google\Service\YouTube\VideoListResponse;

class YoutubeVideoRepository
{
    private Videos $videos;

    private array $listParts = ['snippet', 'contentDetails'];
    
    public function __construct(YouTube $youtube)
    {
        $this->videos = $youtube->videos;
    }

    public function listVideos(array $ids) : VideoListResponse
    {
        $params = [
            'id' => implode(',', $ids),
        ];
        return $this->videos->listVideos($this->listParts, $params);
    }

    public function getItems(VideoListResponse $response) : array
    {
        return $response->getItems();
    }

    public function convertToVideoRecord(Video $item, int $channelId): array
    {
        $object = $item->toSimpleObject();
        return [
            'channel_id' => $channelId,
            'youtube_id' => $object->id,
            'etag' => $object->etag,
            'title' => $object->snippet->title,
            'description' => $object->snippet->description,
            'duration' => $object->contentDetails->duration,
            'published_at' => new \Datetime($object->snippet->publishedAt),
            'tags' => property_exists($object->snippet, 'tags') ? $object->snippet->tags : [],
        ];
    }
    
}
