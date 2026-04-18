<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Contracts;

use PlinCode\InstagramDigest\Models\Profile;

interface DigestAction
{
    public function key(): string;

    public function label(): string;

    public function handle(Profile $profile): void;
}
