// public/js/post-menu.js

function toggleMenu(dotButton) {
    // Tìm menu nằm ngay cạnh cái nút 3 chấm vừa bấm
    const menu = dotButton.nextElementSibling;
    
    // Đóng tất cả các menu khác đang mở (nếu có)
    document.querySelectorAll('.options-menu').forEach(m => {
        if (m !== menu) m.classList.remove('active');
    });

    // Đóng/mở menu hiện tại
    menu.classList.toggle('active');
}

// Click ra ngoài thì đóng menu
window.onclick = function(event) {
    if (!event.target.closest('.post-options')) {
        document.querySelectorAll('.options-menu').forEach(m => {
            m.classList.remove('active');
        });
    }
};