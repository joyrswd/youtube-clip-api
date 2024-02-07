<?php

namespace App\Repositories;

use App\Models\Tag;

class TagRepository
{
    public function store(string $tagName, int $videoId): int
    {
        $tag = $this->findByName($tagName);
        if (empty($tag)) {
            $tag = new Tag();
            $tag->name = $tagName;
            $tag->save();
        }
        $tag->videos()->attach($videoId);
        return $tag->id;
    }

    private function findByName(string $tagName): ?Tag
    {
        return Tag::where('name', $tagName)->first();
    }

}
