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

    public function update(string $youtbeId, string $title): int
    {
        $channelId = $this->channelRepository->updateByYoutubeId($youtbeId, [
            'title' => $title,
        ]);
        return $channelId;
    }

    public function upsert(string $channelId, string $title): int
    {
        $channel = $this->findByYoutubeId($channelId);
        if (empty($channel)) {
            return $this->create($channelId, $title);
        } else {
            return $this->update($channelId, $title);
        }
    }

    public function findByYoutubeId(string $youtubeId): array
    {
        return $this->channelRepository->findByYoutubeId($youtubeId);
    }

    public function getAllChannels(): array
    {
        return $this->channelRepository->getAll();
    }
}
