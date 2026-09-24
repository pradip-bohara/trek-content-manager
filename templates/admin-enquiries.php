<?php
/**
 * Trek Manager - Custom All Enquiries Management Template
 *
 * @package Trek_Content_Manager
 * @var WP_Query $query
 * @var string $status_filter
 * @var string $search_query
 * @var int $total_count
 * @var array $status_counts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'trek_get_initials' ) ) {
    function trek_get_initials( $name ) {
        $name = trim( wp_strip_all_tags( $name ) );
        if ( empty( $name ) ) {
            return 'TR';
        }
        $parts = preg_split( '/\s+/', $name );
        $initials = '';
        if ( isset( $parts[0] ) && ! empty( $parts[0] ) ) {
            $initials .= mb_substr( $parts[0], 0, 1 );
        }
        if ( isset( $parts[1] ) && ! empty( $parts[1] ) ) {
            $initials .= mb_substr( $parts[1], 0, 1 );
        }
        return strtoupper( $initials ?: 'TR' );
    }
}

$status_counts = isset( $status_counts ) && is_array( $status_counts ) ? $status_counts : array(
    'all'       => $total_count ?? 0,
    'new'       => 0,
    'contacted' => 0,
    'follow-up' => 0,
    'closed'    => 0,
);

$has_active_filters = ( 'all' !== $status_filter && ! empty( $status_filter ) ) || ! empty( $search_query );
$reset_url = admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' );
$current_paged = max( 1, absint( $_GET['paged'] ?? 1 ) );
$total_found   = $query->found_posts;
$total_pages   = $query->max_num_pages;
$start_item    = ( ( $current_paged - 1 ) * 20 ) + 1;
$end_item      = min( $current_paged * 20, $total_found );
?>

<div class="wrap trek-enquiries-wrap">
    <!-- Header -->
    <div class="trek-enquiries-header">
        <div class="trek-enquiries-title-group">
            <div class="trek-enquiries-badge-row">
                <h1 class="trek-enquiries-title"><?php esc_html_e( 'All Enquiries', 'trek-content-manager' ); ?></h1>
                <span class="trek-enquiries-count-pill"><?php echo esc_html( $total_count ); ?> <?php esc_html_e( 'Total Leads', 'trek-content-manager' ); ?></span>
            </div>
            <p class="trek-enquiries-subtitle"><?php esc_html_e( 'Manage traveller booking requests, lead contacts, and customer itineraries.', 'trek-content-manager' ); ?></p>
        </div>
        <div class="trek-enquiries-header-actions">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-export-enquiries' ) ); ?>" class="button trek-btn-export">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span><?php esc_html_e( 'Export CSV', 'trek-content-manager' ); ?></span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="trek-enquiries-toolbar">
        <!-- Status Tabs with real per-status counts -->
        <ul class="trek-filter-tabs" role="tablist">
            <li class="<?php echo 'all' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'all', 'paged' => 1 ) ) ); ?>">
                    <span><?php esc_html_e( 'All', 'trek-content-manager' ); ?></span>
                    <span class="trek-tab-count"><?php echo esc_html( $status_counts['all'] ?? $total_count ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'new' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'new', 'paged' => 1 ) ) ); ?>">
                    <span class="trek-status-indicator new"></span>
                    <span><?php esc_html_e( 'New', 'trek-content-manager' ); ?></span>
                    <span class="trek-tab-count"><?php echo esc_html( $status_counts['new'] ?? 0 ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'contacted' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'contacted', 'paged' => 1 ) ) ); ?>">
                    <span class="trek-status-indicator contacted"></span>
                    <span><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></span>
                    <span class="trek-tab-count"><?php echo esc_html( $status_counts['contacted'] ?? 0 ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'follow-up' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'follow-up', 'paged' => 1 ) ) ); ?>">
                    <span class="trek-status-indicator follow-up"></span>
                    <span><?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?></span>
                    <span class="trek-tab-count"><?php echo esc_html( $status_counts['follow-up'] ?? 0 ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'closed' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'closed', 'paged' => 1 ) ) ); ?>">
                    <span class="trek-status-indicator closed"></span>
                    <span><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></span>
                    <span class="trek-tab-count"><?php echo esc_html( $status_counts['closed'] ?? 0 ); ?></span>
                </a>
            </li>
        </ul>

        <!-- Right Side: Search & Clear Filter -->
        <div class="trek-toolbar-controls">
            <?php if ( $has_active_filters ) : ?>
                <a href="<?php echo esc_url( $reset_url ); ?>" class="trek-btn-clear-filter" title="<?php esc_attr_e( 'Reset all filters', 'trek-content-manager' ); ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    <span><?php esc_html_e( 'Clear Filters', 'trek-content-manager' ); ?></span>
                </a>
            <?php endif; ?>

            <!-- Search Form -->
            <form method="get" class="trek-toolbar-form">
                <input type="hidden" name="post_type" value="trek_destination" />
                <input type="hidden" name="page" value="trek-enquiries" />
                <input type="hidden" name="status" value="<?php echo esc_attr( $status_filter ); ?>" />

                <div class="trek-search-input-wrap">
                    <svg class="trek-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search by name, email, trip...', 'trek-content-manager' ); ?>" class="trek-input-search" />
                    <?php if ( ! empty( $search_query ) ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( array( 's' => '', 'paged' => 1 ) ) ); ?>" class="trek-search-clear-btn" aria-label="<?php esc_attr_e( 'Clear search', 'trek-content-manager' ); ?>">✕</a>
                    <?php endif; ?>
                    <button type="submit" class="button trek-btn-search"><?php esc_html_e( 'Search', 'trek-content-manager' ); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enquiries Table Card -->
    <div class="trek-table-card">
        <table class="trek-custom-table trek-enquiries-table">
            <thead>
                <tr>
                    <th style="min-width: 200px;"><?php esc_html_e( 'Traveller Profile', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Contact / WhatsApp', 'trek-content-manager' ); ?></th>
                    <th style="min-width: 170px;"><?php esc_html_e( 'Destination / Journey', 'trek-content-manager' ); ?></th>
                    <th class="text-center" style="width: 95px;"><?php esc_html_e( 'Travellers', 'trek-content-manager' ); ?></th>
                    <th class="text-center" style="width: 85px;"><?php esc_html_e( 'Tries', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Origin / Source', 'trek-content-manager' ); ?></th>
                    <th style="min-width: 130px;"><?php esc_html_e( 'Date Received', 'trek-content-manager' ); ?></th>
                    <th style="width: 130px;"><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                    <th class="text-right" style="width: 110px;"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $query->have_posts() ) : ?>
                    <?php while ( $query->have_posts() ) : $query->the_post(); 
                        $enq_id      = get_the_ID();
                        $trav_name   = get_the_title() ?: __( 'Guest Traveller', 'trek-content-manager' );
                        $initials    = trek_get_initials( $trav_name );
                        $email       = get_post_meta( $enq_id, '_trek_enquiry_email', true );
                        $phone       = get_post_meta( $enq_id, '_trek_enquiry_phone', true );
                        $status      = get_post_meta( $enq_id, '_trek_enquiry_status', true ) ?: 'New';
                        $status_slug = sanitize_title( $status );
                        $trek_id     = get_post_meta( $enq_id, '_trek_enquiry_trek_id', true );
                        $trip_title  = get_post_meta( $enq_id, '_trek_enquiry_trek_title', true );
                        if ( ! $trip_title && $trek_id ) {
                            $trip_title = get_the_title( $trek_id );
                        }
                        $travellers  = get_post_meta( $enq_id, '_trek_enquiry_travellers', true ) ?: ( get_post_meta( $enq_id, '_trek_enquiry_count', true ) ?: '1' );
                        $tries       = intval( get_post_meta( $enq_id, '_trek_enquiry_lookup_count', true ) ?: 1 );
                        $source      = get_post_meta( $enq_id, '_trek_enquiry_source', true ) ?: ( get_post_meta( $enq_id, '_trek_enquiry_type', true ) ?: 'Website' );
                        $country     = get_post_meta( $enq_id, '_trek_enquiry_country', true ) ?: ( get_post_meta( $enq_id, '_trek_enquiry_region', true ) ?: '' );
                        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
                        $time_ago    = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'trek-content-manager' );
                    ?>
                    <tr class="trek-enquiry-row" data-id="<?php echo esc_attr( $enq_id ); ?>">
                        <!-- 1. Traveller Profile (Avatar + Name + Email) -->
                        <td>
                            <div class="trek-traveller-cell">
                                <span class="trek-avatar-circle" title="<?php echo esc_attr( $trav_name ); ?>">
                                    <?php echo esc_html( $initials ); ?>
                                </span>
                                <div class="trek-traveller-info">
                                    <a href="#" class="trek-traveller-name trek-btn-view-detail" data-id="<?php echo esc_attr( $enq_id ); ?>">
                                        <?php echo esc_html( $trav_name ); ?>
                                    </a>
                                    <?php if ( $email ) : ?>
                                        <a href="mailto:<?php echo esc_attr( $email ); ?>" class="trek-traveller-email" title="<?php esc_attr_e( 'Send email', 'trek-content-manager' ); ?>">
                                            <?php echo esc_html( $email ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="trek-cell-empty">—</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Phone / WhatsApp -->
                        <td>
                            <?php if ( $phone ) : ?>
                                <div class="trek-phone-cell">
                                    <a href="tel:<?php echo esc_attr( $phone ); ?>" class="trek-phone-link"><?php echo esc_html( $phone ); ?></a>
                                    <?php if ( ! empty( $clean_phone ) ) : ?>
                                        <a href="https://wa.me/<?php echo esc_attr( $clean_phone ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Chat on WhatsApp', 'trek-content-manager' ); ?>" class="trek-wa-badge">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <span class="trek-cell-empty">—</span>
                            <?php endif; ?>
                        </td>

                        <!-- 3. Destination / Journey -->
                        <td>
                            <div class="trek-trip-cell">
                                <?php if ( $trek_id && get_post( $trek_id ) ) : ?>
                                    <a href="<?php echo esc_url( get_edit_post_link( $trek_id ) ); ?>" class="trek-trip-title font-medium" title="<?php esc_attr_e( 'View Trek in WordPress', 'trek-content-manager' ); ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="trek-trip-icon"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <span><?php echo esc_html( $trip_title ?: get_the_title( $trek_id ) ); ?></span>
                                    </a>
                                <?php else : ?>
                                    <span class="trek-trip-title">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="trek-trip-icon"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <span><?php echo esc_html( $trip_title ?: __( 'General Enquiry', 'trek-content-manager' ) ); ?></span>
                                    </span>
                                <?php endif; ?>
                                <?php if ( ! empty( $country ) ) : ?>
                                    <span class="trek-trip-country"><?php echo esc_html( $country ); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 4. Travellers Count ("count") -->
                        <td class="text-center">
                            <span class="trek-travellers-badge" title="<?php esc_attr_e( 'Number of Travellers', 'trek-content-manager' ); ?>">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span><?php echo esc_html( $travellers ); ?></span>
                            </span>
                        </td>

                        <!-- 5. Search Tries / Attempts ("tries") -->
                        <td class="text-center">
                            <span class="trek-badge-tries" title="<?php esc_attr_e( 'Search/Matcher attempts', 'trek-content-manager' ); ?>">
                                <?php echo esc_html( sprintf( _n( '%d try', '%d tries', $tries, 'trek-content-manager' ), $tries ) ); ?>
                            </span>
                        </td>

                        <!-- 6. Origin / Source -->
                        <td>
                            <span class="trek-source-badge <?php echo esc_attr( sanitize_title( $source ) ); ?>">
                                <?php echo esc_html( $source ); ?>
                            </span>
                        </td>

                        <!-- 7. Date Received ("date") -->
                        <td>
                            <div class="trek-date-cell">
                                <span class="trek-date-main"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
                                <span class="trek-date-ago"><?php echo esc_html( $time_ago ); ?></span>
                            </div>
                        </td>

                        <!-- 8. Status ("clears sowin makes") -->
                        <td>
                            <select class="trek-select-status trek-status-pill <?php echo esc_attr( $status_slug ); ?>" data-id="<?php echo esc_attr( $enq_id ); ?>">
                                <option value="New" <?php selected( $status, 'New' ); ?>><?php esc_html_e( 'New', 'trek-content-manager' ); ?></option>
                                <option value="Contacted" <?php selected( $status, 'Contacted' ); ?>><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></option>
                                <option value="Follow Up" <?php selected( $status, 'Follow Up' ); ?>><?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?></option>
                                <option value="Closed" <?php selected( $status, 'Closed' ); ?>><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></option>
                            </select>
                        </td>

                        <!-- 9. Actions -->
                        <td class="trek-td-actions text-right">
                            <button type="button" class="trek-btn-action view trek-btn-view-detail" data-id="<?php echo esc_attr( $enq_id ); ?>" title="<?php esc_attr_e( 'View Details in Drawer', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>

                            <a href="<?php echo esc_url( get_edit_post_link( $enq_id ) ); ?>" class="trek-btn-action edit" title="<?php esc_attr_e( 'Edit in WordPress', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>

                            <button type="button" class="trek-btn-action delete trek-btn-delete-enquiry" data-id="<?php echo esc_attr( $enq_id ); ?>" title="<?php esc_attr_e( 'Delete Enquiry', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" class="trek-empty-state-cell">
                            <div class="trek-empty-state-box">
                                <div class="trek-empty-icon">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                                </div>
                                <h3 class="trek-empty-title"><?php esc_html_e( 'No enquiries found', 'trek-content-manager' ); ?></h3>
                                <p class="trek-empty-desc"><?php esc_html_e( 'There are no enquiry leads matching your selected status filter or search keywords.', 'trek-content-manager' ); ?></p>
                                <?php if ( $has_active_filters ) : ?>
                                    <a href="<?php echo esc_url( $reset_url ); ?>" class="button button-primary trek-btn-reset-empty"><?php esc_html_e( 'Reset Filters', 'trek-content-manager' ); ?></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Table Pagination Footer -->
        <?php if ( $total_found > 0 ) : ?>
            <div class="trek-table-footer">
                <div class="trek-table-summary">
                    <?php
                    /* translators: 1: start item number, 2: end item number, 3: total number of items */
                    printf( esc_html__( 'Showing %1$d–%2$d of %3$d enquiries', 'trek-content-manager' ), (int) $start_item, (int) $end_item, (int) $total_found );
                    ?>
                </div>

                <?php if ( $total_pages > 1 ) : ?>
                    <div class="trek-pagination">
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '&laquo; ' . __( 'Prev', 'trek-content-manager' ),
                            'next_text' => __( 'Next', 'trek-content-manager' ) . ' &raquo;',
                            'total'     => $total_pages,
                            'current'   => $current_paged,
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Enhanced Slide-over Detail Drawer & Backdrop ("backforep mak better view") -->
    <div id="trek-enquiry-backdrop" class="trek-drawer-backdrop trek-hidden" aria-hidden="true"></div>
    <div id="trek-enquiry-drawer" class="trek-drawer trek-hidden" role="dialog" aria-modal="true" aria-labelledby="drawer-traveller-name">
        <!-- Top Drawer Header -->
        <div class="trek-drawer-header">
            <div class="trek-drawer-header-left">
                <span class="trek-drawer-avatar" id="drawer-avatar">TR</span>
                <div class="trek-drawer-title-meta">
                    <h2 class="trek-drawer-title" id="drawer-traveller-name"><?php esc_html_e( 'Enquiry Details', 'trek-content-manager' ); ?></h2>
                    <div class="trek-drawer-badges-row">
                        <span class="trek-drawer-id" id="drawer-enquiry-id">#0</span>
                        <span class="trek-status-pill new" id="drawer-status-pill">New</span>
                        <span class="trek-drawer-date-time" id="drawer-date"></span>
                    </div>
                </div>
            </div>
            <button type="button" id="trek-drawer-close" class="trek-drawer-close-btn" aria-label="<?php esc_attr_e( 'Close drawer', 'trek-content-manager' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <!-- Quick Action Buttons Bar -->
        <div class="trek-drawer-actions-bar">
            <a href="#" id="drawer-btn-email" class="trek-quick-btn trek-quick-btn--email" target="_blank" rel="noopener noreferrer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></span>
            </a>
            <a href="#" id="drawer-btn-wa" class="trek-quick-btn trek-quick-btn--whatsapp" target="_blank" rel="noopener noreferrer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                <span><?php esc_html_e( 'WhatsApp', 'trek-content-manager' ); ?></span>
            </a>
            <button type="button" id="drawer-btn-copy" class="trek-quick-btn trek-quick-btn--copy">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                <span id="drawer-copy-text"><?php esc_html_e( 'Copy Info', 'trek-content-manager' ); ?></span>
            </button>
            <a href="#" id="drawer-btn-edit-post" class="trek-quick-btn trek-quick-btn--edit" target="_blank" rel="noopener noreferrer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span><?php esc_html_e( 'Edit Post', 'trek-content-manager' ); ?></span>
            </a>
        </div>

        <!-- Drawer Body -->
        <div class="trek-drawer-body">
            <!-- Card 1: Traveller Contact -->
            <div class="trek-drawer-card">
                <h3 class="trek-drawer-card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span><?php esc_html_e( 'Traveller Profile', 'trek-content-manager' ); ?></span>
                </h3>
                <div class="trek-drawer-fields-grid">
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Email Address', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-email">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-phone">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Travellers Count', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-travellers">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Country / Region', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-country">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'IP Address', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-ip">—</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Journey Information -->
            <div class="trek-drawer-card">
                <h3 class="trek-drawer-card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><?php esc_html_e( 'Journey Information', 'trek-content-manager' ); ?></span>
                </h3>
                <div class="trek-drawer-fields-grid">
                    <div class="trek-drawer-field trek-drawer-field--full">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Requested Destination', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-trip">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Lead Origin / Source', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-source">—</span>
                    </div>
                    <div class="trek-drawer-field">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Search Tries / Attempts', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-tries">—</span>
                    </div>
                    <div class="trek-drawer-field" id="drawer-match-score-wrap">
                        <span class="trek-drawer-label"><?php esc_html_e( 'Match Score', 'trek-content-manager' ); ?></span>
                        <span class="trek-drawer-val" id="drawer-match-score">—</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Traveller Message / Itinerary Notes -->
            <div class="trek-drawer-card">
                <h3 class="trek-drawer-card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span><?php esc_html_e( 'Enquiry Message & Itinerary Notes', 'trek-content-manager' ); ?></span>
                </h3>
                <div class="trek-drawer-message-box" id="drawer-message"></div>
            </div>

            <!-- Card 4: Status & Management -->
            <div class="trek-drawer-card trek-drawer-card--status">
                <h3 class="trek-drawer-card-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span><?php esc_html_e( 'Status & Workflow', 'trek-content-manager' ); ?></span>
                </h3>
                <div class="trek-drawer-status-controls">
                    <select id="drawer-status-select" class="trek-select-status">
                        <option value="New"><?php esc_html_e( 'New', 'trek-content-manager' ); ?></option>
                        <option value="Contacted"><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></option>
                        <option value="Follow Up"><?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?></option>
                        <option value="Closed"><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></option>
                    </select>
                    <button type="button" id="drawer-save-status-btn" class="button button-primary">
                        <span><?php esc_html_e( 'Save Status', 'trek-content-manager' ); ?></span>
                    </button>
                    <span id="drawer-status-toast" class="trek-status-toast"></span>
                </div>
            </div>
        </div>
    </div>
</div>
