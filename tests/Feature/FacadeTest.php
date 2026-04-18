<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Facades\InstagramDigest;

it('resolves the facade and reports a version', function () {
    expect(InstagramDigest::version())->toBe('1.0.0');
});
