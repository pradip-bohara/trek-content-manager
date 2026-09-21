/**
 * Trek Manager - Admin Reviews JavaScript
 * AJAX approve, reject, and delete handlers for reviews.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // 1. Approve Review
        $(document).on('click', '.trek-btn-approve-review', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const reviewId = $row.data('id');

            $btn.prop('disabled', true);

            $.ajax({
                url: TrekReviewsData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_approve_review',
                    nonce: TrekReviewsData.nonce,
                    review_id: reviewId
                },
                success: function(res) {
                    if (res.success) {
                        $row.find('.trek-pill').removeClass('pending').addClass('published').text(res.data.status);
                        $btn.replaceWith(
                            '<button type="button" class="trek-btn-action reject trek-btn-reject-review" title="Move to Pending Moderation">' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                            '</button>'
                        );
                    } else {
                        alert(res.data.message || TrekReviewsData.i18n.error);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(TrekReviewsData.i18n.error);
                    $btn.prop('disabled', false);
                }
            });
        });

        // 2. Reject / Unpublish Review
        $(document).on('click', '.trek-btn-reject-review', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const reviewId = $row.data('id');

            $btn.prop('disabled', true);

            $.ajax({
                url: TrekReviewsData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_reject_review',
                    nonce: TrekReviewsData.nonce,
                    review_id: reviewId
                },
                success: function(res) {
                    if (res.success) {
                        $row.find('.trek-pill').removeClass('published').addClass('pending').text(res.data.status);
                        $btn.replaceWith(
                            '<button type="button" class="trek-btn-action approve trek-btn-approve-review" title="Approve & Publish">' +
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
                            '</button>'
                        );
                    } else {
                        alert(res.data.message || TrekReviewsData.i18n.error);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(TrekReviewsData.i18n.error);
                    $btn.prop('disabled', false);
                }
            });
        });

        // 3. Delete Review
        $(document).on('click', '.trek-btn-delete-review', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $row = $btn.closest('tr');
            const reviewId = $row.data('id');

            if (!confirm(TrekReviewsData.i18n.confirmDelete)) {
                return;
            }

            $row.css('opacity', '0.4');

            $.ajax({
                url: TrekReviewsData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_delete_review',
                    nonce: TrekReviewsData.nonce,
                    review_id: reviewId
                },
                success: function(res) {
                    if (res.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(res.data.message || TrekReviewsData.i18n.error);
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    alert(TrekReviewsData.i18n.error);
                    $row.css('opacity', '1');
                }
            });
        });
    });

})(jQuery);
