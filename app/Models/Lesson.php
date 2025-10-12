<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Lesson
 *
 * @property int $id
 * @property string $title
 * @property string|null $content
 * @property string|null $video_url
 * @property int $course_id
 * @property-read \App\Models\Course $course
 * @mixin IdeHelperLesson
 */
class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'video_url',
        'course_id',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    protected $with = ['course'];

    protected static function booted(): void
    {
        static::addGlobalScope('orderByNewest', function ($query) {
            $query->latest();
        });
    }
}
