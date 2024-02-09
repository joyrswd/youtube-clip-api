<?php

namespace App\Services;

use App\Repositories\VideoRepository;

class VideoService
{

    private VideoRepository $videoRepository;

    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepository = $videoRepository;
    }

    public function create($item) : int
    {
        return $this->videoRepository->store($item);
    }

    public function update($item) : int
    {
        return $this->videoRepository->updateByYoutubeId($item['youtube_id'], $item);
    }

    public function upsert($item) : int
    {
        $video = $this->videoRepository->findByYoutubeId($item['youtube_id']);
        if (empty($video)) {
            return $this->create($item);
        } else {
            return $this->update($item);
        }
    }

}