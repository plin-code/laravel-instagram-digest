<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest;

use Illuminate\Support\Facades\Route;
use PlinCode\InstagramDigest\Actions\MarkAsInteresting;
use PlinCode\InstagramDigest\Actions\MarkAsRejected;
use PlinCode\InstagramDigest\Actions\ReproposeAction;
use PlinCode\InstagramDigest\Commands\ScrapeHashtagsCommand;
use PlinCode\InstagramDigest\Commands\SendDigestCommand;
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Rendering\DefaultCardRenderer;
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
            ->hasViews()
            ->hasMigration('create_instagram_digest_profiles_table')
            ->hasMigration('create_instagram_digest_runs_table')
            ->hasCommand(ScrapeHashtagsCommand::class)
            ->hasCommand(SendDigestCommand::class);
    }

    public function packageBooted(): void
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::group([
            'prefix' => (string) config('instagram-digest.route.prefix', 'instagram-digest').'/webhook',
            'middleware' => (array) config('instagram-digest.route.middleware', ['api']),
        ], fn () => $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php'));
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            CardRenderer::class,
            DefaultCardRenderer::class,
        );

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
