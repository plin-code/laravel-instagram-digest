<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest;

use PlinCode\InstagramDigest\Actions\MarkAsInteresting;
use PlinCode\InstagramDigest\Actions\MarkAsRejected;
use PlinCode\InstagramDigest\Actions\ReproposeAction;
use PlinCode\InstagramDigest\Commands\ScrapeHashtagsCommand;
use PlinCode\InstagramDigest\Support\ActionRegistry;
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
            ->hasMigration('create_instagram_digest_runs_table')
            ->hasCommand(ScrapeHashtagsCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ActionRegistry::class, function () {
            $registry = new ActionRegistry;
            $registry->register(new MarkAsInteresting);
            $registry->register(new MarkAsRejected);
            $registry->register(new ReproposeAction);

            return $registry;
        });

        $this->app->singleton(InstagramDigest::class, function ($app) {
            return new InstagramDigest($app->make(ActionRegistry::class));
        });
    }
}
