<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case ARCHIVED = 'archived';
    case PUBLISHED = 'published';
    case DRAFT = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::PUBLISHED => 'Published',
            self::DRAFT => 'Draft',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLISHED => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            self::DRAFT => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            self::ARCHIVED => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        };
    }

    public static function values(): array
    {
        return [
            self::PUBLISHED->value,
            self::DRAFT->value,
            self::ARCHIVED->value,
        ];
    }
}
