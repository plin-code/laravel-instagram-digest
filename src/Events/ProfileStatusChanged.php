<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PlinCode\InstagramDigest\Models\Profile;

class ProfileStatusChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Profile $profile,
        public readonly string $from,
        public readonly string $to,
    ) {}
}
