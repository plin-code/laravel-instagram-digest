<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Actions;

use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Models\Profile;

class MarkAsRejected implements DigestAction
{
    public function key(): string
    {
        return 'rejected';
    }

    public function label(): string
    {
        return (string) __('instagram-digest::actions.rejected');
    }

    public function handle(Profile $profile): void
    {
        $profile->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
        ]);
    }
}
