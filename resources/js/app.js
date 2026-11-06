import './bootstrap';
import './echo';

// Tự động chặn popup khó chịu "This page has expired" khi bị lỗi 419 (CSRF/Session hết hạn)
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.location.reload();
                }
            });
        });
    }
});
