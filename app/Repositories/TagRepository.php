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
        //すでに割り当て済みの場合は何もしない
        if ($tag->videos()->where('video_id', $videoId)->exists() === false) {
            $tag->videos()->attach($videoId);
        }
        return $tag->id;
    }

    public function clear(int $videoId): void
    {
        //指定された動画に紐づくタグを全て削除
        Tag::whereHas('videos', function ($query) use ($videoId) {
            $query->where('video_id', $videoId);
        })->get()->each(function ($tag) use ($videoId) {
            $tag->videos()->detach($videoId);
        });
    }

    private function findByName(string $tagName): ?Tag
    {
        return Tag::where('name', $tagName)->first();
    }

}
