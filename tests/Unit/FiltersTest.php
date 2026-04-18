<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Services\Filters\KeywordFilter;

it('passes when a keyword is present in bio (case-insensitive)', function () {
    $filter = new KeywordFilter(['trekking', 'hiking']);

    expect($filter->passes(['biography' => 'Amo il TREKKING in montagna', 'username' => 'foo']))->toBeTrue();
});

it('passes when a keyword is present in username', function () {
    $filter = new KeywordFilter(['guide']);

    expect($filter->passes(['biography' => '', 'username' => 'mountain_guide']))->toBeTrue();
});

it('fails when no keyword matches', function () {
    $filter = new KeywordFilter(['trekking']);

    expect($filter->passes(['biography' => 'photographer', 'username' => 'pippo']))->toBeFalse();
});

it('passes everything when keyword list is empty', function () {
    $filter = new KeywordFilter([]);

    expect($filter->passes(['biography' => 'anything', 'username' => 'any']))->toBeTrue();
});
