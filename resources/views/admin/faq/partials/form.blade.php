@php
    $isEdit = $faq->exists;
    $formAction = $isEdit ? route('admin.faq.update', $faq) : route('admin.faq.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="lei-wm-form">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <label>Category
        <select name="category_id">
            <option value="">General / Pricing only</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $faq->category_id) == $category->id)>{{ $category->title }}</option>
            @endforeach
        </select>
    </label>
    <label>Question<input type="text" name="question" value="{{ old('question', $faq->question) }}" placeholder="What is an LEI?" required data-rules="required|maxLen:255"></label>
    <label>Answer<textarea name="answer" rows="6" placeholder="Write the full answer shown on the public site..." required data-rules="required">{{ old('answer', $faq->answer) }}</textarea></label>
    <div class="lei-wm-checks">
        <label><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published ?? true))> Published on FAQ page</label>
        <label><input type="checkbox" name="show_on_pricing" value="1" @checked(old('show_on_pricing', $faq->show_on_pricing))> Show on pricing page</label>
    </div>
    <div class="lei-wm-form-actions">
        <button type="submit" class="lei-wm-btn-primary">{{ $isEdit ? 'Save Changes' : 'Create FAQ' }}</button>
        <a href="{{ route('admin.faq.index') }}" class="lei-wm-btn-ghost">Cancel</a>
    </div>
</form>
