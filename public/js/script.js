function toggleMenu(dotButton) {
    const menu = dotButton.nextElementSibling;

    document.querySelectorAll('.options-menu').forEach(m => {
        if (m !== menu) m.classList.remove('active');
    });

    menu.classList.toggle('active');
}

function toggleProfileMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('profileDropdown');

    if (!dropdown) return;

    dropdown.classList.toggle('active');
    document.querySelectorAll('.options-menu').forEach(m => m.classList.remove('active'));
}

function toggleCommentPanel(postId) {
    const panel = document.getElementById('comment-panel-' + postId);
    if (!panel) return;

    const isOpen = panel.classList.toggle('is-open');
    document.querySelectorAll('[aria-controls="comment-panel-' + postId + '"]').forEach(function (el) {
        el.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    if (isOpen) {
        const input = document.getElementById('comment-input-' + postId);
        if (input) {
            input.focus();
        }
    }
}

window.addEventListener('click', function (event) {
    if (!event.target.closest('.post-options')) {
        document.querySelectorAll('.options-menu').forEach(m => {
            m.classList.remove('active');
        });
    }

    if (!event.target.closest('.profile-menu-wrapper')) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) dropdown.classList.remove('active');
    }
});

// Profile 
function openModal() {
    document.getElementById('editProfileModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Ngăn cuộn trang khi mở modal
}

function closeModal() {
    document.getElementById('editProfileModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Đóng modal khi click ra ngoài vùng form
window.onclick = function(event) {
    let modal = document.getElementById('editProfileModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Cửa sổ đăng bài // 
function openPostModal() {
    document.getElementById('postModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Khóa cuộn trang
}

function closePostModal() {
    document.getElementById('postModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Hàm xem trước file đã chọn
function previewFiles(input) {
    const preview = document.getElementById('preview-container');
    preview.innerHTML = ''; // Xóa preview cũ
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.margin = "10px 0";
                
                if (file.type.startsWith('image/')) {
                    div.innerHTML = `<img src="${e.target.result}" style="width:100%; border-radius:8px;">`;
                } else if (file.type.startsWith('video/')) {
                    div.innerHTML = `<video src="${e.target.result}" controls style="width:100%; border-radius:8px;"></video>`;
                }
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

// JS cho phần share // 
