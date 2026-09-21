/**
 * Trek Manager - Admin Enquiries JavaScript
 * Drawer, AJAX status update, detail fetch, and delete handlers.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        let currentEnquiryId = null;

        // 1. Table row status dropdown change
        $(document).on('change', '.trek-select-status', function() {
            const $select = $(this);
            const enquiryId = $select.data('id') || currentEnquiryId;
            const newStatus = $select.val();

            if (!enquiryId) return;

            $select.prop('disabled', true);

            $.ajax({
                url: TrekEnquiriesData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_update_enquiry_status',
                    nonce: TrekEnquiriesData.nonce,
                    enquiry_id: enquiryId,
                    status: newStatus
                },
                success: function(res) {
                    $select.prop('disabled', false);
                    if (res.success) {
                        const slug = newStatus.toLowerCase().replace(/[\s_]+/g, '-');
                        $select.removeClass('new contacted follow-up closed').addClass(slug);

                        // If table row exists, update it too
                        const $rowSelect = $('.trek-enquiry-row[data-id="' + enquiryId + '"] .trek-select-status');
                        if ($rowSelect.length && $rowSelect[0] !== $select[0]) {
                            $rowSelect.val(newStatus).removeClass('new contacted follow-up closed').addClass(slug);
                        }
                    } else {
                        alert(res.data.message || TrekEnquiriesData.i18n.error);
                    }
                },
                error: function() {
                    $select.prop('disabled', false);
                    alert(TrekEnquiriesData.i18n.error);
                }
            });
        });

        // 2. View details / Open Drawer
        $(document).on('click', '.trek-btn-view-detail', function(e) {
            e.preventDefault();
            const enquiryId = $(this).data('id');
            if (!enquiryId) return;

            currentEnquiryId = enquiryId;

            // Open backdrop and drawer with loading state
            $('#trek-enquiry-backdrop').removeClass('trek-hidden');
            $('#trek-enquiry-drawer').removeClass('trek-hidden');
            $('#drawer-traveller-name').text('Loading...');
            $('#drawer-message').html('<p style="color:#64748b;">Loading enquiry details...</p>');

            $.ajax({
                url: TrekEnquiriesData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_get_enquiry_detail',
                    nonce: TrekEnquiriesData.nonce,
                    enquiry_id: enquiryId
                },
                success: function(res) {
                    if (res.success && res.data) {
                        const d = res.data;
                        $('#drawer-traveller-name').text(d.name);
                        $('#drawer-trip').text(d.trip);
                        $('#drawer-email').html(d.email !== '—' ? '<a href="mailto:' + d.email + '">' + d.email + '</a>' : '—');
                        $('#drawer-phone').text(d.phone);
                        $('#drawer-travellers').text(d.travellers);
                        $('#drawer-source').text(d.source);
                        $('#drawer-date').text(d.date);
                        $('#drawer-status-select').val(d.status);
                        $('#drawer-message').html(d.message);
                    } else {
                        alert(res.data.message || TrekEnquiriesData.i18n.error);
                        closeDrawer();
                    }
                },
                error: function() {
                    alert(TrekEnquiriesData.i18n.error);
                    closeDrawer();
                }
            });
        });

        // 3. Save status inside drawer
        $('#drawer-save-status-btn').on('click', function() {
            if (!currentEnquiryId) return;
            const newStatus = $('#drawer-status-select').val();
            const $btn = $(this);

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: TrekEnquiriesData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_update_enquiry_status',
                    nonce: TrekEnquiriesData.nonce,
                    enquiry_id: currentEnquiryId,
                    status: newStatus
                },
                success: function(res) {
                    $btn.prop('disabled', false).text('Save Status');
                    if (res.success) {
                        const slug = newStatus.toLowerCase().replace(/[\s_]+/g, '-');
                        const $rowSelect = $('.trek-enquiry-row[data-id="' + currentEnquiryId + '"] .trek-select-status');
                        if ($rowSelect.length) {
                            $rowSelect.val(newStatus).removeClass('new contacted follow-up closed').addClass(slug);
                        }
                        alert('Status updated successfully.');
                    } else {
                        alert(res.data.message || TrekEnquiriesData.i18n.error);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Save Status');
                    alert(TrekEnquiriesData.i18n.error);
                }
            });
        });

        // 4. Close drawer
        function closeDrawer() {
            $('#trek-enquiry-backdrop').addClass('trek-hidden');
            $('#trek-enquiry-drawer').addClass('trek-hidden');
            currentEnquiryId = null;
        }

        $('#trek-drawer-close, #trek-enquiry-backdrop').on('click', closeDrawer);

        // 5. Delete enquiry
        $(document).on('click', '.trek-btn-delete-enquiry', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const enquiryId = $btn.data('id') || $row.data('id');

            if (!confirm(TrekEnquiriesData.i18n.confirmDelete)) {
                return;
            }

            $row.css('opacity', '0.4');

            $.ajax({
                url: TrekEnquiriesData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_delete_enquiry',
                    nonce: TrekEnquiriesData.nonce,
                    enquiry_id: enquiryId
                },
                success: function(res) {
                    if (res.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        if (currentEnquiryId === enquiryId) {
                            closeDrawer();
                        }
                    } else {
                        alert(res.data.message || TrekEnquiriesData.i18n.error);
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    alert(TrekEnquiriesData.i18n.error);
                    $row.css('opacity', '1');
                }
            });
        });
    });

})(jQuery);
