<?php
/**
 * Trek Manager - Admin Dashboard Template (Single Native Sidebar Architecture)
 *
 * Production UI for the Trek Manager management overview.
 *
 * @package Trek_Content_Manager
 * @var array $data Prepared dashboard data from Trek_Dashboard::get_dashboard_data()
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$dest_stats    = $data['dest_stats'] ?? array();
$user_stats    = $data['user_stats'] ?? array();
$review_stats  = $data['review_stats'] ?? array();
$enquiry_stats = $data['enquiry_stats'] ?? array();
$destinations  = $data['destinations'] ?? array();
$enquiries     = $data['enquiries'] ?? array();
$reviews       = $data['reviews'] ?? array();
$activities    = $data['activities'] ?? array();
$quick_stats   = $data['quick_stats'] ?? array();
$banner_image  = $data['banner_image'] ?? '';

$current_user  = wp_get_current_user();
$user_name     = $current_user->display_name ?: 'admin';
?>

<div class="wrap trek-dashboard-wrap">

    <!-- ==================================================================
         1. TOP DASHBOARD HEADER & ACTION BAR
         ================================================================== -->
    <header class="trek-dashboard-header">
        <div class="trek-header-top-row">
            <!-- Left Branding -->
            <div class="trek-header-brand-group">
                <svg class="trek-header-logo-icon" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 4L32 30H4L18 4Z" fill="#10b981" fill-opacity="0.2" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M18 12L28 30H8L18 12Z" fill="#06b6d4" fill-opacity="0.4" stroke="#06b6d4" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M13 22L18 12L23 22" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="trek-header-brand-text">
                    <div class="trek-brand-title-wrap">
                        <h1 class="trek-main-brand-title"><?php esc_html_e( 'Trek Manager', 'trek-content-manager' ); ?></h1>
                        <span class="trek-main-brand-tag"><?php esc_html_e( 'Adventure Management', 'trek-content-manager' ); ?></span>
                    </div>
                    <p class="trek-brand-desc"><?php esc_html_e( 'Manage destinations, travellers, enquiries and reviews — all in one place.', 'trek-content-manager' ); ?></p>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="trek-header-controls">
                <!-- View Website Button -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="trek-btn-view-website">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span><?php esc_html_e( 'View Website', 'trek-content-manager' ); ?></span>
                </a>

                <!-- User Profile -->
                <div class="trek-user-profile-badge">
                    <?php echo get_avatar( $current_user->ID, 32, '', esc_attr( $user_name ), array( 'class' => 'trek-user-avatar' ) ); ?>
                    <span class="trek-user-name-text"><?php echo esc_html( sprintf( __( 'Howdy, %s', 'trek-content-manager' ), $user_name ) ); ?></span>
                </div>
            </div>
        </div>

        <!-- Himalayan Mountain Panoramic Banner -->
    </header>

    <!-- ==================================================================
         2. FOUR OVERVIEW STATISTICS CARDS
         ================================================================== -->
    <section class="trek-stats-section" aria-label="<?php esc_attr_e( 'Overview Statistics', 'trek-content-manager' ); ?>">
        <div class="trek-stats-grid">

            <!-- Destinations Card -->
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination' ) ); ?>" class="trek-stat-card">
                <div class="trek-stat-card-header">
                    <div class="trek-stat-badge-label">
                        <div class="trek-stat-icon-circle blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <span class="trek-stat-title"><?php esc_html_e( 'Destinations', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-stat-arrow">›</span>
                </div>
                <div class="trek-stat-value"><?php echo esc_html( $dest_stats['total'] ); ?></div>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <span><?php echo esc_html( sprintf( __( 'Published %d', 'trek-content-manager' ), $dest_stats['published'] ) ); ?></span>
                        <span class="trek-stat-separator">|</span>
                        <span><?php echo esc_html( sprintf( __( 'Draft %d', 'trek-content-manager' ), $dest_stats['draft'] ) ); ?></span>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $dest_stats['trend'] ); ?></span>
                </div>
            </a>

            <!-- Travellers / Users Card -->
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-travellers' ) ); ?>" class="trek-stat-card">
                <div class="trek-stat-card-header">
                    <div class="trek-stat-badge-label">
                        <div class="trek-stat-icon-circle green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <span class="trek-stat-title"><?php esc_html_e( 'Travellers / Users', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-stat-arrow">›</span>
                </div>
                <div class="trek-stat-value"><?php echo esc_html( $user_stats['total'] ); ?></div>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <span><?php echo esc_html( $user_stats['sub'] ); ?></span>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $user_stats['trend'] ); ?></span>
                </div>
            </a>

            <!-- Reviews Card -->
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews' ) ); ?>" class="trek-stat-card">
                <div class="trek-stat-card-header">
                    <div class="trek-stat-badge-label">
                        <div class="trek-stat-icon-circle amber">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <span class="trek-stat-title"><?php esc_html_e( 'Reviews', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-stat-arrow">›</span>
                </div>
                <div class="trek-stat-value"><?php echo esc_html( $review_stats['total'] ); ?></div>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <span><?php echo esc_html( sprintf( __( 'Published %d', 'trek-content-manager' ), $review_stats['published'] ) ); ?></span>
                        <span class="trek-stat-separator">|</span>
                        <span><?php echo esc_html( sprintf( __( 'Pending %d', 'trek-content-manager' ), $review_stats['pending'] ) ); ?></span>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $review_stats['trend'] ); ?></span>
                </div>
            </a>

            <!-- Enquiries Card -->
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="trek-stat-card">
                <div class="trek-stat-card-header">
                    <div class="trek-stat-badge-label">
                        <div class="trek-stat-icon-circle rose">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </div>
                        <span class="trek-stat-title"><?php esc_html_e( 'Enquiries', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-stat-arrow">›</span>
                </div>
                <div class="trek-stat-value"><?php echo esc_html( $enquiry_stats['total'] ); ?></div>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <span><?php echo esc_html( sprintf( __( 'New %d', 'trek-content-manager' ), $enquiry_stats['new'] ) ); ?></span>
                        <span class="trek-stat-separator">|</span>
                        <span><?php echo esc_html( sprintf( __( 'Contacted %d', 'trek-content-manager' ), $enquiry_stats['contacted'] ) ); ?></span>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $enquiry_stats['trend'] ); ?></span>
                </div>
            </a>

        </div>
    </section>

    <!-- ==================================================================
         3. MIDDLE SECTION (Destinations, Quick Actions, Traveller Activity)
         ================================================================== -->
    <div class="trek-content-grid-row">

        <!-- Column 1: Recent Destinations Table -->
        <div class="trek-dashboard-card trek-card-destinations">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 4L32 30H4L18 4Z" stroke-linejoin="round"/><polyline points="12 2 2 7 12 12 22 7 12 2"></polyline></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Recent Destinations', 'trek-content-manager' ); ?></h2>
                </div>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination' ) ); ?>" class="trek-link-view-all">
                    <span><?php esc_html_e( 'View All', 'trek-content-manager' ); ?></span> →
                </a>
            </div>

            <div class="trek-table-container">
                <table class="trek-data-table trek-table-destinations">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Image', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Title', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Region', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Category', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Difficulty', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                            <th class="text-right"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $destinations as $d ) : 
                            $status_class = strtolower( $d['status'] );
                        ?>
                        <tr class="trek-row" data-id="<?php echo esc_attr( $d['id'] ); ?>">
                            <td class="trek-td-image">
                                <img src="<?php echo esc_url( $d['image'] ); ?>" alt="<?php echo esc_attr( $d['title'] ); ?>" class="trek-row-thumbnail" loading="lazy" />
                            </td>
                            <td class="trek-td-title">
                                <a href="<?php echo esc_url( $d['edit_url'] ); ?>" class="trek-row-title-link">
                                    <?php echo esc_html( $d['title'] ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $d['region'] ); ?></td>
                            <td><?php echo esc_html( $d['category'] ); ?></td>
                            <td><?php echo esc_html( $d['difficulty'] ); ?></td>
                            <td>
                                <span class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                    <?php echo esc_html( $d['status'] ); ?>
                                </span>
                            </td>
                            <td class="trek-td-actions text-right">
                                <a href="<?php echo esc_url( $d['view_url'] ); ?>" target="_blank" class="trek-action-icon view" title="<?php esc_attr_e( 'View', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <a href="<?php echo esc_url( $d['edit_url'] ); ?>" class="trek-action-icon edit" title="<?php esc_attr_e( 'Edit', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <?php if ( ! empty( $d['delete_url'] ) && '#' !== $d['delete_url'] ) : ?>
                                <a href="<?php echo esc_url( $d['delete_url'] ); ?>" class="trek-action-icon delete trek-btn-delete" title="<?php esc_attr_e( 'Delete', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_destination' ) ); ?>" class="trek-btn-add-destination">
                + <?php esc_html_e( 'Add New Destination', 'trek-content-manager' ); ?>
            </a>
        </div>

        <!-- Column 2: Quick Actions -->
        <div class="trek-dashboard-card trek-card-quick-actions">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Quick Actions', 'trek-content-manager' ); ?></h2>
                </div>
            </div>

            <div class="trek-quick-action-items">
                <!-- + Add Destination -->
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_destination' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <span class="trek-action-name">+ <?php esc_html_e( 'Add Destination', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- + Add Review -->
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_review' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <span class="trek-action-name">+ <?php esc_html_e( 'Add Review', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- View Enquiries -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'View Enquiries', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- Manage Reviews -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Manage Reviews', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- Import JSON -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-json-importer' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle teal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Import JSON', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- Export Enquiries -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-export-enquiries' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle slate">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Export Enquiries', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>
            </div>
        </div>

        <!-- Column 3: Traveller Activity -->
        <div class="trek-dashboard-card trek-card-activity">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Traveller Activity', 'trek-content-manager' ); ?></h2>
                </div>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="trek-link-view-all">
                    <span><?php esc_html_e( 'View All', 'trek-content-manager' ); ?></span> →
                </a>
            </div>

            <div class="trek-activity-items-list">
                <?php foreach ( $activities as $act ) : ?>
                <div class="trek-activity-row">
                    <div class="trek-activity-left">
                        <div class="trek-activity-avatar" style="background-color: <?php echo esc_attr( $act['bg_color'] ); ?>; color: <?php echo esc_attr( $act['color'] ); ?>;">
                            <?php if ( 'enquiry' === $act['type'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?php elseif ( 'review' === $act['type'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <?php elseif ( 'user' === $act['type'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php else : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <?php endif; ?>
                        </div>
                        <div class="trek-activity-info">
                            <span class="trek-activity-name"><?php echo esc_html( $act['name'] ); ?></span>
                            <span class="trek-activity-action">
                                <?php echo esc_html( $act['action'] ); ?>
                                <?php if ( ! empty( $act['target'] ) ) : ?>
                                    (<?php echo esc_html( $act['target'] ); ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div class="trek-activity-timestamp">
                        <?php echo esc_html( $act['date'] ); ?><br>
                        <?php echo esc_html( $act['time'] ); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ==================================================================
         4. BOTTOM SECTION (Recent Enquiries, Recent Reviews, Quick Stats)
         ================================================================== -->
    <div class="trek-content-grid-row bottom-row">

        <!-- Column 1: Recent Enquiries Table -->
        <div class="trek-dashboard-card trek-card-enquiries">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Recent Enquiries', 'trek-content-manager' ); ?></h2>
                </div>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="trek-link-view-all">
                    <span><?php esc_html_e( 'View All', 'trek-content-manager' ); ?></span> →
                </a>
            </div>

            <div class="trek-table-container">
                <table class="trek-data-table trek-table-enquiries">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Phone', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Trip', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                            <th class="text-right"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $enquiries as $enq ) : 
                            $status_class = sanitize_title( $enq['status'] );
                        ?>
                        <tr class="trek-row" data-id="<?php echo esc_attr( $enq['id'] ); ?>">
                            <td class="trek-td-title">
                                <a href="<?php echo esc_url( $enq['edit_url'] ); ?>" class="trek-row-title-link">
                                    <?php echo esc_html( $enq['name'] ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $enq['email'] ); ?></td>
                            <td><?php echo esc_html( $enq['phone'] ); ?></td>
                            <td><?php echo esc_html( $enq['trip'] ); ?></td>
                            <td><?php echo esc_html( $enq['date'] ); ?></td>
                            <td>
                                <span class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                    <?php echo esc_html( $enq['status'] ); ?>
                                </span>
                            </td>
                            <td class="trek-td-actions text-right">
                                <a href="<?php echo esc_url( $enq['edit_url'] ); ?>" class="trek-action-icon edit" title="<?php esc_attr_e( 'Edit Enquiry', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <?php if ( ! empty( $enq['delete_url'] ) && '#' !== $enq['delete_url'] ) : ?>
                                <a href="<?php echo esc_url( $enq['delete_url'] ); ?>" class="trek-action-icon delete trek-btn-delete" title="<?php esc_attr_e( 'Delete', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 2: Recent Reviews Table -->
        <div class="trek-dashboard-card trek-card-reviews">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Recent Reviews', 'trek-content-manager' ); ?></h2>
                </div>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews' ) ); ?>" class="trek-link-view-all">
                    <span><?php esc_html_e( 'View All', 'trek-content-manager' ); ?></span> →
                </a>
            </div>

            <div class="trek-table-container">
                <table class="trek-data-table trek-table-reviews">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Rating', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Reviewer', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Destination', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                            <th class="text-right"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $reviews as $rev ) : 
                            $rating_val = intval( $rev['rating'] );
                            $stars = str_repeat( '★', $rating_val ) . str_repeat( '☆', max( 0, 5 - $rating_val ) );
                            $status_class = sanitize_title( $rev['status'] );
                        ?>
                        <tr class="trek-row" data-id="<?php echo esc_attr( $rev['id'] ); ?>">
                            <td class="trek-td-rating">
                                <span class="trek-stars"><?php echo esc_html( $stars ); ?></span>
                            </td>
                            <td class="trek-td-title">
                                <a href="<?php echo esc_url( $rev['edit_url'] ); ?>" class="trek-row-title-link">
                                    <?php echo esc_html( $rev['reviewer'] ); ?>
                                </a>
                            </td>
                            <td><?php echo esc_html( $rev['destination'] ); ?></td>
                            <td>
                                <span class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                    <?php echo esc_html( $rev['status'] ); ?>
                                </span>
                            </td>
                            <td class="trek-td-actions text-right">
                                <a href="<?php echo esc_url( $rev['edit_url'] ); ?>" class="trek-action-icon edit" title="<?php esc_attr_e( 'Edit', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <?php if ( ! empty( $rev['delete_url'] ) && '#' !== $rev['delete_url'] ) : ?>
                                <a href="<?php echo esc_url( $rev['delete_url'] ); ?>" class="trek-action-icon delete trek-btn-delete" title="<?php esc_attr_e( 'Delete', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 3: Quick Stats -->
        <div class="trek-dashboard-card trek-card-quick-stats">
            <div class="trek-card-top-bar">
                <div class="trek-card-heading-wrap">
                    <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    <h2 class="trek-card-title"><?php esc_html_e( 'Quick Stats', 'trek-content-manager' ); ?></h2>
                </div>
            </div>

            <div class="trek-quick-stats-rows">
                <?php foreach ( $quick_stats as $qs ) : ?>
                <div class="trek-quick-stat-item">
                    <div class="trek-stat-label-wrap">
                        <div class="trek-stat-icon-dot" style="background-color: <?php echo esc_attr( $qs['bg'] ); ?>; color: <?php echo esc_attr( $qs['color'] ); ?>;">
                            <?php if ( 'location' === $qs['icon'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?php elseif ( 'user' === $qs['icon'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <?php elseif ( 'star' === $qs['icon'] ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            <?php else : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <?php endif; ?>
                        </div>
                        <span class="trek-quick-stat-name"><?php echo esc_html( $qs['label'] ); ?></span>
                    </div>
                    <div class="trek-stat-num-badge">
                        <span class="trek-quick-stat-val"><?php echo esc_html( $qs['value'] ); ?></span>
                        <span class="trek-quick-stat-badge"><?php echo esc_html( $qs['trend'] ); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>
