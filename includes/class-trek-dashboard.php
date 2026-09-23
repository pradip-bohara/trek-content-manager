<?php
/**
 * Trek Manager - Admin Dashboard Controller
 *
 * Handles admin menu registration, asset enqueuing, and 100% live database querying
 * for the dedicated Trek Manager Admin Dashboard workspace.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Dashboard {

    /**
     * Hook into WordPress admin
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ), 5 );
        add_action( 'admin_menu', array( __CLASS__, 'reorder_admin_submenu' ), 999 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // AJAX handlers for dashboard interactions
        add_action( 'wp_ajax_trek_dashboard_search', array( __CLASS__, 'ajax_dashboard_search' ) );
        add_action( 'wp_ajax_trek_dashboard_delete_item', array( __CLASS__, 'ajax_delete_item' ) );

        // Default login & admin landing redirects (with native WP dashboard access preserved)
        add_filter( 'login_redirect', array( __CLASS__, 'filter_login_redirect' ), 10, 3 );
        add_action( 'admin_init', array( __CLASS__, 'handle_default_admin_redirect' ) );
        add_action( 'admin_menu', array( __CLASS__, 'customize_wp_dashboard_menu' ), 999 );
    }

    /**
     * Register the Trek Manager Dashboard submenu page.
     * We attach it under the 'edit.php?post_type=trek_destination' menu.
     */
    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'Trek Manager Dashboard', 'trek-content-manager' ),
            __( 'Dashboard', 'trek-content-manager' ),
            'edit_posts',
            'trek-manager-dashboard',
            array( __CLASS__, 'render_dashboard_page' ),
            0
        );
    }

    /**
     * Reorder submenus so that:
     * 1. Dashboard (index 0 - default click landing)
     * 2. All Destinations
     * 3. Add New Destination
     * 4. Trip Categories
     * 5. Regions
     * 6. Difficulties
     * 7. All Reviews
     * 8. All Enquiries
     * 9. Quick Guide
     * 10. Export Enquiries
     * 11. JSON Importer
     */
    public static function reorder_admin_submenu() {
        global $submenu;

        $parent = 'edit.php?post_type=trek_destination';
        if ( ! isset( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
            return;
        }

        $items = $submenu[ $parent ];
        $desired_order = array(
            'trek-manager-dashboard',
            'edit.php?post_type=trek_destination',
            'post-new.php?post_type=trek_destination',
            'edit-tags.php?taxonomy=trek_category&post_type=trek_destination',
            'edit-tags.php?taxonomy=trek_region&post_type=trek_destination',
            'edit-tags.php?taxonomy=trek_difficulty&post_type=trek_destination',
            'trek-travellers',
            'trek-reviews',
            'trek-enquiries',
            'trek-manager-guide',
            'trek-export-enquiries',
            'trek-json-importer',
        );

        $ordered = array();
        $remaining = $items;

        foreach ( $desired_order as $slug ) {
            foreach ( $remaining as $k => $item ) {
                if ( isset( $item[2] ) && $item[2] === $slug ) {
                    $ordered[] = $item;
                    unset( $remaining[ $k ] );
                    break;
                }
            }
        }

        // Append any remaining items
        foreach ( $remaining as $item ) {
            $ordered[] = $item;
        }

        $submenu[ $parent ] = array_values( $ordered );
    }

    /**
     * Keep native WordPress Dashboard accessible in the sidebar
     * while making Trek Dashboard available under the "Dashboard" menu as well.
     */
    public static function customize_wp_dashboard_menu() {
        global $submenu;

        // 1. Add Trek Dashboard under the native WordPress Dashboard menu
        add_submenu_page(
            'index.php',
            __( 'Trek Manager Dashboard', 'trek-content-manager' ),
            __( 'Trek Dashboard', 'trek-content-manager' ),
            'edit_posts',
            'edit.php?post_type=trek_destination&page=trek-manager-dashboard'
        );

        // 2. Adjust the native "Home" link to use ?wp_core=1 so clicking it opens native WP without redirecting
        if ( isset( $submenu['index.php'] ) && is_array( $submenu['index.php'] ) ) {
            foreach ( $submenu['index.php'] as &$item ) {
                if ( isset( $item[2] ) && 'index.php' === $item[2] ) {
                    $item[2] = 'index.php?wp_core=1';
                    $item[0] = __( 'WordPress Home', 'trek-content-manager' );
                }
            }
            unset( $item );
        }
    }

    /**
     * Safe login redirect: Send authorized users directly to Trek Manager Dashboard upon login.
     *
     * @param string           $redirect_to           The redirect destination URL.
     * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
     * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
     * @return string
     */
    public static function filter_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( is_wp_error( $user ) || ! is_a( $user, 'WP_User' ) ) {
            return $redirect_to;
        }

        // Only redirect users with permission to edit or manage trek content
        if ( user_can( $user, 'manage_options' ) || user_can( $user, 'edit_posts' ) ) {
            $admin_url = admin_url();
            $index_url = admin_url( 'index.php' );

            // If user did not specifically request another deep-linked admin page, send to Trek Dashboard
            if ( empty( $requested_redirect_to ) || $admin_url === $requested_redirect_to || $index_url === $requested_redirect_to ) {
                return admin_url( 'edit.php?post_type=trek_destination&page=trek-manager-dashboard' );
            }
        }

        return $redirect_to;
    }

    /**
     * Default Admin Landing Redirect:
     * When an administrator or editor accesses /wp-admin/ or /wp-admin/index.php directly,
     * route them directly to the Trek Content Manager Dashboard.
     *
     * Native WordPress dashboard remains fully accessible via:
     * /wp-admin/index.php?wp_core=1 or by clicking "WordPress Home" in the sidebar.
     */
    public static function handle_default_admin_redirect() {
        global $pagenow;

        // 1. NEVER redirect AJAX, Cron, REST API, or WP-CLI requests
        if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }
        if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
            return;
        }

        // 2. Allow native WordPress dashboard if specifically requested via ?wp_core=1
        if ( isset( $_GET['wp_core'] ) && '1' === (string) $_GET['wp_core'] ) {
            return;
        }

        // 3. Only intercept visits to the root admin index.php without a sub-page parameter
        if ( 'index.php' === $pagenow && empty( $_GET['page'] ) ) {
            if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
                wp_safe_redirect( admin_url( 'edit.php?post_type=trek_destination&page=trek-manager-dashboard' ) );
                exit;
            }
        }
    }

    /**
     * Enqueue CSS and JS only on the Trek Manager Dashboard screen.
     *
     * @param string $hook The current admin page hook.
     */
    public static function enqueue_assets( $hook ) {
        $is_dashboard = ( 'trek_destination_page_trek-manager-dashboard' === $hook ) || ( isset( $_GET['page'] ) && 'trek-manager-dashboard' === $_GET['page'] );
        if ( ! $is_dashboard ) {
            return;
        }

        // Google Fonts: Inter & Caveat
        wp_enqueue_style(
            'trek-dashboard-google-fonts',
            'https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Inter:wght@400;500;600;700;800&display=swap',
            array(),
            null
        );

        // Trek Manager Admin Dashboard Scoped Stylesheet
        wp_enqueue_style(
            'trek-admin-dashboard-css',
            TREK_MANAGER_PLUGIN_URL . 'assets/css/trek-admin-dashboard.css',
            array(),
            TREK_MANAGER_VERSION
        );

        // Trek Manager Admin Dashboard Interactive Script
        wp_enqueue_script(
            'trek-admin-dashboard-js',
            TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-admin-dashboard.js',
            array( 'jquery' ),
            TREK_MANAGER_VERSION,
            true
        );

        wp_localize_script(
            'trek-admin-dashboard-js',
            'TrekDashboardData',
            array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( 'trek_dashboard_nonce' ),
                'siteUrl'   => home_url( '/' ),
                'i18n'      => array(
                    'confirmDelete' => __( 'Are you sure you want to delete this record?', 'trek-content-manager' ),
                    'error'         => __( 'An error occurred. Please try again.', 'trek-content-manager' ),
                ),
            )
        );
    }

    /**
     * Query 100% real WordPress & Trek Manager database data for the dashboard.
     *
     * @return array
     */
    public static function get_dashboard_data() {
        // 1. Destinations Statistics (100% Real from Database)
        $dest_counts = wp_count_posts( 'trek_destination' );
        $dest_pub    = intval( $dest_counts->publish ?? 0 );
        $dest_draft  = intval( $dest_counts->draft ?? 0 );
        $dest_pend   = intval( $dest_counts->pending ?? 0 );
        $dest_priv   = intval( $dest_counts->private ?? 0 );
        $dest_total  = $dest_pub + $dest_draft + $dest_pend + $dest_priv;

        // Destinations created this month
        $dest_this_month = count( get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'date_query'     => array(
                array(
                    'after'     => 'first day of this month 00:00:00',
                    'inclusive' => true,
                ),
            ),
        ) ) );

        $dest_stats = array(
            'total'     => $dest_total,
            'published' => $dest_pub,
            'draft'     => $dest_draft,
            'trend'     => $dest_this_month > 0 ? sprintf( '+%d this month', $dest_this_month ) : __( 'Active', 'trek-content-manager' ),
        );

        // 2. Travellers / Users Statistics (100% Real Unique Emails Collected)
        $unique_traveller_count = class_exists( 'Trek_Travellers' ) ? Trek_Travellers::get_unique_travellers_count() : count_users()['total_users'];
        $total_registered_users = count_users()['total_users'];

        // Enquiries/travellers added this month
        $enq_this_month = count( get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'date_query'     => array(
                array(
                    'after'     => 'first day of this month 00:00:00',
                    'inclusive' => true,
                ),
            ),
        ) ) );

        $user_stats = array(
            'total'      => $unique_traveller_count,
            'registered' => $total_registered_users,
            'sub'        => sprintf( __( '%d Registered Users', 'trek-content-manager' ), $total_registered_users ),
            'trend'      => $enq_this_month > 0 ? sprintf( '+%d this month', $enq_this_month ) : __( 'Active', 'trek-content-manager' ),
        );

        // 3. Reviews Statistics (100% Real from Database)
        $review_counts = wp_count_posts( 'trek_review' );
        $rev_pub       = intval( $review_counts->publish ?? 0 );
        $rev_pend      = intval( $review_counts->pending ?? 0 );
        $rev_total     = $rev_pub + $rev_pend;

        $rev_this_month = count( get_posts( array(
            'post_type'      => 'trek_review',
            'post_status'    => array( 'publish', 'pending', 'draft' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'date_query'     => array(
                array(
                    'after'     => 'first day of this month 00:00:00',
                    'inclusive' => true,
                ),
            ),
        ) ) );

        $review_stats = array(
            'total'     => $rev_total,
            'published' => $rev_pub,
            'pending'   => $rev_pend,
            'trend'     => $rev_this_month > 0 ? sprintf( '+%d this month', $rev_this_month ) : __( 'Active', 'trek-content-manager' ),
        );

        // 4. Enquiries Statistics (100% Real from Database)
        $all_enq_ids = get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );
        $enq_total   = count( $all_enq_ids );

        $new_count       = 0;
        $contacted_count = 0;
        foreach ( $all_enq_ids as $enq_id ) {
            $st = get_post_meta( $enq_id, '_trek_enquiry_status', true );
            if ( empty( $st ) || 'new' === strtolower( $st ) ) {
                $new_count++;
            } elseif ( 'contacted' === strtolower( $st ) ) {
                $contacted_count++;
            }
        }

        $enquiry_stats = array(
            'total'     => $enq_total,
            'new'       => $new_count,
            'contacted' => $contacted_count,
            'trend'     => $enq_this_month > 0 ? sprintf( '+%d this month', $enq_this_month ) : __( 'Active', 'trek-content-manager' ),
        );

        // 5. Recent Destinations (Real Posts)
        $destinations = array();
        $db_dests = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! empty( $db_dests ) ) {
            foreach ( $db_dests as $d ) {
                $img_url = get_the_post_thumbnail_url( $d->ID, 'thumbnail' );
                if ( ! $img_url ) {
                    $img_url = get_post_meta( $d->ID, '_trek_card_image_url', true );
                }
                if ( ! $img_url ) {
                    $img_url = get_post_meta( $d->ID, '_trek_hero_bg_img', true );
                }
                if ( ! $img_url ) {
                    $hero = get_post_meta( $d->ID, '_trek_hero_image', true );
                    $img_url = ! empty( $hero ) ? $hero : '';
                }

                $regions    = wp_get_post_terms( $d->ID, 'trek_region' );
                $categories = wp_get_post_terms( $d->ID, 'trek_category' );
                $diffs      = wp_get_post_terms( $d->ID, 'trek_difficulty' );

                $region_name = ( ! empty( $regions ) && ! is_wp_error( $regions ) ) ? $regions[0]->name : ( get_post_meta( $d->ID, '_trek_fact_region', true ) ?: '—' );
                $region_slug = ( ! empty( $regions ) && ! is_wp_error( $regions ) ) ? $regions[0]->slug : '';

                $cat_name    = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->name : '—';
                $cat_slug    = ( ! empty( $categories ) && ! is_wp_error( $categories ) ) ? $categories[0]->slug : '';

                $diff_name   = ( ! empty( $diffs ) && ! is_wp_error( $diffs ) ) ? $diffs[0]->name : ( get_post_meta( $d->ID, '_trek_difficulty', true ) ?: '—' );
                $diff_slug   = ( ! empty( $diffs ) && ! is_wp_error( $diffs ) ) ? $diffs[0]->slug : '';

                $destinations[] = array(
                    'id'              => $d->ID,
                    'title'           => $d->post_title ?: __( '(Untitled)', 'trek-content-manager' ),
                    'image'           => $img_url,
                    'region'          => $region_name,
                    'region_slug'     => $region_slug,
                    'category'        => $cat_name,
                    'category_slug'   => $cat_slug,
                    'difficulty'      => $diff_name,
                    'difficulty_slug' => $diff_slug,
                    'status'          => ( 'publish' === $d->post_status ) ? 'Published' : 'Draft',
                    'view_url'        => get_permalink( $d->ID ),
                    'edit_url'        => get_edit_post_link( $d->ID ),
                    'delete_url'      => get_delete_post_link( $d->ID ),
                );
            }
        }

        // 6. Recent Enquiries (Real Posts)
        $enquiries = array();
        $db_enqs = get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! empty( $db_enqs ) ) {
            foreach ( $db_enqs as $e ) {
                $email  = get_post_meta( $e->ID, '_trek_enquiry_email', true );
                $phone  = get_post_meta( $e->ID, '_trek_enquiry_phone', true );
                $trip   = get_post_meta( $e->ID, '_trek_enquiry_trek_title', true );
                $t_id   = get_post_meta( $e->ID, '_trek_enquiry_trek_id', true );
                if ( ! $trip && $t_id ) {
                    $trip = get_the_title( $t_id );
                }
                if ( ! $trip ) {
                    $trip = '—';
                }

                $status       = get_post_meta( $e->ID, '_trek_enquiry_status', true ) ?: 'New';
                $source       = get_post_meta( $e->ID, '_trek_enquiry_source', true ) ?: __( 'Website Enquiry', 'trek-content-manager' );
                $lookup_count = intval( get_post_meta( $e->ID, '_trek_enquiry_lookup_count', true ) ?: 1 );

                $enquiries[] = array(
                    'id'           => $e->ID,
                    'name'         => $e->post_title ?: __( 'Guest Traveller', 'trek-content-manager' ),
                    'email'        => $email ?: '',
                    'phone'        => $phone ?: '',
                    'trip'         => $trip,
                    'trek_id'      => $t_id,
                    'source'       => $source,
                    'lookup_count' => $lookup_count,
                    'date'         => get_the_date( 'M j, Y', $e->ID ),
                    'time'         => get_the_time( 'g:i A', $e->ID ),
                    'status'       => $status,
                    'edit_url'     => get_edit_post_link( $e->ID ),
                    'delete_url'   => get_delete_post_link( $e->ID ),
                );
            }
        }

        // 7. Recent Reviews (Real Posts)
        $reviews = array();
        $db_revs = get_posts( array(
            'post_type'      => 'trek_review',
            'post_status'    => array( 'publish', 'pending', 'draft' ),
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! empty( $db_revs ) ) {
            foreach ( $db_revs as $r ) {
                $rating = floatval( get_post_meta( $r->ID, '_trek_rating', true ) ?: 5 );
                $t_id   = get_post_meta( $r->ID, '_trek_id', true );
                $trip   = ( $t_id && get_post( $t_id ) ) ? get_the_title( $t_id ) : '—';
                $appr   = get_post_meta( $r->ID, '_trek_approved', true );
                $status = ( '1' === $appr || 'publish' === $r->post_status ) ? 'Published' : 'Pending';

                $reviews[] = array(
                    'id'          => $r->ID,
                    'rating'      => $rating,
                    'reviewer'    => $r->post_title ?: __( 'Anonymous Reviewer', 'trek-content-manager' ),
                    'destination' => $trip,
                    'trek_id'     => $t_id,
                    'status'      => $status,
                    'view_url'    => ( $t_id && get_post( $t_id ) ) ? get_permalink( $t_id ) : home_url( '/' ),
                    'edit_url'    => get_edit_post_link( $r->ID ),
                    'delete_url'  => get_delete_post_link( $r->ID ),
                );
            }
        }

        // 8. Traveller Activity Feed (100% Real from Database)
        $activities = array();

        // Real Enquiries in Activity
        foreach ( array_slice( $enquiries, 0, 3 ) as $enq ) {
            $activities[] = array(
                'name'      => $enq['name'],
                'action'    => __( 'Submitted an enquiry', 'trek-content-manager' ),
                'target'    => $enq['trip'],
                'date'      => $enq['date'],
                'time'      => get_the_time( 'g:i A', $enq['id'] ),
                'timestamp' => get_post_time( 'U', false, $enq['id'] ),
                'type'      => 'enquiry',
                'url'       => $enq['edit_url'],
                'color'     => '#10b981',
                'bg_color'  => '#ecfdf5',
            );
        }

        // Real Reviews in Activity
        foreach ( array_slice( $reviews, 0, 3 ) as $rev ) {
            $activities[] = array(
                'name'      => $rev['reviewer'],
                'action'    => __( 'Posted a review', 'trek-content-manager' ),
                'target'    => $rev['destination'],
                'date'      => get_the_date( 'M j, Y', $rev['id'] ),
                'time'      => get_the_time( 'g:i A', $rev['id'] ),
                'timestamp' => get_post_time( 'U', false, $rev['id'] ),
                'type'      => 'review',
                'url'       => $rev['edit_url'],
                'color'     => '#2563eb',
                'bg_color'  => '#eff6ff',
            );
        }

        // Real Destinations in Activity (recently published/created)
        foreach ( array_slice( $destinations, 0, 2 ) as $dst ) {
            $activities[] = array(
                'name'      => __( 'Destination Added', 'trek-content-manager' ),
                'action'    => __( 'Created/Updated', 'trek-content-manager' ),
                'target'    => $dst['title'],
                'date'      => get_the_date( 'M j, Y', $dst['id'] ),
                'time'      => get_the_time( 'g:i A', $dst['id'] ),
                'timestamp' => get_post_time( 'U', false, $dst['id'] ),
                'type'      => 'destination',
                'url'       => $dst['edit_url'],
                'color'     => '#06b6d4',
                'bg_color'  => '#ecfeff',
            );
        }

        // Real User Registrations in Activity
        $recent_users = get_users( array(
            'number'  => 2,
            'orderby' => 'user_registered',
            'order'   => 'DESC',
        ) );
        foreach ( $recent_users as $u ) {
            $reg_time = strtotime( $u->user_registered );
            $activities[] = array(
                'name'      => $u->display_name ?: $u->user_login,
                'action'    => __( 'Registered as user', 'trek-content-manager' ),
                'target'    => '',
                'date'      => date_i18n( 'M j, Y', $reg_time ),
                'time'      => date_i18n( 'g:i A', $reg_time ),
                'timestamp' => $reg_time,
                'type'      => 'user',
                'url'       => get_edit_user_link( $u->ID ),
                'color'     => '#8b5cf6',
                'bg_color'  => '#f5f3ff',
            );
        }

        // Sort activities by timestamp DESC
        usort( $activities, function( $a, $b ) {
            return ( $b['timestamp'] ?? 0 ) <=> ( $a['timestamp'] ?? 0 );
        } );

        $activities = array_slice( $activities, 0, 5 );

        // 9. Quick Stats
        $quick_stats = array(
            array(
                'label' => __( 'Total Destinations', 'trek-content-manager' ),
                'value' => $dest_stats['total'],
                'trend' => $dest_stats['trend'],
                'icon'  => 'location',
                'color' => '#10b981',
                'bg'    => '#ecfdf5',
                'url'   => admin_url( 'edit.php?post_type=trek_destination' ),
            ),
            array(
                'label' => __( 'Total Travellers', 'trek-content-manager' ),
                'value' => $user_stats['total'],
                'trend' => $user_stats['trend'],
                'icon'  => 'user',
                'color' => '#10b981',
                'bg'    => '#ecfdf5',
                'url'   => admin_url( 'edit.php?post_type=trek_destination&page=trek-travellers' ),
            ),
            array(
                'label' => __( 'Total Reviews', 'trek-content-manager' ),
                'value' => $review_stats['total'],
                'trend' => $review_stats['trend'],
                'icon'  => 'star',
                'color' => '#f59e0b',
                'bg'    => '#fffbeb',
                'url'   => admin_url( 'edit.php?post_type=trek_destination&page=trek-reviews' ),
            ),
            array(
                'label' => __( 'Total Enquiries', 'trek-content-manager' ),
                'value' => $enquiry_stats['total'],
                'trend' => $enquiry_stats['trend'],
                'icon'  => 'mail',
                'color' => '#8b5cf6',
                'bg'    => '#f5f3ff',
                'url'   => admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ),
            ),
        );

        // Himalayan Mountain Banner Image
        $upload_dir   = wp_upload_dir();
        $local_banner = $upload_dir['baseurl'] . '/2026/09/sanjay-hona-n_Hm7MvQHYE-unsplash-1024x484.jpg';
        $local_file   = $upload_dir['basedir'] . '/2026/09/sanjay-hona-n_Hm7MvQHYE-unsplash-1024x484.jpg';
        $banner_image = file_exists( $local_file ) ? $local_banner : 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80';

        return array(
            'dest_stats'    => $dest_stats,
            'user_stats'    => $user_stats,
            'review_stats'  => $review_stats,
            'enquiry_stats' => $enquiry_stats,
            'destinations'  => $destinations,
            'enquiries'     => $enquiries,
            'reviews'       => $reviews,
            'activities'    => $activities,
            'quick_stats'   => $quick_stats,
            'banner_image'  => $banner_image,
        );

        // Himalayan Mountain Banner Image
        $upload_dir   = wp_upload_dir();
        $local_banner = $upload_dir['baseurl'] . '/2026/09/sanjay-hona-n_Hm7MvQHYE-unsplash-1024x484.jpg';
        $local_file   = $upload_dir['basedir'] . '/2026/09/sanjay-hona-n_Hm7MvQHYE-unsplash-1024x484.jpg';
        $banner_image = file_exists( $local_file ) ? $local_banner : 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80';

        return array(
            'dest_stats'    => $dest_stats,
            'user_stats'    => $user_stats,
            'review_stats'  => $review_stats,
            'enquiry_stats' => $enquiry_stats,
            'destinations'  => $destinations,
            'enquiries'     => $enquiries,
            'reviews'       => $reviews,
            'activities'    => $activities,
            'quick_stats'   => $quick_stats,
            'banner_image'  => $banner_image,
        );
    }

    /**
     * AJAX: Dashboard live search
     */
    public static function ajax_dashboard_search() {
        check_ajax_referer( 'trek_dashboard_nonce', 'nonce' );

        $query = sanitize_text_field( $_POST['query'] ?? '' );
        if ( empty( $query ) ) {
            wp_send_json_success( array( 'results' => array() ) );
        }

        $results = array();

        // Search destinations
        $dest_posts = get_posts( array(
            'post_type'      => 'trek_destination',
            's'              => $query,
            'posts_per_page' => 5,
        ) );
        foreach ( $dest_posts as $p ) {
            $results[] = array(
                'type'  => 'Destination',
                'title' => $p->post_title,
                'url'   => get_edit_post_link( $p->ID ),
            );
        }

        // Search reviews
        $rev_posts = get_posts( array(
            'post_type'      => 'trek_review',
            's'              => $query,
            'posts_per_page' => 5,
        ) );
        foreach ( $rev_posts as $p ) {
            $results[] = array(
                'type'  => 'Review',
                'title' => $p->post_title,
                'url'   => get_edit_post_link( $p->ID ),
            );
        }

        // Search enquiries
        $enq_posts = get_posts( array(
            'post_type'      => 'trek_enquiry',
            's'              => $query,
            'posts_per_page' => 5,
        ) );
        foreach ( $enq_posts as $p ) {
            $results[] = array(
                'type'  => 'Enquiry',
                'title' => $p->post_title,
                'url'   => get_edit_post_link( $p->ID ),
            );
        }

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * AJAX: Delete an item from dashboard table
     */
    public static function ajax_delete_item() {
        check_ajax_referer( 'trek_dashboard_nonce', 'nonce' );

        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['item_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid item ID.', 'trek-content-manager' ) ) );
        }

        wp_trash_post( $post_id );
        wp_send_json_success( array( 'message' => __( 'Item moved to trash.', 'trek-content-manager' ) ) );
    }

    /**
     * Render the Trek Manager Dashboard template
     */
    public static function render_dashboard_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        $data = self::get_dashboard_data();
        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-dashboard.php';

        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Trek Manager Dashboard template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }

    /**
     * Render Quick Guide template
     */
    public static function render_guide_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-quick-guide.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Quick Guide template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }

    /**
     * Render Export Enquiries template
     */
    public static function render_export_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-export-enquiries.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Export Enquiries template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }
}
