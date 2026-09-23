<?php
/**
 * Trek Manager - Admin Dashboard Template
 *
 * Production UI for the Trek Manager management overview.
 * 100% dynamic database data, interactive search, and deep-linked management actions.
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
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-manager-dashboard' ) ); ?>" class="trek-header-brand-link" title="<?php esc_attr_e( 'Trek Manager Dashboard', 'trek-content-manager' ); ?>">
                    <svg class="trek-header-logo-icon" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 4L32 30H4L18 4Z" fill="#10b981" fill-opacity="0.2" stroke="#10b981" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M18 12L28 30H8L18 12Z" fill="#06b6d4" fill-opacity="0.4" stroke="#06b6d4" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M13 22L18 12L23 22" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <div class="trek-header-brand-text">
                    <div class="trek-brand-title-wrap">
                        <h1 class="trek-main-brand-title">
                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-manager-dashboard' ) ); ?>" style="color:inherit; text-decoration:none;">
                                <?php esc_html_e( 'Trek Manager', 'trek-content-manager' ); ?>
                            </a>
                        </h1>
                        <span class="trek-main-brand-tag"><?php esc_html_e( 'Adventure Management', 'trek-content-manager' ); ?></span>
                    </div>
                    <p class="trek-brand-desc"><?php esc_html_e( 'Manage destinations, travellers, enquiries and reviews — all in one place.', 'trek-content-manager' ); ?></p>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="trek-header-controls">
                <!-- Live Search Box -->
                <div class="trek-search-box">
                    <svg class="trek-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="trek-global-search" class="trek-search-input" placeholder="<?php esc_attr_e( 'Search destinations, enquiries, reviews...', 'trek-content-manager' ); ?>" autocomplete="off" aria-label="<?php esc_attr_e( 'Search records', 'trek-content-manager' ); ?>">
                    <div id="trek-search-dropdown" class="trek-search-dropdown trek-hidden"></div>
                </div>

                <!-- Add Destination Button -->
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_destination' ) ); ?>" class="trek-btn-header-add">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span><?php esc_html_e( 'Add Destination', 'trek-content-manager' ); ?></span>
                </a>

                <!-- View Website Button -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer" class="trek-btn-view-website">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span><?php esc_html_e( 'View Website', 'trek-content-manager' ); ?></span>
                </a>

                <!-- Switch to WordPress Core Dashboard -->
                <a href="<?php echo esc_url( admin_url( 'index.php?wp_core=1' ) ); ?>" class="trek-btn-wp-switch" title="<?php esc_attr_e( 'Switch to WordPress Dashboard', 'trek-content-manager' ); ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span><?php esc_html_e( 'WP Admin', 'trek-content-manager' ); ?></span>
                </a>

                <!-- User Profile -->
                <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="trek-user-profile-badge" title="<?php esc_attr_e( 'Edit Profile', 'trek-content-manager' ); ?>">
                    <?php echo get_avatar( $current_user->ID, 32, '', esc_attr( $user_name ), array( 'class' => 'trek-user-avatar' ) ); ?>
                    <span class="trek-user-name-text"><?php echo esc_html( sprintf( __( 'Howdy, %s', 'trek-content-manager' ), $user_name ) ); ?></span>
                </a>
            </div>
        </div>
    </header>

    <!-- ==================================================================
         2. FOUR OVERVIEW STATISTICS CARDS
         ================================================================== -->
    <section class="trek-stats-section" aria-label="<?php esc_attr_e( 'Overview Statistics', 'trek-content-manager' ); ?>">
        <div class="trek-stats-grid">

            <!-- Destinations Card -->
            <div class="trek-stat-card">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination' ) ); ?>" class="trek-stat-card-main-link">
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
                </a>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&post_status=publish' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View Published Destinations', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'Published %d', 'trek-content-manager' ), $dest_stats['published'] ) ); ?>
                        </a>
                        <span class="trek-stat-separator">|</span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&post_status=draft' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View Draft Destinations', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'Draft %d', 'trek-content-manager' ), $dest_stats['draft'] ) ); ?>
                        </a>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $dest_stats['trend'] ); ?></span>
                </div>
            </div>

            <!-- Travellers / Users Card -->
            <div class="trek-stat-card">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-travellers' ) ); ?>" class="trek-stat-card-main-link">
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
                </a>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View WordPress Users', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( $user_stats['sub'] ); ?>
                        </a>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $user_stats['trend'] ); ?></span>
                </div>
            </div>

            <!-- Reviews Card -->
            <div class="trek-stat-card">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews' ) ); ?>" class="trek-stat-card-main-link">
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
                </a>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews&status=published' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View Published Reviews', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'Published %d', 'trek-content-manager' ), $review_stats['published'] ) ); ?>
                        </a>
                        <span class="trek-stat-separator">|</span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews&status=pending' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View Pending Reviews', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'Pending %d', 'trek-content-manager' ), $review_stats['pending'] ) ); ?>
                        </a>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $review_stats['trend'] ); ?></span>
                </div>
            </div>

            <!-- Enquiries Card -->
            <div class="trek-stat-card">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="trek-stat-card-main-link">
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
                </a>
                <div class="trek-stat-footer">
                    <div class="trek-stat-breakdown">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries&status=new' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View New Enquiries', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'New %d', 'trek-content-manager' ), $enquiry_stats['new'] ) ); ?>
                        </a>
                        <span class="trek-stat-separator">|</span>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries&status=contacted' ) ); ?>" class="trek-stat-sublink" title="<?php esc_attr_e( 'View Contacted Enquiries', 'trek-content-manager' ); ?>">
                            <?php echo esc_html( sprintf( __( 'Contacted %d', 'trek-content-manager' ), $enquiry_stats['contacted'] ) ); ?>
                        </a>
                    </div>
                    <span class="trek-stat-trend-badge"><?php echo esc_html( $enquiry_stats['trend'] ); ?></span>
                </div>
            </div>

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
                        <?php if ( ! empty( $destinations ) ) : ?>
                            <?php foreach ( $destinations as $d ) : 
                                $status_class = strtolower( $d['status'] );
                            ?>
                            <tr class="trek-row" data-id="<?php echo esc_attr( $d['id'] ); ?>">
                                <td class="trek-td-image">
                                    <a href="<?php echo esc_url( $d['edit_url'] ); ?>" title="<?php esc_attr_e( 'Edit Journey', 'trek-content-manager' ); ?>">
                                        <?php if ( ! empty( $d['image'] ) ) : ?>
                                            <img src="<?php echo esc_url( $d['image'] ); ?>" alt="<?php echo esc_attr( $d['title'] ); ?>" class="trek-row-thumbnail" loading="lazy" />
                                        <?php else : ?>
                                            <div class="trek-row-thumbnail-placeholder">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="trek-td-title">
                                    <a href="<?php echo esc_url( $d['edit_url'] ); ?>" class="trek-row-title-link">
                                        <?php echo esc_html( $d['title'] ); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ( ! empty( $d['region_slug'] ) ) : ?>
                                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&trek_region=' . $d['region_slug'] ) ); ?>" class="trek-term-link">
                                            <?php echo esc_html( $d['region'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $d['region'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! empty( $d['category_slug'] ) ) : ?>
                                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&trek_category=' . $d['category_slug'] ) ); ?>" class="trek-term-link">
                                            <?php echo esc_html( $d['category'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $d['category'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! empty( $d['difficulty_slug'] ) ) : ?>
                                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&trek_difficulty=' . $d['difficulty_slug'] ) ); ?>" class="trek-term-link">
                                            <?php echo esc_html( $d['difficulty'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $d['difficulty'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&post_status=' . $status_class ) ); ?>" class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                        <?php echo esc_html( $d['status'] ); ?>
                                    </a>
                                </td>
                                <td class="trek-td-actions text-right">
                                    <a href="<?php echo esc_url( $d['view_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="trek-action-icon view" title="<?php esc_attr_e( 'View', 'trek-content-manager' ); ?>">
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
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="trek-td-empty">
                                    <div class="trek-empty-state">
                                        <svg class="trek-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 4L32 30H4L18 4Z"/><polyline points="12 2 2 7 12 12 22 7 12 2"/></svg>
                                        <p class="trek-empty-title"><?php esc_html_e( 'No destinations found', 'trek-content-manager' ); ?></p>
                                        <p class="trek-empty-desc"><?php esc_html_e( 'Create your first trekking journey or import packages from JSON.', 'trek-content-manager' ); ?></p>
                                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_destination' ) ); ?>" class="trek-btn-empty-action">
                                            + <?php esc_html_e( 'Add New Destination', 'trek-content-manager' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
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

                <!-- All Destinations -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'All Destinations', 'trek-content-manager' ); ?></span>
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
                        <div class="trek-action-icon-circle rose">
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

                <!-- Travellers CRM -->
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-travellers' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Travellers CRM', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- Trip Categories -->
                <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=trek_category&post_type=trek_destination' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle amber">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 7h10v10H7z"></path><path d="M3 3h18v18H3z"></path></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Trip Categories', 'trek-content-manager' ); ?></span>
                    </div>
                    <span class="trek-action-arrow">›</span>
                </a>

                <!-- Regions & Maps -->
                <a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=trek_region&post_type=trek_destination' ) ); ?>" class="trek-action-row">
                    <div class="trek-action-label-wrap">
                        <div class="trek-action-icon-circle teal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                        </div>
                        <span class="trek-action-name"><?php esc_html_e( 'Regions & Maps', 'trek-content-manager' ); ?></span>
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
                <?php if ( ! empty( $activities ) ) : ?>
                    <?php foreach ( $activities as $act ) : ?>
                    <a href="<?php echo esc_url( $act['url'] ?: '#' ); ?>" class="trek-activity-row trek-activity-row-link">
                        <div class="trek-activity-left">
                            <div class="trek-activity-avatar" style="background-color: <?php echo esc_attr( $act['bg_color'] ); ?>; color: <?php echo esc_attr( $act['color'] ); ?>;">
                                <?php if ( 'enquiry' === $act['type'] ) : ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <?php elseif ( 'review' === $act['type'] ) : ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <?php elseif ( 'destination' === $act['type'] ) : ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 4L32 30H4L18 4Z"/><polyline points="12 2 2 7 12 12 22 7 12 2"/></svg>
                                <?php else : ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <?php endif; ?>
                            </div>
                            <div class="trek-activity-info">
                                <span class="trek-activity-name"><?php echo esc_html( $act['name'] ); ?></span>
                                <span class="trek-activity-action">
                                    <?php echo esc_html( $act['action'] ); ?>
                                    <?php if ( ! empty( $act['target'] ) ) : ?>
                                        <strong class="trek-activity-target">(<?php echo esc_html( $act['target'] ); ?>)</strong>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="trek-activity-timestamp">
                            <?php echo esc_html( $act['date'] ); ?><br>
                            <?php echo esc_html( $act['time'] ); ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="trek-empty-state-sm">
                        <p><?php esc_html_e( 'No recent traveller activity recorded yet.', 'trek-content-manager' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ==================================================================
         4. BOTTOM SECTION (Recent Enquiries & Recent Reviews - 2 Columns)
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
                            <th><?php esc_html_e( 'Traveller', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Phone', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Source', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Tries', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'trek-content-manager' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                            <th class="text-right"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $enquiries ) ) : ?>
                            <?php foreach ( $enquiries as $enq ) : 
                                $status_class = sanitize_title( $enq['status'] );
                                $clean_ph     = ! empty( $enq['phone'] ) ? preg_replace( '/[^0-9]/', '', $enq['phone'] ) : '';
                                $tries        = intval( $enq['lookup_count'] ?? 1 );
                            ?>
                            <tr class="trek-row" data-id="<?php echo esc_attr( $enq['id'] ); ?>">
                                <td class="trek-td-title">
                                    <a href="<?php echo esc_url( $enq['edit_url'] ); ?>" class="trek-row-title-link">
                                        <?php echo esc_html( $enq['name'] ); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ( ! empty( $enq['email'] ) ) : ?>
                                        <a href="mailto:<?php echo esc_attr( $enq['email'] ); ?>" class="trek-table-link">
                                            <?php echo esc_html( $enq['email'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! empty( $enq['phone'] ) ) : ?>
                                        <a href="tel:<?php echo esc_attr( $clean_ph ); ?>" class="trek-table-link">
                                            <?php echo esc_html( $enq['phone'] ); ?>
                                        </a>
                                        <?php if ( ! empty( $clean_ph ) ) : ?>
                                            <a href="https://wa.me/<?php echo esc_attr( $clean_ph ); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'WhatsApp', 'trek-content-manager' ); ?>" class="trek-wa-icon">💬</a>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="trek-badge-source"><?php echo esc_html( $enq['source'] ?? __( 'Website', 'trek-content-manager' ) ); ?></span>
                                </td>
                                <td>
                                    <span class="trek-badge-tries" title="<?php esc_attr_e( 'Search/Matcher attempts', 'trek-content-manager' ); ?>">
                                        <?php echo esc_html( sprintf( _n( '%d try', '%d tries', $tries, 'trek-content-manager' ), $tries ) ); ?>
                                    </span>
                                </td>
                                <td class="trek-td-datetime">
                                    <span class="trek-date-val"><?php echo esc_html( $enq['date'] ); ?></span>
                                    <?php if ( ! empty( $enq['time'] ) ) : ?>
                                        <span class="trek-time-val"><?php echo esc_html( $enq['time'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries&status=' . $status_class ) ); ?>" class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                        <?php echo esc_html( $enq['status'] ); ?>
                                    </a>
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
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="trek-td-empty">
                                    <div class="trek-empty-state">
                                        <svg class="trek-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                        <p class="trek-empty-title"><?php esc_html_e( 'No enquiries received yet', 'trek-content-manager' ); ?></p>
                                        <p class="trek-empty-desc"><?php esc_html_e( 'New booking inquiries and Traveller Matcher leads will appear here automatically.', 'trek-content-manager' ); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
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
                        <?php if ( ! empty( $reviews ) ) : ?>
                            <?php foreach ( $reviews as $rev ) : 
                                $rating_num   = ! empty( $rev['rating'] ) ? number_format( (float) $rev['rating'], 1 ) : '5.0';
                                $status_class = sanitize_title( $rev['status'] );
                            ?>
                            <tr class="trek-row" data-id="<?php echo esc_attr( $rev['id'] ); ?>">
                                <td class="trek-td-rating">
                                    <span class="trek-rating-badge" title="<?php echo esc_attr( sprintf( __( '%s out of 5 stars', 'trek-content-manager' ), $rating_num ) ); ?>">
                                        <span class="trek-rating-star">★</span>
                                        <span class="trek-rating-num"><?php echo esc_html( $rating_num ); ?></span>
                                    </span>
                                </td>
                                <td class="trek-td-title">
                                    <a href="<?php echo esc_url( $rev['edit_url'] ); ?>" class="trek-row-title-link">
                                        <?php echo esc_html( $rev['reviewer'] ); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ( ! empty( $rev['trek_id'] ) && get_post( $rev['trek_id'] ) ) : ?>
                                        <a href="<?php echo esc_url( get_edit_post_link( $rev['trek_id'] ) ); ?>" class="trek-table-link font-medium">
                                            <?php echo esc_html( $rev['destination'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <span><?php echo esc_html( $rev['destination'] ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews&status=' . $status_class ) ); ?>" class="trek-pill <?php echo esc_attr( $status_class ); ?>">
                                        <?php echo esc_html( $rev['status'] ); ?>
                                    </a>
                                </td>
                                <td class="trek-td-actions text-right">
                                    <a href="<?php echo esc_url( $rev['view_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="trek-action-icon view" title="<?php esc_attr_e( 'View', 'trek-content-manager' ); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>
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
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="trek-td-empty">
                                    <div class="trek-empty-state">
                                        <svg class="trek-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        <p class="trek-empty-title"><?php esc_html_e( 'No reviews submitted yet', 'trek-content-manager' ); ?></p>
                                        <p class="trek-empty-desc"><?php esc_html_e( 'Customer testimonials and ratings will appear here.', 'trek-content-manager' ); ?></p>
                                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_review' ) ); ?>" class="trek-btn-empty-action">
                                            + <?php esc_html_e( 'Add First Review', 'trek-content-manager' ); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ==================================================================
         5. QUICK STATS OVERVIEW SECTION (FULL WIDTH BELOW)
         ================================================================== -->
    <div class="trek-dashboard-card trek-card-quick-stats-full">
        <div class="trek-card-top-bar">
            <div class="trek-card-heading-wrap">
                <svg class="trek-card-icon green" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                <h2 class="trek-card-title"><?php esc_html_e( 'Quick Statistics Overview', 'trek-content-manager' ); ?></h2>
            </div>
            <span class="trek-quick-stats-note"><?php esc_html_e( 'Click any metric to manage records directly', 'trek-content-manager' ); ?></span>
        </div>

        <div class="trek-quick-stats-grid-4">
            <?php foreach ( $quick_stats as $qs ) : ?>
            <a href="<?php echo esc_url( $qs['url'] ?? '#' ); ?>" class="trek-quick-stat-box" title="<?php echo esc_attr( sprintf( __( 'Go to %s', 'trek-content-manager' ), $qs['label'] ) ); ?>">
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
            </a>
            <?php endforeach; ?>
        </div>
    </div>

</div>
