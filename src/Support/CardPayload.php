<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Support;

final class CardPayload
{
    /**
     * @param  array<int, array{text: string, callback_data: string}>  $buttons
     */
    public function __construct(
        public readonly string $caption,
        public readonly ?string $photoUrl,
        public readonly array $buttons,
    ) {}
}
