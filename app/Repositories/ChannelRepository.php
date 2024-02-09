<?php

namespace App\Repositories;

use App\Models\Channel;

class ChannelRepository
{
    public function store(array $data): int
    {
        $channel = new Channel();
        $channel->youtube_id = $data['youtube_id'];
        $channel->title = $data['title'];
        $channel->save();
        return $channel->id;
    }

    public function updateByYoutubeId(string $channelId, array $data): int
    {
        $channel = $this->getByYoutubeId($channelId);
        $channel->title = $data['title'];
        $channel->save();
        return $channel->id;
    }

    public function findByYoutubeId(string $youtubeId):array
    {
        $record = $this->getByYoutubeId($youtubeId);
        return empty($record) ? [] : $record->toArray();
    }

    private function getByYoutubeId(string $youtubeId): ?Channel
    {
        return Channel::where('youtube_id', $youtubeId)->first();
    }

}
