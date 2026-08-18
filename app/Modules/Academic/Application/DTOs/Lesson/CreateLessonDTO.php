<?php

namespace App\Modules\Academic\Application\DTOs\Lesson;

class CreateLessonDTO
{
    public $syllabus_id;
    public $title;
    public $lesson_titles;
    public $content;
    public $file_path;
    public $video_url;
    public $order;
    public $status;

    public function __construct(array $data)
    {
        $this->syllabus_id = $data['syllabus_id'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->lesson_titles = $data['lesson_titles'] ?? [];
        $this->content = $data['content'] ?? null;
        $this->file_path = $data['file_path'] ?? null;
        $this->video_url = $data['video_url'] ?? null;
        $this->order = $data['order'] ?? 1;
        $this->status = $data['status'] ?? 'published';
    }

    public function toArray(): array
    {
        return [
            'syllabus_id' => $this->syllabus_id,
            'title' => $this->title,
            'lesson_titles' => $this->lesson_titles,
            'content' => $this->content,
            'file_path' => $this->file_path,
            'video_url' => $this->video_url,
            'order' => $this->order,
            'status' => $this->status,
        ];
    }
}
