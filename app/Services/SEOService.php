<?php

namespace App\Services;

class SEOService
{
    public string $title = 'Default Website Title';
    public string $description = 'Default website description...';
    public array $keywords = ['keyword1', 'keyword2'];
    public ?string $ogImage = null; // URL to Open Graph image
    public string $canonical;
    public string $author = 'Kapel St. Yohanes Rasul';
    public ?array $schema = null;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->canonical = url()->current();
    }

    public function setTitle(string $title, bool $appendDefault = true): self
    {
        $this->title = $appendDefault ? $title . ' | ' . config('app.name') : $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setKeywords(array|string $keywords): self
    {
        $this->keywords = is_array($keywords) ? $keywords : explode(', ', $keywords);
        return $this;
    }

    public function setOgImage(string $url): self
    {
        $this->ogImage = $url;
        return $this;
    }

    public function setCanonical(string $url): self
    {
        $this->canonical = $url;
        return $this;
    }

    public function setSchema(array $schema): self
    {
        $this->schema = $schema;
        return $this;
    }
}
