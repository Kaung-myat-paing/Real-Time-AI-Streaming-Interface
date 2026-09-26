<?php

namespace App\Services;

use Closure;

interface AiStreamServiceContract
{
    public function stream(string $prompt, Closure $onEvent, Closure $onError): void;
}
