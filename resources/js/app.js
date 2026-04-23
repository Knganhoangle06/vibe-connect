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
        modal.style.display = "flex";
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

window.toggleMenu = function (dotButton) {
    const menu = dotButton.nextElementSibling;

    document.querySelectorAll(".options-menu").forEach((m) => {
        if (m !== menu) m.classList.remove("active");
    });

    if (menu) menu.classList.toggle("active");
};

window.toggleCommentPanel = function (postId) {
    const panel = document.getElementById("comment-panel-" + postId);
    if (!panel) return;

    const isOpen = panel.classList.toggle("is-open");
    document
        .querySelectorAll('[aria-controls="comment-panel-' + postId + '"]')
        .forEach(function (el) {
            el.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });

    if (isOpen) {
        const input = document.getElementById("comment-input-" + postId);
        if (input) {
            input.focus();
        }
    }
};

window.toggleProfileMenu = function (event) {
    event.stopPropagation();
    const dropdown = document.getElementById("profileDropdown");

    if (!dropdown) return;

    dropdown.classList.toggle("active");
    document
        .querySelectorAll(".options-menu")
        .forEach((m) => m.classList.remove("active"));
};

window.previewFiles = function (input) {
    const previewContainer = document.getElementById("preview-container");
    previewContainer.innerHTML = "";

    // Khôi phục thuộc tính để dọn dẹp format của lần chọn cũ
    previewContainer.removeAttribute("style");

    if (input.files && input.files.length > 0) {
        const isSingle = input.files.length === 1;

        if (isSingle) {
            previewContainer.style.display = "block";
        } else {
            previewContainer.style.display = "grid";
            previewContainer.style.gridTemplateColumns =
                "repeat(auto-fit, minmax(180px, 1fr))";
            previewContainer.style.gap = "10px";
            previewContainer.style.maxHeight = "350px";
            previewContainer.style.overflowY = "auto";
            previewContainer.style.marginTop = "10px";
        }

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
                element.style.boxShadow = "0 1px 3px rgba(0,0,0,0.1)";
                element.style.backgroundColor = "#000";

                if (isSingle) {
                    element.style.maxHeight = "350px";
                    element.style.objectFit = "contain";
                    element.style.marginTop = "10px";
                } else {
                    element.style.height = "180px";
                    element.style.objectFit = "cover";
                }

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

document.addEventListener("DOMContentLoaded", function () {
    if (window.Echo) {
        window.Echo.join("online")
            .here((users) => {
                users.forEach((user) => {
                    document
                        .querySelectorAll(`.user-status-${user.id}`)
                        .forEach((el) => el.classList.add("online"));
                });
            })
            .joining((user) => {
                document
                    .querySelectorAll(`.user-status-${user.id}`)
                    .forEach((el) => el.classList.add("online"));
            })
            .leaving((user) => {
                document
                    .querySelectorAll(`.user-status-${user.id}`)
                    .forEach((el) => el.classList.remove("online"));
            });
    } else {
        console.warn(
            "Laravel Echo chưa được khởi tạo. Hãy kiểm tra lại file bootstrap.js",
        );
    }
});
// JS cho phần share //
