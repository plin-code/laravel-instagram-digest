<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PlinCode\InstagramDigest\Models\Run;

class ScrapingRunCompleted
{
    use Dispatchable;

    public function __construct(public readonly Run $run) {}
}
