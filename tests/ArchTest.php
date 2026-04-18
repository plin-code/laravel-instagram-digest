<?php

declare(strict_types=1);

arch('strict types are declared in all source files')
    ->expect('PlinCode\InstagramDigest')
    ->toUseStrictTypes();

arch('contracts live under Contracts/')
    ->expect('PlinCode\InstagramDigest\Contracts')
    ->toBeInterfaces();

arch('no debugging statements')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('no facade usage in models')
    ->expect('PlinCode\InstagramDigest\Models')
    ->not->toUse('Illuminate\Support\Facades');
