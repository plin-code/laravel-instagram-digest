<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Contracts;

use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\CardPayload;

interface CardRenderer
{
    /**
     * @param  array<DigestAction>  $actions
     */
    public function render(Profile $profile, array $actions): CardPayload;
}
