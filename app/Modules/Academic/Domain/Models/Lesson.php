<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use SoftDeletes;
    
    protected $table = 'lessons';
    
    protected $fillable = [
        'syllabus_id',
        'title',
        'lesson_titles',
        'content',
        'file_path',
        'video_url',
        'order',
        'status',
        'progress_status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'lesson_titles' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    /**
     * Returns structured array of sub-lessons, ensuring backward compatibility with plain strings.
     */
    public function getSubLessonsAttribute(): array
    {
        $raw = $this->lesson_titles;
        if (!is_array($raw)) {
            return [];
        }

        $list = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $list[] = [
                    'title' => $item['title'] ?? '',
                    'status' => $item['status'] ?? 'not_started',
                    'started_at' => isset($item['started_at']) ? $item['started_at'] : null,
                    'completed_at' => isset($item['completed_at']) ? $item['completed_at'] : null,
                ];
            } elseif (is_string($item) && trim($item) !== '') {
                $list[] = [
                    'title' => trim($item),
                    'status' => 'not_started',
                    'started_at' => null,
                    'completed_at' => null,
                ];
            }
        }

        return $list;
    }

    /**
     * Calculates completion percentage of sub-lessons.
     */
    public function getProgressPercentageAttribute(): int
    {
        $subLessons = $this->sub_lessons;
        if (empty($subLessons)) {
            return $this->progress_status === 'completed' ? 100 : ($this->progress_status === 'in_progress' ? 50 : 0);
        }

        $completed = count(array_filter($subLessons, fn ($l) => ($l['status'] ?? '') === 'completed'));
        return (int) round(($completed / count($subLessons)) * 100);
    }

    /**
     * Recalculates chapter progress status and dates based on its sub-lessons.
     */
    public function recalculateChapterProgress(): void
    {
        $subLessons = $this->sub_lessons;
        if (empty($subLessons)) {
            return;
        }

        $total = count($subLessons);
        $completedCount = 0;
        $inProgressCount = 0;
        $earliestStart = null;
        $latestEnd = null;

        foreach ($subLessons as $sub) {
            $status = $sub['status'] ?? 'not_started';
            if ($status === 'completed') {
                $completedCount++;
            } elseif ($status === 'in_progress') {
                $inProgressCount++;
            }

            if (!empty($sub['started_at'])) {
                if (!$earliestStart || $sub['started_at'] < $earliestStart) {
                    $earliestStart = $sub['started_at'];
                }
            }
            if (!empty($sub['completed_at'])) {
                if (!$latestEnd || $sub['completed_at'] > $latestEnd) {
                    $latestEnd = $sub['completed_at'];
                }
            }
        }

        if ($completedCount === $total && $total > 0) {
            $this->progress_status = 'completed';
            $this->started_at = $earliestStart ? \Carbon\Carbon::parse($earliestStart) : ($this->started_at ?: now());
            $this->completed_at = $latestEnd ? \Carbon\Carbon::parse($latestEnd) : now();
        } elseif ($completedCount > 0 || $inProgressCount > 0) {
            $this->progress_status = 'in_progress';
            $this->started_at = $earliestStart ? \Carbon\Carbon::parse($earliestStart) : ($this->started_at ?: now());
            $this->completed_at = null;
        } else {
            $this->progress_status = 'not_started';
            $this->started_at = null;
            $this->completed_at = null;
        }

        $this->save();
    }
}
