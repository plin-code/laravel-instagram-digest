<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'hashtags_scanned' => 'array',
        'profiles_found' => 'integer',
        'profiles_added' => 'integer',
        'profiles_updated' => 'integer',
    ];

    protected $attributes = [
        'profiles_found' => 0,
        'profiles_added' => 0,
        'profiles_updated' => 0,
    ];

    public function getTable(): string
    {
        return (string) config('instagram-digest.tables.runs', 'instagram_digest_runs');
    }
}
