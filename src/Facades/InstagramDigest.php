<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string version()
 * @method static \PlinCode\InstagramDigest\InstagramDigest hashtagsUsing(\Closure $resolver)
 * @method static \PlinCode\InstagramDigest\InstagramDigest keywordsUsing(\Closure $resolver)
 * @method static \PlinCode\InstagramDigest\InstagramDigest minFollowersUsing(\Closure $resolver)
 * @method static \PlinCode\InstagramDigest\InstagramDigest chatIdUsing(\Closure $resolver)
 * @method static \PlinCode\InstagramDigest\InstagramDigest dailyCountUsing(\Closure $resolver)
 * @method static array<string> resolveHashtags()
 * @method static array<string> resolveKeywords()
 * @method static int resolveMinFollowers()
 * @method static string resolveChatId()
 * @method static int resolveDailyCount()
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
