<?php
/**
 * Trek Manager - Trip Planner Submissions Admin Template
 *
 * Dedicated, modern management interface for trip planner leads.
 * Displays all submission details, client relationship matching (same email/phone),
 * CSV export, view slide-over drawer, and instant deletion.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = Trek_Planner_Submissions::get_table_name();
Trek_Planner_Submissions::maybe_create_table();

$status_filter = sanitize_text_field( $_GET['status'] ?? 'all' );
$search_query  = sanitize_text_field( $_GET['s'] ?? '' );
$paged         = max( 1, absint( $_GET['paged'] ?? 1 ) );
$per_page      = 20;
$offset        = ( $paged - 1 ) * $per_page;

// Count by status
$counts_query = $wpdb->get_results(
    "SELECT status, COUNT(id) as count_val FROM {$table_name} GROUP BY status",
    ARRAY_A
);

$all_count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name}" );

$status_map = array(
    'all'         => $all_count,
    'new'         => 0,
    'contacted'   => 0,
    'in-progress' => 0,
    'confirmed'   => 0,
    'closed'      => 0,
);

if ( ! empty( $counts_query ) ) {
    foreach ( $counts_query as $row ) {
        $val  = strtolower( trim( $row['status'] ?: 'new' ) );
        $slug = str_replace( ' ', '-', $val );
        if ( isset( $status_map[ $slug ] ) ) {
            $status_map[ $slug ] = (int) $row['count_val'];
        } elseif ( 'new' === $slug || empty( $row['status'] ) ) {
            $status_map['new'] += (int) $row['count_val'];
        }
    }
}

// Build query
$where_clauses = array( "1=1" );
$params        = array();

if ( 'all' !== $status_filter && ! empty( $status_filter ) ) {
    $target_status   = ucwords( str_replace( '-', ' ', $status_filter ) );
    $where_clauses[] = "status = %s";
    $params[]        = $target_status;
}

if ( ! empty( $search_query ) ) {
    $like            = '%' . $wpdb->esc_like( $search_query ) . '%';
    $where_clauses[] = "(name LIKE %s OR email LIKE %s OR phone LIKE %s OR adventure LIKE %s OR country LIKE %s OR message LIKE %s OR lead_uid LIKE %s)";
    $params[]        = $like;
    $params[]        = $like;
    $params[]        = $like;
    $params[]        = $like;
    $params[]        = $like;
    $params[]        = $like;
    $params[]        = $like;
}

$where_sql = implode( ' AND ', $where_clauses );

// Total filtered rows for pagination
if ( ! empty( $params ) ) {
    $total_items = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$table_name} WHERE {$where_sql}", $params ) );
    $query_sql   = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d", array_merge( $params, array( $per_page, $offset ) ) );
} else {
    $total_items = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE {$where_sql}" );
    $query_sql   = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset );
}

$leads       = $wpdb->get_results( $query_sql, ARRAY_A );
$total_pages = ceil( $total_items / $per_page );

$admin_nonce  = wp_create_nonce( 'trek_planner_admin_nonce' );
$export_nonce = wp_create_nonce( 'trek_export_leads_nonce' );
$ajax_url     = admin_url( 'admin-ajax.php' );
$export_url   = admin_url( 'admin-post.php?action=trek_export_planner_leads_csv&status=' . rawurlencode( $status_filter ) . '&_wpnonce=' . $export_nonce );
?>

<div class="wrap ya-admin-leads-wrap">

    <!-- Header Section -->
    <div class="ya-leads-header">
        <div class="ya-leads-title-box">
            <div class="ya-leads-badge-row">
                <h1 class="ya-leads-title"><?php esc_html_e( 'Trip Planner Submissions', 'trek-content-manager' ); ?></h1>
                <span class="ya-leads-count-pill"><?php echo esc_html( $all_count ); ?> <?php esc_html_e( 'Total Leads', 'trek-content-manager' ); ?></span>
                <span class="ya-leads-live-tag">● <?php esc_html_e( 'Dedicated Database Table', 'trek-content-manager' ); ?></span>
            </div>
            <p class="ya-leads-subtitle">
                <?php esc_html_e( 'All traveller submissions from the Trip Planner Form. Shows all client details, repeat-enquiry relationship matching, and 1-click WhatsApp chat.', 'trek-content-manager' ); ?>
            </p>
        </div>
        <div class="ya-leads-actions">
            <!-- CSV Download Button -->
            <a href="<?php echo esc_url( $export_url ); ?>" class="button ya-btn-download-csv">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span><?php esc_html_e( 'Download CSV', 'trek-content-manager' ); ?></span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-trip-planner-submissions' ) ); ?>" class="button ya-btn-refresh">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                <span><?php esc_html_e( 'Refresh', 'trek-content-manager' ); ?></span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="ya-kpi-grid">
        <div class="ya-kpi-card ya-kpi-total">
            <div class="ya-kpi-info">
                <span class="ya-kpi-label"><?php esc_html_e( 'Total Leads', 'trek-content-manager' ); ?></span>
                <strong class="ya-kpi-num"><?php echo esc_html( $all_count ); ?></strong>
            </div>
            <div class="ya-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="ya-kpi-card ya-kpi-new">
            <div class="ya-kpi-info">
                <span class="ya-kpi-label"><?php esc_html_e( 'New Leads', 'trek-content-manager' ); ?></span>
                <strong class="ya-kpi-num"><?php echo esc_html( $status_map['new'] ); ?></strong>
            </div>
            <div class="ya-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
        </div>
        <div class="ya-kpi-card ya-kpi-contacted">
            <div class="ya-kpi-info">
                <span class="ya-kpi-label"><?php esc_html_e( 'Contacted / In Progress', 'trek-content-manager' ); ?></span>
                <strong class="ya-kpi-num"><?php echo esc_html( $status_map['contacted'] + $status_map['in-progress'] ); ?></strong>
            </div>
            <div class="ya-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
        </div>
        <div class="ya-kpi-card ya-kpi-confirmed">
            <div class="ya-kpi-info">
                <span class="ya-kpi-label"><?php esc_html_e( 'Confirmed Expeditions', 'trek-content-manager' ); ?></span>
                <strong class="ya-kpi-num"><?php echo esc_html( $status_map['confirmed'] ); ?></strong>
            </div>
            <div class="ya-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>
    </div>

    <!-- Toolbar: Status Filter Tabs & Search -->
    <div class="ya-leads-toolbar">
        <ul class="ya-status-tabs">
            <li class="<?php echo 'all' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'all', 'paged' => 1 ) ) ); ?>">
                    <span><?php esc_html_e( 'All', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['all'] ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'new' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'new', 'paged' => 1 ) ) ); ?>">
                    <span class="ya-status-dot dot-new"></span>
                    <span><?php esc_html_e( 'New', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['new'] ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'contacted' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'contacted', 'paged' => 1 ) ) ); ?>">
                    <span class="ya-status-dot dot-contacted"></span>
                    <span><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['contacted'] ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'in-progress' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'in-progress', 'paged' => 1 ) ) ); ?>">
                    <span class="ya-status-dot dot-in-progress"></span>
                    <span><?php esc_html_e( 'In Progress', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['in-progress'] ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'confirmed' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'confirmed', 'paged' => 1 ) ) ); ?>">
                    <span class="ya-status-dot dot-confirmed"></span>
                    <span><?php esc_html_e( 'Confirmed', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['confirmed'] ); ?></span>
                </a>
            </li>
            <li class="<?php echo 'closed' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'closed', 'paged' => 1 ) ) ); ?>">
                    <span class="ya-status-dot dot-closed"></span>
                    <span><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></span>
                    <span class="ya-pill"><?php echo esc_html( $status_map['closed'] ); ?></span>
                </a>
            </li>
        </ul>

        <form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="ya-search-form">
            <input type="hidden" name="post_type" value="trek_destination">
            <input type="hidden" name="page" value="trek-trip-planner-submissions">
            <?php if ( 'all' !== $status_filter ) : ?>
                <input type="hidden" name="status" value="<?php echo esc_attr( $status_filter ); ?>">
            <?php endif; ?>
            <div class="ya-search-input-wrap">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search by name, email, phone, adventure, country...', 'trek-content-manager' ); ?>">
                <?php if ( ! empty( $search_query ) ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( array( 's' => false ) ) ); ?>" class="ya-search-clear">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="button"><?php esc_html_e( 'Search', 'trek-content-manager' ); ?></button>
        </form>
    </div>

    <!-- Submissions Table -->
    <div class="ya-table-container">
        <table class="wp-list-table widefat fixed striped ya-leads-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 220px;"><?php esc_html_e( 'Traveller & Relationship', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 170px;"><?php esc_html_e( 'WhatsApp / Phone', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 200px;"><?php esc_html_e( 'Adventure (Experience)', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 160px;"><?php esc_html_e( 'Budget & Travellers', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 110px;"><?php esc_html_e( 'Preferred Date', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 140px;"><?php esc_html_e( 'Source & Notes', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 120px;"><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 105px;"><?php esc_html_e( 'Submitted', 'trek-content-manager' ); ?></th>
                    <th scope="col" style="width: 140px; text-align: right;"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $leads ) ) : ?>
                    <?php foreach ( $leads as $lead ) :
                        $lead_id     = (int) $lead['id'];
                        $name        = $lead['name'];
                        $email       = $lead['email'];
                        $phone       = $lead['phone'];
                        $country     = $lead['country'];
                        $adventure   = $lead['adventure'];
                        $budget      = $lead['budget'] ?: 'Discuss after team call';
                        $travellers  = $lead['travellers'] ?: '2 People';
                        $travel_date = $lead['travel_date'] ?: 'Flexible';
                        $source      = $lead['source'] ?: 'Website';
                        $message     = $lead['message'];
                        $status      = $lead['status'] ?: 'New';
                        $is_repeat   = ! empty( $lead['is_repeat_customer'] );
                        $uid         = $lead['lead_uid'] ?: ( 'PL-' . $lead_id );

                        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
                        $wa_url      = ! empty( $clean_phone ) ? 'https://wa.me/' . $clean_phone : '';

                        // Avatar Initials
                        $initials = '';
                        $parts    = explode( ' ', trim( $name ) );
                        if ( ! empty( $parts[0] ) ) $initials .= mb_substr( $parts[0], 0, 1 );
                        if ( ! empty( $parts[1] ) ) $initials .= mb_substr( $parts[1], 0, 1 );
                        $initials = strtoupper( $initials ?: 'YA' );
                    ?>
                        <tr id="ya-lead-row-<?php echo esc_attr( $lead_id ); ?>" class="ya-lead-row status-<?php echo esc_attr( sanitize_title( $status ) ); ?>">
                            
                            <!-- Traveller & Relationship Matching -->
                            <td>
                                <div class="ya-traveller-cell">
                                    <div class="ya-avatar <?php echo $is_repeat ? 'avatar-repeat' : ''; ?>">
                                        <?php echo esc_html( $initials ); ?>
                                    </div>
                                    <div class="ya-traveller-info">
                                        <div class="ya-name-row">
                                            <strong class="ya-traveller-name"><?php echo esc_html( $name ); ?></strong>
                                        </div>
                                        <?php if ( $email ) : ?>
                                            <a href="mailto:<?php echo esc_attr( $email ); ?>" class="ya-traveller-email"><?php echo esc_html( $email ); ?></a>
                                        <?php endif; ?>
                                        <div class="ya-badge-line" style="margin-top: 4px;">
                                            <?php if ( $country ) : ?>
                                                <span class="ya-country-pill" title="<?php esc_attr_e( 'Country of residence', 'trek-content-manager' ); ?>">
                                                    📍 <?php echo esc_html( $country ); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ( $is_repeat ) : ?>
                                                <span class="ya-relationship-badge badge-repeat" title="<?php esc_attr_e( 'Same email or phone number matched prior enquiry', 'trek-content-manager' ); ?>">
                                                    ★ <?php esc_html_e( 'Returning Client', 'trek-content-manager' ); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="ya-relationship-badge badge-new" title="<?php esc_attr_e( 'First recorded enquiry from this traveller', 'trek-content-manager' ); ?>">
                                                    <?php esc_html_e( 'New Client', 'trek-content-manager' ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- WhatsApp / Phone -->
                            <td>
                                <div class="ya-phone-cell">
                                    <span class="ya-phone-text"><?php echo esc_html( $phone ?: '—' ); ?></span>
                                    <?php if ( $wa_url ) : ?>
                                        <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="ya-wa-pill" title="<?php esc_attr_e( 'Open WhatsApp conversation with traveller', 'trek-content-manager' ); ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.79 14.07c-.24.68-1.4 1.25-1.93 1.33-.51.08-1.18.11-3.41-.81-2.85-1.18-4.69-4.08-4.83-4.27-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07 1-2.35.26-.28.58-.35.77-.35.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.58.83 2.03.9 2.18.07.15.12.33.02.53-.1.2-.15.32-.3.49-.15.17-.32.38-.45.51-.15.15-.31.32-.13.63.17.31.78 1.28 1.67 2.07 1.15 1.02 2.11 1.34 2.42 1.49.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.7-.15.28.11 1.77.83 2.07.98.3.15.5.22.58.35.07.13.07.75-.17 1.43z"/></svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Adventure Selected (What would you like to experience?) -->
                            <td>
                                <strong class="ya-adventure-name"><?php echo esc_html( $adventure ); ?></strong>
                                <span class="ya-lead-uid-tag">Ref: <?php echo esc_html( $uid ); ?></span>
                            </td>

                            <!-- Budget & Travellers -->
                            <td>
                                <div class="ya-meta-stack">
                                    <span class="ya-budget-badge <?php echo strpos($budget, 'team call') !== false ? 'budget-discuss' : ''; ?>">
                                        <?php echo esc_html( $budget ); ?>
                                    </span>
                                    <span class="ya-travellers-text">
                                        👥 <?php echo esc_html( $travellers ); ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Preferred Date -->
                            <td>
                                <span class="ya-date-cell">
                                    📅 <?php echo esc_html( $travel_date ); ?>
                                </span>
                            </td>

                            <!-- Source & Notes -->
                            <td>
                                <div class="ya-source-stack">
                                    <span class="ya-source-tag">Via: <?php echo esc_html( $source ); ?></span>
                                    <?php if ( ! empty( $message ) ) : ?>
                                        <div class="ya-message-preview" title="<?php echo esc_attr( $message ); ?>">
                                            "<?php echo esc_html( wp_trim_words( $message, 7, '...' ) ); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Status Dropdown (Instant AJAX Update) -->
                            <td>
                                <select class="ya-status-select status-<?php echo esc_attr( sanitize_title( $status ) ); ?>" data-lead-id="<?php echo esc_attr( $lead_id ); ?>" aria-label="<?php esc_attr_e( 'Change Lead Status', 'trek-content-manager' ); ?>">
                                    <option value="New" <?php selected( $status, 'New' ); ?>>New</option>
                                    <option value="Contacted" <?php selected( $status, 'Contacted' ); ?>>Contacted</option>
                                    <option value="In Progress" <?php selected( $status, 'In Progress' ); ?>>In Progress</option>
                                    <option value="Confirmed" <?php selected( $status, 'Confirmed' ); ?>>Confirmed</option>
                                    <option value="Closed" <?php selected( $status, 'Closed' ); ?>>Closed</option>
                                </select>
                            </td>

                            <!-- Submission Date -->
                            <td>
                                <span class="ya-time-text" title="<?php echo esc_attr( $lead['created_at'] ); ?>">
                                    <?php echo esc_html( human_time_diff( strtotime( $lead['created_at'] ), current_time( 'timestamp' ) ) . ' ago' ); ?>
                                </span>
                            </td>

                            <!-- Actions: View & Delete -->
                            <td style="text-align: right;">
                                <div class="ya-actions-cell">
                                    <button type="button" class="button button-small ya-btn-view" data-lead-id="<?php echo esc_attr( $lead_id ); ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 3px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <?php esc_html_e( 'View', 'trek-content-manager' ); ?>
                                    </button>
                                    <button type="button" class="button button-small button-link-delete ya-btn-delete" data-lead-id="<?php echo esc_attr( $lead_id ); ?>" title="<?php esc_attr_e( 'Delete enquiry', 'trek-content-manager' ); ?>">
                                        ✕
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 48px 20px;">
                            <div class="ya-empty-state">
                                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#a0b0a8" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <h3><?php esc_html_e( 'No submissions found', 'trek-content-manager' ); ?></h3>
                                <p><?php esc_html_e( 'No trip planner leads matched your current filters or search term.', 'trek-content-manager' ); ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                echo paginate_links( array(
                    'base'      => add_query_arg( 'paged', '%#%' ),
                    'format'    => '',
                    'prev_text' => '&laquo; Prev',
                    'next_text' => 'Next &raquo;',
                    'total'     => $total_pages,
                    'current'   => $paged,
                ) );
                ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Slide-Over Detail Drawer -->
<div class="ya-drawer-overlay" id="ya-lead-drawer-overlay"></div>
<div class="ya-drawer" id="ya-lead-drawer" role="dialog" aria-modal="true" aria-labelledby="ya-drawer-name">
    <div class="ya-drawer-header">
        <div class="ya-drawer-title-box">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span class="ya-drawer-badge" id="ya-drawer-status">New</span>
                <span class="ya-drawer-uid" id="ya-drawer-uid" style="font-size:12px;color:#a0b0a8;font-weight:600;">PL-00000</span>
            </div>
            <h2 id="ya-drawer-name">Traveller Name</h2>
            <span class="ya-drawer-sub" id="ya-drawer-date">Submitted on ...</span>
        </div>
        <button type="button" class="ya-drawer-close" id="ya-drawer-close" aria-label="Close drawer">✕</button>
    </div>

    <div class="ya-drawer-body">

        <!-- Client Relationship Card (Repeat vs New) -->
        <div class="ya-relationship-card" id="ya-drawer-rel-card">
            <!-- Dynamically populated via JS -->
        </div>

        <!-- Quick Action CTAs -->
        <div class="ya-drawer-ctas">
            <a href="#" id="ya-drawer-wa-btn" target="_blank" rel="noopener" class="ya-btn-wa-action">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.79 14.07c-.24.68-1.4 1.25-1.93 1.33-.51.08-1.18.11-3.41-.81-2.85-1.18-4.69-4.08-4.83-4.27-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07 1-2.35.26-.28.58-.35.77-.35.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.58.83 2.03.9 2.18.07.15.12.33.02.53-.1.2-.15.32-.3.49-.15.17-.32.38-.45.51-.15.15-.31.32-.13.63.17.31.78 1.28 1.67 2.07 1.15 1.02 2.11 1.34 2.42 1.49.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.7-.15.28.11 1.77.83 2.07.98.3.15.5.22.58.35.07.13.07.75-.17 1.43z"/></svg>
                <span><?php esc_html_e( 'Chat on WhatsApp', 'trek-content-manager' ); ?></span>
            </a>
            <a href="#" id="ya-drawer-email-btn" class="ya-btn-email-action">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <span><?php esc_html_e( 'Send Email', 'trek-content-manager' ); ?></span>
            </a>
        </div>

        <!-- Section: Trip Requirements -->
        <div class="ya-detail-group">
            <h3><?php esc_html_e( 'What would you like to experience?', 'trek-content-manager' ); ?></h3>
            <div class="ya-adventure-highlight" id="ya-drawer-adventure">—</div>
            
            <div class="ya-grid-kv" style="margin-top: 14px;">
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Estimated Budget (Optional)', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-budget">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Travellers *', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-travellers">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Preferred Date *', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-travel-date">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'How did you hear about us?', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-source">—</span>
                </div>
            </div>
        </div>

        <!-- Section: Traveller Contact Information -->
        <div class="ya-detail-group">
            <h3><?php esc_html_e( 'Traveller Details', 'trek-content-manager' ); ?></h3>
            <div class="ya-grid-kv">
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Full Name *', 'trek-content-manager' ); ?></span>
                    <strong class="ya-v" id="ya-drawer-name-val">—</strong>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Email *', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-email">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'WhatsApp Number *', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-phone">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'Country of Residence *', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-country">—</span>
                </div>
                <div class="ya-kv">
                    <span class="ya-k"><?php esc_html_e( 'IP Address', 'trek-content-manager' ); ?></span>
                    <span class="ya-v" id="ya-drawer-ip">—</span>
                </div>
            </div>
        </div>

        <!-- Section: Message / Tell us more -->
        <div class="ya-detail-group">
            <h3><?php esc_html_e( 'Tell us more *', 'trek-content-manager' ); ?></h3>
            <div class="ya-message-box" id="ya-drawer-message">
                ...
            </div>
        </div>

        <!-- Footer Actions in Drawer -->
        <div class="ya-drawer-footer">
            <button type="button" class="button button-link-delete" id="ya-drawer-delete-btn" style="color:#d63638;font-weight:600;">
                🗑️ <?php esc_html_e( 'Delete this lead', 'trek-content-manager' ); ?>
            </button>
            <span style="font-size:12px;color:#94a3b8;">Ref ID: <span id="ya-drawer-footer-id">—</span></span>
        </div>
    </div>
</div>

<style>
/* Modern Scoped Styles for Trip Planner Leads Management */
.ya-admin-leads-wrap {
    margin: 20px 20px 40px 2px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}
.ya-leads-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #ffffff;
    padding: 24px 30px;
    border-radius: 10px;
    border: 1px solid #dce2de;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}
.ya-leads-badge-row {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ya-leads-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #012422;
}
.ya-leads-count-pill {
    background: #eef2ed;
    color: #3a4f2e;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 12px;
}
.ya-leads-live-tag {
    color: #2e7d32;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.ya-leads-subtitle {
    margin: 6px 0 0;
    color: #667774;
    font-size: 13px;
}
.ya-leads-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ya-btn-download-csv {
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    height: 38px !important;
    padding: 0 16px !important;
    background: #012422 !important;
    color: #ffffff !important;
    border-color: #012422 !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 6px rgba(1, 36, 34, 0.2);
}
.ya-btn-download-csv:hover {
    background: #d1ad54 !important;
    border-color: #d1ad54 !important;
    color: #012422 !important;
}
.ya-btn-refresh {
    display: inline-flex !important;
    align-items: center;
    gap: 6px;
    height: 38px !important;
    padding: 0 14px !important;
    border-radius: 6px !important;
}

/* KPI Cards */
.ya-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}
.ya-kpi-card {
    background: #fff;
    padding: 20px 22px;
    border-radius: 10px;
    border: 1px solid #dce2de;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.ya-kpi-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    margin-bottom: 6px;
}
.ya-kpi-num {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1;
}
.ya-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: grid;
    place-items: center;
}
.ya-kpi-icon svg { width: 22px; height: 22px; }
.ya-kpi-total .ya-kpi-icon { background: #e0f2fe; color: #0284c7; }
.ya-kpi-new .ya-kpi-icon { background: #fee2e2; color: #dc2626; }
.ya-kpi-contacted .ya-kpi-icon { background: #fef3c7; color: #d97706; }
.ya-kpi-confirmed .ya-kpi-icon { background: #dcfce7; color: #16a34a; }

/* Toolbar */
.ya-leads-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    padding: 12px 18px;
    border-radius: 8px;
    border: 1px solid #dce2de;
    margin-bottom: 18px;
    gap: 14px;
    flex-wrap: wrap;
}
.ya-status-tabs {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 6px;
}
.ya-status-tabs li a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    color: #475569;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    background: #f1f5f9;
    transition: all 0.2s;
}
.ya-status-tabs li.active a,
.ya-status-tabs li a:hover {
    background: #012422;
    color: #fff;
}
.ya-pill {
    background: rgba(0,0,0,0.08);
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
}
.ya-status-tabs li.active .ya-pill {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.ya-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}
.dot-new { background: #dc2626; }
.dot-contacted { background: #d97706; }
.dot-in-progress { background: #0284c7; }
.dot-confirmed { background: #16a34a; }
.dot-closed { background: #64748b; }

.ya-search-form {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ya-search-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.ya-search-input-wrap svg {
    position: absolute;
    left: 12px;
    color: #94a3b8;
}
.ya-search-input-wrap input {
    padding-left: 34px !important;
    padding-right: 28px !important;
    height: 36px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    min-width: 270px;
    font-size: 13px;
}
.ya-search-clear {
    position: absolute;
    right: 10px;
    color: #94a3b8;
    text-decoration: none;
    font-size: 11px;
}

/* Table Design */
.ya-table-container {
    background: #fff;
    border: 1px solid #dce2de;
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.ya-leads-table {
    border: none !important;
    margin: 0 !important;
}
.ya-leads-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0 !important;
}
.ya-leads-table tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    font-size: 13px;
    border-bottom: 1px solid #f1f5f9;
}
.ya-lead-row:hover td {
    background: #fdfefd !important;
}

/* Traveller Cell */
.ya-traveller-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ya-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #012422;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.ya-avatar.avatar-repeat {
    background: #d1ad54;
    color: #012422;
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #d1ad54;
}
.ya-traveller-info {
    display: flex;
    flex-direction: column;
}
.ya-traveller-name {
    font-size: 14px;
    color: #0f172a;
}
.ya-traveller-email {
    font-size: 12px;
    color: #64748b;
    text-decoration: none;
}
.ya-traveller-email:hover {
    color: #0284c7;
    text-decoration: underline;
}
.ya-country-pill {
    font-size: 11px;
    color: #475569;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
}
.ya-relationship-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.badge-repeat {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.badge-new {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

/* WhatsApp / Phone Cell */
.ya-phone-cell {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.ya-phone-text {
    font-size: 13px;
    color: #1e293b;
    font-weight: 500;
}
.ya-wa-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #25D366;
    color: #fff !important;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 12px;
    width: fit-content;
    transition: transform 0.15s;
}
.ya-wa-pill:hover {
    transform: scale(1.05);
}

/* Adventure Cell */
.ya-adventure-name {
    color: #012422;
    font-size: 13px;
    display: block;
    line-height: 1.35;
}
.ya-lead-uid-tag {
    font-size: 11px;
    color: #94a3b8;
    display: block;
    margin-top: 2px;
}

/* Meta stack & budget */
.ya-meta-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ya-budget-badge {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}
.ya-budget-badge.budget-discuss {
    background: #fffbeb;
    color: #b45309;
    border-color: #fef3c7;
}
.ya-travellers-text {
    font-size: 12px;
    color: #64748b;
}

/* Travel Date */
.ya-date-cell {
    font-size: 12px;
    color: #1e293b;
    font-weight: 500;
}

/* Source & Notes */
.ya-source-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ya-source-tag {
    font-size: 11px;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    width: fit-content;
}
.ya-message-preview {
    font-size: 11px;
    color: #64748b;
    font-style: italic;
    max-width: 135px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Status Select */
.ya-status-select {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    cursor: pointer;
}
.ya-status-select.status-new { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
.ya-status-select.status-contacted { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
.ya-status-select.status-in-progress { background: #e0f2fe; color: #075985; border-color: #7dd3fc; }
.ya-status-select.status-confirmed { background: #dcfce7; color: #166534; border-color: #86efac; }
.ya-status-select.status-closed { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

.ya-time-text {
    font-size: 12px;
    color: #64748b;
}

/* Actions */
.ya-actions-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.ya-btn-view {
    height: 30px !important;
    padding: 0 10px !important;
    font-size: 12px !important;
    border-radius: 5px !important;
}
.ya-btn-delete {
    height: 30px !important;
    width: 30px !important;
    padding: 0 !important;
    color: #ef4444 !important;
    border-color: #fca5a5 !important;
    display: grid !important;
    place-items: center;
    border-radius: 5px !important;
}
.ya-btn-delete:hover {
    background: #fee2e2 !important;
    color: #b91c1c !important;
}

/* Slide-Over Drawer */
.ya-drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(1, 36, 34, 0.45);
    backdrop-filter: blur(3px);
    z-index: 99998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s;
}
.ya-drawer-overlay.is-active {
    opacity: 1;
    visibility: visible;
}
.ya-drawer {
    position: fixed;
    top: 0;
    right: -520px;
    width: 500px;
    max-width: 90vw;
    height: 100vh;
    background: #ffffff;
    box-shadow: -10px 0 35px rgba(0,0,0,0.15);
    z-index: 99999;
    transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
}
.ya-drawer.is-active {
    right: 0;
}
.ya-drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px 28px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.ya-drawer-title-box h2 {
    margin: 0;
    font-size: 20px;
    color: #012422;
}
.ya-drawer-sub {
    font-size: 12px;
    color: #64748b;
    display: block;
    margin-top: 4px;
}
.ya-drawer-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    text-transform: uppercase;
}
.ya-drawer-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #64748b;
    cursor: pointer;
    line-height: 1;
}
.ya-drawer-close:hover {
    color: #0f172a;
}
.ya-drawer-body {
    padding: 24px 28px;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

/* Relationship Card in Drawer */
.ya-relationship-card {
    padding: 14px 18px;
    border-radius: 8px;
}
.ya-rel-repeat {
    background: #fefce8;
    border: 1px solid #fef08a;
    color: #854d0e;
}
.ya-rel-repeat strong {
    color: #713f12;
}
.ya-rel-new {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

/* Drawer CTAs */
.ya-drawer-ctas {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.ya-btn-wa-action,
.ya-btn-email-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    transition: transform 0.15s;
}
.ya-btn-wa-action {
    background: #25D366;
    color: #fff !important;
}
.ya-btn-email-action {
    background: #012422;
    color: #fff !important;
}
.ya-btn-wa-action:hover,
.ya-btn-email-action:hover {
    transform: translateY(-2px);
}

.ya-detail-group h3 {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 6px;
}
.ya-adventure-highlight {
    font-size: 16px;
    font-weight: 700;
    color: #012422;
    background: #f8fafc;
    padding: 10px 14px;
    border-radius: 6px;
    border-left: 3px solid #d1ad54;
}
.ya-grid-kv {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 18px;
}
.ya-kv {
    display: flex;
    flex-direction: column;
}
.ya-k {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 2px;
}
.ya-v {
    font-size: 13px;
    color: #0f172a;
}
.ya-message-box {
    background: #f8fafc;
    padding: 14px 16px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-size: 13px;
    line-height: 1.6;
    color: #334155;
    max-height: 200px;
    overflow-y: auto;
}
.ya-drawer-footer {
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<script>
(function() {
    const ajaxUrl = '<?php echo esc_js( $ajax_url ); ?>';
    const adminNonce = '<?php echo esc_js( $admin_nonce ); ?>';

    // Status Changer (AJAX)
    document.querySelectorAll('.ya-status-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const leadId = this.dataset.leadId;
            const newStatus = this.value;
            const self = this;

            self.disabled = true;

            const fd = new FormData();
            fd.append('action', 'trek_update_planner_lead_status');
            fd.append('nonce', adminNonce);
            fd.append('lead_id', leadId);
            fd.append('status', newStatus);

            fetch(ajaxUrl, {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                self.disabled = false;
                if (data && data.success) {
                    // Update class
                    self.className = 'ya-status-select status-' + newStatus.toLowerCase().replace(/\s+/g, '-');
                    const row = document.getElementById('ya-lead-row-' + leadId);
                    if (row) {
                        row.className = 'ya-lead-row status-' + newStatus.toLowerCase().replace(/\s+/g, '-');
                    }
                } else {
                    alert('Could not update status. Please try again.');
                }
            })
            .catch(() => {
                self.disabled = false;
                alert('Network error while updating status.');
            });
        });
    });

    // Delete Lead (AJAX)
    function deleteLead(leadId) {
        if (!confirm('Are you sure you want to permanently delete this lead? This action cannot be undone.')) {
            return;
        }

        const fd = new FormData();
        fd.append('action', 'trek_delete_planner_lead');
        fd.append('nonce', adminNonce);
        fd.append('lead_id', leadId);

        fetch(ajaxUrl, {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                const row = document.getElementById('ya-lead-row-' + leadId);
                if (row) {
                    row.style.transition = 'opacity 0.3s, transform 0.3s';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 300);
                }
                closeDrawer();
            } else {
                alert('Could not delete lead. Please try again.');
            }
        })
        .catch(() => {
            alert('Network error while deleting lead.');
        });
    }

    document.querySelectorAll('.ya-btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            deleteLead(leadId);
        });
    });

    // Drawer Elements
    const overlay = document.getElementById('ya-lead-drawer-overlay');
    const drawer = document.getElementById('ya-lead-drawer');
    const closeBtn = document.getElementById('ya-drawer-close');
    let currentDrawerLeadId = null;

    function openDrawer(leadId) {
        currentDrawerLeadId = leadId;
        overlay.classList.add('is-active');
        drawer.classList.add('is-active');

        // Loading state
        document.getElementById('ya-drawer-name').textContent = 'Loading lead details...';
        document.getElementById('ya-drawer-message').textContent = 'Fetching details from database...';

        const fd = new FormData();
        fd.append('action', 'trek_get_planner_lead_detail');
        fd.append('nonce', adminNonce);
        fd.append('lead_id', leadId);

        fetch(ajaxUrl, {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(res => {
            if (res && res.success && res.data) {
                const d = res.data;
                document.getElementById('ya-drawer-name').textContent = d.name || 'Traveller';
                document.getElementById('ya-drawer-name-val').textContent = d.name || '—';
                document.getElementById('ya-drawer-status').textContent = d.status || 'New';
                document.getElementById('ya-drawer-uid').textContent = d.lead_uid || ('PL-' + d.id);
                document.getElementById('ya-drawer-date').textContent = 'Submitted on ' + (d.created || '');
                document.getElementById('ya-drawer-adventure').textContent = d.adventure || '—';
                document.getElementById('ya-drawer-budget').textContent = d.budget || 'Discuss after team call';
                document.getElementById('ya-drawer-travellers').textContent = d.travellers || '2 People';
                document.getElementById('ya-drawer-travel-date').textContent = d.travel_date || 'Flexible';
                document.getElementById('ya-drawer-source').textContent = d.source || 'Website';
                document.getElementById('ya-drawer-country').textContent = d.country || '—';
                document.getElementById('ya-drawer-email').textContent = d.email || '—';
                document.getElementById('ya-drawer-phone').textContent = d.phone || '—';
                document.getElementById('ya-drawer-ip').textContent = d.ip || '—';
                document.getElementById('ya-drawer-message').innerHTML = d.message || '<em>No message provided.</em>';
                document.getElementById('ya-drawer-footer-id').textContent = d.lead_uid || d.id;

                // Relationship Card (Repeat vs New)
                const relCard = document.getElementById('ya-drawer-rel-card');
                if (d.is_repeat_customer) {
                    let relHtml = `
                        <div class="ya-rel-repeat" style="padding:12px;border-radius:6px;border:1px solid #fef08a;background:#fefce8;color:#854d0e;">
                            <div style="font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;">
                                <span>★ Returning Client</span>
                                <span style="font-size:11px;background:#fef08a;padding:2px 6px;border-radius:10px;">${d.matched_count || 1} prior enquiries matched</span>
                            </div>
                            <p style="margin:4px 0 0;font-size:12px;line-height:1.5;">This traveller previously submitted enquiries with matching email (<strong>${escapeHtml(d.email)}</strong>) or phone number.</p>
                        </div>
                    `;
                    relCard.innerHTML = relHtml;
                } else {
                    relCard.innerHTML = `
                        <div class="ya-rel-new" style="padding:12px;border-radius:6px;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;">
                            <div style="font-weight:700;font-size:13px;">✓ New Client</div>
                            <p style="margin:4px 0 0;font-size:12px;">First recorded enquiry from this email & phone number.</p>
                        </div>
                    `;
                }

                // WhatsApp Button
                const waBtn = document.getElementById('ya-drawer-wa-btn');
                if (d.whatsapp_url) {
                    waBtn.href = d.whatsapp_url;
                    waBtn.style.display = 'inline-flex';
                } else {
                    waBtn.style.display = 'none';
                }

                // Email Button
                const emailBtn = document.getElementById('ya-drawer-email-btn');
                if (d.email && d.email !== '—') {
                    emailBtn.href = 'mailto:' + encodeURIComponent(d.email) + '?subject=' + encodeURIComponent('Your Nepal Expedition Inquiry: ' + d.adventure);
                    emailBtn.style.display = 'inline-flex';
                } else {
                    emailBtn.style.display = 'none';
                }
            } else {
                document.getElementById('ya-drawer-name').textContent = 'Error loading details';
            }
        })
        .catch(() => {
            document.getElementById('ya-drawer-name').textContent = 'Error connecting to server';
        });
    }

    function closeDrawer() {
        overlay.classList.remove('is-active');
        drawer.classList.remove('is-active');
        currentDrawerLeadId = null;
    }

    document.querySelectorAll('.ya-btn-view').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const leadId = this.dataset.leadId;
            openDrawer(leadId);
        });
    });

    closeBtn?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);

    // Drawer delete button
    document.getElementById('ya-drawer-delete-btn')?.addEventListener('click', function() {
        if (currentDrawerLeadId) {
            deleteLead(currentDrawerLeadId);
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        const p = document.createElement('p');
        p.appendChild(document.createTextNode(str));
        return p.innerHTML;
    }
})();
</script>
