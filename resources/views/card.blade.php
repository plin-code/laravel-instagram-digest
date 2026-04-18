👤 {{ $profile->full_name ?: $profile->instagram_username }}
@if ($profile->is_verified)✓ @endif
@if ($profile->followers_count)
👥 {{ number_format($profile->followers_count) }} {{ __('instagram-digest::card.followers') }}
@endif
@if ($profile->biography)

📝 "{{ \Illuminate\Support\Str::limit($profile->biography, 120) }}"
@endif

🔗 instagram.com/{{ $profile->instagram_username }}
@if ($profile->source_hashtag)

🏷 #{{ $profile->source_hashtag }}
@endif
