<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string version()
 *
 * @see \PlinCode\InstagramDigest\InstagramDigest
 */
class InstagramDigest extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PlinCode\InstagramDigest\InstagramDigest::class;
    }
}
