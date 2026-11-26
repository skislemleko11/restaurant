(() => {
    const adminLinks = document.getElementById('admin-menu-links');
    if (!adminLinks) {
        return;
    }

    fetch('/admin/menu', {
        headers: { Accept: 'text/html' },
        credentials: 'same-origin'
    }).then((response) => {
        if (response.ok) {
            adminLinks.hidden = false;
        }
    }).catch(() => {
        adminLinks.hidden = true;
    });
})();
