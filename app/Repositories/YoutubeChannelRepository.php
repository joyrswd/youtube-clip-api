<?php

namespace App\Repositories;

use Google\Service\YouTube;
use Google\Service\YouTube\Resource\Channels;
use Google\Service\YouTube\ChannelListResponse;
use Google\Service\YouTube\ChannelSnippet;

class YoutubeChannelRepository
{
    private Channels $channels;

    public function __construct(YouTube $youtube)
    {
        $this->channels = $youtube->channels;
    }

    public function getChannelById(string $youtubeId): ChannelListResponse
    {
        return $this->channels->listChannels('snippet, contentDetails', ['id' => $youtubeId]);
    }

    public function getItems(ChannelListResponse $channels): array
    {
        return $channels->getItems();
    }

    public function getSnippet(array $channels): ChannelSnippet
    {
        $channel = $channels[0];
        return $channel->getSnippet();
    }

    public function getSnippetTitle(ChannelSnippet $snippet): string
    {
        return $snippet->getTitle();
    }
}
