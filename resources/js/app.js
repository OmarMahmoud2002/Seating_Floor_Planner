import './bootstrap';

const shell = document.querySelector('.app-shell');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarCloseButtons = document.querySelectorAll('[data-sidebar-close]');

if (shell && sidebarToggle) {
    const mobileQuery = window.matchMedia('(max-width: 980px)');

    const setToggleState = (isOpen) => {
        sidebarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        sidebarToggle.setAttribute('aria-label', isOpen ? 'إغلاق القائمة' : 'فتح القائمة');
    };

    setToggleState(mobileQuery.matches ? false : !shell.classList.contains('sidebar-collapsed'));

    const closeMobileSidebar = () => {
        shell.classList.remove('sidebar-open');
        setToggleState(false);
    };

    sidebarToggle.addEventListener('click', () => {
        if (mobileQuery.matches) {
            const isOpen = shell.classList.toggle('sidebar-open');
            setToggleState(isOpen);
            return;
        }

        const isCollapsed = shell.classList.toggle('sidebar-collapsed');
        setToggleState(!isCollapsed);
    });

    sidebarCloseButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (mobileQuery.matches) {
                closeMobileSidebar();
                return;
            }

            shell.classList.add('sidebar-collapsed');
            setToggleState(false);
        });
    });

    mobileQuery.addEventListener('change', () => {
        shell.classList.remove('sidebar-open');
        setToggleState(mobileQuery.matches ? false : !shell.classList.contains('sidebar-collapsed'));
    });
}

document.querySelectorAll('[data-copy-text]').forEach((button) => {
    const originalLabel = button.dataset.copyLabel || button.textContent.trim();

    button.addEventListener('click', async () => {
        const text = button.dataset.copyText || '';

        try {
            await navigator.clipboard.writeText(text);
            button.classList.add('is-copied');
            button.textContent = 'تم نسخ الرابط';

            window.setTimeout(() => {
                button.classList.remove('is-copied');
                button.textContent = originalLabel;
            }, 1800);
        } catch (error) {
            button.textContent = 'تعذر النسخ';

            window.setTimeout(() => {
                button.textContent = originalLabel;
            }, 1800);
        }
    });
});

document.querySelectorAll('.modal-dialog').forEach((dialog) => {
    const openButtons = document.querySelectorAll(`[data-modal-open="${dialog.id}"]`);
    const closeButtons = dialog.querySelectorAll('[data-modal-close]');

    const openDialog = () => {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
            return;
        }

        dialog.setAttribute('open', 'open');
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openDialog);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    if (dialog.dataset.openOnLoad === 'true') {
        openDialog();
    }
});

const confirmDialog = document.getElementById('confirm-action-modal');

if (confirmDialog) {
    const titleNode = confirmDialog.querySelector('#confirm-action-title');
    const messageNode = confirmDialog.querySelector('#confirm-action-message');
    const acceptButton = confirmDialog.querySelector('[data-confirm-accept]');
    let pendingForm = null;

    const closeConfirmDialog = () => {
        pendingForm = null;

        if (typeof confirmDialog.close === 'function') {
            confirmDialog.close();
            return;
        }

        confirmDialog.removeAttribute('open');
    };

    document.querySelectorAll('form[data-confirm-message]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            pendingForm = form;

            if (titleNode) {
                titleNode.textContent = form.dataset.confirmTitle || 'تأكيد الحذف';
            }

            if (messageNode) {
                messageNode.textContent = form.dataset.confirmMessage || 'هل تريد تنفيذ هذا الإجراء؟';
            }

            if (acceptButton) {
                acceptButton.textContent = form.dataset.confirmAccept || 'حذف';
            }

            if (typeof confirmDialog.showModal === 'function') {
                confirmDialog.showModal();
                return;
            }

            confirmDialog.setAttribute('open', 'open');
        });
    });

    acceptButton?.addEventListener('click', () => {
        if (!pendingForm) {
            closeConfirmDialog();
            return;
        }

        pendingForm.dataset.confirmed = 'true';
        pendingForm.submit();
    });

    confirmDialog.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', closeConfirmDialog);
    });

    confirmDialog.addEventListener('click', (event) => {
        if (event.target === confirmDialog) {
            closeConfirmDialog();
        }
    });

    confirmDialog.addEventListener('close', () => {
        pendingForm = null;
    });
}
