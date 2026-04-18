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

use PlinCode\InstagramDigest\Services\Filters\MinFollowersFilter;

it('MinFollowersFilter passes at threshold', function () {
    $filter = new MinFollowersFilter(5000);

    expect($filter->passes(['followers_count' => 5000]))->toBeTrue()
        ->and($filter->passes(['followers_count' => 4999]))->toBeFalse()
        ->and($filter->passes(['followers_count' => 100000]))->toBeTrue();
});

it('MinFollowersFilter treats missing followers_count as zero', function () {
    $filter = new MinFollowersFilter(1);

    expect($filter->passes([]))->toBeFalse();
});
