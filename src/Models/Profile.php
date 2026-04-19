<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $instagram_username
 * @property string|null $instagram_id
 * @property string|null $full_name
 * @property string|null $biography
 * @property string|null $profile_pic_url
 * @property int $followers_count
 * @property int $following_count
 * @property bool $is_verified
 * @property string $status
 * @property int|null $telegram_message_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
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
