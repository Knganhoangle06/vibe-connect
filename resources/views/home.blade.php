@extends('layouts.app')

@section('content')
    <div class="main-layout-container">
        @include('layouts.partials.sidebar-left')

        <main class="feed-content">
            @include('posts.partials.create')

            @include('posts.partials.feed')
        </main>

        @include('layouts.partials.sidebar-right')
    </div>
@endsection
