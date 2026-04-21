<div id="posts-container">
    @foreach ($posts as $post)
        <div class="post-card" id="post-{{ $post->id }}"
            style="background: white; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.2); padding: 15px; margin-bottom: 20px;">

            <div class="post-header"
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div class="post-user-info" style="display: flex; gap: 10px; align-items: center;">
                    <img src="{{ $post->user->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar"
                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <div class="post-meta">
                        <h4 class="user-name" style="margin: 0; font-size: 1rem; color: #050505;">
                            <a href="{{ route('profile.show', $post->user->id) }}"
                                style="text-decoration: none; color: inherit;">
                                {{ $post->user->name }}
                            </a>
                        </h4>
                        <small class="post-time" style="color: #65676b; font-size: 0.8rem;">
                            {{ $post->created_at->diffForHumans() }} · <i class="fa-solid fa-earth-americas"></i>
                        </small>
                    </div>
                </div>

                <div class="post-actions-right" style="display: flex; align-items: center; gap: 15px;">
                    @if ($post->user_id !== Auth::id())
                        <x-friend-button :targetUser="$post->user" />
                    @endif

                    <div class="post-options" style="position: relative;">
                        <div class="menu-dots" onclick="togglePostMenu(this)"
                            style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; background: #f0f2f5;">
                            <i class="fa-solid fa-ellipsis" style="color: #65676b;"></i>
                        </div>

                        <div class="options-dropdown"
                            style="display: none; position: absolute; right: 0; top: 40px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; width: 200px; z-index: 10; padding: 8px;">
                            <a href="#"
                                style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: black; font-size: 14px; border-radius: 6px;"><i
                                    class="fa-regular fa-bookmark"></i> Save post</a>
                            @if (Auth::id() == $post->user_id)
                                <a href="{{ route('posts.edit', $post->id) }}"
                                    style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: black; font-size: 14px; border-radius: 6px;"><i
                                        class="fa-regular fa-pen-to-square"></i> Edit</a>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST"
                                    style="margin: 0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-delete"
                                        style="display: flex; align-items: center; gap: 10px; padding: 10px; text-decoration: none; color: #f3425f; font-size: 14px; border-radius: 6px; width: 100%; background: none; border: none; cursor: pointer; font-weight: 600;"><i
                                            class="fa-regular fa-trash-can"></i> Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="post-body" style="margin-bottom: 15px;">
                @if ($post->content)
                    <p style="font-size: 1rem; line-height: 1.5; margin: 0 0 10px 0; color: #050505;">
                        {{ $post->content }}</p>
                @endif

                @if ($post->originalPost)
                    <div class="shared-post-container"
                        style="border: 1px solid #ced0d4; border-radius: 8px; padding: 12px; background-color: #ffffff; margin-top: 10px;">
                        <div class="shared-post-header"
                            style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <img src="{{ $post->originalPost->user->avatar ?? 'https://i.pravatar.cc/150' }}"
                                class="user-avatar-small"
                                style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <div>
                                <div class="shared-post-user-name" style="font-weight: 600; font-size: 0.95rem;">
                                    <a href="{{ route('profile.show', $post->originalPost->user->id) }}"
                                        style="text-decoration: none; color: inherit;">
                                        {{ $post->originalPost->user->name }}
                                    </a>
                                </div>
                                <div class="shared-post-time" style="color: var(--text-gray); font-size: 0.8rem;">
                                    {{ $post->originalPost->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        @if ($post->originalPost->content)
                            <div class="shared-post-content"
                                style="font-size: 0.9rem; line-height: 1.4; color: #050505;">
                                {{ $post->originalPost->content }}
                            </div>
                        @endif

                        @if ($post->originalPost->media_url)
                            <div class="post-media-container" style="margin-top: 10px;">
                                @if ($post->originalPost->media_type === 'image')
                                    <img src="{{ asset($post->originalPost->media_url) }}"
                                        style="width: 100%; border-radius: 8px;">
                                @elseif($post->originalPost->media_type === 'video')
                                    <video controls style="width: 100%; border-radius: 8px;">
                                        <source src="{{ asset($post->originalPost->media_url) }}" type="video/mp4">
                                    </video>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                @if (!$post->originalPost && $post->media_url)
                    <div class="post-media-container" style="margin-top: 10px;">
                        @if ($post->media_type === 'image')
                            <img src="{{ asset($post->media_url) }}" style="width: 100%; border-radius: 8px;">
                        @elseif($post->media_type === 'video')
                            <video controls style="width: 100%; border-radius: 8px;">
                                <source src="{{ asset($post->media_url) }}" type="video/mp4">
                            </video>
                        @endif
                    </div>
                @endif
            </div>

            <div class="post-footer"
                style="display: flex; justify-content: space-around; border-top: 1px solid #ced0d4; padding-top: 10px; margin-top: 15px;">
                <div class="reaction-container footer-btn" style="flex: 1;">
                    <div class="reaction-popover">
                        <!-- Cầu nối tàng hình nối liền popover và nút Thích -->
                        <div
                            style="position: absolute; bottom: -15px; left: 0; width: 100%; height: 15px; background: transparent;">
                        </div>
                        @foreach (['like' => '👍', 'love' => '❤️', 'haha' => '😆', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'] as $type => $emoji)
                            <form action="{{ route('posts.react', $post->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button type="submit" class="reaction-icon-btn"
                                    title="{{ ucfirst($type) }}">{{ $emoji }}</button>
                            </form>
                        @endforeach
                    </div>
                    @php $myReaction = $post->reactions->where('user_id', auth()->id())->first(); @endphp
                    <div
                        style="cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; color: {{ $myReaction ? '#1877f2' : '#65676b' }}; font-weight: 600;">
                        <i class="fa-regular fa-thumbs-up"></i>
                        {{ $myReaction ? ucfirst($myReaction->type) : 'Thích' }}
                        @if ($post->reactions->count() > 0)
                            ({{ $post->reactions->count() }})
                        @endif
                    </div>
                </div>

                <a href="{{ route('posts.show', $post->id) }}" class="footer-btn"
                    style="flex: 1; text-decoration: none; color: #65676b; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600;">
                    <i class="fa-regular fa-comment"></i> Bình luận
                    @if (isset($post->comments_count) && $post->comments_count > 0)
                        ({{ $post->comments_count }})
                    @elseif($post->comments && $post->comments->count() > 0)
                        ({{ $post->comments->count() }})
                    @endif
                </a>

                <form action="{{ route('posts.share', $post->id) }}" method="POST" style="flex: 1; display: flex;">
                    @csrf
                    <button type="submit" class="footer-btn"
                        style="width: 100%; background: none; border: none; padding: 8px 0; color: #65676b; font-weight: 600; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i
                            class="fa-solid fa-share"></i> Chia sẻ</button>
                </form>
            </div>

            @if ($post->comments && $post->comments->count() > 0)
                <div class="post-comments-mini"
                    style="padding-top: 10px; border-top: 1px solid #ced0d4; margin-top: 15px;">
                    @php $latestComment = $post->comments->first(); @endphp
                    @if ($latestComment)
                        <div class="comment-item" style="display: flex; gap: 10px;">
                            <img src="{{ $latestComment->user->avatar ?? 'https://i.pravatar.cc/150' }}"
                                class="user-avatar-small"
                                style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                            <div style="background: #f0f2f5; padding: 8px 12px; border-radius: 18px;">
                                <strong style="font-size: 0.9rem; color: #050505;">
                                    <a href="{{ route('profile.show', $latestComment->user->id) }}"
                                        style="text-decoration: none; color: inherit;">
                                        {{ $latestComment->user->name }}
                                    </a>
                                </strong>
                                <p style="margin:0; font-size: 0.9rem; color: #050505;">{{ $latestComment->content }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>
