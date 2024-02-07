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

}