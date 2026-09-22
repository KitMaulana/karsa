// Aktivasi Web Push (VAPID) -- CLAUDE.md §13.
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);

    return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)));
}

function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : (document.querySelector('meta[name="csrf-token"]')?.content ?? '');
}

window.karsaEnablePush = async function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Perangkat/browser ini tidak mendukung notifikasi push.');

        return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        return;
    }

    const vapidPublicKey = window.karsaVapidPublicKey;
    if (!vapidPublicKey) {
        alert('Notifikasi push belum dikonfigurasi oleh admin.');

        return;
    }

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    await fetch('/push-subscriptions', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(subscription.toJSON()),
    });

    window.location.reload();
};
