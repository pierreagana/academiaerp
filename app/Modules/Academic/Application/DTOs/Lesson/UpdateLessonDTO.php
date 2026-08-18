<?php

namespace App\Modules\Academic\Application\DTOs\Lesson;

class UpdateLessonDTO
{
    public $title;
    public $lesson_titles;
    public $content;
    public $file_path;
    public $video_url;
    public $order;
    public $status;

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? null;
        $this->lesson_titles = $data['lesson_titles'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->file_path = $data['file_path'] ?? null;
        $this->video_url = $data['video_url'] ?? null;
        $this->order = $data['order'] ?? null;
        $this->status = $data['status'] ?? null;
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->title !== null) $data['title'] = $this->title;
        if ($this->lesson_titles !== null) $data['lesson_titles'] = $this->lesson_titles;
        if ($this->content !== null) $data['content'] = $this->content;
        if ($this->file_path !== null) $data['file_path'] = $this->file_path;
        if ($this->video_url !== null) $data['video_url'] = $this->video_url;
        if ($this->order !== null) $data['order'] = $this->order;
        if ($this->status !== null) $data['status'] = $this->status;
        return $data;
    }
}
