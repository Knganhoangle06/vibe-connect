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