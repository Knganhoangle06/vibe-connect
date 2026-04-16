<div id="posts-container">
    @foreach ($posts as $post)
        <div class="post-card">
            <div class="post-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div class="post-user-info" style="display: flex; gap: 10px; align-items: center;">
                    <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <div class="post-meta">
                        <h4 class="user-name" style="margin: 0; font-size: 1rem; color: #050505;">{{ $post->user->name }}</h4>
                        <small class="post-time" style="color: #65676b; font-size: 0.8rem;">
                            {{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i>
                        </small>
                    </div>
                </div>

                <div class="post-actions-right" style="display: flex; align-items: center; gap: 15px;">

                    <x-friend-button :targetUser="$post->user" />

                    <div class="post-options" style="position: relative;">
                        <div class="menu-dots" onclick="togglePostMenu(this)" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer;">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                        <div class="options-dropdown" style="display: none; position: absolute; right: 0; top: 40px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; width: 200px; z-index: 10; padding: 8px;">
                            <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: black; font-size: 14px; border-radius: 6px;"><i class="fa-regular fa-bookmark"></i> Save post</a>
                            @if (Auth::id() == $post->user_id)
                                <a href="{{ route('posts.edit', $post->id) }}" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: black; font-size: 14px; border-radius: 6px;"><i class="fa-regular fa-pen-to-square"></i> Edit</a>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="margin: 0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-delete" style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: #f3425f; font-size: 14px; border-radius: 6px; width: 100%; background: none; border: none; cursor: pointer; font-weight: 600;"><i class="fa-regular fa-trash-can"></i> Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="post-body">
                @if ($post->content)
                    <p class="post-text" style="margin-bottom: 15px; line-height: 1.5; font-size: 0.95rem;">{{ $post->content }}</p>
                @endif

                @if ($post->media_url)
                    <div class="post-media-container" style="margin-top: 10px;">
                        @if ($post->media_type === 'image')
                            <img src="{{ asset($post->media_url) }}" class="post-image-content" style="width: 100%; border-radius: 8px;">
                        @elseif($post->media_type === 'video')
                            <video controls class="post-video-content" style="width: 100%; border-radius: 8px;">
                                <source src="{{ asset($post->media_url) }}" type="video/mp4">
                            </video>
                        @endif
                    </div>
                @endif
            </div>

            <div class="post-footer" style="display: flex; justify-content: space-around; border-top: 1px solid #ced0d4; padding-top: 10px; margin-top: 15px;">
                <button class="footer-btn" style="flex: 1; background: none; border: none; padding: 8px 0; border-radius: 5px; color: #65676b; font-weight: 600; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-regular fa-thumbs-up"></i> Like</button>
                <button class="footer-btn" style="flex: 1; background: none; border: none; padding: 8px 0; border-radius: 5px; color: #65676b; font-weight: 600; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-regular fa-comment"></i> Comment</button>
                <button class="footer-btn" style="flex: 1; background: none; border: none; padding: 8px 0; border-radius: 5px; color: #65676b; font-weight: 600; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-solid fa-share"></i> Share</button>
            </div>
        </div>
    @endforeach
</div>
