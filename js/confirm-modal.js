/**
 * confirm-modal.js
 *
 * Shared confirmation modal for destructive actions (delete, logout).
 * Instead of the browser's ugly confirm() dialog, this shows a styled
 * modal that matches the app's light & minimal design.
 *
 * Usage:
 *   confirmAction('Are you sure?', function() { /* do something */ });
 *   confirmAction('Delete this item?', function() { window.location.href = 'delete-url'; }, 'danger');
 *
 * The third argument (type) changes the confirm button style:
 *   'danger'  — red button (for delete actions)
 *   'warning' — blue button (for logout, other non-destructive confirmations)
 *
 * Best Practice: This JS dynamically creates the modal HTML and appends it
 * to the page body. This avoids repeating modal markup in every PHP file.
 */

(function() {
    // ── Create modal HTML and inject into the page ──────────
    var overlay = document.createElement('div');
    overlay.id = 'confirmModal';
    overlay.className = 'confirm-overlay';
    overlay.innerHTML =
        '<div class="confirm-box">' +
            '<div class="confirm-header">' +
                '<h3 id="confirmTitle">Confirm</h3>' +
                '<button class="confirm-close-btn" id="confirmCloseBtn">&times;</button>' +
            '</div>' +
            '<p class="confirm-message" id="confirmMessage">Are you sure?</p>' +
            '<div class="confirm-actions">' +
                '<button class="confirm-btn confirm-btn-cancel" id="confirmCancel">Cancel</button>' +
                '<button class="confirm-btn confirm-btn-ok" id="confirmOk">Confirm</button>' +
            '</div>' +
        '</div>';

    // Append to body once DOM is ready
    if (document.body) {
        document.body.appendChild(overlay);
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            document.body.appendChild(overlay);
        });
    }

    // ── Cache DOM references ────────────────────────────────
    var modal     = overlay;
    var titleEl   = overlay.querySelector('#confirmTitle');
    var messageEl = overlay.querySelector('#confirmMessage');
    var okBtn     = overlay.querySelector('#confirmOk');
    var cancelBtn = overlay.querySelector('#confirmCancel');
    var closeBtn  = overlay.querySelector('#confirmCloseBtn');

    // The callback to run when user clicks "Confirm"
    var onConfirmCallback = null;

    // ── Show/Hide helpers ───────────────────────────────────
    function showModal() {
        modal.classList.add('active');
    }

    function hideModal() {
        modal.classList.remove('active');
        onConfirmCallback = null;
    }

    // ── Event listeners ─────────────────────────────────────
    cancelBtn.addEventListener('click', hideModal);
    closeBtn.addEventListener('click', hideModal);

    // Close when clicking the overlay background (not the box itself)
    modal.addEventListener('click', function(e) {
        if (e.target === modal) hideModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) hideModal();
    });

    okBtn.addEventListener('click', function() {
        if (typeof onConfirmCallback === 'function') {
            onConfirmCallback();
        }
        hideModal();
    });

    // ── Public API ──────────────────────────────────────────

    /**
     * Show a confirmation modal.
     *
     * @param {string} message  — The question to display
     * @param {function} onConfirm — Callback if user clicks Confirm
     * @param {string} type — 'danger' (red button) or 'warning' (blue button, default)
     */
    window.confirmAction = function(message, onConfirm, type) {
        type = type || 'warning';

        // Set title based on action type
        titleEl.textContent = (type === 'danger') ? 'Delete Confirmation' : 'Confirm Action';
        messageEl.textContent = message;

        // Style the confirm button based on type
        okBtn.className = 'confirm-btn ' + (type === 'danger' ? 'confirm-btn-danger' : 'confirm-btn-ok');
        okBtn.textContent = (type === 'danger') ? 'Delete' : 'Confirm';

        onConfirmCallback = onConfirm;
        showModal();

        // Best Practice: Focus the cancel button so pressing Enter doesn't
        // accidentally confirm a destructive action
        cancelBtn.focus();
    };

    /**
     * Shortcut for logout confirmation — pre-fills the message.
     * Attach to logout buttons: onclick="confirmLogout()"
     */
    window.confirmLogout = function() {
        confirmAction(
            'Are you sure you want to log out?',
            function() {
                // Navigate to the logout endpoint
                window.location.href = document.querySelector('.logout-btn').getAttribute('href')
                    || '../backend/logout.php';
            },
            'warning'
        );
    };

    /**
     * Shortcut for delete confirmation.
     * Usage: confirmDelete('Delete this item?', 'item.php?delete_id=5')
     */
    window.confirmDelete = function(message, url) {
        confirmAction(
            message,
            function() { window.location.href = url; },
            'danger'
        );
    };

    /**
     * Shortcut for delete via POST form (used by user.php).
     * Usage: confirmDeletePost('Delete this user?', '../backend/user_action.php', {action:'delete', id:5})
     */
    window.confirmDeletePost = function(message, actionUrl, data) {
        confirmAction(
            message,
            function() {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;
                for (var key in data) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = data[key];
                    form.appendChild(input);
                }
                document.body.appendChild(form);
                form.submit();
            },
            'danger'
        );
    };
})();
