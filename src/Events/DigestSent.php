<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DigestSent
{
    use Dispatchable;

    /**
     * @param  array<string>  $profileIds
     */
    public function __construct(public readonly array $profileIds) {}
}
