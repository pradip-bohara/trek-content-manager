/**
 * Trek Manager - Admin Enquiries JavaScript
 * Drawer, AJAX status update, detail fetch, copy to clipboard, and delete handlers.
 *
 * @package Trek_Content_Manager
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        let currentEnquiryId = null;
        let currentEnquiryData = null;

        // Helper: Extract initials for avatar circle
        function getInitials(name) {
            if (!name) return 'TR';
            const parts = name.trim().split(/\s+/);
            let initials = '';
            if (parts[0]) initials += parts[0].charAt(0);
            if (parts[1]) initials += parts[1].charAt(0);
            return initials.toUpperCase() || 'TR';
        }

        // Helper: Show toast in drawer
        function showToast(text, isError = false) {
            const $toast = $('#drawer-status-toast');
            $toast.text(text).css('color', isError ? '#ef4444' : '#10b981').addClass('is-visible');
            setTimeout(function() {
                $toast.removeClass('is-visible');
            }, 2500);
        }

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

                        // If table row exists, sync it
                        const $rowSelect = $('.trek-enquiry-row[data-id="' + enquiryId + '"] .trek-select-status');
                        if ($rowSelect.length && $rowSelect[0] !== $select[0]) {
                            $rowSelect.val(newStatus).removeClass('new contacted follow-up closed').addClass(slug);
                        }

                        // If drawer is open with this enquiry, update its badge
                        if (currentEnquiryId === enquiryId) {
                            $('#drawer-status-pill').text(newStatus).removeClass('new contacted follow-up closed').addClass(slug);
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

        // 2. View details / Open Slide-Over Drawer
        $(document).on('click', '.trek-btn-view-detail', function(e) {
            e.preventDefault();
            const enquiryId = $(this).data('id');
            if (!enquiryId) return;

            currentEnquiryId = enquiryId;

            // Open backdrop and drawer with smooth animation
            $('#trek-enquiry-backdrop').removeClass('trek-hidden');
            $('#trek-enquiry-drawer').removeClass('trek-hidden');
            $('body').css('overflow', 'hidden');

            // Reset drawer loading state
            $('#drawer-avatar').text('…');
            $('#drawer-traveller-name').text('Loading...');
            $('#drawer-enquiry-id').text('#' + enquiryId);
            $('#drawer-status-pill').text('Loading').removeClass('new contacted follow-up closed');
            $('#drawer-date').text('');
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
                        currentEnquiryData = d;
                        const statusSlug = d.status.toLowerCase().replace(/[\s_]+/g, '-');

                        // Header elements
                        $('#drawer-avatar').text(getInitials(d.name));
                        $('#drawer-traveller-name').text(d.name);
                        $('#drawer-enquiry-id').text('#' + d.id);
                        $('#drawer-status-pill').text(d.status).removeClass('new contacted follow-up closed').addClass(statusSlug);
                        $('#drawer-date').text(d.date);

                        // Quick action buttons
                        if (d.email && d.email !== '—') {
                            $('#drawer-btn-email').attr('href', 'mailto:' + d.email).show();
                        } else {
                            $('#drawer-btn-email').hide();
                        }

                        if (d.whatsapp_url) {
                            $('#drawer-btn-wa').attr('href', d.whatsapp_url).show();
                        } else {
                            $('#drawer-btn-wa').hide();
                        }

                        if (d.edit_url) {
                            $('#drawer-btn-edit-post').attr('href', d.edit_url).show();
                        } else {
                            $('#drawer-btn-edit-post').hide();
                        }

                        // Body Profile Card
                        $('#drawer-email').html(d.email && d.email !== '—' ? '<a href="mailto:' + d.email + '">' + d.email + '</a>' : '—');
                        $('#drawer-phone').html(d.phone && d.phone !== '—' ? '<a href="tel:' + d.phone + '">' + d.phone + '</a>' : '—');
                        $('#drawer-travellers').text(d.travellers);
                        $('#drawer-country').text(d.country);
                        $('#drawer-ip').text(d.ip);

                        // Body Journey Card
                        if (d.trek_url) {
                            $('#drawer-trip').html('<a href="' + d.trek_url + '" target="_blank" rel="noopener noreferrer" style="font-weight:600;">' + d.trip + '</a>');
                        } else {
                            $('#drawer-trip').text(d.trip);
                        }
                        $('#drawer-source').text(d.source);
                        $('#drawer-tries').text(d.tries || '1 try');

                        if (d.match_score) {
                            $('#drawer-match-score').text(d.match_score + ' pts');
                            $('#drawer-match-score-wrap').show();
                        } else {
                            $('#drawer-match-score-wrap').hide();
                        }

                        // Body Message & Status Card
                        $('#drawer-message').html(d.message);
                        $('#drawer-status-select').val(d.status).removeClass('new contacted follow-up closed').addClass(statusSlug);
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

        // 3. Quick Copy Info Button
        $('#drawer-btn-copy').on('click', function(e) {
            e.preventDefault();
            if (!currentEnquiryData) return;

            const d = currentEnquiryData;
            const summary = [
                'TREK ENQUIRY #' + d.id,
                '--------------------------------',
                'Name:       ' + d.name,
                'Email:      ' + d.email,
                'Phone:      ' + d.phone,
                'Trip:       ' + d.trip,
                'Travellers: ' + d.travellers,
                'Tries:      ' + (d.tries || '1 try'),
                'Country:    ' + d.country,
                'Source:     ' + d.source,
                'Status:     ' + d.status,
                'Date:       ' + d.date,
                '--------------------------------',
                'Message / Notes:',
                $('<div>').html(d.message).text().trim()
            ].join('\n');

            navigator.clipboard.writeText(summary).then(function() {
                const $btn = $('#drawer-btn-copy');
                const originalHtml = $btn.html();
                $btn.html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> <span style="color:#10b981;">Copied!</span>');
                showToast('Enquiry details copied to clipboard!');
                setTimeout(function() {
                    $btn.html(originalHtml);
                }, 2000);
            }).catch(function() {
                showToast('Failed to copy', true);
            });
        });

        // 4. Save status inside drawer
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
                        $('#drawer-status-pill').text(newStatus).removeClass('new contacted follow-up closed').addClass(slug);

                        // Sync in table row
                        const $rowSelect = $('.trek-enquiry-row[data-id="' + currentEnquiryId + '"] .trek-select-status');
                        if ($rowSelect.length) {
                            $rowSelect.val(newStatus).removeClass('new contacted follow-up closed').addClass(slug);
                        }

                        if (currentEnquiryData) {
                            currentEnquiryData.status = newStatus;
                        }

                        showToast('Status updated to ' + newStatus);
                    } else {
                        showToast(res.data.message || TrekEnquiriesData.i18n.error, true);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Save Status');
                    showToast(TrekEnquiriesData.i18n.error, true);
                }
            });
        });

        // 5. Close drawer
        function closeDrawer() {
            $('#trek-enquiry-backdrop').addClass('trek-hidden');
            $('#trek-enquiry-drawer').addClass('trek-hidden');
            $('body').css('overflow', '');
            currentEnquiryId = null;
            currentEnquiryData = null;
        }

        $('#trek-drawer-close, #trek-enquiry-backdrop').on('click', closeDrawer);

        // ESC key closes drawer
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && !$('#trek-enquiry-drawer').hasClass('trek-hidden')) {
                closeDrawer();
            }
        });

        // 6. Delete enquiry
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
                        $row.fadeOut(250, function() {
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
