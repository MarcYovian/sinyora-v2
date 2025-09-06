<?php

namespace App\View\Components;

use App\Services\SEOService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeoMetaTags extends Component
{
    public SEOService $seo;
    /**
     * Create a new component instance.
     */
    public function __construct(SEOService $seo)
    {
        $this->seo = $seo;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.seo-meta-tags');
    }
}
