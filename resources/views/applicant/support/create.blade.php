@extends('applicant.layouts.app')

@section('title', 'Raise New Ticket')

@section('content')
<div class="lei-portal-page-head">
    <div>
        <h1>Raise New Ticket</h1>
        <p>Describe your issue below. Our support team typically responds within 4 business hours.</p>
    </div>
</div>

<form method="POST" action="{{ route('applicant.support.store') }}" class="lei-portal-card">
    @csrf
    <div class="lei-portal-form-grid">
        <div class="lei-portal-field">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            @error('category')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" required>
                <option value="low" @selected(old('priority') === 'low')>Low</option>
                <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                <option value="high" @selected(old('priority') === 'high')>High</option>
                <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
            </select>
            @error('priority')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field full">
            <label for="subject">Subject</label>
            <input id="subject" name="subject" value="{{ old('subject') }}" placeholder="Brief summary of your inquiry" required>
            @error('subject')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="lei-portal-field full">
            <label for="description">Detailed Description</label>
            <textarea id="description" name="description" rows="6" placeholder="Please provide as much detail as possible, including application reference numbers if relevant..." required>{{ old('description') }}</textarea>
            @error('description')<p class="lei-portal-field-error">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="lei-portal-actions">
        <a href="{{ route('applicant.support.index') }}" class="lei-btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Tickets</a>
        <button type="submit" class="lei-btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Ticket</button>
    </div>
</form>
@endsection
