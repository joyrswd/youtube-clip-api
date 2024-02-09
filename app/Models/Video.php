<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Video extends Model
{
    use HasFactory, SoftDeletes, Searchable;


    protected $fillable = [
        'title',
        'description',
        'youtube_id',
        'channel_id',
        'duration',
        'published_at',
        'etag',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'url' => 'https://www.youtube.com/watch?v=' . $this->youtube_id,
            'thumbnail' => 'https://i.ytimg.com/vi/' . $this->youtube_id . '/default.jpg',
            'title' => $this->title,
            'description' => $this->sanitizeText($this->description),
            'channel' => $this->channel->title,
            'tags' => $this->tags->pluck('name'),
            'duration' => $this->duration,
            'time'  => $this->convertToTime($this->duration),
            'published_at' => $this->published_at->format('Y-m-d H:i:s'),
            'timesatmp' => $this->published_at->getTimeStamp(),
        ];
    }

    private function sanitizeText(string $description) : string
    {
        //URLを削除
        $noUrl = preg_replace('/[^\n]*(?:https?|ftp):\/\/\S+/', '', $description);
        //メールアドレスを削除
        $noMail = preg_replace('/[^\n]*\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', '', $noUrl);
        return $noMail;
    }

    private function convertToTime($duration)
    {
        $start = new \DateTime('@0');
        $start->modify("+ {$duration} seconds");
        return ($duration >= 3600) ? $start->format('H:i:s') : $start->format('i:s');
    }
}
