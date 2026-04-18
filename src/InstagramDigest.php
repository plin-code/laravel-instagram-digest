<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest;

use Closure;
use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\ActionRegistry;

class InstagramDigest
{
    private ?Closure $hashtagsResolver = null;

    private ?Closure $keywordsResolver = null;

    private ?Closure $minFollowersResolver = null;

    private ?Closure $chatIdResolver = null;

    private ?Closure $dailyCountResolver = null;

    public function __construct(private readonly ActionRegistry $registry) {}

    public function version(): string
    {
        return '1.0.0';
    }

    public function registry(): ActionRegistry
    {
        return $this->registry;
    }

    public function registerAction(string $key, string $label, Closure $handler): self
    {
        $this->registry->register(new class($key, $label, $handler) implements DigestAction
        {
            public function __construct(
                private readonly string $key,
                private readonly string $label,
                private readonly Closure $handler,
            ) {}

            public function key(): string
            {
                return $this->key;
            }

            public function label(): string
            {
                return $this->label;
            }

            public function handle(Profile $p): void
            {
                ($this->handler)($p);
            }
        });

        return $this;
    }

    /**
     * @param  array<DigestAction>  $actions
     */
    public function defaultActions(array $actions): self
    {
        $this->registry->replaceAll($actions);

        return $this;
    }

    public function hashtagsUsing(Closure $resolver): self
    {
        $this->hashtagsResolver = $resolver;

        return $this;
    }

    public function keywordsUsing(Closure $resolver): self
    {
        $this->keywordsResolver = $resolver;

        return $this;
    }

    public function minFollowersUsing(Closure $resolver): self
    {
        $this->minFollowersResolver = $resolver;

        return $this;
    }

    public function chatIdUsing(Closure $resolver): self
    {
        $this->chatIdResolver = $resolver;

        return $this;
    }

    public function dailyCountUsing(Closure $resolver): self
    {
        $this->dailyCountResolver = $resolver;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function resolveHashtags(): array
    {
        $value = $this->hashtagsResolver
            ? ($this->hashtagsResolver)()
            : config('instagram-digest.hashtags', []);

        return $this->toStringList($value);
    }

    /**
     * @return array<string>
     */
    public function resolveKeywords(): array
    {
        $value = $this->keywordsResolver
            ? ($this->keywordsResolver)()
            : config('instagram-digest.keywords', []);

        return $this->toStringList($value);
    }

    public function resolveMinFollowers(): int
    {
        return (int) ($this->minFollowersResolver
            ? ($this->minFollowersResolver)()
            : config('instagram-digest.min_followers', 0));
    }

    public function resolveChatId(): string
    {
        return (string) ($this->chatIdResolver
            ? ($this->chatIdResolver)()
            : config('instagram-digest.telegram.chat_id'));
    }

    public function resolveDailyCount(): int
    {
        return (int) ($this->dailyCountResolver
            ? ($this->dailyCountResolver)()
            : config('instagram-digest.digest.daily_count', 20));
    }

    /**
     * @return array<string>
     */
    private function toStringList(mixed $value): array
    {
        if (is_iterable($value)) {
            $value = is_array($value) ? $value : iterator_to_array($value);
        } else {
            $value = (array) $value;
        }

        return array_values(array_map('strval', $value));
    }
}
