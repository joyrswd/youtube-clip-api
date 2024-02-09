<?php

namespace App\Services;

use App\Repositories\TagRepository;

class TagService
{

    private TagRepository $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function addTags(array $items, int $videoId) : void
    {
        $this->tagRepository->clear($videoId);
        foreach ($items as $tag) {
            $this->tagRepository->store($tag, $videoId);
        }
    }

}