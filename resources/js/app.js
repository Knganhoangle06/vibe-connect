import "./bootstrap";

// Hỗ trợ AlpineJS (nếu dùng sau này)
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

// ==========================================
// CÁC HÀM GIAO DIỆN TỪ NHÁNH REACTION CŨ
// (Gắn vào window để gọi được từ HTML onclick)
// ==========================================

window.openPostModal = function () {
    let modal = document.getElementById("postModal");
    if (modal) {
        modal.style.display = "block";
        document.body.style.overflow = "hidden";
    }
};

window.closePostModal = function () {
    let modal = document.getElementById("postModal");
    if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "auto";
    }
};

window.toggleMenu = function (element) {
    // Đóng tất cả các menu khác
    document.querySelectorAll(".options-menu").forEach((menu) => {
        if (menu !== element.nextElementSibling) {
            menu.classList.remove("active");
        }
    });
    // Bật menu hiện tại
    const menu = element.nextElementSibling;
    if (menu) {
        menu.classList.toggle("active");
    }
};

window.toggleCommentPanel = function (postId) {
    const panel = document.getElementById(`comment-panel-${postId}`);
    if (panel) {
        panel.classList.toggle("is-open");
    }
};

window.previewFiles = function (input) {
    const previewContainer = document.getElementById("preview-container");
    previewContainer.innerHTML = "";

    if (input.files) {
        Array.from(input.files).forEach((file) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const isVideo = file.type.startsWith("video/");
                const element = isVideo
                    ? document.createElement("video")
                    : document.createElement("img");

                element.src = e.target.result;
                element.style.width = "100%";
                element.style.borderRadius = "8px";
                element.style.marginTop = "10px";
                if (isVideo) element.controls = true;

                previewContainer.appendChild(element);
            };
            reader.readAsDataURL(file);
        });
    }
};

// Đóng modal nếu click ra ngoài vùng trắng
window.onclick = function (event) {
    let modal = document.getElementById("postModal");
    if (event.target == modal) {
        window.closePostModal();
    }

    // Đóng dropdown menu nếu click ra ngoài
    if (
        !event.target.matches(".fa-ellipsis") &&
        !event.target.matches(".menu-dots")
    ) {
        document.querySelectorAll(".options-menu").forEach((menu) => {
            menu.classList.remove("active");
        });
    }
};
