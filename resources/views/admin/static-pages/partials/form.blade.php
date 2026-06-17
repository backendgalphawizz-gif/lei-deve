@php
    $isEdit = $page->exists;
    $formAction = $isEdit
        ? route('admin.static-pages.update', $page)
        : route('admin.static-pages.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="lei-sp-form" id="leiSpForm">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="lei-sp-form-grid">
        <div class="lei-sp-form-main">
            <div class="lei-sp-field">
                <label for="sp_title">Page Title <span class="lei-sp-req">*</span></label>
                <input type="text" id="sp_title" name="title" value="{{ old('title', $page->title) }}" placeholder="e.g. Privacy Policy" required>
            </div>

            <div class="lei-sp-field">
                <label for="sp_slug">URL Slug</label>
                <div class="lei-sp-slug-wrap">
                    <span class="lei-sp-slug-prefix">/</span>
                    <input type="text" id="sp_slug" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="auto-generated-from-title" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                </div>
                <small class="lei-sp-hint">Leave blank to auto-generate from title. Lowercase letters, numbers, and hyphens only.</small>
            </div>

            <div class="lei-sp-field">
                <label for="sp_content">Page Content <span class="lei-sp-req">*</span></label>
                <textarea id="sp_content" name="content" rows="14" placeholder="HTML or plain text content..." required>{{ old('content', $page->content) }}</textarea>
            </div>
        </div>

        <aside class="lei-sp-form-side">
            <div class="lei-sp-side-card">
                <h4>Publish Settings</h4>

                <div class="lei-sp-field">
                    <label for="sp_status">Status</label>
                    <select id="sp_status" name="status" required>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $page->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lei-sp-field">
                    <label for="sp_type">Page Type</label>
                    <select id="sp_type" name="page_type" required>
                        @foreach ($pageTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('page_type', $page->page_type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lei-sp-field">
                    <label for="sp_sort">Sort Order</label>
                    <input type="number" id="sp_sort" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0" max="9999">
                </div>

                <label class="lei-sp-check">
                    <input type="checkbox" name="is_in_footer" value="1" @checked(old('is_in_footer', $page->is_in_footer))>
                    <span>Show in site footer</span>
                </label>
            </div>

            <div class="lei-sp-side-card">
                <h4>SEO Metadata</h4>
                <div class="lei-sp-field">
                    <label for="sp_meta_title">Meta Title</label>
                    <input type="text" id="sp_meta_title" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="150">
                </div>
                <div class="lei-sp-field">
                    <label for="sp_meta_desc">Meta Description</label>
                    <textarea id="sp_meta_desc" name="meta_description" rows="4" maxlength="255">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>
            </div>

            <div class="lei-sp-form-actions">
                <button type="submit" class="lei-sp-btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Page' }}</button>
                <a href="{{ route('admin.static-pages.index') }}" class="lei-sp-btn-ghost">Cancel</a>
            </div>
        </aside>
    </div>
</form>
