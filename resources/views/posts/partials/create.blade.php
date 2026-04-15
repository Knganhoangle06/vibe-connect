<div class="card create-post-card">
    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="post-input-wrapper">
            <img src="{{ Auth::user()->avatar ?? 'https://i.pravatar.cc/150' }}" class="user-avatar">
            <textarea name="content" placeholder="What's on your mind, {{ Auth::user()->name }}?" rows="2"></textarea>
        </div>
        <hr class="divider">
        <div class="post-actions-bar">
            <label for="file-upload" class="action-btn">
                <i class="fa-solid fa-image" style="color: #45bd62;"></i> Photo/Video
                <input id="file-upload" type="file" name="media" style="display:none;">
            </label>
            <span class="action-btn"><i class="fa-regular fa-face-smile" style="color: #f7b928;"></i> Feeling</span>
            <button type="submit" class="btn-post-submit">Post</button>
        </div>
    </form>
</div>
