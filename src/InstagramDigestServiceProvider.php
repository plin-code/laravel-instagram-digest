<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InstagramDigestServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-instagram-digest')
            ->hasConfigFile('instagram-digest')
            ->hasTranslations()
            ->hasMigration('create_instagram_digest_profiles_table')
            ->hasMigration('create_instagram_digest_runs_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(InstagramDigest::class, fn () => new InstagramDigest);
    }
}
