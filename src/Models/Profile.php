<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'pending',
        'followers_count' => 0,
        'is_verified' => false,
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'followers_count' => 'integer',
        'following_count' => 'integer',
        'metadata' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('instagram-digest.tables.profiles', 'instagram_digest_profiles');
    }
}
