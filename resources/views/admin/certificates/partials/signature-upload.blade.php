@php($caUser = auth()->user())
<div class="lei-ca-signature-card">
    <h4><i class="fa-solid fa-signature" aria-hidden="true"></i> Your Digital Signature</h4>
    <p class="lei-ca-hint">Upload a PNG or JPG of your signature. It will be printed on every certificate you digitally sign.</p>

    @if ($caUser->caSignatureDataUri())
        <div class="lei-ca-sig-preview-wrap">
            <img src="{{ $caUser->caSignatureDataUri() }}" alt="Uploaded digital signature" class="lei-ca-sig-preview">
        </div>
        <div class="lei-ca-sig-actions">
            <form method="POST" action="{{ route('admin.certificates.signature.upload') }}" enctype="multipart/form-data" class="lei-ca-sig-form">
                @csrf
                <label class="lei-ca-sig-upload-btn">
                    <input type="file" name="signature" accept="image/png,image/jpeg,image/jpg,image/webp" required onchange="this.form.submit()">
                    Replace signature
                </label>
            </form>
            <form method="POST"
                  action="{{ route('admin.certificates.signature.remove') }}"
                  data-confirm="Remove your uploaded signature?"
                  data-confirm-title="Remove Signature"
                  data-confirm-button="Remove"
                  data-confirm-variant="warning">
                @csrf
                @method('DELETE')
                <button type="submit" class="lei-icon-btn lei-icon-btn--danger" title="Remove signature" aria-label="Remove signature">
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('admin.certificates.signature.upload') }}" enctype="multipart/form-data" class="lei-ca-sig-form lei-ca-sig-form--empty">
            @csrf
            <label class="lei-ca-sig-dropzone">
                <input type="file" name="signature" accept="image/png,image/jpeg,image/jpg,image/webp" required onchange="this.form.submit()">
                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                <span>Click to upload signature image</span>
                <small>PNG, JPG or WebP · max 2 MB</small>
            </label>
        </form>
        @if (! empty($warnIfMissing))
            <p class="lei-ca-sig-warn"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> Upload your signature before signing certificates.</p>
        @endif
    @endif

    @error('signature')
        <p class="lei-ca-sig-error">{{ $message }}</p>
    @enderror
</div>
