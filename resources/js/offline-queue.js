// Antrean laporan offline (CLAUDE.md §14): simpan laporan + berkas ke IndexedDB
// saat offline, kirim otomatis lewat Background Sync atau event 'online' sebagai
// cadangan untuk browser yang tidak mendukung Background Sync.
import { get, set, del, keys, createStore } from 'idb-keyval';

const reportStore = createStore('karsa-reports-db', 'karsa-reports-queue');

function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

export async function queueReport(fields, files) {
    const id = crypto.randomUUID();
    await set(id, { fields, files }, reportStore);

    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const registration = await navigator.serviceWorker.ready;
        try {
            await registration.sync.register('karsa-sync-reports');
        } catch (e) {
            // Background Sync gagal didaftarkan -- andalkan event 'online' di bawah.
        }
    }

    return id;
}

export async function flushReportQueue() {
    const allKeys = await keys(reportStore);

    for (const key of allKeys) {
        const item = await get(key, reportStore);
        if (!item) continue;

        try {
            const formData = new FormData();
            Object.entries(item.fields).forEach(([k, v]) => formData.append(k, v));
            (item.files || []).forEach((file) => formData.append('media[]', file, file.name));

            const response = await fetch('/api/v1/reports', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-XSRF-TOKEN': getCsrfToken(), Accept: 'application/json' },
                body: formData,
            });

            if (response.ok) {
                await del(key, reportStore);
            }
        } catch (e) {
            // Masih offline atau gagal -- coba lagi di kesempatan berikutnya.
            break;
        }
    }
}

export async function queuedReportCount() {
    return (await keys(reportStore)).length;
}

window.addEventListener('online', () => {
    flushReportQueue();
});

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data?.type === 'flush-report-queue') {
            flushReportQueue();
        }
    });
}

window.karsaOfflineQueue = { queueReport, flushReportQueue, queuedReportCount };
