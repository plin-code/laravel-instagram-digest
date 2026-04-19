<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PlinCode\InstagramDigest\Models\Profile;

/**
 * Dispatched for every profile observed by the scraper that passes the filters.
 *
 * Fires on BOTH the first sighting (isNew=true, the profile was just created)
 * AND subsequent sightings (isNew=false, the profile already existed; metadata
 * may have been refreshed). Consumers that only care about first-sighting
 * should gate on $isNew === true.
 */
class ProfileDiscovered
{
    use Dispatchable;

    public function __construct(
        public readonly Profile $profile,
        public readonly bool $isNew,
    ) {}
}
