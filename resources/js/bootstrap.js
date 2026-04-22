import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// === LARAVEL ECHO & REVERB CONFIG (From Friendships) ===
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// LOGIC CHẤM XANH (Presence Channel)
document.addEventListener("DOMContentLoaded", function() {
    if (window.Echo) {
        window.Echo.join('online')
            .here((users) => {
                users.forEach(user => {
                    document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.add('online'));
                });
            })
            .joining((user) => {
                document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.add('online'));
            })
            .leaving((user) => {
                document.querySelectorAll(`.user-status-${user.id}`).forEach(el => el.classList.remove('online'));
            });
    }
});
