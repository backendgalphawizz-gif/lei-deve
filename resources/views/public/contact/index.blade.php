@extends('public.layouts.app')

@section('title', 'Contact Us')
@section('body_class', 'page-contact')

@section('content')
@php $info = $sections->get('info')?->content ?? []; @endphp
<section class="lei-pub-section">
    <div class="lei-pub-container">
        <div class="lei-pub-contact-card">
            <div class="lei-pub-contact-info">
                <h2>{{ $sections->get('info')?->title ?? 'Get in Touch' }}</h2>
                <div class="lei-pub-contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <div>
                        <strong>{{ $info['headquarters_label'] ?? 'Global Headquarters' }}</strong>
                        <p>{{ $businessSettings->address_line }}, {{ $businessSettings->city }}, {{ $businessSettings->postal_code }}, {{ $businessSettings->country }}</p>
                    </div>
                </div>
                <div class="lei-pub-contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <div>
                        <strong>{{ $info['email_label'] ?? 'General Inquiries' }}</strong>
                        <p>{{ $businessSettings->support_email }}</p>
                    </div>
                </div>
                <div class="lei-pub-contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <div>
                        <strong>{{ $info['phone_label'] ?? '24/7 Hotline' }}</strong>
                        <p>{{ $businessSettings->support_phone }}</p>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('contact.submit') }}" class="lei-pub-contact-form">
                @csrf
                <div class="lei-pub-form-row">
                    <label>Full Name<input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="John Doe" required></label>
                    <label>Work Email<input type="email" name="email" value="{{ old('email') }}" placeholder="john@company.com" required></label>
                </div>
                <label>Subject
                    <select name="subject" required>
                        @foreach ($subjects as $value => $label)
                            <option value="{{ $value }}" @selected(old('subject') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Message<textarea name="message" rows="5" placeholder="How can we assist your organization today?" required>{{ old('message') }}</textarea></label>
                <button type="submit" class="lei-pub-btn full">Send Message</button>
            </form>
        </div>
    </div>
</section>
@endsection
