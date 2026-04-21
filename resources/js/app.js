import "./bootstrap";

document.addEventListener("DOMContentLoaded", () => {
    const postsContainer = document.getElementById("posts-container");

    if (postsContainer) {
        window.Echo.channel("news-feed").listen("PostCreated", (event) => {
            console.log("Có bài viết mới từ hệ thống:", event.post);

            const post = event.post;

            if (document.getElementById(`post-${post.id}`)) {
                return;
            }

            const avatarUrl = post.user.avatar ? post.user.avatar : "https://i.pravatar.cc/150";

            let mediaHtml = "";
            if (post.media_url) {
                const mediaPath = "/" + post.media_url;

                if (post.media_type === "image") {
                    mediaHtml = `
                            <div class="post-media-container">
                                <img src="${mediaPath}" class="post-image-content">
                            </div>`;
                } else if (post.media_type === "video") {
                    mediaHtml = `
                            <div class="post-media-container">
                                <video controls class="post-video-content">
                                    <source src="${mediaPath}" type="video/mp4">
                                </video>
                            </div>`;
                }
            }

            const postHtml = `
                    <div class="post-card" id="post-${post.id}">
                        <div class="post-header">
                            <div class="post-user-info">
                                <img src="${avatarUrl}" class="user-avatar">
                                <div class="post-meta">
                                    <h4 class="user-name">${post.user.name}</h4>
                                    <small class="post-time">Just now · <i class="fa-solid fa-earth-americas"></i></small>
                                </div>
                            </div>
                            <div class="post-options">
                                <div class="menu-dots"><i class="fa-solid fa-ellipsis"></i></div>
                            </div>
                        </div>

                        <div class="post-body">
                            ${post.content ? `<p class="post-text">${post.content}</p>` : ""}
                            ${mediaHtml}
                        </div>

                        <div class="post-footer">
                            <button class="footer-btn"><i class="fa-regular fa-thumbs-up"></i> Like</button>
                            <button class="footer-btn"><i class="fa-regular fa-comment"></i> Comment</button>
                            <button class="footer-btn"><i class="fa-solid fa-share"></i> Share</button>
                        </div>
                    </div>
                `;

            postsContainer.insertAdjacentHTML("afterbegin", postHtml);
        });
    }

    // KÊNH PRESENCE: Xử lý hiển thị chấm xanh toàn cục
    if (window.Echo) {
        window.Echo.join(`online`)
            .here((users) => {
                // Ai đang online sẵn
                users.forEach(user => {
                    document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.add('online'));
                });
            })
            .joining((user) => {
                // Ai vừa đăng nhập
                document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.add('online'));
            })
            .leaving((user) => {
                // Ai vừa thoát
                document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.remove('online'));
            });
    }
});
