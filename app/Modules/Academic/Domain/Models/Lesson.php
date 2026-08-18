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
        'status'
    ];

    protected $casts = [
        'lesson_titles' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
