(() => {
    'use strict';

    const sidebar = document.getElementById('patrona-sidebar');
    const shell = sidebar?.closest('.admin-shell');
    if (!shell) return;

    const toolbar = shell.querySelector('[data-sidebar-toolbar]');
    const bottomToggle = sidebar.querySelector('[data-sidebar-collapse]');
    const groupToggle = sidebar.querySelector('.sidebar-group-toggle');
    const links = document.getElementById(groupToggle.getAttribute('aria-controls'));
    const desktop = window.matchMedia('(min-width: 701px)');
    const storageKey = 'patrona_sidebar_collapsed';
    let collapsed = false;
    try {
        collapsed = sessionStorage.getItem(storageKey) === '1';
    } catch {}

    const toolbarToggle = document.createElement('button');
    toolbarToggle.type = 'button';
    toolbarToggle.className = 'patrona-sidebar-toggle';
    toolbarToggle.innerHTML = '<i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>';
    toolbar?.prepend(toolbarToggle);

    function render() {
        const compact = desktop.matches && collapsed;
        shell.classList.toggle('sidebar-collapsed', compact);
        const label = compact ? 'Expandir menú lateral' : 'Contraer menú lateral';
        for (const button of [toolbarToggle, bottomToggle]) {
            button.title = label;
            button.setAttribute('aria-label', label);
            button.setAttribute('aria-controls', sidebar.id);
            button.setAttribute('aria-expanded', String(!compact));
        }
    }

    function toggleSidebar() {
        if (!desktop.matches) return;
        collapsed = !collapsed;
        try {
            sessionStorage.setItem(storageKey, collapsed ? '1' : '0');
        } catch {}
        render();
    }

    toolbarToggle.addEventListener('click', toggleSidebar);
    bottomToggle.addEventListener('click', toggleSidebar);
    desktop.addEventListener('change', render);
    groupToggle.addEventListener('click', () => {
        if (shell.classList.contains('sidebar-collapsed')) {
            toggleSidebar();
            links.hidden = false;
        } else {
            links.hidden = !links.hidden;
        }
        groupToggle.setAttribute('aria-expanded', String(!links.hidden));
    });

    render();
})();
