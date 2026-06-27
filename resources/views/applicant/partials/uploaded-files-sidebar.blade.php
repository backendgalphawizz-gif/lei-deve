@php
    use Illuminate\Support\Facades\Storage;

    $slots = $slots ?? [
        ['key' => 'certificate_of_incorporation', 'label' => 'Certificate of Incorporation', 'required' => true],
        ['key' => 'articles_of_association', 'label' => 'Articles of Association', 'required' => false],
        ['key' => 'proof_of_authority', 'label' => 'Proof of Authority', 'required' => true],
    ];

    $serverFiles = [];
    foreach ($slots as $slot) {
        $path = $draft[$slot['key']] ?? null;
        if ($path) {
            $size = '—';
            if (Storage::disk('public')->exists($path)) {
                $bytes = Storage::disk('public')->size($path);
                $size = $bytes >= 1048576
                    ? number_format($bytes / 1048576, 1).' MB'
                    : number_format($bytes / 1024, 1).' KB';
            }
            $serverFiles[] = [
                'key' => $slot['key'],
                'label' => $slot['label'],
                'name' => basename($path),
                'size' => $size,
            ];
        }
    }

    $total = count($slots);
    $count = count($serverFiles);
    $percent = $total > 0 ? (int) round(($count / $total) * 100) : 0;
@endphp

<aside
    class="lei-portal-upload-sidebar"
    id="leiDocUploadSidebar"
    data-total="{{ $total }}"
    data-server-files="{{ htmlspecialchars(json_encode($serverFiles), ENT_QUOTES, 'UTF-8') }}"
>
    <h3>Uploaded Files</h3>

    <p class="lei-portal-upload-sidebar-empty" data-upload-empty @if ($count) hidden @endif>No files uploaded yet.</p>

    <ul class="lei-portal-upload-sidebar-list" data-upload-list @if (! $count) hidden @endif>
        @foreach ($serverFiles as $file)
            <li data-doc-key="{{ $file['key'] }}">
                <div class="lei-portal-upload-sidebar-icon"><i class="fa-regular fa-file-pdf"></i></div>
                <div>
                    <strong>{{ Str::limit($file['name'], 28) }}</strong>
                    <span>{{ $file['size'] }}</span>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="lei-portal-upload-sidebar-progress">
        <div class="lei-portal-upload-sidebar-progress-head">
            <span>Total Documents</span>
            <strong data-upload-count>{{ $count }}/{{ $total }}</strong>
        </div>
        <div class="lei-portal-upload-sidebar-bar" role="progressbar" data-upload-bar aria-valuenow="{{ $count }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
            <span data-upload-bar-fill style="width: {{ $percent }}%"></span>
        </div>
    </div>
</aside>
