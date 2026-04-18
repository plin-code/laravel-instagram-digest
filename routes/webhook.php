<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PlinCode\InstagramDigest\Http\Controllers\WebhookController;

Route::post('{secret?}', [WebhookController::class, 'handle'])
    ->name('instagram-digest.webhook');
