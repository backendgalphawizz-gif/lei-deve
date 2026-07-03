@php
    $meta = $record ?? $registry->recordMeta($application);
@endphp
<div class="lei-reg-action-banner lei-reg-action-banner--{{ $meta['status_tone'] }}">
    @if ($meta['status'] === 'expired')
        <div class="lei-reg-action-banner-text">
            <strong>LEI validity has ended</strong>
            <span>This LEI expired on {{ $application->expiry_date?->format('F j, Y') }}. Renew now to restore your active registry status.</span>
        </div>
        <a href="{{ $meta['renew_url'] }}" class="lei-pub-btn gold">Renew LEI</a>
    @elseif ($meta['status'] === 'expiring')
        <div class="lei-reg-action-banner-text">
            <strong>Renewal window is open</strong>
            <span>Valid until {{ $application->expiry_date?->format('F j, Y') }}. Renew early to avoid lapse.</span>
        </div>
        <a href="{{ $meta['renew_url'] }}" class="lei-pub-btn gold">Renew LEI</a>
    @else
        <div class="lei-reg-action-banner-text">
            <strong>LEI is active</strong>
            <span>Published in our registry until {{ $application->expiry_date?->format('F j, Y') ?? 'further notice' }}.</span>
        </div>
        <a href="{{ route('register') }}" class="lei-pub-btn outline">Register Another Entity</a>
    @endif
</div>
