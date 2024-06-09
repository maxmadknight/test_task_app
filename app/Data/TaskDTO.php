<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TaskDTO extends Data
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string|Optional $status,
        public int|Optional $priority,
        public ?int $parent_id
    ) {}
}
