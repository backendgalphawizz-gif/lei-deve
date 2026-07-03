<div class="lei-reg-action-banner lei-reg-action-banner--{{ $record['status_tone'] }}">
    <div class="lei-reg-action-banner-text">
        <strong>GLEIF global record</strong>
        <span>This entity is registered in the GLEIF global LEI index. Renewal is handled by the managing LOU unless you transfer to our registry.</span>
    </div>
    <div class="lei-reg-cta-actions" style="margin-left:0;">
        <a href="{{ $record['gleif_url'] }}" class="lei-pub-btn outline" target="_blank" rel="noopener">View on GLEIF</a>
        <a href="{{ route('registry.register-with-us', ['lei' => $record['lei_number']]) }}" class="lei-pub-btn gold">Register with Us</a>
    </div>
</div>
