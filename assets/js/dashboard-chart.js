(() => {
    document.querySelectorAll('.queue-chart-panel').forEach(panel => {
        const days = [...panel.querySelectorAll('.queue-chart-day')];
        const show = active => days.forEach(day => day.toggleAttribute('data-tooltip-hidden', day !== active));

        days.forEach(day => {
            day.addEventListener('pointerenter', () => show(day));
            day.addEventListener('focus', () => show(day));
            day.addEventListener('click', () => show(day));
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') show(null);
        });
        document.addEventListener('pointerdown', event => {
            if (!days.some(day => day.contains(event.target))) show(null);
        });
    });
})();
