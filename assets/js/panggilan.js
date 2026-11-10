(() => {
    const board = document.getElementById('panggilan-antrian');
    if (!board) return;

    const lanes = [...board.querySelectorAll('[data-call-lane]')];
    const liveLabel = board.querySelector('[data-live-label]');
    const syncMessage = board.querySelector('[data-sync-message]');
    let timer;
    let inFlight = false;

    const setText = (element, text) => {
        if (element.textContent !== text) element.textContent = text;
    };

    const render = data => {
        // Validasi seluruh respons sebelum mengganti salah satu jalur.
        if (!data || !data.antrian || !lanes.every(lane => {
            const current = data.antrian[lane.dataset.callLane];
            return current === null || (current && typeof current.nomor_antrian === 'string' && typeof current.plat_nomor === 'string');
        })) throw new Error('Data panggilan tidak valid');

        lanes.forEach(lane => {
            const type = lane.dataset.callLane;
            const current = data.antrian[type];
            const number = lane.querySelector('[data-call-number]');
            lane.classList.toggle('is-empty', !current);
            number.style.setProperty('--number-length', Math.max(4, current?.nomor_antrian.length || 0));
            setText(number, current?.nomor_antrian || '—');
            setText(lane.querySelector('[data-call-plate]'), current?.plat_nomor || 'Belum ada panggilan');
            setText(lane.querySelector('[data-call-note]'), current ? `Silakan menuju area ${type}.` : 'Menunggu panggilan petugas.');
        });
    };

    const refresh = async () => {
        clearTimeout(timer);
        if (document.hidden || inFlight) return;
        inFlight = true;
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);
        try {
            const response = await fetch(board.dataset.endpoint, {
                cache: 'no-store', credentials: 'same-origin', signal: controller.signal,
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) throw new Error('Panggilan tidak dapat dimuat');
            render(await response.json());
            delete board.dataset.stale;
            setText(liveLabel, 'Langsung');
            setText(syncMessage, 'Diperbarui otomatis setiap 5 detik.');
        } catch {
            board.dataset.stale = 'true';
            setText(liveLabel, 'Offline');
            setText(syncMessage, 'Koneksi terputus. Menampilkan data terakhir dan mencoba kembali.');
        } finally {
            clearTimeout(timeout);
            inFlight = false;
            if (!document.hidden) timer = setTimeout(refresh, 5000);
        }
    };

    document.addEventListener('visibilitychange', refresh);
    window.addEventListener('pageshow', event => { if (event.persisted) refresh(); });
    refresh();
})();
