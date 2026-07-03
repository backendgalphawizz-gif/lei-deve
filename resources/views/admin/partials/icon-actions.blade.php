@php
    $viewUrl = $viewUrl ?? null;
    $editUrl = $editUrl ?? null;
    $externalUrl = $externalUrl ?? null;
    $approveUrl = $approveUrl ?? null;
    $activateUrl = $activateUrl ?? null;
    $deleteUrl = $deleteUrl ?? null;
    $deleteConfirm = $deleteConfirm ?? 'Delete this item? This cannot be undone.';
    $deleteTitle = $deleteTitle ?? 'Confirm Delete';
    $deleteMethod = $deleteMethod ?? 'DELETE';
    $confirmUrl = $confirmUrl ?? null;
    $confirmMessage = $confirmMessage ?? null;
    $confirmTitle = $confirmTitle ?? 'Confirm Action';
    $confirmButton = $confirmButton ?? 'Confirm';
    $confirmVariant = $confirmVariant ?? 'danger';
    $confirmIcon = $confirmIcon ?? null;
@endphp

<div class="lei-icon-actions">
    @if ($viewUrl)
        <a href="{{ $viewUrl }}" class="lei-icon-btn" title="View" aria-label="View">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
        </a>
    @endif
    @if ($editUrl)
        <a href="{{ $editUrl }}" class="lei-icon-btn" title="Edit" aria-label="Edit">
            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
        </a>
    @endif
    @if ($externalUrl)
        <a href="{{ $externalUrl }}" class="lei-icon-btn" title="Open" aria-label="Open" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>
    @endif
    @if ($approveUrl)
        <form method="POST" action="{{ $approveUrl }}" class="lei-icon-action-form">
            @csrf
            <button type="submit" class="lei-icon-btn lei-icon-btn--success" title="Approve" aria-label="Approve">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </button>
        </form>
    @endif
    @if ($activateUrl)
        <form method="POST" action="{{ $activateUrl }}" class="lei-icon-action-form">
            @csrf
            <button type="submit" class="lei-icon-btn lei-icon-btn--success" title="Reactivate" aria-label="Reactivate">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
            </button>
        </form>
    @endif
    @if ($confirmUrl && $confirmMessage)
        <form method="POST"
              action="{{ $confirmUrl }}"
              class="lei-icon-action-form"
              data-confirm="{{ $confirmMessage }}"
              data-confirm-title="{{ $confirmTitle }}"
              data-confirm-button="{{ $confirmButton }}"
              data-confirm-variant="{{ $confirmVariant }}"
              @if ($confirmIcon) data-confirm-icon="{{ $confirmIcon }}" @endif>
            @csrf
            {{ $confirmExtra ?? '' }}
            <button type="submit" class="lei-icon-btn lei-icon-btn--{{ $confirmVariant === 'primary' ? 'primary' : ($confirmVariant === 'warning' ? 'warning' : 'danger') }}" title="{{ $confirmButton }}" aria-label="{{ $confirmButton }}">
                <i class="fa-solid {{ $confirmIcon ?? 'fa-circle-exclamation' }}" aria-hidden="true"></i>
            </button>
        </form>
    @endif
    @if ($deleteUrl)
        <form method="POST"
              action="{{ $deleteUrl }}"
              class="lei-icon-action-form"
              data-confirm="{{ $deleteConfirm }}"
              data-confirm-title="{{ $deleteTitle }}"
              data-confirm-button="Delete"
              data-confirm-variant="danger">
            @csrf
            @if (strtoupper($deleteMethod) !== 'POST')
                @method($deleteMethod)
            @endif
            <button type="submit" class="lei-icon-btn lei-icon-btn--danger" title="Delete" aria-label="Delete">
                <i class="fa-solid fa-trash" aria-hidden="true"></i>
            </button>
        </form>
    @endif
</div>
