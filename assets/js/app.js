(() => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.querySelector('.menu-toggle');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const mobile = window.matchMedia('(max-width: 1000px)');

    if (sidebar && toggle && backdrop) {
        const setOpen = (open, restoreFocus = true) => {
            document.body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            sidebar.inert = mobile.matches && !open;
            backdrop.hidden = !open;
            if (open) sidebar.querySelector('.sidebar-close').focus();
            else if (restoreFocus) toggle.focus();
        };
        toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
        backdrop.addEventListener('click', () => setOpen(false));
        sidebar.querySelector('.sidebar-close').addEventListener('click', () => setOpen(false));
        mobile.addEventListener('change', () => setOpen(false, false));
        setOpen(false, false);
        document.addEventListener('keydown', event => {
            if (!document.body.classList.contains('nav-open')) return;
            if (event.key === 'Escape') setOpen(false);
            if (event.key === 'Tab') {
                const items = [...sidebar.querySelectorAll('a[href], button')].filter(el => el.getClientRects().length);
                const first = items[0], last = items[items.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });
    }

    document.querySelectorAll('.table-wrap').forEach(wrapper => {
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        const section = wrapper.closest('section');
        wrapper.setAttribute('aria-label', section?.querySelector('h2')?.textContent || 'Tabel data, geser untuk melihat semua kolom');
    });
})();
