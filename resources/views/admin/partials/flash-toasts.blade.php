@if (session()->hasAny(['success', 'error', 'info', 'warning']))
    @php
        $flashMessages = array_filter([
            'success' => session('success'),
            'error' => session('error'),
            'info' => session('info'),
            'warning' => session('warning'),
        ], fn ($value) => filled($value));
    @endphp
    @if ($flashMessages !== [])
        <script>window.__leiFlashMessages = @json($flashMessages);</script>
    @endif
@endif
