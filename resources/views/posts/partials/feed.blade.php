<div id="posts-container">
    @foreach ($posts as $post)
        <div class="post-card">
            <div class="post-header">
                <div class="post-user-info">
                    <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar">
                    <div class="post-meta">
                        <h4 class="user-name">{{ $post->user->name }}</h4>
                        <small class="post-time">
                            {{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i>
                        </small>
                    </div>
                </div>

                <div class="post-options">
                    <div class="menu-dots" onclick="togglePostMenu(this)">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="options-dropdown">
                        <a href="#"><i class="fa-regular fa-bookmark"></i> Save post</a>
                        @if (Auth::id() == $post->user_id)
                            <a href="{{ route('posts.edit', $post->id) }}"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete"><i class="fa-regular fa-trash-can"></i> Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="post-body">
                @if ($post->content)
                    <p class="post-text">{{ $post->content }}</p>
                @endif

                @if ($post->media_url)
                    <div class="post-media-container">
                        @if ($post->media_type === 'image')
                            <img src="{{ asset($post->media_url) }}" class="post-image-content">
                        @elseif($post->media_type === 'video')
                            <video controls class="post-video-content">
                                <source src="{{ asset($post->media_url) }}" type="video/mp4">
                            </video>
                        @endif
                    </div>
                @endif
            </div>

            <div class="post-footer">
                <button class="footer-btn"><i class="fa-regular fa-thumbs-up"></i> Like</button>
                <button class="footer-btn"><i class="fa-regular fa-comment"></i> Comment</button>
                <button class="footer-btn"><i class="fa-solid fa-share"></i> Share</button>
            </div>
        </div>
    @endforeach
</div>
