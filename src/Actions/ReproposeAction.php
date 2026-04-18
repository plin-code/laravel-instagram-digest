<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Actions;

use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Models\Profile;

class ReproposeAction implements DigestAction
{
    public function key(): string
    {
        return 'repropose';
    }

    public function label(): string
    {
        return (string) __('instagram-digest::actions.repropose');
    }

    public function handle(Profile $profile): void
    {
        $profile->update([
            'status' => 'pending',
            'telegram_message_id' => null,
            'reviewed_at' => now(),
        ]);
    }
}
