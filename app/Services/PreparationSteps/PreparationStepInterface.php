<?php

namespace App\Services\PreparationSteps;

use Closure;

interface PreparationStepInterface
{
    public function handle(array $data, Closure $next): array;
}
