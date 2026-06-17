<a href="{{ route('admin.dashboard') }}">{{ $businessSettings->breadcrumb_root ?? 'Registry' }}</a>
@if (!empty($trail))
    @foreach ($trail as $item)
        <span> / </span>
        @if (!empty($item['url']))
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span>{{ $item['label'] }}</span>
        @endif
    @endforeach
@elseif (!empty($current))
    <span> / </span>
    <span>{{ $current }}</span>
@endif
