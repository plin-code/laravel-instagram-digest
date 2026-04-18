<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PlinCode\InstagramDigest\Models\Profile;

class ProfileDiscovered
{
    use Dispatchable;

    public function __construct(
        public readonly Profile $profile,
        public readonly bool $isNew,
    ) {}
}
