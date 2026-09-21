/**
 * Trek Manager - Admin Dashboard JavaScript
 * Global search, AJAX actions, and table row management.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initGlobalSearch();
        initDeleteActions();
    });

    /**
     * 1. Global Search
     */
    function initGlobalSearch() {
        const $input    = $('#trek-global-search');
        const $dropdown = $('#trek-search-dropdown');
        let searchTimer = null;

        if (!$input.length) return;

        // Instant DOM filtering across all tables
        $input.on('input', function() {
            const query = $(this).val().toLowerCase().trim();

            // Filter all table rows in the dashboard
            $('.trek-data-table tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                if (!query || text.indexOf(query) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // AJAX search dropdown for multi-entity results
            clearTimeout(searchTimer);
            if (query.length >= 2) {
                searchTimer = setTimeout(function() {
                    $.ajax({
                        url: TrekDashboardData.ajaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'trek_dashboard_search',
                            nonce: TrekDashboardData.nonce,
                            query: query
                        },
                        success: function(res) {
                            if (res.success && res.data.results && res.data.results.length > 0) {
                                let html = '';
                                res.data.results.forEach(function(item) {
                                    html += '<a href="' + item.url + '" class="trek-search-result-item">';
                                    html += '<span>' + $('<div>').text(item.title).html() + '</span>';
                                    html += '<span class="trek-search-result-type">' + item.type + '</span>';
                                    html += '</a>';
                                });
                                $dropdown.html(html).removeClass('trek-hidden');
                            } else {
                                $dropdown.html('<div style="padding:10px;font-size:12px;color:#64748b;text-align:center;">No records found</div>').removeClass('trek-hidden');
                            }
                        }
                    });
                }, 250);
            } else {
                $dropdown.addClass('trek-hidden').empty();
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.trek-search-box').length) {
                $dropdown.addClass('trek-hidden');
            }
        });
    }

    /**
     * 2. Delete Actions on Dashboard Tables
     */
    function initDeleteActions() {
        $(document).on('click', '.trek-btn-delete', function(e) {
            const $btn = $(this);
            const href = $btn.attr('href');

            // If it's a direct WordPress delete link with nonce, let standard confirmation handle it
            if (href && href !== '#' && href.indexOf('action=trash') !== -1) {
                if (!confirm(TrekDashboardData.i18n.confirmDelete)) {
                    e.preventDefault();
                }
                return;
            }

            // Otherwise AJAX delete
            e.preventDefault();
            const $row = $btn.closest('tr');
            const itemId = $row.data('id');

            if (!itemId) return;

            if (!confirm(TrekDashboardData.i18n.confirmDelete)) {
                return;
            }

            $row.css('opacity', '0.4');

            $.ajax({
                url: TrekDashboardData.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'trek_dashboard_delete_item',
                    nonce: TrekDashboardData.nonce,
                    item_id: itemId
                },
                success: function(res) {
                    if (res.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(res.data.message || TrekDashboardData.i18n.error);
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    alert(TrekDashboardData.i18n.error);
                    $row.css('opacity', '1');
                }
            });
        });
    }

})(jQuery);
