<?php

namespace App\Services;

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;

class YoutubeService
{

    private string $apiKey;
    private YouTube $api;
    private array $listParts = ['snippet', 'contentDetails'];

    public function __construct()
    {
        $this->apiKey = env('YOUTUBE_API_KEY');
        $this->api = $this->connectYoutubeAPI();
    }

    private function connectYoutubeAPI(): YouTube
    {
        //YoutubeAPIに接続する処理
        $client = new Client();
        $client->setApplicationName("Youtube Crawler");
        $client->setDeveloperKey($this->apiKey);
        return new YouTube($client);
    }

    public function getChannelTitleById(string $youtubeId): ?string
    {
        //YoutubeAPIからチャンネル情報を取得する処理
        $response = $this->api->channels->listChannels('snippet, contentDetails', ['id' => $youtubeId]);
        $items = $response->getItems();
        if (empty($items)) {
            return null;
        }
        $channel = $items[0];
        return $channel->getSnippet()->getTitle();
    }

    public function findVideoIds(string $id, ?string $token = null) : array
    {
        $items = [];
        $params = [
            'channelId' => $id,
            'order' => 'date',
            'type' => 'video',
            'maxResults' => 50,
        ];
        if ($token) {
            $params['pageToken'] = $token;
        }
        $response = $this->api->search->listSearch('id', $params);
        $token = $response->getNextPageToken();
        foreach ($response->getItems() as $item) {
            $items[] = $item->getId()->getVideoId();
        }
        return [$items, $token];
    }

    public function findVideInfoByIds(array $ids): array
    {
        $params = [
            'id' => implode(',', $ids),
        ];
        $response = $this->api->videos->listVideos($this->listParts, $params);
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