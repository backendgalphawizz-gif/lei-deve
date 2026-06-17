@php
    $ext = strtolower($type ?? 'default');
    $meta = match ($ext) {
        'pdf' => ['tone' => 'pdf', 'label' => 'PDF'],
        'docx', 'doc' => ['tone' => 'docx', 'label' => 'DOC'],
        'jpg', 'jpeg' => ['tone' => 'jpg', 'label' => 'JPG'],
        'png' => ['tone' => 'jpg', 'label' => 'PNG'],
        default => ['tone' => 'default', 'label' => 'FILE'],
    };
@endphp
<span class="lei-file-type-icon lei-file-type-icon--{{ $meta['tone'] }}" aria-hidden="true">{{ $meta['label'] }}</span>
