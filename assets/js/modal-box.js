(function() {
    'use strict';


    const $overlay = $('#modal-box-overlay');
    const $container = $('#modal-box-container');
    const $title = $('#modal-box-title');
    const $message = $('#modal-box-message');
    const $footer = $('#modal-box-footer');


    let currentOnConfirm = null;
    let currentOnCancel = null;

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} Escaped HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) {
            return map[m];
        });
    }

    /**
     * Close modal box
     */
    function closeModalBox() {
        $overlay.removeClass('active');
        $('body').css('overflow', '');
        

        currentOnConfirm = null;
        currentOnCancel = null;
    }

    /**
     * Handle confirm button click
     */
    function handleConfirm() {
        if (typeof currentOnConfirm === 'function') {
            currentOnConfirm();
        }
        closeModalBox();
    }

    /**
     * Handle cancel button click
     */
    function handleCancel() {
        if (typeof currentOnCancel === 'function') {
            currentOnCancel();
        }
        closeModalBox();
    }

    /**
     * Show modal box
     * @param {Object} options - Configuration object
     * @param {string} options.title - Modal title
     * @param {string} options.message - Modal message content
     * @param {string} options.type - Modal type: 'yesno' or 'acknowledge'
     * @param {Function} options.onConfirm - Callback function when OK/Confirm is clicked
     * @param {Function} [options.onCancel] - Callback function when Cancel is clicked (only for yesno type)
     */
    function showModalBox(options) {

        if (!options || typeof options !== 'object') {
            console.error('ModalBox: options object is required');
            return;
        }

        const title = options.title || '';
        const message = options.message || '';
        let type = options.type || 'acknowledge';
        const onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        const onCancel = typeof options.onCancel === 'function' ? options.onCancel : null;


        if (type !== 'yesno' && type !== 'acknowledge') {
            console.warn('ModalBox: Invalid type. Using "acknowledge" instead.');
            type = 'acknowledge';
        }


        $title.html(escapeHtml(title));
        $message.html(escapeHtml(message));


        currentOnConfirm = onConfirm;
        currentOnCancel = onCancel;


        $footer.empty();

        if (type === 'yesno') {

            const $cancelBtn = $('<button>', {
                type: 'button',
                class: 'modal-box-btn modal-box-btn-secondary',
                text: 'Cancel'
            }).on('click', handleCancel);

            const $okBtn = $('<button>', {
                type: 'button',
                class: 'modal-box-btn modal-box-btn-primary',
                text: 'OK'
            }).on('click', handleConfirm);

            $footer.append($cancelBtn, $okBtn);
        } else {

            const $okBtn = $('<button>', {
                type: 'button',
                class: 'modal-box-btn modal-box-btn-primary',
                text: 'OK'
            }).on('click', handleConfirm);

            $footer.append($okBtn);
        }


        $overlay.addClass('active');
        $('body').css('overflow', 'hidden');
    }


    $(document).ready(function() {


        $overlay.on('click', function(e) {
            if (e.target === this) {
                handleCancel();
            }
        });


        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $overlay.hasClass('active')) {
                handleCancel();
            }
        });
    });


    window.showModalBox = showModalBox;

})();
