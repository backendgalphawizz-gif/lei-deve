@extends('public.layouts.app')

@section('title', $page->meta_title ?: $page->title)

@section('meta_description', $page->meta_description)

@section('content')
<section class="lei-pub-section">
    <div class="lei-pub-container lei-pub-static-page">
        <h1>{{ $page->title }}</h1>
        <div class="lei-pub-prose">{!! $page->content !!}</div>
    </div>
</section>
@endsection
