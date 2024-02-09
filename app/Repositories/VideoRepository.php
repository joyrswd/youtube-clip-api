<?php

namespace App\Repositories;

use App\Models\Video;

class VideoRepository
{
    public function store(array $data): int
    {
        $video = new Video();
        $video->channel_id = $data['channel_id'];
        $video->youtube_id = $data['youtube_id'];
        $video->etag = $data['etag'];
        $video->title = $data['title'];
        $video->description = $data['description'];
        $video->duration = $this->convertToTime($data['duration']);
        $video->published_at = $data['published_at'];
        $video->save();
        return $video->id;
    }

    public function updateByYoutubeId(string $youtbeId, array $data): int
    {
        $video = $this->getByYoutubeId($youtbeId);
        $video->channel_id = $data['channel_id'];
        $video->etag = $data['etag'];
        $video->title = $data['title'];
        $video->description = $data['description'];
        $video->duration = $this->convertToTime($data['duration']);
        $video->published_at = $data['published_at'];
        $video->save();
        return $video->id;
    }

    private function convertToTime(string $iso8601): int
    {
        $interval = new \DateInterval($iso8601);
        return ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
    }

    public function findByYoutubeId(string $youtubeId):array
    {
        $record = $this->getByYoutubeId($youtubeId);
        return empty($record) ? [] : $record->toArray();
    }

    private function getByYoutubeId(string $youtubeId): ?Video
    {
        return Video::where('youtube_id', $youtubeId)->first();
    }
}
