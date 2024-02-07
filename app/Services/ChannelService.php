<?php

namespace App\Services;

use App\Repositories\ChannelRepository;

class ChannelService
{

    private ChannelRepository $channelRepository;

    public function __construct(ChannelRepository $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function create(string $youtubeId, string $title): int
    {
        $channelId = $this->channelRepository->store([
            'youtube_id' => $youtubeId,
            'title' => $title,
        ]);
        return $channelId;
    }

    public function findByYoutubeId(string $youtubeId): array
    {
        return $this->channelRepository->findByYoutubeId($youtubeId);
    }
}
