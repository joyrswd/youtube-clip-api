<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title'];

    protected $casts = [
        'new_stocked_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone('Asia/Tokyo')->toIso8601String();
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }
}
