/**
 * Trek Manager - All Travellers Management Script
 * Handles table filtering, selection, inline editing, and email sending via AJAX.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        var $wrap = $('.trek-travellers-wrap');
        if (!$wrap.length) return;

        var $table = $('#trek-travellers-table');
        var $rows = $table.find('.trek-traveller-row');
        var $selectAll = $('#trek-select-all-travellers');
        var $bulkBtn = $('#trek-btn-open-bulk');
        var $selectedCount = $('#trek-selected-count');

        // Current active filter
        var currentSourceFilter = 'all';

        /* ==============================================================
           1. FILTER TABS & LIVE SEARCH
           ============================================================== */
        $('.trek-filter-tabs li').on('click', function(e) {
            e.preventDefault();
            $('.trek-filter-tabs li').removeClass('active');
            $(this).addClass('active');

            currentSourceFilter = $(this).data('filter') || 'all';
            applyFilters();
        });

        $('#trek-travellers-search').on('input', function() {
            applyFilters();
        });

        function applyFilters() {
            var searchVal = $.trim($('#trek-travellers-search').val()).toLowerCase();

            $rows.each(function() {
                var $row = $(this);
                var sources = ($row.data('sources') || '').toString();
                var name = ($row.data('name') || '').toString();
                var email = ($row.data('email') || '').toString();
                var phone = ($row.data('phone') || '').toString();

                var matchesFilter = (currentSourceFilter === 'all') || (sources.indexOf(currentSourceFilter) !== -1);
                var matchesSearch = !searchVal || (name.indexOf(searchVal) !== -1 || email.indexOf(searchVal) !== -1 || phone.indexOf(searchVal) !== -1);

                if (matchesFilter && matchesSearch) {
                    $row.show();
                } else {
                    $row.hide();
                    $row.find('.trek-traveller-checkbox').prop('checked', false);
                    $row.removeClass('is-selected');
                }
            });

            updateSelectionState();
        }

        /* ==============================================================
           2. CHECKBOX SELECTION & BULK ACTIONS
           ============================================================== */
        $selectAll.on('change', function() {
            var isChecked = $(this).is(':checked');
            $rows.filter(':visible').each(function() {
                $(this).find('.trek-traveller-checkbox').prop('checked', isChecked);
                $(this).toggleClass('is-selected', isChecked);
            });
            updateSelectionState();
        });

        $table.on('change', '.trek-traveller-checkbox', function() {
            $(this).closest('tr').toggleClass('is-selected', $(this).is(':checked'));
            updateSelectionState();
        });

        function updateSelectionState() {
            var $visibleCheckboxes = $rows.filter(':visible').find('.trek-traveller-checkbox');
            var $checked = $visibleCheckboxes.filter(':checked');
            var count = $checked.length;

            $selectedCount.text(count);

            if (count > 0) {
                $bulkBtn.prop('disabled', false);
            } else {
                $bulkBtn.prop('disabled', true);
            }

            if ($visibleCheckboxes.length > 0 && count === $visibleCheckboxes.length) {
                $selectAll.prop('checked', true).prop('indeterminate', false);
            } else if (count > 0) {
                $selectAll.prop('checked', false).prop('indeterminate', true);
            } else {
                $selectAll.prop('checked', false).prop('indeterminate', false);
            }
        }

        /* ==============================================================
           3. MODAL CONTROLS
           ============================================================== */
        $('[data-close-modal]').on('click', function() {
            closeAllModals();
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        $('.trek-modal-backdrop').on('click', function(e) {
            if ($(e.target).hasClass('trek-modal-backdrop')) {
                closeAllModals();
            }
        });

        function closeAllModals() {
            $('.trek-modal-backdrop').addClass('trek-hidden');
            $('.trek-modal-status').addClass('trek-hidden').text('').removeClass('success error');
        }

        /* ==============================================================
           4. EDIT TRAVELLER DETAILS
           ============================================================== */
        var $activeEditRow = null;

        $table.on('click', '.trek-btn-edit-traveller', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $activeEditRow = $btn.closest('tr');

            var origEmail = $btn.data('original-email') || '';
            var name = $btn.data('name') || '';
            var email = $btn.data('email') || '';
            var phone = $btn.data('phone') || '';

            $('#edit-original-email').val(origEmail);
            $('#edit-traveller-name').val(name);
            $('#edit-traveller-email').val(email);
            $('#edit-traveller-phone').val(phone);

            $('#trek-edit-status').addClass('trek-hidden').text('').removeClass('success error');
            $('#trek-edit-traveller-modal').removeClass('trek-hidden');
            $('#edit-traveller-name').focus();
        });

        $('#trek-edit-traveller-form').on('submit', function(e) {
            e.preventDefault();
            var $status = $('#trek-edit-status');
            var $submit = $('#trek-btn-save-traveller');

            $submit.prop('disabled', true).text(TrekTravellersData.i18n.sending || 'Saving...');
            $status.addClass('trek-hidden').removeClass('success error');

            $.ajax({
                url: TrekTravellersData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'trek_save_traveller',
                    nonce: TrekTravellersData.nonce,
                    original_email: $('#edit-original-email').val(),
                    name: $('#edit-traveller-name').val(),
                    email: $('#edit-traveller-email').val(),
                    phone: $('#edit-traveller-phone').val()
                },
                success: function(res) {
                    $submit.prop('disabled', false).text('Save Changes');
                    if (res.success) {
                        $status.addClass('success').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.saved);

                        // Update row in DOM
                        if ($activeEditRow && res.data.traveller) {
                            var t = res.data.traveller;
                            $activeEditRow.find('.trek-traveller-name').text(t.name);
                            $activeEditRow.find('.trek-td-email .trek-traveller-link').attr('href', 'mailto:' + t.email).text(t.email);

                            var $phoneCell = $activeEditRow.find('.trek-td-phone');
                            if (t.phone) {
                                var cleanPhone = t.phone.replace(/[^0-9+]/g, '');
                                var waPhone = cleanPhone.replace(/^\+/, '');
                                $phoneCell.html(
                                    '<div class="trek-phone-group">' +
                                    '<a href="tel:' + cleanPhone + '" class="trek-traveller-link phone">' + $('<div>').text(t.phone).html() + '</a>' +
                                    '<a href="https://wa.me/' + waPhone + '" target="_blank" class="trek-wa-icon" title="Message on WhatsApp">' +
                                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="#10b981"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>' +
                                    '</a></div>'
                                );
                            } else {
                                $phoneCell.html('<span class="trek-text-muted">No phone added</span>');
                            }

                            // Update dataset
                            $activeEditRow.data('name', t.name.toLowerCase());
                            $activeEditRow.data('email', t.email.toLowerCase());
                            $activeEditRow.data('phone', (t.phone || '').toLowerCase());

                            var $editBtn = $activeEditRow.find('.trek-btn-edit-traveller');
                            $editBtn.data('name', t.name);
                            $editBtn.data('email', t.email);
                            $editBtn.data('phone', t.phone);

                            var $emailBtn = $activeEditRow.find('.trek-btn-send-single-email');
                            $emailBtn.data('name', t.name);
                            $emailBtn.data('email', t.email);

                            var $cb = $activeEditRow.find('.trek-traveller-checkbox');
                            $cb.val(t.email);
                            $cb.data('name', t.name);
                        }

                        setTimeout(function() {
                            closeAllModals();
                        }, 1000);
                    } else {
                        $status.addClass('error').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.error);
                    }
                },
                error: function() {
                    $submit.prop('disabled', false).text('Save Changes');
                    $status.addClass('error').removeClass('trek-hidden').text(TrekTravellersData.i18n.error);
                }
            });
        });

        /* ==============================================================
           5. SEND INDIVIDUAL EMAIL
           ============================================================== */
        $table.on('click', '.trek-btn-send-single-email', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var name = $btn.data('name') || '';
            var email = $btn.data('email') || '';

            $('#single-email-to').val(email);
            $('#single-recipient-display').html('<strong>' + $('<div>').text(name).html() + '</strong> &lt;' + $('<div>').text(email).html() + '&gt;');
            $('#single-email-subject').val('');
            $('#single-email-message').val('');

            $('#trek-single-email-status').addClass('trek-hidden').text('').removeClass('success error');
            $('#trek-send-email-modal').removeClass('trek-hidden');
            $('#single-email-subject').focus();
        });

        $('#trek-send-single-email-form').on('submit', function(e) {
            e.preventDefault();
            var $status = $('#trek-single-email-status');
            var $submit = $('#trek-btn-submit-single-email');

            $submit.prop('disabled', true).text(TrekTravellersData.i18n.sending || 'Sending...');
            $status.addClass('trek-hidden').removeClass('success error');

            $.ajax({
                url: TrekTravellersData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'trek_send_traveller_email',
                    nonce: TrekTravellersData.nonce,
                    to_email: $('#single-email-to').val(),
                    subject: $('#single-email-subject').val(),
                    message: $('#single-email-message').val()
                },
                success: function(res) {
                    $submit.prop('disabled', false).html(
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Email'
                    );
                    if (res.success) {
                        $status.addClass('success').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.sent);
                        setTimeout(function() {
                            closeAllModals();
                        }, 1200);
                    } else {
                        $status.addClass('error').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.error);
                    }
                },
                error: function() {
                    $submit.prop('disabled', false).text('Send Email');
                    $status.addClass('error').removeClass('trek-hidden').text(TrekTravellersData.i18n.error);
                }
            });
        });

        /* ==============================================================
           6. SEND BULK EMAIL
           ============================================================== */
        $bulkBtn.on('click', function(e) {
            e.preventDefault();
            var $checked = $rows.filter(':visible').find('.trek-traveller-checkbox:checked');
            if (!$checked.length) {
                alert(TrekTravellersData.i18n.selectPrompt);
                return;
            }

            var tagsHtml = '';
            $checked.each(function() {
                var email = $(this).val();
                tagsHtml += '<span class="trek-recipient-chip">' + $('<div>').text(email).html() + '</span>';
            });

            $('#bulk-recipient-count').text($checked.length);
            $('#bulk-recipients-tags').html(tagsHtml);
            $('#bulk-email-subject').val('');
            $('#bulk-email-message').val('');

            $('#trek-bulk-email-status').addClass('trek-hidden').text('').removeClass('success error');
            $('#trek-bulk-email-modal').removeClass('trek-hidden');
            $('#bulk-email-subject').focus();
        });

        $('#trek-bulk-email-form').on('submit', function(e) {
            e.preventDefault();
            var $checked = $rows.filter(':visible').find('.trek-traveller-checkbox:checked');
            var emails = [];
            $checked.each(function() {
                emails.push($(this).val());
            });

            if (!emails.length) {
                alert(TrekTravellersData.i18n.selectPrompt);
                return;
            }

            var $status = $('#trek-bulk-email-status');
            var $submit = $('#trek-btn-submit-bulk-email');

            $submit.prop('disabled', true).text(TrekTravellersData.i18n.sending || 'Sending...');
            $status.addClass('trek-hidden').removeClass('success error');

            $.ajax({
                url: TrekTravellersData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'trek_bulk_send_email',
                    nonce: TrekTravellersData.nonce,
                    emails: emails,
                    subject: $('#bulk-email-subject').val(),
                    message: $('#bulk-email-message').val()
                },
                success: function(res) {
                    $submit.prop('disabled', false).html(
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Bulk Email'
                    );
                    if (res.success) {
                        $status.addClass('success').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.sent);
                        setTimeout(function() {
                            closeAllModals();
                            $selectAll.prop('checked', false);
                            $rows.find('.trek-traveller-checkbox').prop('checked', false);
                            $rows.removeClass('is-selected');
                            updateSelectionState();
                        }, 1500);
                    } else {
                        $status.addClass('error').removeClass('trek-hidden').text(res.data.message || TrekTravellersData.i18n.error);
                    }
                },
                error: function() {
                    $submit.prop('disabled', false).text('Send Bulk Email');
                    $status.addClass('error').removeClass('trek-hidden').text(TrekTravellersData.i18n.error);
                }
            });
        });

        /* ==============================================================
           7. EXPORT CSV
           ============================================================== */
        $('#trek-btn-export-travellers').on('click', function() {
            var csv = [];
            csv.push(['"Name"', '"Email"', '"Phone"', '"Sources"', '"Activity"', '"Last Active"'].join(','));

            $rows.filter(':visible').each(function() {
                var $row = $(this);
                var name = $row.find('.trek-traveller-name').text().trim().replace(/"/g, '""');
                var email = $row.find('.trek-td-email a').text().trim().replace(/"/g, '""');
                var phone = $row.find('.trek-td-phone .phone').text().trim().replace(/"/g, '""') || '';
                var sources = ($row.data('sources') || '').toString().replace(/"/g, '""');
                var activity = $row.find('.trek-activity-stats').text().trim().replace(/"/g, '""');
                var lastActive = $row.find('.trek-date-text').text().trim().replace(/"/g, '""');

                csv.push(['"' + name + '"', '"' + email + '"', '"' + phone + '"', '"' + sources + '"', '"' + activity + '"', '"' + lastActive + '"'].join(','));
            });

            var csvString = csv.join("\r\n");
            var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'trek-travellers-' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

    });

})(jQuery);
