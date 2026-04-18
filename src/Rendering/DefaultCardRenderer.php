<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Rendering;

use Illuminate\Support\Facades\View;
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\CardPayload;

class DefaultCardRenderer implements CardRenderer
{
    /**
     * @param  array<DigestAction>  $actions
     */
    public function render(Profile $profile, array $actions): CardPayload
    {
        $caption = trim(View::make('instagram-digest::card', ['profile' => $profile])->render());

        $buttons = array_map(
            fn (DigestAction $a) => [
                'text' => $a->label(),
                'callback_data' => $a->key().':'.$profile->id,
            ],
            $actions,
        );

        return new CardPayload(
            caption: $caption,
            photoUrl: $profile->profile_pic_url,
            buttons: $buttons,
        );
    }
}
