<?php
/**
 * Trek Content Manager - Custom Post Type & Complete Dynamic Form Builder
 * 
 * Features:
 * - Native WordPress Media Uploader (Upload / Select from Media Library)
 * - WYSIWYG WordPress Editors (wp_editor) for Overview, Essential Info, Inclusions/Exclusions, etc.
 * - Secondary Big Heading support for Hero section
 * - Repeaters for: Itinerary Days, Inclusions/Exclusions, Gallery Images, Essential Info, Side Points
 * - Review Custom Post Type & Moderation for storing submitted reviews
 * - Dedicated Form Fields for Quick Facts, Hero, Route Map, WhatsApp & Contact settings
 * - JSON Auto-Fill Tool: Drag-and-drop or paste JSON to auto-populate all metaboxes
 * - 100% Dynamic - Zero JSON knowledge required for admins
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_CPT {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 0 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 0 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_trek_destination', array( __CLASS__, 'save_meta_box_data' ) );
        add_action( 'save_post_trek_review', array( __CLASS__, 'save_review_meta_data' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_submenus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
        add_filter( 'manage_trek_review_posts_columns', array( __CLASS__, 'set_review_columns' ) );
        add_action( 'manage_trek_review_posts_custom_column', array( __CLASS__, 'render_review_column' ), 10, 2 );
        add_filter( 'manage_trek_enquiry_posts_columns', array( __CLASS__, 'set_enquiry_columns' ) );
        add_action( 'manage_trek_enquiry_posts_custom_column', array( __CLASS__, 'render_enquiry_column' ), 10, 2 );
        add_action( 'admin_post_trek_export_enquiries', array( __CLASS__, 'export_enquiries_csv' ) );
        add_filter( 'post_row_actions', array( __CLASS__, 'add_destination_row_actions' ), 10, 2 );
        add_filter( 'post_row_actions', array( __CLASS__, 'add_review_row_actions' ), 10, 2 );
        add_action( 'admin_post_trek_toggle_review_approval', array( __CLASS__, 'admin_toggle_review_approval' ) );
        add_action( 'admin_post_trek_export_single_json', array( __CLASS__, 'export_single_json' ) );
        add_action( 'wp_ajax_ya_submit_trek_review', array( __CLASS__, 'ajax_submit_review' ), 1 );
        add_action( 'wp_ajax_nopriv_ya_submit_trek_review', array( __CLASS__, 'ajax_submit_review' ), 1 );
    }

    /**
     * Register Custom Post Types: Destinations and Reviews
     */
    public static function register_post_types() {
        // 1. Trek Destinations
        $labels = array(
            'name'                  => _x( 'Trek Destinations', 'Post Type General Name', 'trek-content-manager' ),
            'singular_name'         => _x( 'Trek Destination', 'Post Type Singular Name', 'trek-content-manager' ),
            'menu_name'             => __( 'Trek Manager', 'trek-content-manager' ),
            'name_admin_bar'        => __( 'Trek Destination', 'trek-content-manager' ),
            'all_items'             => __( 'All Destinations', 'trek-content-manager' ),
            'add_new_item'          => __( 'Add New Destination', 'trek-content-manager' ),
            'add_new'               => __( 'Add New', 'trek-content-manager' ),
            'new_item'              => __( 'New Destination', 'trek-content-manager' ),
            'edit_item'             => __( 'Edit Destination', 'trek-content-manager' ),
            'update_item'           => __( 'Update Destination', 'trek-content-manager' ),
            'view_item'             => __( 'View Destination', 'trek-content-manager' ),
            'search_items'          => __( 'Search Treks', 'trek-content-manager' ),
            'not_found'             => __( 'No trek destinations found', 'trek-content-manager' ),
            'not_found_in_trash'    => __( 'No trek destinations found in Trash', 'trek-content-manager' ),
        );

        $args = array(
            'label'                 => __( 'Trek Destination', 'trek-content-manager' ),
            'description'           => __( 'High-altitude treks, expeditions, and destinations', 'trek-content-manager' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
            'taxonomies'            => array( 'trek_category', 'trek_region', 'trek_difficulty' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 6,
            'menu_icon'             => 'dashicons-location-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            // NOTE: 'all-trips' is intentionally NOT used here as the CPT archive slug
            // because the user has a WordPress page at /all-trips/ built with Elementor.
            // The CPT archive is disabled (false) so that /all-trips/ resolves to the
            // Elementor page without conflict. Individual trek posts live at /trip/[slug]/
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
            'rewrite'               => array( 'slug' => 'trip', 'with_front' => false ),
        );

        register_post_type( 'trek_destination', $args );

        // 2. Trek Reviews (Database Storage for Submissions)
        $review_labels = array(
            'name'                  => _x( 'Trek Reviews', 'Post Type General Name', 'trek-content-manager' ),
            'singular_name'         => _x( 'Trek Review', 'Post Type Singular Name', 'trek-content-manager' ),
            'menu_name'             => __( 'Reviews', 'trek-content-manager' ),
            'name_admin_bar'        => __( 'Trek Review', 'trek-content-manager' ),
            'all_items'             => __( 'All Reviews', 'trek-content-manager' ),
            'add_new_item'          => __( 'Add New Review', 'trek-content-manager' ),
            'add_new'               => __( 'Add New Review', 'trek-content-manager' ),
            'edit_item'             => __( 'Edit Review', 'trek-content-manager' ),
            'update_item'           => __( 'Update Review', 'trek-content-manager' ),
            'view_item'             => __( 'View Review', 'trek-content-manager' ),
            'search_items'          => __( 'Search Reviews', 'trek-content-manager' ),
            'not_found'             => __( 'No reviews found', 'trek-content-manager' ),
            'not_found_in_trash'    => __( 'No reviews found in Trash', 'trek-content-manager' ),
        );

        $review_args = array(
            'label'                 => __( 'Trek Review', 'trek-content-manager' ),
            'labels'                => $review_labels,
            'supports'              => array( 'title', 'editor' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
        );

        register_post_type( 'trek_review', $review_args );

        // 3. Trek Enquiries (Frontend contact / booking requests)
        $enquiry_labels = array(
            'name'                  => _x( 'Trek Enquiries', 'Post Type General Name', 'trek-content-manager' ),
            'singular_name'         => _x( 'Trek Enquiry', 'Post Type Singular Name', 'trek-content-manager' ),
            'menu_name'             => __( 'Enquiries', 'trek-content-manager' ),
            'name_admin_bar'        => __( 'Trek Enquiry', 'trek-content-manager' ),
            'all_items'             => __( 'All Enquiries', 'trek-content-manager' ),
            'add_new_item'          => __( 'Add New Enquiry', 'trek-content-manager' ),
            'edit_item'             => __( 'Edit Enquiry', 'trek-content-manager' ),
            'view_item'             => __( 'View Enquiry', 'trek-content-manager' ),
            'search_items'          => __( 'Search Enquiries', 'trek-content-manager' ),
            'not_found'             => __( 'No enquiries found', 'trek-content-manager' ),
            'not_found_in_trash'    => __( 'No enquiries found in Trash', 'trek-content-manager' ),
        );

        $enquiry_args = array(
            'label'                 => __( 'Trek Enquiry', 'trek-content-manager' ),
            'labels'                => $enquiry_labels,
            'supports'              => array( 'title', 'editor' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'capability_type'       => 'post',
            'map_meta_cap'          => true,
            'show_in_rest'          => true,
        );

        register_post_type( 'trek_enquiry', $enquiry_args );
    }

    public static function register_taxonomies() {
        register_taxonomy( 'trek_category', array( 'trek_destination', 'post' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __( 'Trip Categories', 'trek-content-manager' ),
                'singular_name' => __( 'Trip Category', 'trek-content-manager' )
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'trip-category' ),
        ) );

        register_taxonomy( 'trek_region', array( 'trek_destination', 'post' ), array(
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __( 'Regions', 'trek-content-manager' ),
                'singular_name' => __( 'Region', 'trek-content-manager' )
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'trek-region' ),
        ) );

        register_taxonomy( 'trek_difficulty', array( 'trek_destination' ), array(
            'hierarchical'      => false,
            'labels'            => array(
                'name'          => __( 'Difficulties', 'trek-content-manager' ),
                'singular_name' => __( 'Difficulty', 'trek-content-manager' ),
            ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'trek-difficulty' ),
        ) );
    }

    public static function add_admin_submenus() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'Trek Manager Guide', 'trek-content-manager' ),
            __( 'Quick Guide', 'trek-content-manager' ),
            'edit_posts',
            'trek-manager-guide',
            array( 'Trek_Dashboard', 'render_guide_page' )
        );
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'Export Enquiries', 'trek-content-manager' ),
            __( 'Export Enquiries', 'trek-content-manager' ),
            'edit_posts',
            'trek-export-enquiries',
            array( 'Trek_Dashboard', 'render_export_page' )
        );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            global $post_type;
            if ( in_array( $post_type, array( 'trek_destination', 'trek_review' ), true ) ) {
                wp_enqueue_media();
            }
        }
    }

    /**
     * Custom Columns for Trek Reviews
     */
    public static function set_review_columns( $columns ) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = __( 'Reviewer Name', 'trek-content-manager' );
        $new_columns['trek_review_title'] = __( 'Review Title', 'trek-content-manager' );
        $new_columns['trek_email'] = __( 'Email', 'trek-content-manager' );
        $new_columns['trek_name'] = __( 'Trek Destination', 'trek-content-manager' );
        $new_columns['trek_rating'] = __( 'Rating', 'trek-content-manager' );
        $new_columns['trek_location'] = __( 'Location', 'trek-content-manager' );
        $new_columns['trek_tour_date'] = __( 'Tour Date', 'trek-content-manager' );
        $new_columns['trek_status'] = __( 'Status', 'trek-content-manager' );
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    public static function render_review_column( $column, $post_id ) {
        switch ( $column ) {
            case 'trek_review_title':
                $rev_title = get_post_meta( $post_id, '_trek_review_title', true );
                echo '<strong>' . esc_html( $rev_title ?: get_the_title( $post_id ) ) . '</strong>';
                break;
            case 'trek_email':
                $email = get_post_meta( $post_id, '_trek_reviewer_email', true ) ?: get_post_meta( $post_id, '_trek_email', true );
                echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
                break;
            case 'trek_name':
                $trek_id = get_post_meta( $post_id, '_trek_id', true );
                if ( $trek_id && get_post( $trek_id ) ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $trek_id ) ) . '"><strong>' . esc_html( get_the_title( $trek_id ) ) . '</strong></a>';
                } else {
                    echo '<em>' . esc_html__( 'General / Unlinked', 'trek-content-manager' ) . '</em>';
                }
                break;
            case 'trek_rating':
                $rating = floatval( get_post_meta( $post_id, '_trek_rating', true ) ?: 5 );
                $full_stars = floor( $rating );
                $half_star  = ( ( $rating - $full_stars ) >= 0.4 );
                $empty_stars = max( 0, 5 - $full_stars - ( $half_star ? 1 : 0 ) );
                $stars_str = str_repeat( '★', $full_stars ) . ( $half_star ? '½' : '' ) . str_repeat( '☆', $empty_stars );
                echo '<span style="color:#cfab55; font-size:16px;">' . esc_html( $stars_str ) . '</span> (' . esc_html( $rating ) . '/5)';
                break;
            case 'trek_location':
                $city = get_post_meta( $post_id, '_trek_city', true );
                $country = get_post_meta( $post_id, '_trek_country', true );
                $loc = array_filter( array( $city, $country ) );
                echo esc_html( ! empty( $loc ) ? implode( ', ', $loc ) : '—' );
                break;
            case 'trek_tour_date':
                $tour_date = get_post_meta( $post_id, '_trek_tour_date', true );
                echo esc_html( $tour_date ?: '—' );
                break;
            case 'trek_status':
                $approved = get_post_meta( $post_id, '_trek_approved', true );
                if ( '1' === $approved && 'publish' === get_post_status( $post_id ) ) {
                    echo '<span style="color:#617555; font-weight:700; background:#f4f6f2; padding:3px 8px; border-radius:4px; border:1px solid #dce5da;">' . esc_html__( 'Approved', 'trek-content-manager' ) . '</span>';
                } else {
                    echo '<span style="color:#b26b00; font-weight:700; background:#fff8e6; padding:3px 8px; border-radius:4px; border:1px solid #fed7aa;">' . esc_html__( 'Pending Moderation', 'trek-content-manager' ) . '</span>';
                }
                break;
        }
    }

    /** Custom dashboard columns for enquiries saved by the trip planning popup. */
    public static function set_enquiry_columns( $columns ) {
        return array(
            'cb'               => $columns['cb'],
            'title'            => __( 'Traveller', 'trek-content-manager' ),
            'enquiry_trek'     => __( 'Trip', 'trek-content-manager' ),
            'enquiry_email'    => __( 'Email', 'trek-content-manager' ),
            'enquiry_phone'    => __( 'Phone / WhatsApp', 'trek-content-manager' ),
            'enquiry_people'   => __( 'Travellers', 'trek-content-manager' ),
            'enquiry_source'   => __( 'Source', 'trek-content-manager' ),
            'date'             => $columns['date'],
        );
    }

    public static function render_enquiry_column( $column, $post_id ) {
        switch ( $column ) {
            case 'enquiry_trek':
                $trek_id = absint( get_post_meta( $post_id, '_trek_enquiry_trek_id', true ) );
                $trek_title = get_post_meta( $post_id, '_trek_enquiry_trek_title', true );
                if ( $trek_id && get_post( $trek_id ) ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $trek_id ) ) . '"><strong>' . esc_html( get_the_title( $trek_id ) ) . '</strong></a>';
                } elseif ( ! empty( $trek_title ) ) {
                    echo '<strong>' . esc_html( $trek_title ) . '</strong>';
                } else {
                    echo '—';
                }
                break;
            case 'enquiry_email':
                $email = get_post_meta( $post_id, '_trek_enquiry_email', true );
                echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
                break;
            case 'enquiry_phone':
                $phone = get_post_meta( $post_id, '_trek_enquiry_phone', true );
                if ( $phone ) {
                    $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
                    echo esc_html( $phone );
                    if ( ! empty( $clean_phone ) ) {
                        echo ' <a href="https://wa.me/' . esc_attr( $clean_phone ) . '" target="_blank" title="Chat on WhatsApp" style="color:#25D366; text-decoration:none; font-weight:bold;">💬</a>';
                    }
                } else {
                    echo '—';
                }
                break;
            case 'enquiry_people':
                echo esc_html( get_post_meta( $post_id, '_trek_enquiry_travellers', true ) ?: '—' );
                break;
            case 'enquiry_source':
                $source = get_post_meta( $post_id, '_trek_enquiry_source', true ) ?: 'frontend';
                if ( 'journey_finder' === $source || 'Journey Selector' === $source ) {
                    echo '<span style="display:inline-block;color:#022f2e;background:#e2f2e9;padding:3px 9px;border-radius:6px;font-weight:600;font-size:11px;border:1px solid #b7dfc8;">🗺️ ' . esc_html__( 'Journey Selector', 'trek-content-manager' ) . '</span>';
                } else {
                    echo esc_html( $source );
                }
                break;
        }
    }

    /** Dashboard page used to download all stored WordPress enquiries as a CSV file. */
    public static function render_enquiry_export_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have permission to export enquiries.', 'trek-content-manager' ) );
        }
        $export_url = wp_nonce_url( admin_url( 'admin-post.php?action=trek_export_enquiries' ), 'trek_export_enquiries' );
        $count = wp_count_posts( 'trek_enquiry' );
        $total = intval( $count->private ?? 0 ) + intval( $count->publish ?? 0 );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Export Trek Enquiries', 'trek-content-manager' ); ?></h1>
            <p><?php printf( esc_html__( '%d enquiry records are currently stored in WordPress.', 'trek-content-manager' ), $total ); ?></p>
            <p><a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Download Enquiries CSV', 'trek-content-manager' ); ?></a></p>
            <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_enquiry' ) ); ?>"><?php esc_html_e( 'View and manage enquiries', 'trek-content-manager' ); ?></a></p>
        </div>
        <?php
    }

    public static function export_enquiries_csv() {
        if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'trek_export_enquiries' ) ) {
            wp_die( esc_html__( 'You do not have permission to export enquiries.', 'trek-content-manager' ) );
        }
        $enquiries = get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'private', 'publish' ),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=trek-enquiries-' . gmdate( 'Y-m-d' ) . '.csv' );
        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array( 'Date', 'Name', 'Trip', 'Email', 'Phone / WhatsApp', 'Travellers', 'Message', 'Source' ) );
        foreach ( $enquiries as $enquiry ) {
            $trek_id = absint( get_post_meta( $enquiry->ID, '_trek_enquiry_trek_id', true ) );
            $trek_title = $trek_id ? get_the_title( $trek_id ) : ( get_post_meta( $enquiry->ID, '_trek_enquiry_trek_title', true ) ?: '' );
            fputcsv( $output, array(
                get_the_date( 'Y-m-d H:i', $enquiry ),
                $enquiry->post_title,
                $trek_title,
                get_post_meta( $enquiry->ID, '_trek_enquiry_email', true ),
                get_post_meta( $enquiry->ID, '_trek_enquiry_phone', true ),
                get_post_meta( $enquiry->ID, '_trek_enquiry_travellers', true ),
                wp_strip_all_tags( $enquiry->post_content ),
                get_post_meta( $enquiry->ID, '_trek_enquiry_source', true ),
            ) );
        }
        fclose( $output );
        exit;
    }

    /**
     * Add "Export JSON" action link to each trek destination row in admin list table
     */
    public static function add_destination_row_actions( $actions, $post ) {
        if ( 'trek_destination' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {
            $export_url = wp_nonce_url( admin_url( 'admin-post.php?action=trek_export_single_json&id=' . $post->ID ), 'trek_export_single_json_' . $post->ID );
            $actions['trek_export_json'] = '<a href="' . esc_url( $export_url ) . '" style="color:#0284c7;font-weight:600;">📥 ' . esc_html__( 'Export JSON', 'trek-content-manager' ) . '</a>';
        }
        return $actions;
    }

    /**
     * Export a single destination post as a clean canonical JSON file
     */
    public static function export_single_json() {
        $post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || ! check_admin_referer( 'trek_export_single_json_' . $post_id ) ) {
            wp_die( esc_html__( 'You do not have permission to export this destination.', 'trek-content-manager' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || 'trek_destination' !== $post->post_type ) {
            wp_die( esc_html__( 'Destination not found.', 'trek-content-manager' ) );
        }

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        $filename = 'trek-' . ( $post->post_name ?: $post_id ) . '.json';

        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $filename ) );
        echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Add "Approve" / "Unapprove" quick actions to trek review rows in WP Admin
     */
    public static function add_review_row_actions( $actions, $post ) {
        if ( 'trek_review' === $post->post_type && current_user_can( 'edit_post', $post->ID ) ) {
            $approved = get_post_meta( $post->ID, '_trek_approved', true );
            $is_approved = ( '1' === $approved && 'publish' === $post->post_status );
            $action_type = $is_approved ? 'unapprove' : 'approve';
            $url = wp_nonce_url( admin_url( 'admin-post.php?action=trek_toggle_review_approval&id=' . $post->ID . '&toggle=' . $action_type ), 'trek_toggle_review_' . $post->ID );
            if ( $is_approved ) {
                $actions['trek_unapprove'] = '<a href="' . esc_url( $url ) . '" style="color:#d97706;font-weight:600;">' . esc_html__( 'Unapprove', 'trek-content-manager' ) . '</a>';
            } else {
                $actions['trek_approve'] = '<a href="' . esc_url( $url ) . '" style="color:#059669;font-weight:700;">' . esc_html__( '✓ Approve & Publish', 'trek-content-manager' ) . '</a>';
            }
        }
        return $actions;
    }

    /**
     * Admin action to toggle review approval status
     */
    public static function admin_toggle_review_approval() {
        $post_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        $toggle  = isset( $_GET['toggle'] ) && 'unapprove' === $_GET['toggle'] ? 'unapprove' : 'approve';
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || ! check_admin_referer( 'trek_toggle_review_' . $post_id ) ) {
            wp_die( esc_html__( 'You do not have permission to moderate this review.', 'trek-content-manager' ) );
        }

        if ( 'approve' === $toggle ) {
            update_post_meta( $post_id, '_trek_approved', '1' );
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'publish',
            ) );
        } else {
            update_post_meta( $post_id, '_trek_approved', '0' );
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'pending',
            ) );
        }

        wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=trek_review' ) );
        exit;
    }

    /**
     * Public AJAX Review Submission Handler
     * Strictly stores reviews with post_status = 'pending' and _trek_approved = '0'.
     * Never automatically published.
     */
    public static function ajax_submit_review() {
        // Verify nonce
        $nonce_valid = false;
        if ( isset( $_POST['security'] ) && wp_verify_nonce( $_POST['security'], 'trek_review_nonce' ) ) {
            $nonce_valid = true;
        } elseif ( isset( $_POST['security'] ) && wp_verify_nonce( $_POST['security'], 'wp_rest' ) ) {
            $nonce_valid = true;
        } elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) && wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'wp_rest' ) ) {
            $nonce_valid = true;
        } elseif ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'trek_review_nonce' ) ) {
            $nonce_valid = true;
        }

        if ( ! $nonce_valid ) {
            wp_send_json_error( array( 'message' => __( 'Security verification failed. Please refresh the page and try again.', 'trek-content-manager' ) ), 403 );
            return;
        }

        $name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        $email      = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
        $title      = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $comment    = isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '';
        $rating_raw = isset( $_POST['rating'] ) ? floatval( $_POST['rating'] ) : 5.0;
        $rating     = max( 1.0, min( 5.0, $rating_raw ) );
        $type       = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'Solo Traveler';
        $city       = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
        $country    = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
        $tour_date  = isset( $_POST['tour_date'] ) ? sanitize_text_field( wp_unslash( $_POST['tour_date'] ) ) : '';
        $trek_id    = isset( $_POST['trek_id'] ) ? absint( $_POST['trek_id'] ) : 0;

        // Server-side field validation
        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter your full name.', 'trek-content-manager' ) ), 400 );
            return;
        }

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'trek-content-manager' ) ), 400 );
            return;
        }

        if ( empty( $title ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a review title.', 'trek-content-manager' ) ), 400 );
            return;
        }

        if ( empty( $comment ) ) {
            wp_send_json_error( array( 'message' => __( 'Please write your review.', 'trek-content-manager' ) ), 400 );
            return;
        }

        if ( ! $trek_id || 'trek_destination' !== get_post_type( $trek_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid trek destination selected.', 'trek-content-manager' ) ), 400 );
            return;
        }

        // Insert as PENDING review (Never automatically published!)
        $review_post_id = wp_insert_post( array(
            'post_type'    => 'trek_review',
            'post_title'   => $name . ' – ' . $title,
            'post_content' => $comment,
            'post_status'  => 'pending',
        ) );

        if ( is_wp_error( $review_post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Failed to save review in database. Please try again.', 'trek-content-manager' ) ), 500 );
            return;
        }

        // Store all associated review meta
        update_post_meta( $review_post_id, '_trek_id', $trek_id );
        update_post_meta( $review_post_id, '_trek_rating', $rating );
        update_post_meta( $review_post_id, '_trek_reviewer_name', $name );
        update_post_meta( $review_post_id, '_trek_reviewer_email', $email );
        update_post_meta( $review_post_id, '_trek_review_title', $title );
        update_post_meta( $review_post_id, '_trek_traveler_type', $type );
        update_post_meta( $review_post_id, '_trek_city', $city );
        update_post_meta( $review_post_id, '_trek_country', $country );
        update_post_meta( $review_post_id, '_trek_tour_date', $tour_date );
        update_post_meta( $review_post_id, '_trek_approved', '0' ); // Explicitly pending moderation

        wp_send_json_success( array(
            'success'   => true,
            'review_id' => $review_post_id,
            'message'   => __( 'Thank you for sharing your travel experience. Your review has been submitted successfully and is now awaiting approval. Once approved, it will appear in the traveler reviews section.', 'trek-content-manager' ),
        ) );
    }

    public static function add_meta_boxes() {
        // 0. Quick JSON Import & Auto-Fill Tool
        add_meta_box(
            'ya_trek_quick_json_metabox',
            __( '⚡ Quick JSON Auto-Fill Destination Data', 'trek-content-manager' ),
            array( __CLASS__, 'render_quick_json_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 1. Unified Hero Section, Card Display & Media Settings
        add_meta_box(
            'ya_trek_hero_metabox',
            __( '🏔️ 1. Hero Section, Card Display & Media Settings', 'trek-content-manager' ),
            array( __CLASS__, 'render_hero_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 2. Quick Facts
        add_meta_box(
            'ya_trek_facts_metabox',
            __( '📊 2. Trip Facts Grid (Duration, Route, Difficulty, Group Size & More)', 'trek-content-manager' ),
            array( __CLASS__, 'render_facts_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 3. Overview & Route Visual Map
        add_meta_box(
            'ya_trek_overview_metabox',
            __( '📝 3. Overview Narrative (WYSIWYG), Highlights & Route Visual Map', 'trek-content-manager' ),
            array( __CLASS__, 'render_overview_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 4. Day-by-Day Itinerary Repeater
        add_meta_box(
            'ya_trek_itinerary_metabox',
            __( '📅 4. Day-by-Day Itinerary Builder (Repeater & Multi-Image Gallery)', 'trek-content-manager' ),
            array( __CLASS__, 'render_itinerary_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 5. Inclusions & Exclusions Repeater
        add_meta_box(
            'ya_trek_inex_metabox',
            __( '✅ 5. What’s Included & What’s Not Included (Headings & WYSIWYG Descriptions)', 'trek-content-manager' ),
            array( __CLASS__, 'render_inex_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 6. Essential Information Accordions
        add_meta_box(
            'ya_trek_essential_metabox',
            __( 'ℹ️ 6. Essential Information Accordions (Top Headings + WYSIWYG & Accordion Items)', 'trek-content-manager' ),
            array( __CLASS__, 'render_essential_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 7. Reviews Settings & Curated Reviews
        add_meta_box(
            'ya_trek_reviews_metabox',
            __( '⭐ 7. Traveller Reviews & Curated Testimonials', 'trek-content-manager' ),
            array( __CLASS__, 'render_reviews_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 8. Contact, WhatsApp & Booking Settings
        add_meta_box(
            'ya_trek_contact_metabox',
            __( '💬 8. Join Fixed Departure, WhatsApp & Booking Settings', 'trek-content-manager' ),
            array( __CLASS__, 'render_contact_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 9. Structured JSON Payload & API Data
        add_meta_box(
            'ya_trek_json_metabox',
            __( '📦 9. Structured JSON Payload & REST API Data', 'trek-content-manager' ),
            array( __CLASS__, 'render_json_metabox' ),
            'trek_destination',
            'normal',
            'low'
        );

        // Review Details Metabox for `trek_review` post type
        add_meta_box(
            'ya_trek_review_detail_metabox',
            __( 'Review Details & Moderation', 'trek-content-manager' ),
            array( __CLASS__, 'render_review_detail_metabox' ),
            'trek_review',
            'normal',
            'high'
        );

        add_meta_box(
            'ya_trek_enquiry_detail_metabox',
            __( 'Enquiry Details', 'trek-content-manager' ),
            array( __CLASS__, 'render_enquiry_detail_metabox' ),
            'trek_enquiry',
            'normal',
            'high'
        );
    }

    /**
     * Common Admin Styles & JS Media Helpers
     */
    private static function render_admin_script_helper() {
        static $rendered = false;
        if ( $rendered ) return;
        $rendered = true;
        ?>
        <style>
            .ya-admin-box { background: #fff; border: 1px solid #ccd0d4; padding: 18px; border-radius: 8px; margin-bottom: 16px; }
            .ya-admin-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px; }
            .ya-admin-row--3 { grid-template-columns: repeat(3, 1fr); }
            .ya-admin-row--4 { grid-template-columns: repeat(4, 1fr); }
            .ya-admin-field { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
            .ya-admin-field label { font-weight: 600; font-size: 13px; color: #173331; }
            .ya-admin-field input[type="text"], .ya-admin-field input[type="number"], .ya-admin-field input[type="email"], .ya-admin-field select, .ya-admin-field textarea {
                padding: 8px 12px; border: 1px solid #ccd0d4; border-radius: 5px; font-size: 13px; width: 100%; box-sizing: border-box;
            }
            .ya-admin-field textarea { min-height: 80px; }
            .ya-admin-subhead { font-size: 14px; font-weight: 700; color: #022f2e; margin: 16px 0 8px; padding-bottom: 6px; border-bottom: 2px solid #045857; display: flex; align-items: center; justify-content: space-between; }
            .ya-rep-item { background: #fbfaf7; border: 1px solid #e0dad0; border-radius: 8px; padding: 16px; margin-bottom: 14px; position: relative; box-shadow: 0 1px 4px rgba(0,0,0,0.03); }
            .ya-rep-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-weight: 700; color: #022f2e; font-size: 14px; }
            .ya-img-preview { max-width: 180px; max-height: 110px; border-radius: 6px; display: block; margin-top: 8px; object-fit: cover; border: 1px solid #ddd; background: #eee; }
            .ya-btn-delete { background: #fff1f0; border: 1px solid #ffa39e; color: #cf1322; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 12px; }
            .ya-btn-delete:hover { background: #ff4d4f; color: #fff; }
            .ya-tag-input-wrap { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
            .ya-tag-badge { display: inline-flex; align-items: center; gap: 5px; background: #e6f7ff; color: #0050b3; border: 1px solid #91d5ff; border-radius: 12px; padding: 2px 8px; font-size: 11px; }

            /* 2-Column Hero & Layout Enhancements */
            .ya-hero-2col-layout { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start; margin-bottom: 18px; }
            @media (max-width: 990px) { .ya-hero-2col-layout { grid-template-columns: 1fr; } }
            .ya-panel-left { background: #fbfbfa; border: 1px solid #dcd7cc; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
            .ya-panel-right { background: #ffffff; border: 1px solid #dcd7cc; border-radius: 8px; padding: 18px; display: flex; flex-direction: column; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
            .ya-panel-section-title { font-size: 13px; font-weight: 700; color: #022f2e; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 4px 0; padding-bottom: 6px; border-bottom: 2px solid #045857; display: flex; align-items: center; gap: 6px; }
            .ya-tax-checklist { max-height: 130px; overflow-y: auto; border: 1px solid #ccd0d4; background: #fff; padding: 8px 12px; border-radius: 5px; }
            .ya-tax-checklist label { display: flex; align-items: center; gap: 7px; margin-bottom: 5px; font-size: 13px; font-weight: 500; cursor: pointer; color: #2c3338; }
            .ya-tax-checklist label:hover { color: #045857; }
            
            /* Status Badges */
            .ya-status-picker { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
            .ya-status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid #d1d5db; background: #ffffff; color: #4b5563; transition: all 0.15s ease; user-select: none; }
            .ya-status-pill input[type="radio"] { margin: 0; cursor: pointer; }
            .ya-status-pill.is-active, .ya-status-pill:has(input:checked) { font-weight: 700; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
            .ya-status-pill--open.is-active, .ya-status-pill--open:has(input:checked) { background: #ecfdf5; border-color: #10b981; color: #065f46; }
            .ya-status-pill--limited.is-active, .ya-status-pill--limited:has(input:checked) { background: #fffbeb; border-color: #f59e0b; color: #92400e; }
            .ya-status-pill--soldout.is-active, .ya-status-pill--soldout:has(input:checked) { background: #f3f4f6; border-color: #6b7280; color: #374151; }
            .ya-status-pill--closed.is-active, .ya-status-pill--closed:has(input:checked) { background: #fef2f2; border-color: #ef4444; color: #991b1b; }

            /* Vertical Facts Dive Grid */
            .ya-facts-dive-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; margin-top: 10px; }
            .ya-fact-card { background: #ffffff; border: 1px solid #e0dad0; border-radius: 6px; padding: 10px 12px; display: flex; flex-direction: column; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
            .ya-fact-card label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #045857; letter-spacing: 0.4px; }
            .ya-fact-card input, .ya-fact-card select { width: 100%; border: 1px solid #ccd0d4; border-radius: 4px; padding: 6px 8px; font-size: 13px; box-sizing: border-box; }
            .ya-fact-card input:focus, .ya-fact-card select:focus { border-color: #045857; outline: none; box-shadow: 0 0 0 1px #045857; }

            /* Overview Side-by-Side */
            .ya-overview-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
            @media (max-width: 900px) { .ya-overview-grid { grid-template-columns: 1fr; } }

            /* 3-Column Itinerary Day Builder */
            .ya-itin-3col { display: grid; grid-template-columns: 240px 1fr 280px; gap: 16px; align-items: start; }
            @media (max-width: 1020px) { .ya-itin-3col { grid-template-columns: 1fr; } }
            .ya-day-gallery-wrap { background: #faf8f5; border: 1px dashed #d5cebf; border-radius: 6px; padding: 12px; }
            .ya-day-gallery-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
            .ya-day-gallery-thumb { position: relative; width: 72px; height: 54px; border-radius: 5px; overflow: hidden; border: 1px solid #d0c9bb; background: #eee; }
            .ya-day-gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
            .ya-day-gallery-thumb .ya-thumb-remove { position: absolute; top: 2px; right: 2px; background: rgba(207,19,34,0.85); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; line-height: 18px; text-align: center; cursor: pointer; padding: 0; }
            .ya-day-gallery-thumb .ya-thumb-remove:hover { background: #cf1322; }

            /* Inclusions & Exclusions Modern Cards */
            .ya-inex-card { background: #ffffff; border: 1px solid #e0dad0; border-radius: 8px; padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
            .ya-inex-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-weight: 700; font-size: 13px; }
            .ya-inex-card-inc { border-left: 4px solid #045857; }
            .ya-inex-card-exc { border-left: 4px solid #8a2424; }

            /* Quick JSON Import Banner */
            .ya-json-quick-banner { background: linear-gradient(135deg, #022f2e 0%, #045857 100%); color: #fff; border-radius: 8px; padding: 18px 20px; margin-bottom: 12px; }
            .ya-json-quick-dropzone { border: 2px dashed rgba(255,255,255,0.4); border-radius: 8px; padding: 22px; text-align: center; background: rgba(255,255,255,0.06); cursor: pointer; transition: all 0.2s ease; margin-top: 12px; }
            .ya-json-quick-dropzone:hover, .ya-json-quick-dropzone.is-dragover { background: rgba(255,255,255,0.12); border-color: #c6a45f; }
            .ya-json-alert { padding: 10px 14px; border-radius: 6px; margin-top: 10px; font-size: 13px; display: none; }
            .ya-json-alert--success { background: #e6f6ec; color: #0a7a3b; border: 1px solid #a3e6be; }
            .ya-json-alert--error { background: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; }
            /* Modern admin editor refinement */
            .ya-admin-box,.ya-panel-left,.ya-panel-right,.ya-rep-item,.ya-inex-card{border-color:#dfe7e3;box-shadow:0 8px 24px rgba(2,47,46,.05)}
            .ya-admin-box{padding:20px;background:#fff}
            .ya-admin-field input[type="text"],.ya-admin-field input[type="number"],.ya-admin-field input[type="email"],.ya-admin-field input[type="url"],.ya-admin-field select,.ya-admin-field textarea,.ya-fact-card input,.ya-fact-card select{min-height:40px;border:1px solid #cfdad5;border-radius:8px;background:#fff;transition:border-color .18s ease,box-shadow .18s ease}
            .ya-admin-field input:focus,.ya-admin-field select:focus,.ya-admin-field textarea:focus{border-color:#0b6b62;box-shadow:0 0 0 3px rgba(11,107,98,.10);outline:0}
            .ya-admin-field label{color:#153b36}.ya-admin-field .description{color:#71807b;line-height:1.5}
            .ya-rep-item{background:linear-gradient(180deg,#fff 0%,#fbfdfc 100%);border-radius:12px}
            .ya-rep-head{padding-bottom:10px;border-bottom:1px solid #e8efec}
            .ya-tax-checklist{border-color:#cfdad5;border-radius:8px}
            .ya-json-quick-banner{box-shadow:0 10px 28px rgba(2,47,46,.14)}
            .ya-admin-field input[type="checkbox"],.ya-admin-field input[type="radio"]{accent-color:#0b6b62}
            @media (max-width:782px){.ya-admin-row,.ya-admin-row--3,.ya-admin-row--4{grid-template-columns:1fr}.ya-admin-box{padding:14px}.ya-hero-2col-layout,.ya-overview-grid,.ya-itin-3col{grid-template-columns:1fr}}

        </style>

        <script>
            // Helper to dynamically initialize WP TinyMCE editor on dynamic repeater textareas
            function yaInitWpEditor(textareaId) {
                if (window.wp && wp.editor) {
                    try {
                        if (window.tinyMCE && tinyMCE.get(textareaId)) {
                            wp.editor.remove(textareaId);
                        }
                    } catch(e) {}
                    wp.editor.initialize(textareaId, {
                        tinymce: {
                            wpautop: true,
                            plugins: 'paste,lists,link,wordpress,wplink',
                            toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo'
                        },
                        quicktags: true
                    });
                }
            }

            function yaOpenMediaUploader(button, inputId, previewId) {
                const customUploader = wp.media({
                    title: 'Select or Upload Image',
                    button: { text: 'Use this Image' },
                    multiple: false
                });
                customUploader.on('select', function() {
                    const attachment = customUploader.state().get('selection').first().toJSON();
                    document.getElementById(inputId).value = attachment.url;
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = attachment.url;
                        preview.style.display = 'block';
                    }
                });
                customUploader.open();
            }

            function yaOpenFileUploader(button, inputId) {
                const customUploader = wp.media({
                    title: 'Select or Upload Map / Data File',
                    button: { text: 'Use this File' },
                    multiple: false
                });
                customUploader.on('select', function() {
                    const attachment = customUploader.state().get('selection').first().toJSON();
                    document.getElementById(inputId).value = attachment.url;
                });
                customUploader.open();
            }

            // Multi-image selection helper for Itinerary Day galleries
            function yaOpenMultiMediaUploader(button, inputId, containerId) {
                const uploader = wp.media({
                    title: 'Select Day Gallery Images',
                    button: { text: 'Add to Day Gallery' },
                    multiple: true
                });
                uploader.on('select', function() {
                    const selection = uploader.state().get('selection').toJSON();
                    const input = document.getElementById(inputId);
                    const container = document.getElementById(containerId);
                    let urls = input.value ? input.value.split(',').filter(Boolean) : [];

                    selection.forEach(function(item) {
                        if (item.url && !urls.includes(item.url)) {
                            urls.push(item.url);
                            const thumb = document.createElement('div');
                            thumb.className = 'ya-day-gallery-thumb';
                            thumb.setAttribute('data-url', item.url);
                            thumb.innerHTML = `
                                <img src="${item.url}" alt="">
                                <button type="button" class="ya-thumb-remove" title="Remove" onclick="yaRemoveDayThumb(this, '${inputId}')">✕</button>
                            `;
                            container.appendChild(thumb);
                        }
                    });
                    input.value = urls.join(',');
                });
                uploader.open();
            }

            function yaRemoveDayThumb(btn, inputId) {
                const thumb = btn.closest('.ya-day-gallery-thumb');
                const url = thumb.getAttribute('data-url');
                const input = document.getElementById(inputId);
                let urls = input.value ? input.value.split(',').filter(Boolean) : [];
                urls = urls.filter(function(u) { return u !== url; });
                input.value = urls.join(',');
                thumb.remove();
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Ensure all TinyMCE editors sync content into textareas before form submission
                const postForm = document.getElementById('post');
                if (postForm) {
                    postForm.addEventListener('submit', function() {
                        if (typeof tinyMCE !== 'undefined') {
                            tinyMCE.triggerSave();
                        }
                    });
                }

                // Real-time bidirectional field sync for dual metabox fields
                document.querySelectorAll('[data-sync]').forEach(function(el) {
                    var eventType = (el.tagName === 'SELECT') ? 'change' : 'input';
                    el.addEventListener(eventType, function() {
                        var syncKey = this.getAttribute('data-sync');
                        var val = this.value;
                        document.querySelectorAll('[data-sync="' + syncKey + '"]').forEach(function(target) {
                            if (target !== el) {
                                target.value = val;
                            }
                        });
                    });
                });

                // Status radio buttons visual pill highlight
                document.querySelectorAll('.ya-status-pill input[type="radio"]').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        var group = this.name;
                        var val = this.value;
                        document.querySelectorAll('input[type="radio"][value="' + val + '"]').forEach(function(matched) {
                            matched.checked = true;
                        });
                        document.querySelectorAll('.ya-status-pill').forEach(function(pill) {
                            var r = pill.querySelector('input[type="radio"]');
                            if (r && r.checked) {
                                pill.classList.add('is-active');
                            } else {
                                pill.classList.remove('is-active');
                            }
                        });
                    });
                });

                // Conditional toggle for Nepal Map Region & District
                function checkNepalRegion() {
                    const mapSection = document.getElementById('ya-nepal-map-section');
                    if (!mapSection) return;
                    let isNepal = false;
                    document.querySelectorAll('.ya-region-cb:checked').forEach(function(cb) {
                        const name = (cb.parentElement.textContent || '').toLowerCase();
                        const slug = (cb.getAttribute('data-slug') || '').toLowerCase();
                        if (name.includes('nepal') || name.includes('everest') || name.includes('annapurna') || name.includes('manaslu') || name.includes('langtang') || name.includes('mustang') || name.includes('dolpo') || name.includes('kanchenjunga') || slug.includes('nepal') || slug.includes('everest') || slug.includes('annapurna') || slug.includes('manaslu') || slug.includes('langtang')) {
                            isNepal = true;
                        }
                    });
                    const anyChecked = document.querySelectorAll('.ya-region-cb:checked').length > 0;
                    if (isNepal || !anyChecked) {
                        mapSection.style.display = 'block';
                    } else {
                        mapSection.style.display = 'none';
                    }
                }
                document.querySelectorAll('.ya-region-cb').forEach(function(cb) {
                    cb.addEventListener('change', checkNepalRegion);
                });
                checkNepalRegion();
            });
        </script>
        <?php
    }

    /**
     * 0. Quick JSON Import & Auto-Fill Destination Data Metabox
     */
    public static function render_quick_json_metabox( $post ) {
        self::render_admin_script_helper();
        ?>
        <div class="ya-json-quick-banner">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0 0 4px 0; color:#fff; font-size:16px;">⚡ Quick JSON Import & Auto-Fill Destination Data</h3>
                    <p style="margin:0; font-size:12px; opacity:0.9;">Upload or drag & drop a JSON file to instantly auto-populate all fields, facts, narrative, itinerary days, inclusions, and settings.</p>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="button button-secondary" onclick="document.getElementById('ya-quick-json-file-input').click();">
                        📁 Select JSON File
                    </button>
                    <button type="button" class="button button-secondary" onclick="document.getElementById('ya-quick-json-paste-box').style.display = document.getElementById('ya-quick-json-paste-box').style.display === 'none' ? 'block' : 'none';">
                        📋 Paste Raw JSON
                    </button>
                </div>
            </div>

            <input type="file" id="ya-quick-json-file-input" accept=".json" style="display:none;" onchange="yaHandleQuickJsonUpload(this.files)">

            <div id="ya-quick-json-dropzone" class="ya-json-quick-dropzone">
                <span class="dashicons dashicons-cloud-upload" style="font-size:32px; width:32px; height:32px; color:#c6a45f; margin-bottom:4px;"></span>
                <div style="font-weight:600; font-size:13px; color:#fff;">Drag & Drop a Destination JSON File Here</div>
                <div style="font-size:11px; opacity:0.8; margin-top:2px;">Or click the "Select JSON File" button above to auto-fill the entire form</div>
            </div>

            <div id="ya-quick-json-paste-box" style="display:none; margin-top:14px; background:rgba(0,0,0,0.2); padding:12px; border-radius:6px;">
                <label style="display:block; font-size:12px; font-weight:600; color:#fff; margin-bottom:6px;">Paste Raw Destination JSON:</label>
                <textarea id="ya-quick-json-raw-textarea" rows="6" style="width:100%; font-family:monospace; font-size:12px; padding:8px; border-radius:4px; border:1px solid #ccc; box-sizing:border-box;" placeholder='{ "title": "Everest Base Camp Trek", "hero": { ... }, "itinerary": [ ... ] }'></textarea>
                <div style="margin-top:8px; display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="button button-primary" onclick="yaHandleQuickJsonPaste()">⚡ Auto-Fill Form from Pasted JSON</button>
                </div>
            </div>

            <div id="ya-quick-json-alert" class="ya-json-alert"></div>
        </div>

        <script>
            (function() {
                const dropzone = document.getElementById('ya-quick-json-dropzone');
                if (dropzone) {
                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropzone.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.add('is-dragover');
                        }, false);
                    });
                    ['dragleave', 'drop'].forEach(eventName => {
                        dropzone.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.remove('is-dragover');
                        }, false);
                    });
                    dropzone.addEventListener('drop', (e) => {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        if (files && files.length) {
                            yaHandleQuickJsonUpload(files);
                        }
                    }, false);
                    dropzone.addEventListener('click', () => {
                        document.getElementById('ya-quick-json-file-input').click();
                    });
                }
            })();

            function yaShowQuickJsonAlert(msg, isSuccess) {
                const el = document.getElementById('ya-quick-json-alert');
                if (!el) return;
                el.className = 'ya-json-alert ' + (isSuccess ? 'ya-json-alert--success' : 'ya-json-alert--error');
                el.innerHTML = (isSuccess ? '✅ ' : '⚠️ ') + msg;
                el.style.display = 'block';
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function yaHandleQuickJsonUpload(files) {
                if (!files || !files.length) return;
                const file = files[0];
                if (!file.name.endsWith('.json') && file.type !== 'application/json') {
                    yaShowQuickJsonAlert('Please select a valid .json file.', false);
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const parsed = JSON.parse(e.target.result);
                        yaAutoFillDestinationFromJSON(parsed, file.name);
                    } catch(err) {
                        yaShowQuickJsonAlert('JSON Parsing Error: ' + err.message, false);
                    }
                };
                reader.readAsText(file);
            }

            function yaHandleQuickJsonPaste() {
                const val = document.getElementById('ya-quick-json-raw-textarea').value.trim();
                if (!val) {
                    yaShowQuickJsonAlert('Please paste JSON text into the textarea.', false);
                    return;
                }
                try {
                    const parsed = JSON.parse(val);
                    yaAutoFillDestinationFromJSON(parsed, 'Pasted JSON');
                } catch(err) {
                    yaShowQuickJsonAlert('JSON Parsing Error: ' + err.message, false);
                }
            }

            function yaSetFieldValue(name, val) {
                if (val === undefined || val === null) return;
                const input = document.querySelector('[name="' + name + '"]');
                if (input) {
                    input.value = val;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            function yaSetEditorContent(editorId, val) {
                if (val === undefined || val === null) return;
                const textarea = document.getElementById(editorId) || document.querySelector('[name="' + editorId + '"]');
                if (textarea) {
                    textarea.value = val;
                }
                if (window.tinyMCE && tinyMCE.get(editorId)) {
                    tinyMCE.get(editorId).setContent(val);
                }
            }

            function yaSetImgPreview(inputId, previewId, url) {
                if (!url) return;
                const input = document.getElementById(inputId) || document.querySelector('[name="' + inputId + '"]');
                if (input) {
                    input.value = url;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
                const prev = document.getElementById(previewId);
                if (prev) {
                    prev.src = url;
                    prev.style.display = 'block';
                }
            }

            function yaAutoFillDestinationFromJSON(rawData, sourceName) {
                // If it's an array of destinations, pick the first one or matching title
                let data = rawData;
                if (Array.isArray(rawData)) {
                    data = rawData[0] || {};
                }
                if (!data || typeof data !== 'object') {
                    yaShowQuickJsonAlert('Invalid JSON object structure.', false);
                    return;
                }

                let fieldsCount = 0;

                // 1. Post Title & Basic
                const titleVal = data.title || (data.hero && data.hero.title) || '';
                const postTitleInput = document.getElementById('title') || document.querySelector('input[name="post_title"]');
                if (postTitleInput && titleVal) {
                    postTitleInput.value = titleVal;
                    postTitleInput.dispatchEvent(new Event('input', { bubbles: true }));
                    postTitleInput.dispatchEvent(new Event('change', { bubbles: true }));
                    fieldsCount++;
                }

                // 2. Hero Fields
                const hero = data.hero || {};
                if (hero.script_title || data.script_title) { yaSetFieldValue('trek_script_title', hero.script_title || data.script_title); fieldsCount++; }
                if (hero.title || data.hero_title) { yaSetFieldValue('trek_hero_title', hero.title || data.hero_title); fieldsCount++; }
                if (hero.kicker || data.hero_kicker) { yaSetFieldValue('trek_hero_kicker', hero.kicker || data.hero_kicker); fieldsCount++; }
                if (hero.subtitle || data.hero_subtitle || data.subtitle) { yaSetFieldValue('trek_hero_subtitle', hero.subtitle || data.hero_subtitle || data.subtitle); fieldsCount++; }
                if (hero.days || data.hero_days || data.days || data.duration) { yaSetFieldValue('trek_hero_days', hero.days || data.hero_days || data.days || data.duration); fieldsCount++; }
                if (hero.badge_sub || data.hero_badge_sub) { yaSetFieldValue('trek_hero_badge_sub', hero.badge_sub || data.hero_badge_sub); fieldsCount++; }
                
                const heroBg = hero.image || data.hero_bg_img || data.image || '';
                if (heroBg) {
                    yaSetImgPreview('trek_hero_bg_img', 'trek_hero_bg_preview', heroBg);
                    fieldsCount++;
                }

                // Hero slider images
                const sliderImgs = hero.slider_images || data.hero_slider_images || [];
                if (Array.isArray(sliderImgs) && sliderImgs.length) {
                    const sliderContainer = document.getElementById('ya-hero-slider-image-rows');
                    if (sliderContainer) {
                        sliderContainer.innerHTML = '';
                        sliderImgs.forEach((url, sIdx) => {
                            if (!url) return;
                            const index = Date.now() + '_' + sIdx;
                            const row = document.createElement('div');
                            row.className = 'ya-rep-item';
                            row.style.cssText = 'display:flex; gap:10px; align-items:center; margin:8px 0;';
                            row.innerHTML = `
                                <img id="ya_hero_slide_preview_${index}" class="ya-img-preview" src="${url}" style="width:100px; height:64px; margin:0; object-fit:cover;">
                                <input type="text" name="hero_slider_image[]" id="ya_hero_slide_image_${index}" value="${url}" placeholder="Upload or enter wide hero image URL" style="flex-grow:1;">
                                <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'ya_hero_slide_image_${index}', 'ya_hero_slide_preview_${index}')">Choose Image</button>
                                <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()" aria-label="Remove slide image">✕</button>
                            `;
                            sliderContainer.appendChild(row);
                        });
                        fieldsCount += sliderImgs.length;
                    }
                }

                // Taxonomies Auto-Select (Categories & Regions)
                const catArray = Array.isArray(data.category) ? data.category : (Array.isArray(data.categories) ? data.categories : (data.category ? [data.category] : []));
                if (catArray.length) {
                    document.querySelectorAll('input[name="trek_category_ids[]"]').forEach(cb => {
                        const labelText = (cb.parentElement.textContent || '').trim().toLowerCase();
                        if (catArray.some(c => String(c).toLowerCase().trim() === labelText)) {
                            cb.checked = true;
                            fieldsCount++;
                        }
                    });
                }
                const regArray = Array.isArray(data.region) ? data.region : (Array.isArray(data.regions) ? data.regions : (data.region ? [data.region] : (data.country ? [data.country] : [])));
                if (regArray.length) {
                    document.querySelectorAll('input[name="trek_region_ids[]"]').forEach(cb => {
                        const labelText = (cb.parentElement.textContent || '').trim().toLowerCase();
                        const slug = (cb.getAttribute('data-slug') || '').toLowerCase();
                        if (regArray.some(r => {
                            const rClean = String(r).toLowerCase().trim();
                            return rClean === labelText || rClean === slug;
                        })) {
                            cb.checked = true;
                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                            fieldsCount++;
                        }
                    });
                }

                // Card fields
                const card = data.card || {};
                if (card.title || data.card_title) { yaSetFieldValue('trek_card_title', card.title || data.card_title); fieldsCount++; }
                if (card.tag || data.card_tag) { yaSetFieldValue('trek_card_tag', card.tag || data.card_tag); fieldsCount++; }
                if (card.kicker || data.card_kicker) { yaSetFieldValue('trek_card_kicker', card.kicker || data.card_kicker); fieldsCount++; }
                if (card.excerpt || data.card_excerpt) { yaSetFieldValue('trek_card_excerpt', card.excerpt || data.card_excerpt); fieldsCount++; }
                if (card.order !== undefined || data.card_order !== undefined) { yaSetFieldValue('trek_card_order', card.order !== undefined ? card.order : data.card_order); fieldsCount++; }
                const cardImg = card.image || data.card_image || '';
                if (cardImg) {
                    yaSetImgPreview('trek_card_image', 'trek_card_image_preview', cardImg);
                    fieldsCount++;
                }

                // 3. Quick Facts
                const facts = data.quick_facts || data.keyFacts || data.facts || {};
                if (facts.high_point || facts.highestPoint || facts.max_altitude || data.fact_high_point) { yaSetFieldValue('trek_fact_high_point', facts.high_point || facts.highestPoint || facts.max_altitude || data.fact_high_point); fieldsCount++; }
                if (facts.low_point || facts.lowestPoint || facts.lowest_point || data.fact_low_point) { yaSetFieldValue('trek_fact_low_point', facts.low_point || facts.lowestPoint || facts.lowest_point || data.fact_low_point); fieldsCount++; }
                if (facts.region || facts.destination || facts.location || data.fact_region) { yaSetFieldValue('trek_fact_region', facts.region || facts.destination || facts.location || data.fact_region); fieldsCount++; }
                if (facts.trekking || facts.dailyWalking || facts.walking_hours || data.fact_trekking) { yaSetFieldValue('trek_fact_trekking', facts.trekking || facts.dailyWalking || facts.walking_hours || data.fact_trekking); fieldsCount++; }
                if (facts.accom || facts.accommodation || facts.stay || data.fact_accom) { yaSetFieldValue('trek_fact_accom', facts.accom || facts.accommodation || facts.stay || data.fact_accom); fieldsCount++; }
                if (facts.style || facts.tripStyle || data.fact_style) { yaSetFieldValue('trek_fact_style', facts.style || facts.tripStyle || data.fact_style); fieldsCount++; }
                if (facts.season || facts.bestSeason || data.fact_season) { yaSetFieldValue('trek_fact_season', facts.season || facts.bestSeason || data.fact_season); fieldsCount++; }
                if (facts.group_size || facts.groupSize || data.fact_group_size) { yaSetFieldValue('trek_fact_group_size', facts.group_size || facts.groupSize || data.fact_group_size); fieldsCount++; }
                if (facts.distance || data.fact_distance) { yaSetFieldValue('trek_fact_distance', facts.distance || data.fact_distance); fieldsCount++; }
                if (facts.start || facts.starting_point || data.fact_start) { yaSetFieldValue('trek_fact_start', facts.start || facts.starting_point || data.fact_start); fieldsCount++; }
                if (facts.end || facts.ending_point || data.fact_end) { yaSetFieldValue('trek_fact_end', facts.end || facts.ending_point || data.fact_end); fieldsCount++; }
                
                const statusVal = (facts.status || data.status || 'open').toLowerCase();
                const matchedRadio = document.querySelector('input[name="trek_trip_status"][value="' + statusVal + '"]');
                if (matchedRadio) {
                    matchedRadio.checked = true;
                    matchedRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    fieldsCount++;
                }

                // 4. Overview & Route
                const overview = data.overview || {};
                if (overview.kicker || data.overview_kicker) { yaSetFieldValue('trek_overview_kicker', overview.kicker || data.overview_kicker); fieldsCount++; }
                if (overview.script || data.overview_script) { yaSetFieldValue('trek_overview_script', overview.script || data.overview_script); fieldsCount++; }
                if (overview.heading || data.overview_heading) { yaSetFieldValue('trek_overview_heading', overview.heading || data.overview_heading); fieldsCount++; }
                if (overview.subheading || data.overview_subheading) { yaSetFieldValue('trek_overview_subheading', overview.subheading || data.overview_subheading); fieldsCount++; }
                
                const overviewContent = overview.content || data.overview_content || '';
                if (overviewContent) {
                    yaSetEditorContent('trek_overview_content', overviewContent);
                    fieldsCount++;
                }
                const overviewHighlight = overview.highlight || data.overview_highlight || '';
                if (overviewHighlight) {
                    yaSetEditorContent('trek_overview_highlight', overviewHighlight);
                    fieldsCount++;
                }

                const routeImg = overview.route_img || data.route_img || '';
                if (routeImg) {
                    yaSetImgPreview('trek_route_img', 'trek_route_img_preview', routeImg);
                    fieldsCount++;
                }
                if (overview.route_sub || data.route_caption_sub) { yaSetFieldValue('trek_route_caption_sub', overview.route_sub || data.route_caption_sub); fieldsCount++; }
                if (overview.route_heading || data.route_caption_h3) { yaSetFieldValue('trek_route_caption_h3', overview.route_heading || data.route_caption_h3); fieldsCount++; }

                // Highlights repeater
                const hlList = overview.highlights || data.highlights || data.inclusions_list || [];
                if (Array.isArray(hlList) && hlList.length) {
                    const incContainer = document.getElementById('ya-inc-rows');
                    if (incContainer) {
                        incContainer.innerHTML = '';
                        hlList.forEach(hl => {
                            const val = typeof hl === 'string' ? hl : (hl.title || hl.text || '');
                            if (val) {
                                const div = document.createElement('div');
                                div.style.cssText = 'display:flex; gap:8px; margin-bottom:6px;';
                                div.innerHTML = `<input type="text" name="inc_item[]" value="${val.replace(/"/g, '&quot;')}" style="flex-grow:1;"><button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>`;
                                incContainer.appendChild(div);
                            }
                        });
                        fieldsCount += hlList.length;
                    }
                }

                // 5. Itinerary Section Headings & Days
                const itinSec = data.itinerary_section || {};
                if (itinSec.kicker || data.itin_kicker) { yaSetFieldValue('trek_itin_kicker', itinSec.kicker || data.itin_kicker); fieldsCount++; }
                if (itinSec.script || data.itin_script) { yaSetFieldValue('trek_itin_script', itinSec.script || data.itin_script); fieldsCount++; }
                if (itinSec.heading || data.itin_heading) { yaSetFieldValue('trek_itin_heading', itinSec.heading || data.itin_heading); fieldsCount++; }
                if (itinSec.subtext || data.itin_subtext) { yaSetFieldValue('trek_itin_subtext', itinSec.subtext || data.itin_subtext); fieldsCount++; }

                const itinDays = data.itinerary || [];
                if (Array.isArray(itinDays) && itinDays.length) {
                    const itinContainer = document.getElementById('ya-itin-rows');
                    if (itinContainer) {
                        itinContainer.innerHTML = '';
                        itinDays.forEach((day, idx) => {
                            const count = String(idx + 1).padStart(2, '0');
                            const dayNum = day.day || count;
                            const stage = day.stage || day.altitude || '';
                            const title = day.title || '';
                            const pills = day.pills || day.meals || 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 5 hrs';
                            const desc = day.desc || day.description || '';
                            const uid = 'day_' + Date.now() + '_' + idx + '_' + Math.floor(Math.random() * 100);
                            const dayGal = Array.isArray(day.gallery) ? day.gallery : (Array.isArray(day.images) ? day.images : []);
                            const dayGalVal = dayGal.join(',');

                            let galleryThumbs = '';
                            dayGal.forEach(url => {
                                if (url) {
                                    galleryThumbs += `<div class="ya-day-gallery-thumb" data-url="${url}"><img src="${url}" alt=""><button type="button" class="ya-thumb-remove" onclick="yaRemoveDayThumb(this, 'itin_gal_input_${uid}')">✕</button></div>`;
                                }
                            });

                            const div = document.createElement('div');
                            div.className = 'ya-rep-item';
                            div.innerHTML = `
                                <div class="ya-rep-head">
                                    <span>📅 Day ${dayNum}: ${title || 'Day ' + dayNum}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove Day</button>
                                </div>
                                <div class="ya-itin-3col">
                                    <div>
                                        <div class="ya-admin-field">
                                            <label>Day Number</label>
                                            <input type="text" name="itin_day[]" value="${dayNum}">
                                        </div>
                                        <div class="ya-admin-field">
                                            <label>Stage / Elevation / Location</label>
                                            <input type="text" name="itin_stage[]" value="${stage.replace(/"/g, '&quot;')}">
                                        </div>
                                        <div class="ya-admin-field">
                                            <label>Day Main Title</label>
                                            <input type="text" name="itin_title[]" value="${title.replace(/"/g, '&quot;')}">
                                        </div>
                                        <div class="ya-admin-field">
                                            <label>Meta / Meals / Hotel Tags</label>
                                            <input type="text" name="itin_pills[]" value="${pills.replace(/"/g, '&quot;')}">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="ya-admin-field">
                                            <label><strong>Day Trail Narrative (Rich Text Description)</strong></label>
                                            <textarea id="itin_desc_${uid}" name="itin_desc[]" rows="8" style="min-height:180px; width:100%;">${desc}</textarea>
                                        </div>
                                    </div>
                                    <div class="ya-day-gallery-wrap">
                                        <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:6px;">📷 Day Gallery Photos</label>
                                        <input type="hidden" name="itin_gallery[]" id="itin_gal_input_${uid}" value="${dayGalVal}">
                                        <button type="button" class="button button-secondary" style="width:100%;" onclick="yaOpenMultiMediaUploader(this, 'itin_gal_input_${uid}', 'itin_gal_grid_${uid}')">
                                            + Select Photos (Multi-Select)
                                        </button>
                                        <div id="itin_gal_grid_${uid}" class="ya-day-gallery-grid">${galleryThumbs}</div>
                                    </div>
                                </div>
                            `;
                            itinContainer.appendChild(div);
                            setTimeout(() => { yaInitWpEditor('itin_desc_' + uid); }, 50);
                        });
                        fieldsCount += itinDays.length;
                    }
                }

                // 6. Inclusions & Exclusions
                const inex = data.inex || data.inclusions_section || {};
                if (inex.kicker || data.inex_kicker) { yaSetFieldValue('trek_inex_kicker', inex.kicker || data.inex_kicker); fieldsCount++; }
                if (inex.script || data.inex_script) { yaSetFieldValue('trek_inex_script', inex.script || data.inex_script); fieldsCount++; }
                if (inex.heading || data.inex_heading) { yaSetFieldValue('trek_inex_heading', inex.heading || data.inex_heading); fieldsCount++; }
                if (inex.subtext || data.inex_subtext) { yaSetFieldValue('trek_inex_subtext', inex.subtext || data.inex_subtext); fieldsCount++; }

                const fullInc = inex.inclusions || data.inclusions || data.included || data.inclusions_full || [];
                if (Array.isArray(fullInc)) {
                    const incCont = document.getElementById('ya-inc-full-rows');
                    if (incCont) {
                        incCont.innerHTML = '';
                        fullInc.forEach((item, i) => {
                            const title = typeof item === 'string' ? item : (item.title || item.heading || '');
                            const desc = typeof item === 'object' ? (item.desc || item.description || item.content || '') : '';
                            const uid = 'inc_' + Date.now() + '_' + i + '_' + Math.floor(Math.random() * 100);
                            const div = document.createElement('div');
                            div.className = 'ya-inex-card ya-inex-card-inc';
                            div.innerHTML = `
                                <div class="ya-inex-card-head">
                                    <span style="color:#045857;">✔ Included Item #${i+1}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                                </div>
                                <div class="ya-admin-field">
                                    <label>Item Heading / Title</label>
                                    <input type="text" name="inc_title[]" value="${title.replace(/"/g, '&quot;')}" placeholder="e.g. Return domestic flights">
                                </div>
                                <div class="ya-admin-field">
                                    <label>Description (WYSIWYG Editor)</label>
                                    <textarea id="inc_desc_${uid}" name="inc_desc[]" rows="4" style="min-height:90px; width:100%;">${desc}</textarea>
                                </div>
                            `;
                            incCont.appendChild(div);
                            setTimeout(() => { yaInitWpEditor('inc_desc_' + uid); }, 50);
                        });
                    }
                }

                const fullExc = inex.exclusions || data.exclusions || data.excluded || data.exclusions_full || [];
                if (Array.isArray(fullExc)) {
                    const excCont = document.getElementById('ya-exc-full-rows');
                    if (excCont) {
                        excCont.innerHTML = '';
                        fullExc.forEach((item, i) => {
                            const title = typeof item === 'string' ? item : (item.title || item.heading || '');
                            const desc = typeof item === 'object' ? (item.desc || item.description || item.content || '') : '';
                            const uid = 'exc_' + Date.now() + '_' + i + '_' + Math.floor(Math.random() * 100);
                            const div = document.createElement('div');
                            div.className = 'ya-inex-card ya-inex-card-exc';
                            div.innerHTML = `
                                <div class="ya-inex-card-head">
                                    <span style="color:#8a2424;">✖ Excluded Item #${i+1}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                                </div>
                                <div class="ya-admin-field">
                                    <label>Item Heading / Title</label>
                                    <input type="text" name="exc_title[]" value="${title.replace(/"/g, '&quot;')}" placeholder="e.g. International flights and visa fees">
                                </div>
                                <div class="ya-admin-field">
                                    <label>Description (WYSIWYG Editor)</label>
                                    <textarea id="exc_desc_${uid}" name="exc_desc[]" rows="4" style="min-height:90px; width:100%;">${desc}</textarea>
                                </div>
                            `;
                            excCont.appendChild(div);
                            setTimeout(() => { yaInitWpEditor('exc_desc_' + uid); }, 50);
                        });
                    }
                }

                // 7. Gallery Section & Photos
                const galSec = data.gallery_section || (data.gallery && !Array.isArray(data.gallery) ? data.gallery : {});
                if (galSec.kicker || data.gallery_kicker) { yaSetFieldValue('trek_gallery_kicker', galSec.kicker || data.gallery_kicker); fieldsCount++; }
                if (galSec.script || data.gallery_script) { yaSetFieldValue('trek_gallery_script', galSec.script || data.gallery_script); fieldsCount++; }
                if (galSec.heading || data.gallery_heading) { yaSetFieldValue('trek_gallery_heading', galSec.heading || data.gallery_heading); fieldsCount++; }
                if (galSec.subtext || data.gallery_subtext) { yaSetFieldValue('trek_gallery_subtext', galSec.subtext || data.gallery_subtext); fieldsCount++; }

                const galleryList = Array.isArray(data.gallery) ? data.gallery : (galSec.images || data.gallery_images || data.gallery_list || []);
                if (Array.isArray(galleryList) && galleryList.length) {
                    const galCont = document.getElementById('ya-gal-rows');
                    if (galCont) {
                        galCont.innerHTML = '';
                        galleryList.forEach((g, idx) => {
                            const url = typeof g === 'string' ? g : (g.url || '');
                            const lbl = typeof g === 'object' ? (g.label || g.caption || '') : '';
                            const uid = 'gal_' + Date.now() + '_' + idx;
                            if (url) {
                                const div = document.createElement('div');
                                div.className = 'ya-rep-item';
                                div.innerHTML = `
                                    <div class="ya-rep-head">
                                        <span>📷 Photo #${idx + 1}</span>
                                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                                    </div>
                                    <div class="ya-admin-row">
                                        <div class="ya-admin-field">
                                            <label>Photo URL</label>
                                            <div style="display:flex; gap:8px;">
                                                <input type="text" name="gal_url[]" id="gal_url_${uid}" value="${url}">
                                                <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'gal_url_${uid}', 'gal_prev_${uid}')">Upload</button>
                                            </div>
                                            <img id="gal_prev_${uid}" class="ya-img-preview" src="${url}">
                                        </div>
                                        <div class="ya-admin-field">
                                            <label>Caption / Location Label</label>
                                            <input type="text" name="gal_label[]" value="${lbl.replace(/"/g, '&quot;')}">
                                        </div>
                                    </div>
                                `;
                                galCont.appendChild(div);
                            }
                        });
                    }
                }

                // 8. Essential Info & FAQs
                const ess = data.essential || {};
                if (ess.kicker || data.ess_kicker) { yaSetFieldValue('trek_ess_kicker', ess.kicker || data.ess_kicker); fieldsCount++; }
                if (ess.script || data.ess_script) { yaSetFieldValue('trek_ess_script', ess.script || data.ess_script); fieldsCount++; }
                if (ess.heading || data.ess_heading) { yaSetFieldValue('trek_ess_heading', ess.heading || data.ess_heading); fieldsCount++; }
                if (ess.intro || data.ess_intro) { yaSetEditorContent('trek_ess_intro', ess.intro || data.ess_intro); fieldsCount++; }
                if (ess.card_img || data.ess_card_img) { yaSetImgPreview('trek_ess_card_img', 'trek_ess_card_img_preview', ess.card_img || data.ess_card_img); fieldsCount++; }
                if (ess.card_kicker || data.ess_card_kicker) { yaSetFieldValue('trek_ess_card_kicker', ess.card_kicker || data.ess_card_kicker); fieldsCount++; }
                if (ess.card_title || data.ess_card_title) { yaSetFieldValue('trek_ess_card_title', ess.card_title || data.ess_card_title); fieldsCount++; }
                if (ess.card_desc || data.ess_card_desc) { yaSetFieldValue('trek_ess_card_desc', ess.card_desc || data.ess_card_desc); fieldsCount++; }
                if (ess.card_btn || data.ess_card_btn) { yaSetFieldValue('trek_ess_card_btn', ess.card_btn || data.ess_card_btn); fieldsCount++; }
                if (ess.card_email || data.ess_card_email) { yaSetFieldValue('trek_ess_card_email', ess.card_email || data.ess_card_email); fieldsCount++; }
                if (ess.card_file || data.ess_card_file) { yaSetFieldValue('trek_ess_card_file', ess.card_file || data.ess_card_file); fieldsCount++; }

                const faqs = data.faqs || data.accordions || data.essential_list || [];
                if (Array.isArray(faqs) && faqs.length) {
                    const essCont = document.getElementById('ya-ess-rows');
                    if (essCont) {
                        essCont.innerHTML = '';
                        faqs.forEach((faq, idx) => {
                            const q = faq.title || faq.question || '';
                            const a = faq.content || faq.answer || '';
                            const uid = 'ess_' + Date.now() + '_' + idx;
                            const div = document.createElement('div');
                            div.className = 'ya-rep-item';
                            div.innerHTML = `
                                <div class="ya-rep-head">
                                    <span>Accordion Item #${idx + 1}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                                </div>
                                <div class="ya-admin-field">
                                    <label>Question / Section Title</label>
                                    <input type="text" name="ess_title[]" value="${q.replace(/"/g, '&quot;')}">
                                </div>
                                <div class="ya-admin-field">
                                    <label>Detailed Answer / Rich Narrative Content</label>
                                    <textarea id="ess_cnt_${uid}" name="ess_content[]" rows="5" style="width:100%; min-height:100px;">${a}</textarea>
                                </div>
                            `;
                            essCont.appendChild(div);
                            setTimeout(() => { yaInitWpEditor('ess_cnt_' + uid); }, 50);
                        });
                    }
                }

                // 9. Reviews Section & Curated Testimonials
                const revSec = data.reviews_section || (data.reviews && !Array.isArray(data.reviews) ? data.reviews : {});
                if (revSec.kicker || data.review_kicker) { yaSetFieldValue('trek_review_kicker', revSec.kicker || data.review_kicker); fieldsCount++; }
                if (revSec.script || data.review_script) { yaSetFieldValue('trek_review_script', revSec.script || data.review_script); fieldsCount++; }
                if (revSec.heading || data.review_heading) { yaSetFieldValue('trek_review_heading', revSec.heading || data.review_heading); fieldsCount++; }
                if (revSec.subtext || data.review_subtext) { yaSetFieldValue('trek_review_subtext', revSec.subtext || data.review_subtext); fieldsCount++; }

                const revList = Array.isArray(data.reviews) ? data.reviews : (revSec.reviews || data.curated_reviews || data.reviews_list || []);
                if (Array.isArray(revList) && revList.length) {
                    const revCont = document.getElementById('ya-rev-rows');
                    if (revCont) {
                        revCont.innerHTML = '';
                        revList.forEach((rev, idx) => {
                            const name = rev.name || '';
                            const loc = rev.location || rev.country || '';
                            const rating = String(rev.rating || '5');
                            const quote = rev.quote || rev.review || rev.content || '';
                            const div = document.createElement('div');
                            div.className = 'ya-rep-item';
                            div.innerHTML = `
                                <div class="ya-rep-head">
                                    <span>Review: ${name || 'Traveller'}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                                </div>
                                <div class="ya-admin-row ya-admin-row--3">
                                    <div class="ya-admin-field">
                                        <label>Reviewer Name</label>
                                        <input type="text" name="rev_name[]" value="${name.replace(/"/g, '&quot;')}" placeholder="e.g. Jo & Laurie Prowse">
                                    </div>
                                    <div class="ya-admin-field">
                                        <label>City & Country</label>
                                        <input type="text" name="rev_loc[]" value="${loc.replace(/"/g, '&quot;')}" placeholder="e.g. London, United Kingdom">
                                    </div>
                                    <div class="ya-admin-field">
                                        <label>Rating (1 to 5)</label>
                                        <select name="rev_rating[]">
                                            <option value="5"${rating === '5' ? ' selected' : ''}>★★★★★ (5 Stars)</option>
                                            <option value="4"${rating === '4' ? ' selected' : ''}>★★★★☆ (4 Stars)</option>
                                            <option value="3"${rating === '3' ? ' selected' : ''}>★★★☆☆ (3 Stars)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="ya-admin-field">
                                    <label>Review Quote / Comment</label>
                                    <textarea name="rev_quote[]" rows="2" placeholder="e.g. Professionalism and meticulous care...">${quote}</textarea>
                                </div>
                            `;
                            revCont.appendChild(div);
                        });
                        fieldsCount += revList.length;
                    }
                }

                // 10. Fixed Departures, Booking Settings & Contact
                const contact = data.contact || data.departures || {};
                if (contact.dep_kicker || data.dep_kicker) { yaSetFieldValue('trek_dep_kicker', contact.dep_kicker || data.dep_kicker); fieldsCount++; }
                if (contact.dep_script || data.dep_script) { yaSetFieldValue('trek_dep_script', contact.dep_script || data.dep_script); fieldsCount++; }
                if (contact.dep_heading || data.dep_heading) { yaSetFieldValue('trek_dep_heading', contact.dep_heading || data.dep_heading); fieldsCount++; }
                if (contact.dep_subtext || data.dep_subtext) { yaSetFieldValue('trek_dep_subtext', contact.dep_subtext || data.dep_subtext); fieldsCount++; }

                if (contact.box_script || data.dep_box_script) { yaSetFieldValue('trek_dep_box_script', contact.box_script || data.dep_box_script); fieldsCount++; }
                if (contact.box_title || data.dep_box_title) { yaSetFieldValue('trek_dep_box_title', contact.box_title || data.dep_box_title); fieldsCount++; }
                if (contact.box_desc || data.dep_box_desc) { yaSetFieldValue('trek_dep_box_desc', contact.box_desc || data.dep_box_desc); fieldsCount++; }
                if (contact.box_photo || data.dep_box_photo) { yaSetImgPreview('trek_dep_box_photo', 'trek_dep_box_photo_preview', contact.box_photo || data.dep_box_photo); fieldsCount++; }
                if (contact.box_note || data.dep_box_note) { yaSetFieldValue('trek_dep_box_note', contact.box_note || data.dep_box_note); fieldsCount++; }

                if (contact.whatsapp || data.whatsapp_number) { yaSetFieldValue('trek_whatsapp_number', contact.whatsapp || data.whatsapp_number); fieldsCount++; }
                if (contact.email || data.sales_email) { yaSetFieldValue('trek_sales_email', contact.email || data.sales_email); fieldsCount++; }

                // 10c. Departure Dates Repeater
                const depDatesList = contact.departure_dates || data.departure_dates || data.departures || contact.departures || (contact && contact.dates) || [];
                if (Array.isArray(depDatesList) && depDatesList.length) {
                    const depContainer = document.getElementById('ya-dep-dates-rows');
                    const depEmpty = document.getElementById('ya-dep-dates-empty');
                    if (depContainer) {
                        depContainer.innerHTML = '';
                        if (depEmpty) depEmpty.style.display = 'none';
                        depDatesList.forEach((dep, i) => {
                            const d   = dep.date   || '';
                            const p   = dep.price  || '';
                            const s   = dep.seats  || '';
                            const st  = dep.status || 'open';
                            const n   = dep.note   || '';
                            const statusOptions = [
                                {v:'open',      l:'✅ Open'},
                                {v:'limited',   l:'⚠️ Limited'},
                                {v:'soldout',   l:'❌ Sold Out'},
                                {v:'guaranteed',l:'🌟 Guaranteed'},
                            ].map(o => `<option value="${o.v}"${st===o.v?' selected':''}>${o.l}</option>`).join('');
                            const div = document.createElement('div');
                            div.className = 'ya-rep-item';
                            div.style.cssText = 'background:#fff;border:1px solid #c9e8d5;border-radius:6px;padding:12px;margin-bottom:10px;';
                            div.innerHTML = `
                                <div class="ya-rep-head" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                    <span style="font-weight:600;color:#022f2e;font-size:13px;">🗓️ Departure: ${d}</span>
                                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">✕ Remove</button>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 140px;gap:12px;align-items:end;">
                                    <div class="ya-admin-field"><label>Departure Date *</label><input type="date" name="dep_date[]" value="${d}" required></div>
                                    <div class="ya-admin-field"><label>Price</label><input type="text" name="dep_price[]" value="${p.replace(/"/g,'&quot;')}" placeholder="e.g. USD 1,290"></div>
                                    <div class="ya-admin-field"><label>Available Seats</label><input type="text" name="dep_seats[]" value="${s.replace(/"/g,'&quot;')}" placeholder="e.g. 8 places left"></div>
                                    <div class="ya-admin-field"><label>Status</label><select name="dep_status[]">${statusOptions}</select></div>
                                </div>
                                <div class="ya-admin-field" style="margin-top:8px;"><label>Note / Extra Info</label><input type="text" name="dep_note[]" value="${n.replace(/"/g,'&quot;')}" placeholder="e.g. Group discount available"></div>
                            `;
                            depContainer.appendChild(div);
                        });
                        fieldsCount += depDatesList.length;
                    }
                }

                // 12. Pricing
                const mapEmbed = data.map_embed_code || (overview && overview.map_embed_code) || '';
                if (mapEmbed) { yaSetFieldValue('trek_map_embed_code', mapEmbed); fieldsCount++; }
                const mapFile = data.map_file_url || (overview && overview.map_file_url) || '';
                if (mapFile) { yaSetFieldValue('trek_map_file_url', mapFile); fieldsCount++; }
                if (data.map_region) { yaSetFieldValue('trek_map_region', data.map_region); fieldsCount++; }
                if (data.map_district) { yaSetFieldValue('trek_map_district', data.map_district); fieldsCount++; }
                if (data.difficulty) { yaSetFieldValue('trek_difficulty', data.difficulty); fieldsCount++; }

                // 12. Pricing
                const pricing = data.pricing || {};
                if (pricing.price || data.price) { yaSetFieldValue('trek_price', pricing.price || data.price); fieldsCount++; }
                if (pricing.currency || data.currency) { yaSetFieldValue('trek_currency', pricing.currency || data.currency); fieldsCount++; }

                yaShowQuickJsonAlert(`🎉 Successfully loaded and populated destination data from <strong>${sourceName}</strong> (${fieldsCount}+ fields & repeaters populated)! Review changes below and click <strong>Update / Publish</strong> to save.`, true);
            }
        </script>
        <?php
    }

    /**
     * Legacy alias for backwards-compatibility
     */
    public static function render_card_metabox( $post ) {
        self::render_hero_metabox( $post );
    }

    /**
     * 1. Unified Hero Section, Card Display & Media Settings Metabox
     */
    public static function render_hero_metabox( $post ) {
        self::render_admin_script_helper();
        wp_nonce_field( 'save_trek_details_action', 'trek_details_nonce' );

        $script_title   = get_post_meta( $post->ID, '_trek_script_title', true );
        $hero_title     = get_post_meta( $post->ID, '_trek_hero_title', true );
        $card_title     = get_post_meta( $post->ID, '_trek_card_title', true );
        
        $hero_days      = get_post_meta( $post->ID, '_trek_hero_days', true );
        $hero_badge_sub = get_post_meta( $post->ID, '_trek_hero_badge_sub', true );
        $card_meta      = get_post_meta( $post->ID, '_trek_card_meta', true );
        $breadcrumb_reg = get_post_meta( $post->ID, '_trek_breadcrumb_region', true );
        
        $hero_subtitle  = get_post_meta( $post->ID, '_trek_hero_subtitle', true );
        $card_excerpt   = get_post_meta( $post->ID, '_trek_card_excerpt', true );
        
        $card_image     = get_post_meta( $post->ID, '_trek_card_image_url', true );
        $hero_bg_img    = get_post_meta( $post->ID, '_trek_hero_bg_img', true );
        $hero_slider_images = get_post_meta( $post->ID, '_trek_hero_slider_images', true );
        if ( empty( $hero_slider_images ) || ! is_array( $hero_slider_images ) ) {
            $hero_slider_images = array();
        }
        
        $featured_home  = get_post_meta( $post->ID, '_trek_featured_home', true );
        if ( '' === $featured_home && ! metadata_exists( 'post', $post->ID, '_trek_featured_home' ) ) {
            $featured_home = '1';
        }
        $primary_cat    = get_post_meta( $post->ID, '_trek_primary_category', true );
        $card_tag       = get_post_meta( $post->ID, '_trek_card_tag', true );
        $map_region     = get_post_meta( $post->ID, '_trek_map_region', true );
        $map_district   = get_post_meta( $post->ID, '_trek_map_district', true );

        $current_diff   = get_post_meta( $post->ID, '_trek_difficulty', true );
        $diff_terms     = wp_get_post_terms( $post->ID, 'trek_difficulty', array( 'fields' => 'names' ) );
        if ( empty( $current_diff ) && ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) {
            $current_diff = $diff_terms[0];
        }

        // Retrieve taxonomies (Categories and Regions)
        $categories = get_terms( array(
            'taxonomy'   => 'trek_category',
            'hide_empty' => false,
        ) );
        $regions = get_terms( array(
            'taxonomy'   => 'trek_region',
            'hide_empty' => false,
        ) );

        $assigned_cats = wp_get_post_terms( $post->ID, 'trek_category', array( 'fields' => 'ids' ) );
        if ( is_wp_error( $assigned_cats ) ) {
            $assigned_cats = array();
        }

        $assigned_regs = wp_get_post_terms( $post->ID, 'trek_region', array( 'fields' => 'ids' ) );
        if ( is_wp_error( $assigned_regs ) ) {
            $assigned_regs = array();
        }

        $default_meta = $hero_days . ' days';
        $default_card_img = get_the_post_thumbnail_url( $post->ID, 'large' ) ?: ( $hero_bg_img ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=85' );

        $trip_status    = get_post_meta( $post->ID, '_trek_trip_status', true ) ?: 'open';
        $fact_high_point = get_post_meta( $post->ID, '_trek_fact_high_point', true );
        $fact_low_point  = get_post_meta( $post->ID, '_trek_fact_low_point', true );
        $fact_region     = get_post_meta( $post->ID, '_trek_fact_region', true );
        $fact_trekking   = get_post_meta( $post->ID, '_trek_fact_trekking', true );
        $fact_accom      = get_post_meta( $post->ID, '_trek_fact_accom', true );
        $fact_style      = get_post_meta( $post->ID, '_trek_fact_style', true );
        $fact_distance   = get_post_meta( $post->ID, '_trek_fact_distance', true );
        $fact_start      = get_post_meta( $post->ID, '_trek_fact_start', true );
        $fact_end        = get_post_meta( $post->ID, '_trek_fact_end', true );
        $fact_season     = get_post_meta( $post->ID, '_trek_fact_season', true ) ?: 'Autumn (Sep–Nov) & Spring (Mar–May)';
        $fact_group_size = get_post_meta( $post->ID, '_trek_fact_group_size', true ) ?: '2–12 Travellers';
        $price           = get_post_meta( $post->ID, '_trek_price', true ) ?: get_post_meta( $post->ID, '_trek_cost', true );
        $currency        = get_post_meta( $post->ID, '_trek_currency', true ) ?: 'USD';
        ?>
        <div class="ya-admin-box" style="background:#fdfcf9; border:1px solid #dcd7cc; border-radius:8px; padding:18px; margin-bottom:16px;">
            
            <!-- TOP 2-COLUMN SECTION: LEFT (Identification, Taxonomies & Map) | RIGHT (Titles, Badges & Labels) -->
            <div class="ya-hero-2col-layout">
                
                <!-- ================= LEFT COLUMN ================= -->
                <div class="ya-panel-left">
                    <div class="ya-panel-section-title">
                        <span>🏷️ Trip Categories & Regions</span>
                    </div>

                    <!-- 1. Trip Category Multi-Select -->
                    <div class="ya-admin-field">
                        <label style="font-weight:600; display:block; margin-bottom:5px; color:#022f2e;">
                            <?php esc_html_e( 'Trip Category (Multiple Select)', 'trek-content-manager' ); ?>
                        </label>
                        <div class="ya-tax-checklist">
                            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                <?php foreach ( $categories as $cat ) : ?>
                                    <label>
                                        <input type="checkbox" name="trek_category_ids[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( $cat->term_id, $assigned_cats ) ); ?>>
                                        <?php echo esc_html( $cat->name ); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span style="font-size:12px; color:#888;"><?php esc_html_e( 'No categories found.', 'trek-content-manager' ); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="description" style="font-size:11px; color:#666; display:block; margin-top:4px;">
                            <?php printf( __( '<a href="%s" target="_blank">+ Add/Edit Categories</a>', 'trek-content-manager' ), esc_url( admin_url( 'edit-tags.php?taxonomy=trek_category&post_type=trek_destination' ) ) ); ?>
                        </span>
                    </div>

                    <!-- 2. Destination Region Multi-Select -->
                    <div class="ya-admin-field">
                        <label style="font-weight:600; display:block; margin-bottom:5px; color:#022f2e;">
                            <?php esc_html_e( 'Destination Region (Multiple Select)', 'trek-content-manager' ); ?>
                        </label>
                        <div class="ya-tax-checklist">
                            <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                                <?php foreach ( $regions as $reg ) : ?>
                                    <label>
                                        <input type="checkbox" name="trek_region_ids[]" value="<?php echo esc_attr( $reg->term_id ); ?>" class="ya-region-cb" data-slug="<?php echo esc_attr( $reg->slug ); ?>" <?php checked( in_array( $reg->term_id, $assigned_regs ) ); ?>>
                                        <?php echo esc_html( $reg->name ); ?>
                                    </label>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <span style="font-size:12px; color:#888;"><?php esc_html_e( 'No regions found.', 'trek-content-manager' ); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="description" style="font-size:11px; color:#666; display:block; margin-top:4px;">
                            <?php printf( __( '<a href="%s" target="_blank">+ Add/Edit Regions</a>', 'trek-content-manager' ), esc_url( admin_url( 'edit-tags.php?taxonomy=trek_region&post_type=trek_destination' ) ) ); ?>
                        </span>
                    </div>

                    <!-- 3. Trek Difficulty -->
                    <div class="ya-admin-field">
                        <label for="trek_difficulty" style="font-weight:700; color:#022f2e;">
                            <?php esc_html_e( '⚡ Trek Difficulty', 'trek-content-manager' ); ?>
                        </label>
                        <select name="trek_difficulty" id="trek_difficulty" data-sync="difficulty">
                            <option value=""><?php esc_html_e( '— Select Difficulty —', 'trek-content-manager' ); ?></option>
                            <option value="Easy" <?php selected( $current_diff, 'Easy' ); ?>>Easy (Gentle trails & low elevation)</option>
                            <option value="Moderate" <?php selected( $current_diff, 'Moderate' ); ?>>Moderate (Standard mountain walking)</option>
                            <option value="Demanding" <?php selected( $current_diff, 'Demanding' ); ?>>Demanding (High passes & altitude)</option>
                            <option value="Strenuous" <?php selected( $current_diff, 'Strenuous' ); ?>>Strenuous (Long days, rough terrain)</option>
                            <option value="Challenging" <?php selected( $current_diff, 'Challenging' ); ?>>Challenging (Remote wilderness & altitude)</option>
                            <option value="Technical" <?php selected( $current_diff, 'Technical' ); ?>>Technical (Glacial travel & gear)</option>
                        </select>
                    </div>

                    <!-- 4. Interactive Map Settings (Automatically Activated for Nepal Regions) -->
                    <div id="ya-nepal-map-section" style="background:#f4f7f6; padding:12px; border-radius:6px; border:1px solid #d8e6e3; margin-top:4px;">
                        <label style="font-size:12px; font-weight:700; color:#022f2e; display:block; margin-bottom:8px;">
                            <?php esc_html_e( '🗺️ Nepal Tourism Interactive Map', 'trek-content-manager' ); ?>
                        </label>

                        <div class="ya-admin-field" style="margin-bottom:8px;">
                            <label for="trek_map_region" style="font-weight:600; font-size:12px; color:#022f2e;">
                                <?php esc_html_e( 'Map Region', 'trek-content-manager' ); ?>
                            </label>
                            <select name="trek_map_region" id="trek_map_region">
                                <option value=""><?php esc_html_e( '— Auto Detect (From Region / Title) —', 'trek-content-manager' ); ?></option>
                                <option value="everest-region" <?php selected( $map_region, 'everest-region' ); ?>>Everest Region (Khumbu / EBC)</option>
                                <option value="annapurna" <?php selected( $map_region, 'annapurna' ); ?>>Annapurna Region (ABC / Circuit / Sanctuary)</option>
                                <option value="mustang" <?php selected( $map_region, 'mustang' ); ?>>Mustang (Lo Manthang / Upper Mustang)</option>
                                <option value="manaslu" <?php selected( $map_region, 'manaslu' ); ?>>Manaslu (Circuit / Tsum Valley)</option>
                                <option value="ganesh-himal" <?php selected( $map_region, 'ganesh-himal' ); ?>>Ganesh Himal (Ruby Valley)</option>
                                <option value="dhaulagiri" <?php selected( $map_region, 'dhaulagiri' ); ?>>Dhaulagiri Region</option>
                                <option value="kanchenjunga" <?php selected( $map_region, 'kanchenjunga' ); ?>>Kanchenjunga (Far Eastern Nepal)</option>
                                <option value="api-himal" <?php selected( $map_region, 'api-himal' ); ?>>Api Himal (Far Western Nepal)</option>
                                <option value="chitwan-national-park" <?php selected( $map_region, 'chitwan-national-park' ); ?>>Chitwan National Park (Lowland Jungle)</option>
                                <option value="bardia-national-park" <?php selected( $map_region, 'bardia-national-park' ); ?>>Bardia National Park</option>
                                <option value="koshi-tappu-wildlife" <?php selected( $map_region, 'koshi-tappu-wildlife' ); ?>>Koshi Tappu Wildlife Reserve</option>
                                <option value="lumbini" <?php selected( $map_region, 'lumbini' ); ?>>Lumbini (Birthplace of Buddha)</option>
                                <option value="kathmandu" <?php selected( $map_region, 'kathmandu' ); ?>>Kathmandu Valley (Heritage & Culture)</option>
                                <option value="pokhara" <?php selected( $map_region, 'pokhara' ); ?>>Pokhara (Lakes & Annapurna Gateway)</option>
                            </select>
                        </div>

                        <div class="ya-admin-field" style="margin-bottom:0;">
                            <label for="trek_map_district" style="font-weight:600; font-size:12px; color:#022f2e;">
                                <?php esc_html_e( 'Map District Marker', 'trek-content-manager' ); ?>
                            </label>
                            <select name="trek_map_district" id="trek_map_district">
                                <option value=""><?php esc_html_e( '— Auto Detect —', 'trek-content-manager' ); ?></option>
                                <?php
                                $map_districts = array(
                                    'achham', 'arghakhanchi', 'baglung', 'baitadi', 'bajhang', 'bajura', 'banke', 'bara',
                                    'bardiya', 'bhaktapur', 'bhojpur', 'chitwan', 'dadeldhura', 'dailekh', 'dang', 'darchula',
                                    'dhading', 'dhankuta', 'dhanusha', 'dolakha', 'dolpa', 'doti', 'gorkha', 'gulmi', 'humla',
                                    'ilam', 'jhapa', 'jajarkot', 'jumla', 'kailali', 'kalikot', 'kanchanpur', 'kaski', 'kathmandu',
                                    'kavrepalanchok', 'khotang', 'lalitpur', 'lamjung', 'mahottari', 'makwanpur', 'manang', 'morang',
                                    'mugu', 'mustang', 'myagdi', 'nawalpur', 'nuwakot', 'okhaldhunga', 'palpa', 'panchthar', 'parasi',
                                    'parbat', 'parsa', 'pyuthan', 'rautahat', 'rasuwa', 'rolpa', 'rukum east', 'rukum west', 'rupandehi',
                                    'salyan', 'sankhuwasabha', 'saptari', 'sarlahi', 'sindhuli', 'sindhupalchok', 'siraha', 'solukhumbu',
                                    'sunsari', 'surkhet', 'syangja', 'tanahu', 'taplejung', 'terhathum', 'udayapur',
                                );
                                foreach ( $map_districts as $district ) :
                                    ?>
                                    <option value="<?php echo esc_attr( $district ); ?>" <?php selected( $map_district, $district ); ?>><?php echo esc_html( ucwords( str_replace( '-', ' ', $district ) ) ); ?></option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- ================= RIGHT COLUMN ================= -->
                <div class="ya-panel-right">
                    <div class="ya-panel-section-title">
                        <span>✨ Trek Titles & Duration</span>
                    </div>

                    <!-- Two Titles Only: Title (Short) & Hero Big Heading (Long) -->
                    <div class="ya-admin-field">
                        <label for="trek_card_title"><?php esc_html_e( 'Title (Short - For Cards, Sliders & Listings)', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_card_title" id="trek_card_title" value="<?php echo esc_attr( $card_title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post->ID ) ?: 'e.g. The Buddhist Circuit' ); ?>">
                        <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Concise title for cards, sliders and search listings. Defaults to post title.', 'trek-content-manager' ); ?></span>
                    </div>

                    <div class="ya-admin-field">
                        <label for="trek_hero_title"><?php esc_html_e( 'Hero Big Heading (Long Title - Single Page h1)', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_hero_title" id="trek_hero_title" value="<?php echo esc_attr( $hero_title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post->ID ) ?: 'e.g. The Ultimate Trek: Everest Base Camp Experience' ); ?>">
                        <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Large cinematic headline on the single destination view page hero.', 'trek-content-manager' ); ?></span>
                    </div>

                    <!-- Duration & Badge Metrics in a 3-col row -->
                    <div class="ya-admin-row ya-admin-row--3" style="background:#f4f7f6; padding:14px; border-radius:6px; border:1px solid #d8e6e3; margin-top:8px;">
                        <div class="ya-admin-field">
                            <label for="trek_hero_days"><?php esc_html_e( 'Hero Days Badge Number', 'trek-content-manager' ); ?></label>
                            <input type="text" name="trek_hero_days" id="trek_hero_days" value="<?php echo esc_attr( $hero_days ); ?>" placeholder="15">
                            <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'e.g. 15', 'trek-content-manager' ); ?></span>
                        </div>
                        <div class="ya-admin-field">
                            <label for="trek_hero_badge_sub"><?php esc_html_e( 'Hero Badge Subtitle', 'trek-content-manager' ); ?></label>
                            <input type="text" name="trek_hero_badge_sub" id="trek_hero_badge_sub" value="<?php echo esc_attr( $hero_badge_sub ); ?>" placeholder="Days in Nepal">
                            <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'e.g. Days in Nepal', 'trek-content-manager' ); ?></span>
                        </div>
                        <div class="ya-admin-field">
                            <label for="trek_card_meta"><?php esc_html_e( 'Card Duration Badge (Pill)', 'trek-content-manager' ); ?></label>
                            <input type="text" name="trek_card_meta" id="trek_card_meta" value="<?php echo esc_attr( $card_meta ); ?>" placeholder="<?php echo esc_attr( $default_meta ); ?>">
                            <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'e.g. 15 Days', 'trek-content-manager' ); ?></span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= FULL-WIDTH BELOW SECTIONS ================= -->

            <!-- Section 1: Single Page Description -->
            <div style="background:#fff; border:1px solid #e0dad0; border-radius:8px; padding:16px; margin-bottom:16px;">
                <div class="ya-panel-section-title">
                    <span>📝 Page Description (Hero Subtitle)</span>
                </div>
                
                <div class="ya-admin-field" style="margin-top:10px; margin-bottom:4px;">
                    <textarea name="trek_hero_subtitle" id="trek_hero_subtitle" rows="3" placeholder="A carefully paced 15-day journey through Sherpa country, high Himalayan valleys and the legendary Khumbu landscape to Everest Base Camp."><?php echo esc_textarea( $hero_subtitle ); ?></textarea>
                    <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Detailed narrative description displayed under the large heading on the destination page and listing cards.', 'trek-content-manager' ); ?></span>
                </div>
            </div>

            <!-- Section 2: Single Main Featured Image -->
            <div style="background:#fff; border:1px solid #e0dad0; border-radius:8px; padding:16px; margin-bottom:16px;">
                <div class="ya-panel-section-title">
                    <span>🖼️ Featured Trek Image (Card & Hero Display)</span>
                </div>
                <div class="ya-admin-field" style="margin-top:10px; margin-bottom:0;">
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                        <input type="text" name="trek_card_image_url" id="trek_card_image_url" value="<?php echo esc_attr( $card_image ); ?>" style="flex-grow:1;" placeholder="<?php echo esc_attr( $default_card_img ); ?>">
                        <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_card_image_url', 'trek_card_preview')"><?php esc_html_e( 'Upload / Choose Image', 'trek-content-manager' ); ?></button>
                    </div>
                    <img id="trek_card_preview" class="ya-img-preview" src="<?php echo esc_url( $card_image ?: $default_card_img ); ?>" style="width:100%; max-width:480px; max-height:220px; object-fit:cover; border-radius:6px; <?php echo empty( $card_image ) && empty( $default_card_img ) ? 'display:none;' : ''; ?>">
                    <span class="description" style="font-size:11px; color:#666; display:block; margin-top:4px;"><?php esc_html_e( 'Main image used on the single destination hero banner, cards, and sliders.', 'trek-content-manager' ); ?></span>
                </div>
            </div>

            <!-- Section 3: Dedicated Hero Slider Gallery -->
            <div style="background:#f4f8f7; border:1px solid #cfe1dc; border-radius:8px; padding:16px; margin-bottom:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;">
                    <div>
                        <strong style="color:#022f2e; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;"><?php esc_html_e( '🎞️ Hero Slider Gallery', 'trek-content-manager' ); ?></strong>
                        <span class="description" style="display:block; margin-top:3px;"><?php esc_html_e( 'Add multiple wide images for the single destination hero. The first image remains the main hero image; these slides follow automatically.', 'trek-content-manager' ); ?></span>
                    </div>
                    <button type="button" class="button button-primary button-small" id="ya-add-hero-slider-image"><?php esc_html_e( '+ Add Slide Image', 'trek-content-manager' ); ?></button>
                </div>
                <div id="ya-hero-slider-image-rows">
                    <?php foreach ( $hero_slider_images as $index => $hero_slider_image ) : ?>
                        <div class="ya-rep-item" style="display:flex; gap:10px; align-items:center; margin:8px 0;">
                            <img id="ya_hero_slide_preview_<?php echo esc_attr( $index ); ?>" class="ya-img-preview" src="<?php echo esc_url( $hero_slider_image ); ?>" style="width:100px; height:64px; margin:0; object-fit:cover;">
                            <input type="text" name="hero_slider_image[]" id="ya_hero_slide_image_<?php echo esc_attr( $index ); ?>" value="<?php echo esc_attr( $hero_slider_image ); ?>" placeholder="Upload or enter wide hero image URL" style="flex-grow:1;">
                            <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'ya_hero_slide_image_<?php echo esc_attr( $index ); ?>', 'ya_hero_slide_preview_<?php echo esc_attr( $index ); ?>')"><?php esc_html_e( 'Choose Image', 'trek-content-manager' ); ?></button>
                            <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()" aria-label="<?php esc_attr_e( 'Remove slide image', 'trek-content-manager' ); ?>">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <script>
                document.getElementById('ya-add-hero-slider-image')?.addEventListener('click', function() {
                    const container = document.getElementById('ya-hero-slider-image-rows');
                    const index = Date.now();
                    const row = document.createElement('div');
                    row.className = 'ya-rep-item';
                    row.style.cssText = 'display:flex; gap:10px; align-items:center; margin:8px 0;';
                    row.innerHTML = `
                        <img id="ya_hero_slide_preview_${index}" class="ya-img-preview" style="width:100px; height:64px; margin:0; object-fit:cover; display:none;">
                        <input type="text" name="hero_slider_image[]" id="ya_hero_slide_image_${index}" placeholder="Upload or enter wide hero image URL" style="flex-grow:1;">
                        <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'ya_hero_slide_image_${index}', 'ya_hero_slide_preview_${index}')">Choose Image</button>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()" aria-label="Remove slide image">✕</button>
                    `;
                    container.appendChild(row);
                });
            </script>

            <!-- Section 4: Vertical Trip Facts Dive -->
            <div style="background:#fdfcf9; border:1px solid #dcd7cc; border-radius:8px; padding:16px; margin-bottom:16px;">
                <div class="ya-panel-section-title" style="justify-content:space-between;">
                    <span>⚡ Trip Facts Dive (Specifications & Key Parameters)</span>
                    <span style="font-size:11px; font-weight:normal; text-transform:none; color:#666;"><?php esc_html_e( 'Synced with Quick Facts & Schema Data', 'trek-content-manager' ); ?></span>
                </div>
                
                <div class="ya-facts-dive-grid">
                    <!-- 1. Highest Point -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🔺 Highest Point / Altitude', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_high_point" data-sync="fact_high_point" value="<?php echo esc_attr( $fact_high_point ); ?>" placeholder="5,550m · Kala Patthar">
                    </div>

                    <!-- 2. Lowest Point (NEW) -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🔻 Lowest Point / Altitude', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_low_point" data-sync="fact_low_point" value="<?php echo esc_attr( $fact_low_point ); ?>" placeholder="1,400m · Kathmandu">
                    </div>

                    <!-- 3. Starting Point -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🏁 Starting Point', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_start" data-sync="fact_start" value="<?php echo esc_attr( $fact_start ); ?>" placeholder="Kathmandu / Lukla">
                    </div>

                    <!-- 4. Ending Point -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🎯 Ending Point', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_end" data-sync="fact_end" value="<?php echo esc_attr( $fact_end ); ?>" placeholder="Lukla / Kathmandu">
                    </div>

                    <!-- 5. Daily Walking Time -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '⏱️ Daily Walking Time', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_trekking" data-sync="fact_trekking" value="<?php echo esc_attr( $fact_trekking ); ?>" placeholder="5–6 hours daily">
                    </div>

                    <!-- 6. Best Season -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '☀️ Best Season', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_season" data-sync="fact_season" value="<?php echo esc_attr( $fact_season ); ?>" placeholder="Mar–May & Sep–Nov">
                    </div>

                    <!-- 7. Accommodation -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🏡 Accommodation Style', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_accom" data-sync="fact_accom" value="<?php echo esc_attr( $fact_accom ); ?>" placeholder="Boutique Hotel & Lodges">
                    </div>

                    <!-- 8. Trip Style -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '🧭 Trip Style', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_style" data-sync="fact_style" value="<?php echo esc_attr( $fact_style ); ?>" placeholder="High-altitude trekking">
                    </div>

                    <!-- 9. Total Distance -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '📏 Total Distance', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_distance" data-sync="fact_distance" value="<?php echo esc_attr( $fact_distance ); ?>" placeholder="130 km">
                    </div>

                    <!-- 10. Group Size -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '👥 Group Size', 'trek-content-manager' ); ?></label>
                        <input type="text" name="trek_fact_group_size" data-sync="fact_group_size" value="<?php echo esc_attr( $fact_group_size ); ?>" placeholder="2–12 Travellers">
                    </div>

                    <!-- 11. Starting Price -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '💵 Starting Price', 'trek-content-manager' ); ?></label>
                        <input type="number" step="any" name="trek_price" data-sync="price" value="<?php echo esc_attr( $price ); ?>" placeholder="2450">
                    </div>

                    <!-- 12. Currency -->
                    <div class="ya-fact-card">
                        <label><?php esc_html_e( '💱 Currency Code', 'trek-content-manager' ); ?></label>
                        <select name="trek_currency" data-sync="currency">
                            <option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($)</option>
                            <option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€)</option>
                            <option value="GBP" <?php selected( $currency, 'GBP' ); ?>>GBP (£)</option>
                            <option value="AUD" <?php selected( $currency, 'AUD' ); ?>>AUD ($)</option>
                            <option value="CAD" <?php selected( $currency, 'CAD' ); ?>>CAD ($)</option>
                            <option value="NPR" <?php selected( $currency, 'NPR' ); ?>>NPR (Rs)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 5: Trip Status & Home Feature Row -->
            <div style="background:#f4f9f8; border:1px solid #c2e2de; border-radius:8px; padding:16px;">
                <div class="ya-admin-row" style="margin-bottom:0; align-items:center;">
                    <!-- Trip Status Radio Pills -->
                    <div class="ya-admin-field" style="margin-bottom:0;">
                        <label style="font-weight:700; color:#022f2e; margin-bottom:4px;">
                            <?php esc_html_e( '🚦 Trip Availability / Booking Status', 'trek-content-manager' ); ?>
                        </label>
                        <div class="ya-status-picker">
                            <label class="ya-status-pill ya-status-pill--open <?php echo ( 'open' === $trip_status || empty( $trip_status ) ) ? 'is-active' : ''; ?>">
                                <input type="radio" name="trek_trip_status" value="open" data-sync="trip_status" <?php checked( $trip_status === 'open' || empty( $trip_status ) ); ?>>
                                <span>Open for Bookings</span>
                            </label>
                            <label class="ya-status-pill ya-status-pill--limited <?php echo ( 'limited' === $trip_status ) ? 'is-active' : ''; ?>">
                                <input type="radio" name="trek_trip_status" value="limited" data-sync="trip_status" <?php checked( $trip_status === 'limited' ); ?>>
                                <span>Limited Seats</span>
                            </label>
                            <label class="ya-status-pill ya-status-pill--soldout <?php echo ( 'sold_out' === $trip_status ) ? 'is-active' : ''; ?>">
                                <input type="radio" name="trek_trip_status" value="sold_out" data-sync="trip_status" <?php checked( $trip_status === 'sold_out' ); ?>>
                                <span>Sold Out</span>
                            </label>
                            <label class="ya-status-pill ya-status-pill--closed <?php echo ( 'closed' === $trip_status ) ? 'is-active' : ''; ?>">
                                <input type="radio" name="trek_trip_status" value="closed" data-sync="trip_status" <?php checked( $trip_status === 'closed' ); ?>>
                                <span>Closed / Off-Season</span>
                            </label>
                        </div>
                    </div>

                    <!-- Featured Checkbox -->
                    <div class="ya-admin-field" style="margin-bottom:0; align-self:center;">
                        <label style="font-weight:700; color:#022f2e; display:flex; align-items:center; gap:8px; cursor:pointer; background:#fff; padding:10px 14px; border-radius:6px; border:1px solid #bce1db;">
                            <input type="checkbox" name="trek_featured_home" value="1" <?php checked( $featured_home, '1' ); ?>>
                            <span><?php esc_html_e( 'Feature in Signature Journeys Slider & Home', 'trek-content-manager' ); ?></span>
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }

    /**
     * 2. Quick Facts Metabox
     */
    public static function render_facts_metabox( $post ) {
        $fact_high_point = get_post_meta( $post->ID, '_trek_fact_high_point', true );
        $fact_low_point  = get_post_meta( $post->ID, '_trek_fact_low_point', true );
        $fact_region     = get_post_meta( $post->ID, '_trek_fact_region', true );
        $fact_trekking   = get_post_meta( $post->ID, '_trek_fact_trekking', true );
        $fact_accom      = get_post_meta( $post->ID, '_trek_fact_accom', true );
        $fact_style      = get_post_meta( $post->ID, '_trek_fact_style', true );
        $fact_distance   = get_post_meta( $post->ID, '_trek_fact_distance', true );
        $fact_start      = get_post_meta( $post->ID, '_trek_fact_start', true );
        $fact_end        = get_post_meta( $post->ID, '_trek_fact_end', true );
        $fact_season     = get_post_meta( $post->ID, '_trek_fact_season', true ) ?: 'Autumn (Sep–Nov) & Spring (Mar–May)';
        $fact_group_size = get_post_meta( $post->ID, '_trek_fact_group_size', true ) ?: '2–12 Travellers';
        $trip_status     = get_post_meta( $post->ID, '_trek_trip_status', true ) ?: 'open';
        
        $price           = get_post_meta( $post->ID, '_trek_price', true ) ?: get_post_meta( $post->ID, '_trek_cost', true );
        $currency        = get_post_meta( $post->ID, '_trek_currency', true ) ?: 'USD';
        ?>
        <div class="ya-admin-row ya-admin-row--4">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '1. Highest Point / Altitude', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_high_point" data-sync="fact_high_point" value="<?php echo esc_attr( $fact_high_point ); ?>" placeholder="5,550m · Kala Patthar">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '2. Lowest Point / Altitude', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_low_point" data-sync="fact_low_point" value="<?php echo esc_attr( $fact_low_point ); ?>" placeholder="1,400m · Kathmandu">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '3. Destination / Region Fact', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_region" data-sync="fact_region" value="<?php echo esc_attr( $fact_region ); ?>" placeholder="Everest Base Camp">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '4. Daily Walking Time', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_trekking" data-sync="fact_trekking" value="<?php echo esc_attr( $fact_trekking ); ?>" placeholder="Usually 5–6 hours">
            </div>
        </div>
        <div class="ya-admin-row ya-admin-row--4">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '5. Accommodation Style', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_accom" data-sync="fact_accom" value="<?php echo esc_attr( $fact_accom ); ?>" placeholder="Hotel & local lodges">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '6. Trip Style Fact', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_style" data-sync="fact_style" value="<?php echo esc_attr( $fact_style ); ?>" placeholder="High-altitude trek">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '7. Best Season', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_season" data-sync="fact_season" value="<?php echo esc_attr( $fact_season ); ?>" placeholder="Mar–May & Sep–Nov">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '8. Group Size', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_group_size" data-sync="fact_group_size" value="<?php echo esc_attr( $fact_group_size ); ?>" placeholder="2–12 Travellers">
            </div>
        </div>
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '9. Total Distance', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_distance" data-sync="fact_distance" value="<?php echo esc_attr( $fact_distance ); ?>" placeholder="e.g. 130 km">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '10. Starting Point', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_start" data-sync="fact_start" value="<?php echo esc_attr( $fact_start ); ?>" placeholder="e.g. Kathmandu">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '11. Ending Point', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_end" data-sync="fact_end" value="<?php echo esc_attr( $fact_end ); ?>" placeholder="e.g. Lukla">
            </div>
        </div>

        <!-- Pricing, Booking Status & Commercial Details -->
        <div class="ya-admin-row ya-admin-row--3" style="margin-top:12px; background:#f0fdf4; border:1px solid #bbf7d0; padding:14px; border-radius:6px;">
            <div class="ya-admin-field">
                <label style="font-weight:700; color:#166534;"><?php esc_html_e( '💵 Starting Price / Person (Numeric)', 'trek-content-manager' ); ?></label>
                <input type="number" step="any" name="trek_price" data-sync="price" value="<?php echo esc_attr( $price ); ?>" placeholder="e.g. 2450">
                <span class="description" style="font-size:11px; color:#15803d;"><?php esc_html_e( 'Used in Schema.org Product Offer & booking quotes.', 'trek-content-manager' ); ?></span>
            </div>
            <div class="ya-admin-field">
                <label style="font-weight:700; color:#166534;"><?php esc_html_e( '💱 Currency Code', 'trek-content-manager' ); ?></label>
                <select name="trek_currency" data-sync="currency" style="width:100%;">
                    <option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($ - United States Dollar)</option>
                    <option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€ - Euro)</option>
                    <option value="GBP" <?php selected( $currency, 'GBP' ); ?>>GBP (£ - British Pound)</option>
                    <option value="AUD" <?php selected( $currency, 'AUD' ); ?>>AUD ($ - Australian Dollar)</option>
                    <option value="CAD" <?php selected( $currency, 'CAD' ); ?>>CAD ($ - Canadian Dollar)</option>
                    <option value="NPR" <?php selected( $currency, 'NPR' ); ?>>NPR (Rs - Nepalese Rupee)</option>
                </select>
            </div>
            <div class="ya-admin-field">
                <label style="font-weight:700; color:#166534;"><?php esc_html_e( '🚦 Booking Status', 'trek-content-manager' ); ?></label>
                <select name="trek_trip_status" data-sync="trip_status" style="width:100%;">
                    <option value="open" <?php selected( $trip_status, 'open' ); ?>>🟢 Open for Bookings</option>
                    <option value="limited" <?php selected( $trip_status, 'limited' ); ?>>🟡 Limited Seats</option>
                    <option value="sold_out" <?php selected( $trip_status, 'sold_out' ); ?>>⚫ Sold Out</option>
                    <option value="closed" <?php selected( $trip_status, 'closed' ); ?>>🔴 Closed / Off-Season</option>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * 3. Overview Narrative (WYSIWYG), Highlights & Route Visual / Interactive Map
     */
    public static function render_overview_metabox( $post ) {
        $heading           = get_post_meta( $post->ID, '_trek_overview_heading', true );
        $content           = get_post_meta( $post->ID, '_trek_overview_content', true );
        $overview_img      = get_post_meta( $post->ID, '_trek_overview_img', true );

        $inclusions = get_post_meta( $post->ID, '_trek_inclusions_list', true );
        if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
            $inclusions = array();
        }

        $route_img         = get_post_meta( $post->ID, '_trek_route_img', true );
        $map_embed_code    = get_post_meta( $post->ID, '_trek_map_embed_code', true );
        $map_file_url      = get_post_meta( $post->ID, '_trek_map_file_url', true );
        $route_caption_sub = get_post_meta( $post->ID, '_trek_route_caption_sub', true );
        $route_caption_h3  = get_post_meta( $post->ID, '_trek_route_caption_h3', true );
        ?>
        <!-- Section Heading -->
        <div class="ya-admin-field" style="margin-bottom:16px;">
            <label style="font-weight:700; font-size:13px; color:#022f2e;"><?php esc_html_e( 'Overview Section Heading', 'trek-content-manager' ); ?></label>
            <input type="text" name="trek_overview_heading" value="<?php echo esc_attr( $heading ); ?>" placeholder="<?php esc_attr_e( 'e.g. To the Foot of the World’s Highest Mountain', 'trek-content-manager' ); ?>" style="font-size:15px; font-weight:600; padding:8px 12px;">
        </div>

        <!-- Side-by-Side: Left (Narrative & Highlights) | Right (Overview Feature Image) -->
        <div class="ya-overview-grid" style="display:grid; grid-template-columns:1.25fr 0.75fr; gap:20px; align-items:start; margin-bottom:20px;">
            <!-- Left Column: Narrative & Key Highlights -->
            <div>
                <div class="ya-admin-field" style="margin-bottom:16px;">
                    <label style="font-weight:700; color:#022f2e;"><?php esc_html_e( 'Overview Narrative (WYSIWYG Editor)', 'trek-content-manager' ); ?></label>
                    <?php
                    wp_editor( $content, 'trek_overview_content', array(
                        'textarea_name' => 'trek_overview_content',
                        'textarea_rows' => 8,
                        'media_buttons' => false,
                        'teeny'         => false,
                        'quicktags'     => true,
                    ) );
                    ?>
                </div>

                <div class="ya-admin-subhead" style="margin-top:16px;">
                    <span><?php esc_html_e( 'Key Highlight Inclusions / Bullet Points', 'trek-content-manager' ); ?></span>
                    <button type="button" class="button button-secondary button-small" onclick="yaAddSimpleRow('ya-ov-inc-rows', 'inc_item[]')"><?php esc_html_e( '+ Add Highlight Point', 'trek-content-manager' ); ?></button>
                </div>
                <div id="ya-ov-inc-rows" style="margin-bottom:10px;">
                    <?php foreach ( $inclusions as $inc ) : ?>
                        <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                            <span style="color:#045857; font-weight:bold;">✔</span>
                            <input type="text" name="inc_item[]" value="<?php echo esc_attr( $inc ); ?>" style="flex-grow:1;" placeholder="<?php esc_attr_e( 'e.g. Airport welcome and boutique hotel in Kathmandu', 'trek-content-manager' ); ?>">
                            <button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Column: Overview Feature Image Card -->
            <div style="background:#fdfcf9; border:1px solid #dcd7cc; border-radius:8px; padding:16px;">
                <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:8px;">
                    <?php esc_html_e( 'Overview Feature / Story Image', 'trek-content-manager' ); ?>
                </label>
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                    <input type="text" name="trek_overview_img" id="trek_overview_img" value="<?php echo esc_attr( $overview_img ); ?>" style="flex-grow:1;" placeholder="<?php esc_attr_e( 'Upload or enter overview photo URL', 'trek-content-manager' ); ?>">
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_overview_img', 'trek_overview_preview')"><?php esc_html_e( 'Upload', 'trek-content-manager' ); ?></button>
                </div>
                <div style="min-height:160px; background:#f4f0e7; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; border:1px dashed #c8bead;">
                    <img id="trek_overview_preview" class="ya-img-preview" src="<?php echo esc_url( $overview_img ); ?>" style="max-height:220px; width:100%; object-fit:cover; margin:0; <?php echo empty( $overview_img ) ? 'display:none;' : ''; ?>">
                    <span id="trek_overview_placeholder" style="color:#8c8273; font-size:12px; <?php echo ! empty( $overview_img ) ? 'display:none;' : ''; ?>"><?php esc_html_e( 'No image chosen yet', 'trek-content-manager' ); ?></span>
                </div>
                <span class="description" style="font-size:11px; color:#666; margin-top:8px; display:block;">
                    <?php esc_html_e( 'Displays alongside the overview narrative on the single trek page.', 'trek-content-manager' ); ?>
                </span>
            </div>
        </div>

        <!-- Route & Map Section (Dual Option: Image Map or Custom Interactive Map Code / File) -->
        <div style="background:#f4f0e7; padding:18px; border-radius:8px; border:1px solid #dfd8cc; margin-top:14px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #d5cbbe; padding-bottom:8px;">
                <strong style="font-size:14px; color:#022f2e;">🗺️ <?php esc_html_e( 'Route & Map Showcase (Image or Interactive Embed)', 'trek-content-manager' ); ?></strong>
                <span style="font-size:11px; color:#666;"><?php esc_html_e( 'Upload static route map image and/or embed interactive code', 'trek-content-manager' ); ?></span>
            </div>

            <!-- Option 1: Static Route Map Image -->
            <div class="ya-admin-field" style="margin-bottom:14px;">
                <label style="font-weight:600;"><?php esc_html_e( 'Option A: Static Route Map Image', 'trek-content-manager' ); ?></label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_route_img" id="trek_route_img" value="<?php echo esc_attr( $route_img ); ?>" style="flex-grow:1;" placeholder="<?php esc_attr_e( 'Upload Route Map Image URL', 'trek-content-manager' ); ?>">
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_route_img', 'trek_route_preview')"><?php esc_html_e( 'Upload Route Map', 'trek-content-manager' ); ?></button>
                </div>
                <img id="trek_route_preview" class="ya-img-preview" src="<?php echo esc_url( $route_img ); ?>" style="max-height:180px; margin-top:8px; border-radius:6px; <?php echo empty( $route_img ) ? 'display:none;' : ''; ?>">
            </div>

            <!-- Option 2: Custom Interactive Map Code / Embed (HTML, iframe, JS) -->
            <div class="ya-admin-field" style="margin-bottom:14px;">
                <label style="font-weight:600;"><?php esc_html_e( 'Option B: Custom Interactive Map Code / Embed (HTML / iFrame / Mapbox / Leaflet / JS)', 'trek-content-manager' ); ?></label>
                <textarea name="trek_map_embed_code" rows="4" placeholder="<?php esc_attr_e( 'Paste your custom interactive map code here, e.g. <iframe src="..."></iframe>, Mapbox embed, custom SVG map, or JavaScript snippet.', 'trek-content-manager' ); ?>" style="font-family:Consolas, Monaco, monospace; font-size:12px; width:100%;"><?php echo esc_textarea( $map_embed_code ); ?></textarea>
                <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'If interactive embed code is provided, the frontend template can render this interactive map directly with fallback to the static image.', 'trek-content-manager' ); ?></span>
            </div>

            <!-- Option 3: Map Data / GeoJSON / KML / Vector Map File URL -->
            <div class="ya-admin-field" style="margin-bottom:14px;">
                <label style="font-weight:600;"><?php esc_html_e( 'Option C: Interactive Map Data File URL (GeoJSON / KML / GPX / Vector Map File)', 'trek-content-manager' ); ?></label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_map_file_url" id="trek_map_file_url" value="<?php echo esc_attr( $map_file_url ); ?>" style="flex-grow:1;" placeholder="<?php esc_attr_e( 'Upload or enter GeoJSON, KML or GPX route file URL', 'trek-content-manager' ); ?>">
                    <button type="button" class="button button-secondary" onclick="yaOpenFileUploader(this, 'trek_map_file_url')"><?php esc_html_e( 'Upload Map File', 'trek-content-manager' ); ?></button>
                </div>
            </div>

            <!-- Map Captions & Titles -->
            <div class="ya-admin-row" style="margin-top:12px;">
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Route Map Short Kicker', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_route_caption_sub" value="<?php echo esc_attr( $route_caption_sub ); ?>" placeholder="<?php esc_attr_e( 'e.g. Route Map', 'trek-content-manager' ); ?>">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Route Map Main Title', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_route_caption_h3" value="<?php echo esc_attr( $route_caption_h3 ); ?>" placeholder="<?php esc_attr_e( 'e.g. Lukla to Everest Base Camp', 'trek-content-manager' ); ?>">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 4. Day-by-Day Itinerary Builder (3-Column Repeater with Day Multi-Image Gallery & WYSIWYG Narrative)
     */
    public static function render_itinerary_metabox( $post ) {
        $itin_kicker     = get_post_meta( $post->ID, '_trek_itin_kicker', true );
        $itin_script     = get_post_meta( $post->ID, '_trek_itin_script', true );
        $itin_heading    = get_post_meta( $post->ID, '_trek_itin_heading', true );
        $itin_subtext    = get_post_meta( $post->ID, '_trek_itin_subtext', true );

        $itinerary = get_post_meta( $post->ID, '_trek_itinerary_list', true );
        if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
            $itinerary = array();
        }
        ?>
        <div class="ya-admin-box">
            <h4 style="margin-top:0; color:#022f2e;"><?php esc_html_e( 'Itinerary Section Headings', 'trek-content-manager' ); ?></h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Kicker', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_itin_kicker" value="<?php echo esc_attr( $itin_kicker ); ?>" placeholder="e.g. Day by Day">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Script Eyebrow Heading', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_itin_script" value="<?php echo esc_attr( $itin_script ); ?>" placeholder="e.g. Your Journey">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Main Section Heading', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_itin_heading" value="<?php echo esc_attr( $itin_heading ); ?>" placeholder="e.g. 15 Days Through the Khumbu">
                </div>
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( 'Support Subtitle Text', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_itin_subtext" value="<?php echo esc_attr( $itin_subtext ); ?>" placeholder="e.g. Open each day to view the walking plan, meals, accommodation and approximate duration.">
            </div>
        </div>

        <div class="ya-admin-subhead" style="margin-top:20px;">
            <span><?php esc_html_e( 'Day-by-Day Journey Builder (3-Column Layout: Details | Narrative WYSIWYG | Multi-Image Gallery)', 'trek-content-manager' ); ?></span>
            <button type="button" class="button button-primary" id="ya-add-itin-btn"><?php esc_html_e( '+ Add Day', 'trek-content-manager' ); ?></button>
        </div>

        <div id="ya-itin-rows">
            <?php foreach ( $itinerary as $idx => $item ) :
                $day_val     = $item['day'] ?? sprintf( '%02d', $idx + 1 );
                $stage_val   = $item['stage'] ?? ( $item['altitude'] ?? '' );
                $title_val   = $item['title'] ?? '';
                $desc_val    = $item['desc'] ?? ( $item['description'] ?? '' );
                $pills_val   = $item['pills'] ?? ( $item['meals'] ?? '' );
                $day_gal     = $item['gallery'] ?? ( $item['images'] ?? array() );
                if ( is_string( $day_gal ) ) {
                    $day_gal = array_filter( array_map( 'trim', explode( ',', $day_gal ) ) );
                } elseif ( ! is_array( $day_gal ) ) {
                    $day_gal = array();
                }
                $gal_input_val = implode( ',', $day_gal );
                $row_uid       = 'day_' . $idx . '_' . time();
                $editor_id     = 'itin_desc_' . $idx;
            ?>
                <div class="ya-rep-item">
                    <div class="ya-rep-head">
                        <span><?php echo esc_html( sprintf( __( 'Day %s: %s', 'trek-content-manager' ), $day_val, $title_val ?: __( 'Untitled Day', 'trek-content-manager' ) ) ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()"><?php esc_html_e( 'Remove Day', 'trek-content-manager' ); ?></button>
                    </div>

                    <!-- 3-Column Layout: Left (Meta/Tags) | Middle (Rich Narrative WYSIWYG) | Right (Gallery Uploader) -->
                    <div class="ya-itin-3col">
                        <!-- Column 1: Day Details & Elevation -->
                        <div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Day Number', 'trek-content-manager' ); ?></label>
                                <input type="text" name="itin_day[]" value="<?php echo esc_attr( $day_val ); ?>" placeholder="01">
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Stage / Elevation / Location', 'trek-content-manager' ); ?></label>
                                <input type="text" name="itin_stage[]" value="<?php echo esc_attr( $stage_val ); ?>" placeholder="e.g. 3,440m or Namche Bazaar">
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Day Main Title', 'trek-content-manager' ); ?></label>
                                <input type="text" name="itin_title[]" value="<?php echo esc_attr( $title_val ); ?>" placeholder="e.g. Trek to Namche Bazaar">
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Meta / Meals / Hotel Tags', 'trek-content-manager' ); ?></label>
                                <input type="text" name="itin_pills[]" value="<?php echo esc_attr( $pills_val ); ?>" placeholder="e.g. Breakfast & Dinner, Lodge, 5-6 hrs">
                            </div>
                        </div>

                        <!-- Column 2: Day Narrative (WYSIWYG Editor) -->
                        <div>
                            <div class="ya-admin-field">
                                <label><strong><?php esc_html_e( 'Day Trail Narrative (WYSIWYG Rich Text)', 'trek-content-manager' ); ?></strong></label>
                                <?php
                                wp_editor( $desc_val, $editor_id, array(
                                    'textarea_name' => 'itin_desc[]',
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny'         => false,
                                    'quicktags'     => true,
                                ) );
                                ?>
                            </div>
                        </div>

                        <!-- Column 3: Multi-Image Gallery per Day -->
                        <div class="ya-day-gallery-wrap">
                            <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:6px;">
                                📷 <?php esc_html_e( 'Day Gallery Photos', 'trek-content-manager' ); ?>
                            </label>
                            <input type="hidden" name="itin_gallery[]" id="itin_gal_input_<?php echo esc_attr( $row_uid ); ?>" value="<?php echo esc_attr( $gal_input_val ); ?>">
                            <button type="button" class="button button-secondary" style="width:100%;" onclick="yaOpenMultiMediaUploader(this, 'itin_gal_input_<?php echo esc_attr( $row_uid ); ?>', 'itin_gal_grid_<?php echo esc_attr( $row_uid ); ?>')">
                                <?php esc_html_e( '+ Select Photos (Multi-Select)', 'trek-content-manager' ); ?>
                            </button>
                            <div id="itin_gal_grid_<?php echo esc_attr( $row_uid ); ?>" class="ya-day-gallery-grid">
                                <?php foreach ( $day_gal as $img_url ) : ?>
                                    <div class="ya-day-gallery-thumb" data-url="<?php echo esc_url( $img_url ); ?>">
                                        <img src="<?php echo esc_url( $img_url ); ?>" alt="">
                                        <button type="button" class="ya-thumb-remove" title="<?php esc_attr_e( 'Remove image', 'trek-content-manager' ); ?>" onclick="yaRemoveDayThumb(this, 'itin_gal_input_<?php echo esc_attr( $row_uid ); ?>')">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <span class="description" style="font-size:11px; color:#666; margin-top:8px; display:block;">
                                <?php esc_html_e( 'Upload and preview photos captured on this specific day of the trek.', 'trek-content-manager' ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.getElementById('ya-add-itin-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-itin-rows');
                const count = String(container.children.length + 1).padStart(2, '0');
                const uid = 'day_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.innerHTML = `
                    <div class="ya-rep-head">
                        <span>📅 Day ${count}: New Day</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove Day</button>
                    </div>
                    <div class="ya-itin-3col">
                        <div>
                            <div class="ya-admin-field">
                                <label>Day Number</label>
                                <input type="text" name="itin_day[]" value="${count}" placeholder="${count}">
                            </div>
                            <div class="ya-admin-field">
                                <label>Stage / Elevation / Location</label>
                                <input type="text" name="itin_stage[]" placeholder="e.g. 3,870m or Tengboche">
                            </div>
                            <div class="ya-admin-field">
                                <label>Day Main Title</label>
                                <input type="text" name="itin_title[]" placeholder="e.g. Trek to Tengboche Monastery">
                            </div>
                            <div class="ya-admin-field">
                                <label>Meta / Meals / Hotel Tags</label>
                                <input type="text" name="itin_pills[]" value="Breakfast, Lunch & Dinner, Local Lodge, Approx. 5 hrs" placeholder="e.g. Breakfast, Lunch & Dinner, Local Lodge, Approx. 5 hrs">
                            </div>
                        </div>
                        <div>
                            <div class="ya-admin-field">
                                <label><strong>Day Trail Narrative (WYSIWYG Rich Text)</strong></label>
                                <textarea id="itin_desc_${uid}" name="itin_desc[]" rows="8" style="min-height:180px; width:100%;" placeholder="Describe the day's route, trail conditions, scenery and highlights..."></textarea>
                            </div>
                        </div>
                        <div class="ya-day-gallery-wrap">
                            <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:6px;">📷 Day Gallery Photos</label>
                            <input type="hidden" name="itin_gallery[]" id="itin_gal_input_${uid}" value="">
                            <button type="button" class="button button-secondary" style="width:100%;" onclick="yaOpenMultiMediaUploader(this, 'itin_gal_input_${uid}', 'itin_gal_grid_${uid}')">
                                + Select Photos (Multi-Select)
                            </button>
                            <div id="itin_gal_grid_${uid}" class="ya-day-gallery-grid"></div>
                            <span class="description" style="font-size:11px; color:#666; margin-top:8px; display:block;">
                                Upload and preview photos captured on this specific day of the trek.
                            </span>
                        </div>
                    </div>
                `;
                container.appendChild(div);
                setTimeout(() => { yaInitWpEditor('itin_desc_' + uid); }, 50);
            });
        </script>
        <?php
    }

    /**
     * 5. Inclusions & Exclusions Repeater (Headings & WYSIWYG Descriptions)
     */
    public static function render_inex_metabox( $post ) {
        $inex_kicker  = get_post_meta( $post->ID, '_trek_inex_kicker', true );
        $inex_script  = get_post_meta( $post->ID, '_trek_inex_script', true );
        $inex_heading = get_post_meta( $post->ID, '_trek_inex_heading', true );
        $inex_subtext = get_post_meta( $post->ID, '_trek_inex_subtext', true );

        $inclusions = get_post_meta( $post->ID, '_trek_inclusions_full', true );
        if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
            $inclusions = array();
        }

        $exclusions = get_post_meta( $post->ID, '_trek_exclusions_full', true );
        if ( empty( $exclusions ) || ! is_array( $exclusions ) ) {
            $exclusions = array();
        }
        ?>
        <div class="ya-admin-box" style="margin-bottom:20px;">
            <h4 style="margin-top:0; color:#022f2e;"><?php esc_html_e( 'Inclusions & Exclusions Section Headings', 'trek-content-manager' ); ?></h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Kicker', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_inex_kicker" value="<?php echo esc_attr( $inex_kicker ); ?>" placeholder="e.g. What is Covered">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Script Eyebrow Heading', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_inex_script" value="<?php echo esc_attr( $inex_script ); ?>" placeholder="e.g. Clear & Transparent">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Main Section Heading', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_inex_heading" value="<?php echo esc_attr( $inex_heading ); ?>" placeholder="e.g. Inclusions & Exclusions">
                </div>
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( 'Support Subtitle Text', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_inex_subtext" value="<?php echo esc_attr( $inex_subtext ); ?>" placeholder="e.g. Everything included in your bespoke Himalayan journey, with no hidden surprises.">
            </div>
        </div>

        <div class="ya-admin-row">
            <!-- Included In The Trip Column -->
            <div>
                <div class="ya-admin-subhead" style="border-bottom-color:#045857;">
                    <span style="color:#045857;">Included In The Trip</span>
                    <button type="button" class="button button-secondary button-small" id="ya-add-inc-btn">+ Add Included Item</button>
                </div>
                <div id="ya-inc-full-rows">
                    <?php foreach ( $inclusions as $i_idx => $inc ) :
                        $inc_title = is_array( $inc ) ? ( $inc['title'] ?? '' ) : $inc;
                        $inc_desc  = is_array( $inc ) ? ( $inc['desc'] ?? ( $inc['description'] ?? '' ) ) : '';
                        $inc_uid   = 'inc_' . $i_idx . '_' . time();
                        $inc_ed_id = 'inc_desc_' . $i_idx;
                    ?>
                        <div class="ya-inex-card ya-inex-card-inc">
                            <div class="ya-inex-card-head">
                                <span style="color:#045857;">✔ Included Item #<?php echo esc_html( $i_idx + 1 ); ?></span>
                                <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Item Heading / Title', 'trek-content-manager' ); ?></label>
                                <input type="text" name="inc_title[]" value="<?php echo esc_attr( $inc_title ); ?>" placeholder="e.g. Return domestic flights (Kathmandu–Lukla–Kathmandu)">
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Detailed Description (WYSIWYG Editor)', 'trek-content-manager' ); ?></label>
                                <?php
                                wp_editor( $inc_desc, $inc_ed_id, array(
                                    'textarea_name' => 'inc_desc[]',
                                    'textarea_rows' => 4,
                                    'media_buttons' => false,
                                    'teeny'         => false,
                                    'quicktags'     => true,
                                ) );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Excluded / Not Included Column -->
            <div>
                <div class="ya-admin-subhead" style="border-bottom-color:#8a2424;">
                    <span style="color:#8a2424;">Excluded / Not Included</span>
                    <button type="button" class="button button-secondary button-small" id="ya-add-exc-btn">+ Add Excluded Item</button>
                </div>
                <div id="ya-exc-full-rows">
                    <?php foreach ( $exclusions as $e_idx => $exc ) :
                        $exc_title = is_array( $exc ) ? ( $exc['title'] ?? '' ) : $exc;
                        $exc_desc  = is_array( $exc ) ? ( $exc['desc'] ?? ( $exc['description'] ?? '' ) ) : '';
                        $exc_uid   = 'exc_' . $e_idx . '_' . time();
                        $exc_ed_id = 'exc_desc_' . $e_idx;
                    ?>
                        <div class="ya-inex-card ya-inex-card-exc">
                            <div class="ya-inex-card-head">
                                <span style="color:#8a2424;">✖ Excluded Item #<?php echo esc_html( $e_idx + 1 ); ?></span>
                                <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Item Heading / Title', 'trek-content-manager' ); ?></label>
                                <input type="text" name="exc_title[]" value="<?php echo esc_attr( $exc_title ); ?>" placeholder="e.g. International flights and Nepal entry visa fees">
                            </div>
                            <div class="ya-admin-field">
                                <label><?php esc_html_e( 'Detailed Description (WYSIWYG Editor)', 'trek-content-manager' ); ?></label>
                                <?php
                                wp_editor( $exc_desc, $exc_ed_id, array(
                                    'textarea_name' => 'exc_desc[]',
                                    'textarea_rows' => 4,
                                    'media_buttons' => false,
                                    'teeny'         => false,
                                    'quicktags'     => true,
                                ) );
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('ya-add-inc-btn')?.addEventListener('click', function() {
                const c = document.getElementById('ya-inc-full-rows');
                const idx = c.children.length + 1;
                const uid = 'inc_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                const div = document.createElement('div');
                div.className = 'ya-inex-card ya-inex-card-inc';
                div.innerHTML = `
                    <div class="ya-inex-card-head">
                        <span style="color:#045857;">✔ Included Item #${idx}</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                    </div>
                    <div class="ya-admin-field">
                        <label>Item Heading / Title</label>
                        <input type="text" name="inc_title[]" placeholder="e.g. Return domestic flights">
                    </div>
                    <div class="ya-admin-field">
                        <label>Detailed Description (WYSIWYG Editor)</label>
                        <textarea id="inc_desc_${uid}" name="inc_desc[]" rows="4" style="min-height:90px; width:100%;" placeholder="Provide extra details about this inclusion..."></textarea>
                    </div>
                `;
                c.appendChild(div);
                setTimeout(() => { yaInitWpEditor('inc_desc_' + uid); }, 50);
            });

            document.getElementById('ya-add-exc-btn')?.addEventListener('click', function() {
                const c = document.getElementById('ya-exc-full-rows');
                const idx = c.children.length + 1;
                const uid = 'exc_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                const div = document.createElement('div');
                div.className = 'ya-inex-card ya-inex-card-exc';
                div.innerHTML = `
                    <div class="ya-inex-card-head">
                        <span style="color:#8a2424;">✖ Excluded Item #${idx}</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-inex-card').remove()">✕ Remove</button>
                    </div>
                    <div class="ya-admin-field">
                        <label>Item Heading / Title</label>
                        <input type="text" name="exc_title[]" placeholder="e.g. International flights and visa fees">
                    </div>
                    <div class="ya-admin-field">
                        <label>Detailed Description (WYSIWYG Editor)</label>
                        <textarea id="exc_desc_${uid}" name="exc_desc[]" rows="4" style="min-height:90px; width:100%;" placeholder="Provide extra details about this exclusion..."></textarea>
                    </div>
                `;
                c.appendChild(div);
                setTimeout(() => { yaInitWpEditor('exc_desc_' + uid); }, 50);
            });
        </script>
        <?php
    }

    /**
     * 8. Essential Information Accordions (Top Headings + WYSIWYG & Accordion Items)
     */
    public static function render_essential_metabox( $post ) {
        $ess_kicker  = get_post_meta( $post->ID, '_trek_ess_kicker', true );
        $ess_script  = get_post_meta( $post->ID, '_trek_ess_script', true );
        $ess_heading = get_post_meta( $post->ID, '_trek_ess_heading', true );
        $ess_intro   = get_post_meta( $post->ID, '_trek_ess_intro', true );
        if ( empty( $ess_intro ) ) {
            $ess_intro = '<p>Requirements and conditions can change. Confirm final visa, health, insurance and equipment guidance in your departure-specific trip notes.</p>';
        }

        // Side Trip Note Card
        $card_img    = get_post_meta( $post->ID, '_trek_ess_card_img', true );
        $card_kicker = get_post_meta( $post->ID, '_trek_ess_card_kicker', true );
        $card_title  = get_post_meta( $post->ID, '_trek_ess_card_title', true );
        $card_desc   = get_post_meta( $post->ID, '_trek_ess_card_desc', true );
        $card_btn    = get_post_meta( $post->ID, '_trek_ess_card_btn', true );
        $card_email  = get_post_meta( $post->ID, '_trek_ess_card_email', true );
        $card_file   = get_post_meta( $post->ID, '_trek_ess_card_file', true );

        $accordions = get_post_meta( $post->ID, '_trek_essential_list', true );
        if ( empty( $accordions ) || ! is_array( $accordions ) ) {
            $accordions = array();
        }
        ?>
        <div class="ya-admin-box">
            <h4 style="margin-top:0; color:#022f2e;">Top Section Headings & WYSIWYG Narrative</h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label>Kicker</label>
                    <input type="text" name="trek_ess_kicker" value="<?php echo esc_attr( $ess_kicker ); ?>" placeholder="e.g. Before You Go">
                </div>
                <div class="ya-admin-field">
                    <label>Script Title (Heading 1)</label>
                    <input type="text" name="trek_ess_script" value="<?php echo esc_attr( $ess_script ); ?>" placeholder="e.g. Prepare Well">
                </div>
                <div class="ya-admin-field">
                    <label>Main Heading (Heading 2)</label>
                    <input type="text" name="trek_ess_heading" value="<?php echo esc_attr( $ess_heading ); ?>" placeholder="e.g. Essential Everest Trek Information">
                </div>
            </div>
            <div class="ya-admin-field">
                <label><strong>Intro Text (WYSIWYG Editor)</strong></label>
                <?php
                wp_editor( $ess_intro, 'trek_ess_intro', array(
                    'textarea_name' => 'trek_ess_intro',
                    'textarea_rows' => 4,
                    'media_buttons' => false,
                    'teeny'         => false,
                    'quicktags'     => true,
                ) );
                ?>
            </div>
        </div>

        <div class="ya-admin-box" style="background:#f7f5f0;">
            <h4 style="margin-top:0; color:#022f2e;">Side Trip Notes Card</h4>
            <div class="ya-admin-row">
                <div class="ya-admin-field">
                    <label>Side Card Image</label>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="text" name="trek_ess_card_img" id="trek_ess_card_img" value="<?php echo esc_attr( $card_img ); ?>" style="flex-grow:1;" placeholder="Upload or enter side card image URL">
                        <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_ess_card_img', 'trek_ess_card_prev')">Upload</button>
                    </div>
                    <img id="trek_ess_card_prev" class="ya-img-preview" src="<?php echo esc_url( $card_img ); ?>" style="<?php echo empty( $card_img ) ? 'display:none;' : ''; ?>">
                </div>
                <div>
                    <div class="ya-admin-field">
                        <label>Side Card Kicker</label>
                        <input type="text" name="trek_ess_card_kicker" value="<?php echo esc_attr( $card_kicker ); ?>" placeholder="e.g. Detailed Preparation">
                    </div>
                    <div class="ya-admin-field">
                        <label>Side Card Title</label>
                        <input type="text" name="trek_ess_card_title" value="<?php echo esc_attr( $card_title ); ?>" placeholder="e.g. Request the current trip notes">
                    </div>
                    <div class="ya-admin-field">
                        <label>Side Card Email</label>
                        <input type="email" name="trek_ess_card_email" value="<?php echo esc_attr( $card_email ); ?>" placeholder="sales@yetiadventure.com">
                    </div>
                </div>
            </div>
            <div class="ya-admin-field">
                <label>Side Card Description</label>
                <textarea name="trek_ess_card_desc" rows="2" placeholder="e.g. Departure-specific notes should contain the final kit list, inclusions, practical guidance and any updates affecting your chosen date."><?php echo esc_textarea( $card_desc ); ?></textarea>
            </div>
            <div class="ya-admin-field" style="margin-top:12px;">
                <label><strong>Trip Notes / Newsletter PDF File (Direct Download for Travelers)</strong></label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_ess_card_file" id="trek_ess_card_file" value="<?php echo esc_attr( $card_file ); ?>" style="flex-grow:1;" placeholder="Upload PDF or enter download URL (e.g. https://.../trip-notes.pdf)">
                    <button type="button" class="button button-secondary" onclick="yaOpenFileUploader(this, 'trek_ess_card_file')">Upload PDF / File</button>
                </div>
                <p class="description" style="margin:4px 0 0 0; font-size:12px; color:#64748b;">If uploaded, travelers will see a direct "Download Trip Notes (PDF)" button. If left empty, a helpful notice stating that current season notes are being updated will appear with an email request button.</p>
            </div>
        </div>

        <div class="ya-admin-subhead">
            <span>Accordion Items Repeater</span>
            <button type="button" class="button button-primary button-small" id="ya-add-ess-btn">+ Add Accordion Section</button>
        </div>

        <div id="ya-ess-rows">
            <?php foreach ( $accordions as $idx => $item ) : ?>
                <div class="ya-rep-item">
                    <div class="ya-rep-head">
                        <span>Accordion Item: <?php echo esc_html( $item['title'] ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-field" style="margin-bottom:8px;">
                        <label>Accordion Title</label>
                        <input type="text" name="ess_title[]" value="<?php echo esc_attr( $item['title'] ); ?>" placeholder="e.g. Visa and travel documents">
                    </div>
                    <div class="ya-admin-field">
                        <label>Accordion Content (Supports Paragraphs & Lists &lt;ul&gt;&lt;li&gt;)</label>
                        <textarea name="ess_content[]" rows="4" placeholder="Enter detailed guidelines, tips, or bullet points..."><?php echo esc_textarea( $item['content'] ); ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.getElementById('ya-add-ess-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-ess-rows');
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.innerHTML = `
                    <div class="ya-rep-head">
                        <span>New Accordion Item</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-field" style="margin-bottom:8px;">
                        <label>Accordion Title</label>
                        <input type="text" name="ess_title[]" placeholder="e.g. Health, altitude and vaccinations">
                    </div>
                    <div class="ya-admin-field">
                        <label>Accordion Content (Supports HTML formatting)</label>
                        <textarea name="ess_content[]" rows="4" placeholder="Enter detailed information, guidelines or list items..."></textarea>
                    </div>
                `;
                container.appendChild(div);
            });
        </script>
        <?php
    }

    /**
     * 9. Reviews Settings & Curated Reviews
     */
    public static function render_reviews_metabox( $post ) {
        $rev_kicker  = get_post_meta( $post->ID, '_trek_review_kicker', true );
        $rev_script  = get_post_meta( $post->ID, '_trek_review_script', true );
        $rev_heading = get_post_meta( $post->ID, '_trek_review_heading', true );
        $rev_subtext = get_post_meta( $post->ID, '_trek_review_subtext', true );

        // Curated Reviews list
        $curated_reviews = get_post_meta( $post->ID, '_trek_curated_reviews', true );
        if ( empty( $curated_reviews ) || ! is_array( $curated_reviews ) ) {
            $curated_reviews = array();
        }
        ?>
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label>Review Kicker</label>
                <input type="text" name="trek_review_kicker" value="<?php echo esc_attr( $rev_kicker ); ?>" placeholder="e.g. Traveller Stories">
            </div>
            <div class="ya-admin-field">
                <label>Review Script Title</label>
                <input type="text" name="trek_review_script" value="<?php echo esc_attr( $rev_script ); ?>" placeholder="e.g. From the Trail">
            </div>
            <div class="ya-admin-field">
                <label>Review Main Heading</label>
                <input type="text" name="trek_review_heading" value="<?php echo esc_attr( $rev_heading ); ?>" placeholder="e.g. Everest Experience Reviews">
            </div>
        </div>

        <div class="ya-admin-subhead">
            <span>Curated Featured Reviews (Displayed on Trek Page)</span>
            <button type="button" class="button button-primary button-small" id="ya-add-rev-btn">+ Add Curated Review</button>
        </div>

        <div id="ya-rev-rows">
            <?php foreach ( $curated_reviews as $idx => $r ) : ?>
                <div class="ya-rep-item">
                    <div class="ya-rep-head">
                        <span>Review: <?php echo esc_html( $r['name'] ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-row ya-admin-row--3">
                        <div class="ya-admin-field">
                            <label>Reviewer Name</label>
                            <input type="text" name="rev_name[]" value="<?php echo esc_attr( $r['name'] ); ?>" placeholder="e.g. Jo & Laurie Prowse">
                        </div>
                        <div class="ya-admin-field">
                            <label>City & Country</label>
                            <input type="text" name="rev_loc[]" value="<?php echo esc_attr( $r['location'] ); ?>" placeholder="e.g. London, United Kingdom">
                        </div>
                        <div class="ya-admin-field">
                            <label>Rating (1 to 5)</label>
                            <select name="rev_rating[]">
                                <option value="5" <?php selected( $r['rating'], '5' ); ?>>★★★★★ (5 Stars)</option>
                                <option value="4" <?php selected( $r['rating'], '4' ); ?>>★★★★☆ (4 Stars)</option>
                                <option value="3" <?php selected( $r['rating'], '3' ); ?>>★★★☆☆ (3 Stars)</option>
                            </select>
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Review Quote / Comment</label>
                        <textarea name="rev_quote[]" rows="2" placeholder="e.g. They praised their trek manager’s professionalism and said the team made the mountain journey enjoyable from beginning to end."><?php echo esc_textarea( $r['quote'] ); ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="background:#eef6ff; padding:12px 16px; border-radius:6px; border:1px solid #bfdbfe; margin-top:12px;">
            <p style="margin:0; font-size:13px; color:#1e40af;">
                💡 <strong>Review Database Storage:</strong> User submissions made via the "Write a Review" form on the frontend are automatically saved into the WordPress database under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_review' ) ); ?>"><strong>Trek Manager &rarr; Reviews</strong></a> where you can review, edit, approve, or delete them!
            </p>
        </div>

        <script>
            document.getElementById('ya-add-rev-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-rev-rows');
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.innerHTML = `
                    <div class="ya-rep-head">
                        <span>New Review</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-row ya-admin-row--3">
                        <div class="ya-admin-field">
                            <label>Reviewer Name</label>
                            <input type="text" name="rev_name[]" placeholder="e.g. John & Sarah Doe">
                        </div>
                        <div class="ya-admin-field">
                            <label>City & Country</label>
                            <input type="text" name="rev_loc[]" placeholder="e.g. Sydney, Australia">
                        </div>
                        <div class="ya-admin-field">
                            <label>Rating</label>
                            <select name="rev_rating[]">
                                <option value="5" selected>★★★★★ (5 Stars)</option>
                                <option value="4">★★★★☆ (4 Stars)</option>
                                <option value="3">★★★☆☆ (3 Stars)</option>
                                <option value="2">★★☆☆☆ (2 Stars)</option>
                                <option value="1">★☆☆☆☆ (1 Star)</option>
                            </select>
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Review Quote / Comment</label>
                        <textarea name="rev_quote[]" rows="2" placeholder="e.g. An unforgettable experience with exceptional guiding and stunning mountain vistas."></textarea>
                    </div>
                `;
                container.appendChild(div);
            });
        </script>
        <?php
    }

    /**
     * 10. Contact, WhatsApp & Fixed Departure Settings
     */
    public static function render_contact_metabox( $post ) {
        $whatsapp    = get_post_meta( $post->ID, '_trek_whatsapp_number', true );
        $email       = get_post_meta( $post->ID, '_trek_sales_email', true );

        $dep_kicker  = get_post_meta( $post->ID, '_trek_dep_kicker', true );
        $dep_script  = get_post_meta( $post->ID, '_trek_dep_script', true );
        $dep_heading = get_post_meta( $post->ID, '_trek_dep_heading', true );
        $dep_subtext = get_post_meta( $post->ID, '_trek_dep_subtext', true );

        $box_script  = get_post_meta( $post->ID, '_trek_dep_box_script', true );
        $box_title   = get_post_meta( $post->ID, '_trek_dep_box_title', true );
        $box_desc    = get_post_meta( $post->ID, '_trek_dep_box_desc', true );
        $box_photo   = get_post_meta( $post->ID, '_trek_dep_box_photo', true );
        $box_note    = get_post_meta( $post->ID, '_trek_dep_box_note', true );
        ?>
        <div class="ya-admin-box">
            <h4 style="margin-top:0; color:#022f2e;">Fixed Departure Section Headings</h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label>Kicker</label>
                    <input type="text" name="trek_dep_kicker" value="<?php echo esc_attr( $dep_kicker ); ?>" placeholder="e.g. Travel Together">
                </div>
                <div class="ya-admin-field">
                    <label>Script Heading</label>
                    <input type="text" name="trek_dep_script" value="<?php echo esc_attr( $dep_script ); ?>" placeholder="e.g. Choose Your Month">
                </div>
                <div class="ya-admin-field">
                    <label>Main Heading</label>
                    <input type="text" name="trek_dep_heading" value="<?php echo esc_attr( $dep_heading ); ?>" placeholder="e.g. Join a Fixed Departure">
                </div>
            </div>
            <div class="ya-admin-field">
                <label>Intro Subtext</label>
                <input type="text" name="trek_dep_subtext" value="<?php echo esc_attr( $dep_subtext ); ?>" placeholder="e.g. No fixed dates have been hard-coded into this page. Submit your preferred month and the team will confirm the current departure schedule.">
            </div>
        </div>

        <div class="ya-admin-box" style="background:#f7f5f0;">
            <h4 style="margin-top:0; color:#022f2e;">WhatsApp Inquiry Card Details</h4>
            <div class="ya-admin-row">
                <div class="ya-admin-field">
                    <label>Card Script</label>
                    <input type="text" name="trek_dep_box_script" value="<?php echo esc_attr( $box_script ); ?>" placeholder="e.g. Everest Awaits">
                </div>
                <div class="ya-admin-field">
                    <label>Card Heading</label>
                    <input type="text" name="trek_dep_box_title" value="<?php echo esc_attr( $box_title ); ?>" placeholder="e.g. Ask about upcoming group dates">
                </div>
            </div>
            <div class="ya-admin-field">
                <label>Card Description</label>
                <textarea name="trek_dep_box_desc" rows="2" placeholder="e.g. Choose a preferred month, share your group size and send the enquiry directly to Yeti Adventure through WhatsApp."><?php echo esc_textarea( $box_desc ); ?></textarea>
            </div>
            <div class="ya-admin-field">
                <label>Side Photo Image URL</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_dep_box_photo" id="trek_dep_box_photo" value="<?php echo esc_attr( $box_photo ); ?>" style="flex-grow:1;" placeholder="Upload or enter card photo URL">
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_dep_box_photo', 'trek_dep_prev')">Upload</button>
                </div>
                <img id="trek_dep_prev" class="ya-img-preview" src="<?php echo esc_url( $box_photo ); ?>" style="<?php echo empty( $box_photo ) ? 'display:none;' : ''; ?>">
            </div>
            <div class="ya-admin-field">
                <label>Disclaimer Note</label>
                <textarea name="trek_dep_box_note" rows="2" placeholder="e.g. Departure availability, itinerary details and prices should be confirmed directly before booking."><?php echo esc_textarea( $box_note ); ?></textarea>
            </div>
        </div>

        <div class="ya-admin-row">
            <div class="ya-admin-field">
                <label><strong><?php esc_html_e( 'WhatsApp Number (with country code, no + or spaces)', 'trek-content-manager' ); ?></strong></label>
                <input type="text" name="trek_whatsapp_number" value="<?php echo esc_attr( $whatsapp ); ?>" placeholder="9779803197006">
                <span class="description"><?php esc_html_e( 'Receives Fixed Departure inquiries directly via WhatsApp.', 'trek-content-manager' ); ?></span>
            </div>
            <div class="ya-admin-field">
                <label><strong><?php esc_html_e( 'Sales / Trip Notes Email', 'trek-content-manager' ); ?></strong></label>
                <input type="email" name="trek_sales_email" value="<?php echo esc_attr( $email ); ?>" placeholder="sales@yetiadventure.com">
            </div>
        </div>

        <?php
        // Load saved departure dates
        $dep_dates = get_post_meta( $post->ID, '_trek_departure_dates', true );
        if ( empty( $dep_dates ) || ! is_array( $dep_dates ) ) {
            $dep_dates = array();
        }
        ?>
        <div class="ya-admin-box" style="background:#f0faf4; border:1px solid #a7d7be; border-radius:8px; padding:18px; margin-top:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div>
                    <h4 style="margin:0; color:#022f2e; font-size:14px;">📅 Fixed Departure Dates</h4>
                    <p style="margin:4px 0 0; font-size:12px; color:#4a7c59;">Add specific departure dates shown on the trip page. Each date can have a price, available seats, and status.</p>
                </div>
                <button type="button" class="button button-primary button-small" id="ya-add-dep-date-btn">+ Add Departure Date</button>
            </div>

            <div id="ya-dep-dates-rows">
                <?php foreach ( $dep_dates as $idx => $dep ) : ?>
                <div class="ya-rep-item" style="background:#fff; border:1px solid #c9e8d5; border-radius:6px; padding:12px; margin-bottom:10px;">
                    <div class="ya-rep-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-weight:600; color:#022f2e; font-size:13px;">🗓️ Departure: <?php echo esc_html( $dep['date'] ?? '' ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">✕ Remove</button>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 140px; gap:12px; align-items:end;">
                        <div class="ya-admin-field">
                            <label>Departure Date *</label>
                            <input type="date" name="dep_date[]" value="<?php echo esc_attr( $dep['date'] ?? '' ); ?>" required>
                        </div>
                        <div class="ya-admin-field">
                            <label>Price (e.g. USD 1,290)</label>
                            <input type="text" name="dep_price[]" value="<?php echo esc_attr( $dep['price'] ?? '' ); ?>" placeholder="e.g. USD 1,290">
                        </div>
                        <div class="ya-admin-field">
                            <label>Available Seats</label>
                            <input type="text" name="dep_seats[]" value="<?php echo esc_attr( $dep['seats'] ?? '' ); ?>" placeholder="e.g. 8 places left">
                        </div>
                        <div class="ya-admin-field">
                            <label>Status</label>
                            <select name="dep_status[]">
                                <option value="open" <?php selected( $dep['status'] ?? 'open', 'open' ); ?>>✅ Open</option>
                                <option value="limited" <?php selected( $dep['status'] ?? '', 'limited' ); ?>>⚠️ Limited</option>
                                <option value="soldout" <?php selected( $dep['status'] ?? '', 'soldout' ); ?>>❌ Sold Out</option>
                                <option value="guaranteed" <?php selected( $dep['status'] ?? '', 'guaranteed' ); ?>>🌟 Guaranteed</option>
                            </select>
                        </div>
                    </div>
                    <div class="ya-admin-field" style="margin-top:8px;">
                        <label>Note / Extra Info <span style="font-weight:400; color:#888;">(optional)</span></label>
                        <input type="text" name="dep_note[]" value="<?php echo esc_attr( $dep['note'] ?? '' ); ?>" placeholder="e.g. Includes Annapurna permit, group discount available">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ( empty( $dep_dates ) ) : ?>
            <p id="ya-dep-dates-empty" style="color:#888; font-size:13px; font-style:italic; text-align:center; padding:16px 0; margin:0;">No departure dates added yet. Click "Add Departure Date" to add the first one.</p>
            <?php else : ?>
            <p id="ya-dep-dates-empty" style="display:none;"></p>
            <?php endif; ?>
        </div>

        <script>
        document.getElementById('ya-add-dep-date-btn')?.addEventListener('click', function() {
            const container = document.getElementById('ya-dep-dates-rows');
            const empty = document.getElementById('ya-dep-dates-empty');
            if (empty) empty.style.display = 'none';
            const div = document.createElement('div');
            div.className = 'ya-rep-item';
            div.style.cssText = 'background:#fff; border:1px solid #c9e8d5; border-radius:6px; padding:12px; margin-bottom:10px;';
            div.innerHTML = `
                <div class="ya-rep-head" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span style="font-weight:600; color:#022f2e; font-size:13px;">🗓️ New Departure Date</span>
                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">✕ Remove</button>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 140px; gap:12px; align-items:end;">
                    <div class="ya-admin-field">
                        <label>Departure Date *</label>
                        <input type="date" name="dep_date[]" required>
                    </div>
                    <div class="ya-admin-field">
                        <label>Price (e.g. USD 1,290)</label>
                        <input type="text" name="dep_price[]" placeholder="e.g. USD 1,290">
                    </div>
                    <div class="ya-admin-field">
                        <label>Available Seats</label>
                        <input type="text" name="dep_seats[]" placeholder="e.g. 8 places left">
                    </div>
                    <div class="ya-admin-field">
                        <label>Status</label>
                        <select name="dep_status[]">
                            <option value="open" selected>✅ Open</option>
                            <option value="limited">⚠️ Limited</option>
                            <option value="soldout">❌ Sold Out</option>
                            <option value="guaranteed">🌟 Guaranteed</option>
                        </select>
                    </div>
                </div>
                <div class="ya-admin-field" style="margin-top:8px;">
                    <label>Note / Extra Info <span style="font-weight:400; color:#888;">(optional)</span></label>
                    <input type="text" name="dep_note[]" placeholder="e.g. Includes Annapurna permit, group discount available">
                </div>
            `;
            container.appendChild(div);
        });
        </script>
        <?php
    }

    /**
     * 11. Structured JSON Payload & REST API Data Metabox
     */
    public static function render_json_metabox( $post ) {
        $structured_data = get_post_meta( $post->ID, '_trek_structured_data', true );
        if ( empty( $structured_data ) && class_exists( 'Trek_Renderer' ) ) {
            $data_array = Trek_Renderer::get_full_trek_data( $post->ID );
            $structured_data = wp_json_encode( $data_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        }
        $rest_url = rest_url( 'trek-manager/v1/destinations/' . $post->ID );
        ?>
        <div class="ya-admin-box" style="background:#0f172a; color:#e2e8f0; border:1px solid #1e293b; border-radius:8px; padding:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
                <div>
                    <strong style="color:#38bdf8; font-size:14px;">⚡ Live Canonical JSON Payload (Synced with Database & REST API)</strong>
                    <p style="margin:4px 0 0 0; color:#94a3b8; font-size:12px;">
                        REST API Endpoint: <a href="<?php echo esc_url( $rest_url ); ?>" target="_blank" style="color:#7dd3fc; text-decoration:underline;"><?php echo esc_html( $rest_url ); ?></a>
                    </p>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="button" id="ya-copy-cpt-json" style="background:#334155; color:#fff; border-color:#475569;">
                        📋 <?php esc_html_e( 'Copy Full JSON', 'trek-content-manager' ); ?>
                    </button>
                    <button type="button" class="button" id="ya-download-cpt-json" style="background:#0284c7; color:#fff; border-color:#0369a1;">
                        📥 <?php esc_html_e( 'Download .json', 'trek-content-manager' ); ?>
                    </button>
                </div>
            </div>
            <textarea id="ya_trek_structured_json_field" name="trek_structured_data_preview" readonly style="width:100%; height:260px; background:#090d16; color:#a5f3fc; font-family:Consolas,Monaco,monospace; font-size:12px; border:1px solid #334155; border-radius:6px; padding:12px; box-sizing:border-box;"><?php echo esc_textarea( $structured_data ); ?></textarea>
            <span style="font-size:11px; color:#94a3b8; margin-top:6px; display:block;">
                <?php esc_html_e( 'This JSON field is automatically updated with all metaboxes, repeaters, media URLs, pricing, and taxonomies whenever this destination is saved.', 'trek-content-manager' ); ?>
            </span>
        </div>
        <script>
            (function() {
                const btnCopy = document.getElementById('ya-copy-cpt-json');
                const btnDl = document.getElementById('ya-download-cpt-json');
                const textarea = document.getElementById('ya_trek_structured_json_field');
                if (btnCopy && textarea) {
                    btnCopy.addEventListener('click', function() {
                        navigator.clipboard.writeText(textarea.value).then(function() {
                            btnCopy.textContent = '✔ Copied!';
                            setTimeout(function() { btnCopy.textContent = '📋 Copy Full JSON'; }, 2000);
                        });
                    });
                }
                if (btnDl && textarea) {
                    btnDl.addEventListener('click', function() {
                        const blob = new Blob([textarea.value], { type: 'application/json' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'trek-destination-' + <?php echo intval( $post->ID ); ?> + '.json';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                    });
                }
            })();
        </script>
        <?php
    }

    /** Read-only details for enquiries saved from the trip planning popup or journey finder. */
    public static function render_enquiry_detail_metabox( $post ) {
        $trek_id     = absint( get_post_meta( $post->ID, '_trek_enquiry_trek_id', true ) );
        $trek_title  = get_post_meta( $post->ID, '_trek_enquiry_trek_title', true );
        $name        = get_post_meta( $post->ID, '_trek_enquiry_name', true ) ?: $post->post_title;
        $email       = get_post_meta( $post->ID, '_trek_enquiry_email', true );
        $phone       = get_post_meta( $post->ID, '_trek_enquiry_phone', true );
        $travellers  = get_post_meta( $post->ID, '_trek_enquiry_travellers', true );
        $source      = get_post_meta( $post->ID, '_trek_enquiry_source', true );
        $date        = get_post_meta( $post->ID, '_trek_enquiry_date', true ) ?: $post->post_date;
        $ip          = get_post_meta( $post->ID, '_trek_enquiry_ip', true );
        $status      = get_post_meta( $post->ID, '_trek_enquiry_status', true ) ?: 'New';
        $region      = get_post_meta( $post->ID, '_trek_enquiry_region', true );
        $feeling     = get_post_meta( $post->ID, '_trek_enquiry_feeling', true );
        $pace        = get_post_meta( $post->ID, '_trek_enquiry_pace', true );
        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
        ?>
        <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">
            <!-- Top Header Status Strip -->
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:14px; margin-bottom:18px; flex-wrap:wrap; gap:10px;">
                <div>
                    <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#617555;"><?php esc_html_e( 'Enquiry Status', 'trek-content-manager' ); ?></span>
                    <h3 style="margin:2px 0 0; font-size:18px; color:#173331; font-weight:700;"><?php echo esc_html( $name ); ?></h3>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span style="display:inline-block; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600; background:#f0faf4; color:#166534; border:1px solid #bbf7d0;">
                        <?php echo esc_html( $status ); ?>
                    </span>
                    <span style="font-size:12px; color:#64748b;">
                        📅 <?php echo esc_html( date_i18n( 'M j, Y - g:i a', strtotime( $date ) ) ); ?>
                    </span>
                </div>
            </div>

            <!-- Main Info Grid -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:20px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Destination / Journey', 'trek-content-manager' ); ?></span>
                    <?php
                    if ( $trek_id && get_post( $trek_id ) ) {
                        echo '<a href="' . esc_url( get_edit_post_link( $trek_id ) ) . '" style="font-weight:700; color:#045857; text-decoration:none; font-size:14px;">🏔️ ' . esc_html( get_the_title( $trek_id ) ) . '</a>';
                        echo ' <a href="' . esc_url( get_permalink( $trek_id ) ) . '" target="_blank" style="font-size:11px; color:#64748b; text-decoration:underline; margin-left:4px;">(view page ↗)</a>';
                    } elseif ( ! empty( $trek_title ) ) {
                        echo '<strong style="color:#173331; font-size:14px;">🏔️ ' . esc_html( $trek_title ) . '</strong>';
                    } else {
                        echo '<span style="color:#94a3b8;">' . esc_html__( 'General Destination Enquiry', 'trek-content-manager' ) . '</span>';
                    }
                    ?>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Email Address', 'trek-content-manager' ); ?></span>
                    <?php if ( $email ) : ?>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:#0284c7; font-weight:600; font-size:14px; text-decoration:none;">
                            ✉️ <?php echo esc_html( $email ); ?>
                        </a>
                    <?php else : ?>
                        <span style="color:#94a3b8;">—</span>
                    <?php endif; ?>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?></span>
                    <?php if ( $phone ) : ?>
                        <strong style="color:#173331; font-size:14px;"><?php echo esc_html( $phone ); ?></strong>
                        <?php if ( ! empty( $clean_phone ) ) : ?>
                            <a href="https://wa.me/<?php echo esc_attr( $clean_phone ); ?>" target="_blank" style="display:inline-block; margin-left:6px; background:#25D366; color:#fff; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:700; text-decoration:none;">
                                WhatsApp ↗
                            </a>
                        <?php endif; ?>
                    <?php else : ?>
                        <span style="color:#94a3b8;">Not provided</span>
                    <?php endif; ?>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Number of Travellers', 'trek-content-manager' ); ?></span>
                    <strong style="color:#173331; font-size:14px;">👥 <?php echo esc_html( $travellers ?: 'Not specified' ); ?></strong>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Lead Source', 'trek-content-manager' ); ?></span>
                    <?php
                    if ( 'journey_finder' === $source || 'Journey Selector' === $source ) {
                        echo '<span style="display:inline-block; color:#022f2e; background:#e2f2e9; padding:2px 8px; border-radius:4px; font-weight:600; font-size:12px;">🗺️ Journey Selector</span>';
                    } elseif ( 'single-destination-planning-popup' === $source ) {
                        echo '<span style="display:inline-block; color:#92400e; background:#fef3c7; padding:2px 8px; border-radius:4px; font-weight:600; font-size:12px;">🏔️ Trip Planning Popup</span>';
                    } else {
                        echo '<strong style="color:#173331; font-size:13px;">' . esc_html( $source ?: 'Frontend' ) . '</strong>';
                    }
                    ?>
                </div>

                <?php if ( ! empty( $ip ) ) : ?>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px;">
                    <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:4px;"><?php esc_html_e( 'Client IP', 'trek-content-manager' ); ?></span>
                    <code style="background:#edf2f7; padding:2px 6px; border-radius:4px; font-size:12px;"><?php echo esc_html( $ip ); ?></code>
                </div>
                <?php endif; ?>
            </div>

            <!-- Selected Preferences (if available) -->
            <?php if ( ! empty( $region ) || ! empty( $feeling ) || ! empty( $pace ) ) : ?>
                <div style="margin-bottom:20px; padding:14px; background:#f0f7f6; border-radius:8px; border:1px solid #cce5e3;">
                    <strong style="color:#022f2e; display:block; margin-bottom:8px; font-size:13px;">🧭 <?php esc_html_e( 'Selected Travel Preferences', 'trek-content-manager' ); ?>:</strong>
                    <div style="display:flex; gap:16px; flex-wrap:wrap;">
                        <?php if ( ! empty( $region ) ) : ?>
                            <div><span style="color:#646970; font-size:11px; text-transform:uppercase; font-weight:700;"><?php esc_html_e( 'Region', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $region ) ) ); ?></strong></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $feeling ) ) : ?>
                            <div><span style="color:#646970; font-size:11px; text-transform:uppercase; font-weight:700;"><?php esc_html_e( 'Feeling', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( $feeling ) ); ?></strong></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $pace ) ) : ?>
                            <div><span style="color:#646970; font-size:11px; text-transform:uppercase; font-weight:700;"><?php esc_html_e( 'Pace', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( $pace ) ); ?></strong></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Message Box -->
            <div style="background:#fbfaf7; border:1px solid #e0dad0; border-radius:8px; padding:16px; margin-bottom:18px;">
                <span style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:#854d0e; margin-bottom:8px; letter-spacing:0.05em;">
                    💬 <?php esc_html_e( 'Traveler’s Message & Notes', 'trek-content-manager' ); ?>
                </span>
                <div style="font-size:14px; line-height:1.7; color:#173331; white-space:pre-wrap;">
                    <?php echo esc_html( $post->post_content ?: 'No additional message provided.' ); ?>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <?php if ( $email ) : ?>
                    <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo esc_attr( rawurlencode( 'Your enquiry for ' . ( $trek_title ?: 'Yeti Adventure' ) ) ); ?>" class="button button-primary" style="background:#045857; border-color:#023837; border-radius:6px;">
                        ✉️ <?php esc_html_e( 'Reply by Email', 'trek-content-manager' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $clean_phone ) ) : ?>
                    <a href="https://wa.me/<?php echo esc_attr( $clean_phone ); ?>" target="_blank" class="button" style="background:#25D366; color:#fff; border-color:#1ebc59; border-radius:6px;">
                        💬 <?php esc_html_e( 'Chat on WhatsApp', 'trek-content-manager' ); ?>
                    </a>
                <?php endif; ?>
                <?php if ( $trek_id && get_post( $trek_id ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $trek_id ) ); ?>" target="_blank" class="button button-secondary" style="border-radius:6px;">
                        🏔️ <?php esc_html_e( 'View Trek Page', 'trek-content-manager' ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Metabox for `trek_review` Post Type (Admin View & Edit Review Submissions)
     */
    public static function render_review_detail_metabox( $post ) {
        wp_nonce_field( 'save_review_details_action', 'review_details_nonce' );

        $trek_id   = get_post_meta( $post->ID, '_trek_id', true );
        $rating    = get_post_meta( $post->ID, '_trek_rating', true ) ?: 5;
        $rev_title = get_post_meta( $post->ID, '_trek_review_title', true );
        $rev_name  = get_post_meta( $post->ID, '_trek_reviewer_name', true ) ?: $post->post_title;
        $rev_type  = get_post_meta( $post->ID, '_trek_traveler_type', true ) ?: 'Solo Traveler';
        $email     = get_post_meta( $post->ID, '_trek_reviewer_email', true );
        $city      = get_post_meta( $post->ID, '_trek_city', true );
        $country   = get_post_meta( $post->ID, '_trek_country', true );
        $tour_date = get_post_meta( $post->ID, '_trek_tour_date', true );
        $moment    = get_post_meta( $post->ID, '_trek_moment', true );
        $leader    = get_post_meta( $post->ID, '_trek_leader', true );
        $advice    = get_post_meta( $post->ID, '_trek_advice', true );
        $approved  = get_post_meta( $post->ID, '_trek_approved', true );
        if ( '' === $approved ) {
            $approved = ( 'publish' === $post->post_status ) ? '1' : '0';
        }

        $treks = get_posts( array( 'post_type' => 'trek_destination', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
        ?>
        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:14px; margin-bottom:14px;">
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Associated Trek Destination</label>
                <select name="review_trek_id" style="width:100%;">
                    <option value="0">-- General / Not Linked --</option>
                    <?php foreach ( $treks as $t ) : ?>
                        <option value="<?php echo esc_attr( $t->ID ); ?>" <?php selected( $trek_id, $t->ID ); ?>><?php echo esc_html( $t->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Rating (1 - 5 Stars)</label>
                <select name="review_rating" style="width:100%;">
                    <option value="5" <?php selected( $rating, 5 ); ?>>★★★★★ (5 Stars)</option>
                    <option value="4.5" <?php selected( strval($rating), '4.5' ); ?>>★★★★½ (4.5 Stars)</option>
                    <option value="4" <?php selected( $rating, 4 ); ?>>★★★★☆ (4 Stars)</option>
                    <option value="3.5" <?php selected( strval($rating), '3.5' ); ?>>★★★½☆ (3.5 Stars)</option>
                    <option value="3" <?php selected( $rating, 3 ); ?>>★★★☆☆ (3 Stars)</option>
                    <option value="2" <?php selected( $rating, 2 ); ?>>★★☆☆☆ (2 Stars)</option>
                    <option value="1" <?php selected( $rating, 1 ); ?>>★☆☆☆☆ (1 Star)</option>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin-bottom:14px;">
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Review Title</label>
                <input type="text" name="review_title" value="<?php echo esc_attr( $rev_title ); ?>" placeholder="e.g. Life changing Himalayan expedition" style="width:100%;">
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Reviewer Name</label>
                <input type="text" name="review_name" value="<?php echo esc_attr( $rev_name ); ?>" placeholder="e.g. Sarah Jenkins" style="width:100%;">
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Traveler Type</label>
                <select name="review_type" style="width:100%;">
                    <option value="Solo Traveler" <?php selected( $rev_type, 'Solo Traveler' ); ?>>Solo Traveler</option>
                    <option value="Couples Trek" <?php selected( $rev_type, 'Couples Trek' ); ?>>Couples Trek</option>
                    <option value="Small Group" <?php selected( $rev_type, 'Small Group' ); ?>>Small Group</option>
                    <option value="Family Traveler" <?php selected( $rev_type, 'Family Traveler' ); ?>>Family Traveler</option>
                    <option value="Guided Group" <?php selected( $rev_type, 'Guided Group' ); ?>>Guided Group</option>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px; margin-bottom:14px;">
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Reviewer Email</label>
                <input type="email" name="review_email" value="<?php echo esc_attr( $email ); ?>" placeholder="e.g. traveller@example.com" style="width:100%;">
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">City</label>
                <input type="text" name="review_city" value="<?php echo esc_attr( $city ); ?>" placeholder="e.g. London" style="width:100%;">
            </div>
            <div>
                <label style="font-weight:600; display:block; margin-bottom:4px;">Country</label>
                <input type="text" name="review_country" value="<?php echo esc_attr( $country ); ?>" placeholder="e.g. United Kingdom" style="width:100%;">
            </div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:600; display:block; margin-bottom:4px;">Tour Date</label>
            <input type="text" name="review_tour_date" value="<?php echo esc_attr( $tour_date ); ?>" placeholder="e.g. 2026-04" style="width:100%;">
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:600; display:block; margin-bottom:4px;">Inspirational Moment of the Trip</label>
            <textarea name="review_moment" rows="2" placeholder="e.g. Watching the sunrise illuminate Everest and Lhotse from Kala Patthar." style="width:100%;"><?php echo esc_textarea( $moment ); ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:600; display:block; margin-bottom:4px;">What did you think of your trek leader?</label>
            <textarea name="review_leader" rows="2" placeholder="e.g. Outstanding professionalism, caring, knowledgeable and attentive." style="width:100%;"><?php echo esc_textarea( $leader ); ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-weight:600; display:block; margin-bottom:4px;">Advice for Future Travellers</label>
            <textarea name="review_advice" rows="2" placeholder="e.g. Pack good trekking poles and take the acclimatisation days steadily." style="width:100%;"><?php echo esc_textarea( $advice ); ?></textarea>
        </div>

        <div style="background:#f9fafb; padding:12px; border-radius:6px; border:1px solid #e5e7eb;">
            <label style="font-weight:700; color:#111827;">
                <input type="checkbox" name="review_approved" value="1" <?php checked( $approved, '1' ); ?>>
                <?php esc_html_e( 'Approved / Published to Frontend View Page', 'trek-content-manager' ); ?>
            </label>
            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">
                <?php esc_html_e( 'When checked, this review will be marked as Approved (Published) and will appear on the linked trek destination page.', 'trek-content-manager' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save Review Meta Box Data
     */
    public static function save_review_meta_data( $post_id ) {
        if ( ! isset( $_POST['review_details_nonce'] ) || ! wp_verify_nonce( $_POST['review_details_nonce'], 'save_review_details_action' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = array(
            'review_trek_id'   => '_trek_id',
            'review_rating'    => '_trek_rating',
            'review_title'     => '_trek_review_title',
            'review_name'      => '_trek_reviewer_name',
            'review_type'      => '_trek_traveler_type',
            'review_email'     => '_trek_reviewer_email',
            'review_city'      => '_trek_city',
            'review_country'   => '_trek_country',
            'review_tour_date' => '_trek_tour_date',
            'review_moment'    => '_trek_moment',
            'review_leader'    => '_trek_leader',
            'review_advice'    => '_trek_advice',
        );

        foreach ( $fields as $input => $meta_key ) {
            if ( isset( $_POST[ $input ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $input ] ) );
            }
        }

        $approved = isset( $_POST['review_approved'] ) ? '1' : '0';
        update_post_meta( $post_id, '_trek_approved', $approved );
        wp_update_post( array(
            'ID'          => $post_id,
            'post_status' => '1' === $approved ? 'publish' : 'pending',
        ) );
    }

    /**
     * Save all Dynamic Form Fields for Trek Destination
     */
    public static function save_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['trek_details_nonce'] ) || ! wp_verify_nonce( $_POST['trek_details_nonce'], 'save_trek_details_action' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // 1. Text & Simple Meta Fields
        $text_fields = array(
            'trek_card_title',
            'trek_card_meta',
            'trek_card_tag',
            'trek_primary_category',
            'trek_card_kicker',
            'trek_card_excerpt',
            'trek_card_image_url',
            'trek_card_order',
            'trek_script_title',
            'trek_hero_title',
            'trek_hero_kicker',
            'trek_hero_days',
            'trek_hero_badge_sub',
            'trek_breadcrumb_region',
            'trek_map_region',
            'trek_map_district',
            'trek_difficulty',
            'trek_price',
            'trek_currency',
            'trek_trip_status',
            // Note: trek_hero_subtitle is handled separately as textarea below
            'trek_hero_bg_img',
            'trek_fact_high_point',
            'trek_fact_low_point',
            'trek_fact_region',
            'trek_fact_season',
            'trek_fact_group_size',
            'trek_fact_trekking',
            'trek_fact_accom',
            'trek_fact_style',
            'trek_fact_distance',
            'trek_fact_start',
            'trek_fact_end',
            'trek_overview_kicker',
            'trek_overview_script',
            'trek_overview_heading',
            'trek_overview_subheading',
            'trek_overview_img',
            'trek_route_img',
            'trek_map_file_url',
            'trek_route_caption_sub',
            'trek_route_caption_h3',
            'trek_itin_kicker',
            'trek_itin_script',
            'trek_itin_heading',
            'trek_itin_subtext',
            'trek_inex_kicker',
            'trek_inex_script',
            'trek_inex_heading',
            'trek_inex_subtext',
            'trek_gallery_kicker',
            'trek_gallery_script',
            'trek_gallery_heading',
            'trek_gallery_subtext',
            'trek_ess_kicker',
            'trek_ess_script',
            'trek_ess_heading',
            'trek_ess_card_img',
            'trek_ess_card_kicker',
            'trek_ess_card_title',
            'trek_ess_card_desc',
            'trek_ess_card_btn',
            'trek_ess_card_email',
            'trek_ess_card_file',
            'trek_review_kicker',
            'trek_review_script',
            'trek_review_heading',
            'trek_review_subtext',
            'trek_dep_kicker',
            'trek_dep_script',
            'trek_dep_heading',
            'trek_dep_subtext',
            'trek_dep_box_script',
            'trek_dep_box_title',
            'trek_dep_box_desc',
            'trek_dep_box_photo',
            'trek_dep_box_note',
            'trek_whatsapp_number',
            'trek_sales_email'
        );

        foreach ( $text_fields as $f ) {
            if ( ! isset( $_POST[ $f ] ) || is_array( $_POST[ $f ] ) ) {
                continue;
            }

            $value = sanitize_text_field( wp_unslash( $_POST[ $f ] ) );

            if ( '' === trim( $value ) ) {
                delete_post_meta( $post_id, '_' . $f );
            } else {
                update_post_meta( $post_id, '_' . $f, $value );
            }
        }

        // Save Taxonomy Multi-Select Categories and Regions
        if ( isset( $_POST['trek_category_ids'] ) && is_array( $_POST['trek_category_ids'] ) ) {
            $cat_ids = array_map( 'intval', array_filter( $_POST['trek_category_ids'] ) );
            wp_set_object_terms( $post_id, $cat_ids, 'trek_category' );
        } elseif ( ! isset( $_POST['trek_category_id'] ) ) {
            wp_set_object_terms( $post_id, array(), 'trek_category' );
        } elseif ( isset( $_POST['trek_category_id'] ) ) {
            $cat_id = intval( $_POST['trek_category_id'] );
            if ( $cat_id > 0 ) {
                wp_set_object_terms( $post_id, array( $cat_id ), 'trek_category' );
            }
        }
        if ( isset( $_POST['trek_region_ids'] ) && is_array( $_POST['trek_region_ids'] ) ) {
            $reg_ids = array_map( 'intval', array_filter( $_POST['trek_region_ids'] ) );
            wp_set_object_terms( $post_id, $reg_ids, 'trek_region' );
        } elseif ( ! isset( $_POST['trek_region_id'] ) ) {
            wp_set_object_terms( $post_id, array(), 'trek_region' );
        } elseif ( isset( $_POST['trek_region_id'] ) ) {
            $region_id = intval( $_POST['trek_region_id'] );
            if ( $region_id > 0 ) {
                wp_set_object_terms( $post_id, array( $region_id ), 'trek_region' );
            }
        }

        // Difficulty Taxonomy Term Sync
        if ( isset( $_POST['trek_difficulty'] ) ) {
            $diff_val = sanitize_text_field( $_POST['trek_difficulty'] );
            if ( ! empty( $diff_val ) ) {
                wp_set_object_terms( $post_id, array( $diff_val ), 'trek_difficulty' );
            } else {
                wp_set_object_terms( $post_id, array(), 'trek_difficulty' );
            }
        }

        // Home featured status checkbox
        $featured_home = isset( $_POST['trek_featured_home'] ) ? '1' : '0';
        update_post_meta( $post_id, '_trek_featured_home', $featured_home );

        // Textarea fields
        if ( isset( $_POST['trek_hero_subtitle'] ) ) {
            $value = sanitize_textarea_field( wp_unslash( $_POST['trek_hero_subtitle'] ) );
            if ( '' === trim( $value ) ) {
                delete_post_meta( $post_id, '_trek_hero_subtitle' );
            } else {
                update_post_meta( $post_id, '_trek_hero_subtitle', $value );
            }
        }

        // Interactive Map Embed Code (HTML / iFrame / Mapbox / Leaflet)
        if ( isset( $_POST['trek_map_embed_code'] ) ) {
            $raw_embed = wp_unslash( $_POST['trek_map_embed_code'] );
            $embed_code = current_user_can( 'unfiltered_html' ) ? $raw_embed : wp_kses_post( $raw_embed );
            if ( '' === trim( $embed_code ) ) {
                delete_post_meta( $post_id, '_trek_map_embed_code' );
            } else {
                update_post_meta( $post_id, '_trek_map_embed_code', $embed_code );
            }
        }

        // 2. WYSIWYG Editor HTML Fields (Sanitized with wp_kses_post)
        $wysiwyg_fields = array(
            'trek_overview_content',
            'trek_overview_highlight',
            'trek_ess_intro'
        );

        foreach ( $wysiwyg_fields as $wf ) {
            if ( isset( $_POST[ $wf ] ) ) {
                $value = wp_kses_post( $_POST[ $wf ] );
                if ( '' === trim( wp_strip_all_tags( $value ) ) ) {
                    delete_post_meta( $post_id, '_' . $wf );
                } else {
                    update_post_meta( $post_id, '_' . $wf, $value );
                }
            }
        }

        // 3. Overview Inclusions List
        if ( isset( $_POST['inc_item'] ) && is_array( $_POST['inc_item'] ) ) {
            $incs = array_map( 'sanitize_text_field', array_filter( $_POST['inc_item'] ) );
            $incs = array_values( $incs );
            empty( $incs ) ? delete_post_meta( $post_id, '_trek_inclusions_list' ) : update_post_meta( $post_id, '_trek_inclusions_list', $incs );
        } else {
            delete_post_meta( $post_id, '_trek_inclusions_list' );
        }

        // 4. Itinerary Repeater (Sync dual keys: _trek_itinerary_list & _trek_itinerary)
        if ( isset( $_POST['itin_title'] ) && is_array( $_POST['itin_title'] ) ) {
            $itinerary = array();
            $days      = $_POST['itin_day'] ?? array();
            $stages    = $_POST['itin_stage'] ?? array();
            $titles    = $_POST['itin_title'];
            $pills     = $_POST['itin_pills'] ?? array();
            $descs     = $_POST['itin_desc'] ?? array();
            $galleries = $_POST['itin_gallery'] ?? array();

            for ( $i = 0; $i < count( $titles ); $i++ ) {
                if ( ! empty( trim( $titles[ $i ] ) ) ) {
                    $gal_raw = $galleries[ $i ] ?? '';
                    $gal_arr = array();
                    $gal_values = is_array( $gal_raw )
                        ? $gal_raw
                        : ( is_string( $gal_raw ) ? preg_split( '/\s*,\s*/', trim( $gal_raw ), -1, PREG_SPLIT_NO_EMPTY ) : array() );

                    foreach ( $gal_values as $gal_url ) {
                        if ( ! is_string( $gal_url ) ) {
                            continue;
                        }
                        $gal_url = esc_url_raw( trim( $gal_url ) );
                        if ( '' !== $gal_url ) {
                            $gal_arr[] = $gal_url;
                        }
                    }
                    $gal_arr = array_values( array_unique( $gal_arr ) );
                    $itinerary[] = array(
                        'day'     => sanitize_text_field( $days[ $i ] ?? sprintf( '%02d', $i + 1 ) ),
                        'stage'   => sanitize_text_field( $stages[ $i ] ?? '' ),
                        'title'   => sanitize_text_field( $titles[ $i ] ),
                        'pills'   => sanitize_text_field( $pills[ $i ] ?? '' ),
                        'desc'    => wp_kses_post( $descs[ $i ] ?? '' ),
                        'gallery' => $gal_arr
                    );
                }
            }
            if ( empty( $itinerary ) ) {
                delete_post_meta( $post_id, '_trek_itinerary_list' );
                delete_post_meta( $post_id, '_trek_itinerary' );
            } else {
                update_post_meta( $post_id, '_trek_itinerary_list', $itinerary );
                update_post_meta( $post_id, '_trek_itinerary', $itinerary );
            }
        } else {
            delete_post_meta( $post_id, '_trek_itinerary_list' );
            delete_post_meta( $post_id, '_trek_itinerary' );
        }

        // 5. Full Inclusions & Exclusions with Heading & Rich Description (Sync dual keys: _trek_inclusions_full & _trek_inclusions)
        if ( isset( $_POST['inc_title'] ) && is_array( $_POST['inc_title'] ) ) {
            $inclusions = array();
            $inc_titles = $_POST['inc_title'];
            $inc_descs  = $_POST['inc_desc'] ?? array();
            for ( $i = 0; $i < count( $inc_titles ); $i++ ) {
                $t = sanitize_text_field( $inc_titles[ $i ] );
                $d = wp_kses_post( $inc_descs[ $i ] ?? '' );
                if ( '' !== trim( $t ) || '' !== trim( wp_strip_all_tags( $d ) ) ) {
                    $inclusions[] = array(
                        'title' => $t,
                        'desc'  => $d,
                    );
                }
            }
            if ( empty( $inclusions ) ) {
                delete_post_meta( $post_id, '_trek_inclusions_full' );
                delete_post_meta( $post_id, '_trek_inclusions' );
            } else {
                update_post_meta( $post_id, '_trek_inclusions_full', $inclusions );
                update_post_meta( $post_id, '_trek_inclusions', $inclusions );
            }
        } elseif ( isset( $_POST['inc_full_item'] ) && is_array( $_POST['inc_full_item'] ) ) {
            $incs = array_map( 'sanitize_text_field', array_filter( $_POST['inc_full_item'] ) );
            $incs = array_values( $incs );
            if ( empty( $incs ) ) {
                delete_post_meta( $post_id, '_trek_inclusions_full' );
                delete_post_meta( $post_id, '_trek_inclusions' );
            } else {
                update_post_meta( $post_id, '_trek_inclusions_full', $incs );
                update_post_meta( $post_id, '_trek_inclusions', $incs );
            }
        } else {
            delete_post_meta( $post_id, '_trek_inclusions_full' );
            delete_post_meta( $post_id, '_trek_inclusions' );
        }

        if ( isset( $_POST['exc_title'] ) && is_array( $_POST['exc_title'] ) ) {
            $exclusions = array();
            $exc_titles = $_POST['exc_title'];
            $exc_descs  = $_POST['exc_desc'] ?? array();
            for ( $i = 0; $i < count( $exc_titles ); $i++ ) {
                $t = sanitize_text_field( $exc_titles[ $i ] );
                $d = wp_kses_post( $exc_descs[ $i ] ?? '' );
                if ( '' !== trim( $t ) || '' !== trim( wp_strip_all_tags( $d ) ) ) {
                    $exclusions[] = array(
                        'title' => $t,
                        'desc'  => $d,
                    );
                }
            }
            if ( empty( $exclusions ) ) {
                delete_post_meta( $post_id, '_trek_exclusions_full' );
                delete_post_meta( $post_id, '_trek_exclusions' );
            } else {
                update_post_meta( $post_id, '_trek_exclusions_full', $exclusions );
                update_post_meta( $post_id, '_trek_exclusions', $exclusions );
            }
        } elseif ( isset( $_POST['exc_full_item'] ) && is_array( $_POST['exc_full_item'] ) ) {
            $excs = array_map( 'sanitize_text_field', array_filter( $_POST['exc_full_item'] ) );
            $excs = array_values( $excs );
            if ( empty( $excs ) ) {
                delete_post_meta( $post_id, '_trek_exclusions_full' );
                delete_post_meta( $post_id, '_trek_exclusions' );
            } else {
                update_post_meta( $post_id, '_trek_exclusions_full', $excs );
                update_post_meta( $post_id, '_trek_exclusions', $excs );
            }
        } else {
            delete_post_meta( $post_id, '_trek_exclusions_full' );
            delete_post_meta( $post_id, '_trek_exclusions' );
        }

        // 6. Dedicated Hero Slider Gallery
        if ( isset( $_POST['hero_slider_image'] ) && is_array( $_POST['hero_slider_image'] ) ) {
            $hero_slider_images = array();
            foreach ( $_POST['hero_slider_image'] as $hero_slider_image ) {
                if ( ! is_string( $hero_slider_image ) ) {
                    continue;
                }
                $hero_slider_image = esc_url_raw( trim( $hero_slider_image ) );
                if ( ! empty( $hero_slider_image ) ) {
                    $hero_slider_images[] = $hero_slider_image;
                }
            }
            $hero_slider_images = array_values( array_unique( $hero_slider_images ) );
            if ( empty( $hero_slider_images ) ) {
                delete_post_meta( $post_id, '_trek_hero_slider_images' );
            } else {
                update_post_meta( $post_id, '_trek_hero_slider_images', $hero_slider_images );
            }
        } else {
            delete_post_meta( $post_id, '_trek_hero_slider_images' );
        }

        // 7. Gallery Repeater (Sync dual keys: _trek_gallery_list & _trek_gallery_images)
        if ( isset( $_POST['gal_url'] ) && is_array( $_POST['gal_url'] ) ) {
            $gallery = array();
            $urls   = $_POST['gal_url'];
            $labels = $_POST['gal_label'] ?? array();

            for ( $i = 0; $i < count( $urls ); $i++ ) {
                if ( ! empty( trim( $urls[ $i ] ) ) ) {
                    $gallery[] = array(
                        'url'   => esc_url_raw( $urls[ $i ] ),
                        'label' => sanitize_text_field( $labels[ $i ] ?? '' )
                    );
                }
            }
            if ( empty( $gallery ) ) {
                delete_post_meta( $post_id, '_trek_gallery_list' );
                delete_post_meta( $post_id, '_trek_gallery_images' );
            } else {
                update_post_meta( $post_id, '_trek_gallery_list', $gallery );
                update_post_meta( $post_id, '_trek_gallery_images', $gallery );
            }
        } else {
            delete_post_meta( $post_id, '_trek_gallery_list' );
            delete_post_meta( $post_id, '_trek_gallery_images' );
        }

        // 9. Essential Info Accordions (Sync dual keys: _trek_essential_list & _trek_faqs)
        if ( isset( $_POST['ess_title'] ) && is_array( $_POST['ess_title'] ) ) {
            $essential = array();
            $titles   = $_POST['ess_title'];
            $contents = $_POST['ess_content'] ?? array();

            for ( $i = 0; $i < count( $titles ); $i++ ) {
                if ( ! empty( trim( $titles[ $i ] ) ) ) {
                    $essential[] = array(
                        'title'   => sanitize_text_field( $titles[ $i ] ),
                        'content' => wp_kses_post( $contents[ $i ] ?? '' )
                    );
                }
            }
            if ( empty( $essential ) ) {
                delete_post_meta( $post_id, '_trek_essential_list' );
                delete_post_meta( $post_id, '_trek_faqs' );
            } else {
                update_post_meta( $post_id, '_trek_essential_list', $essential );
                update_post_meta( $post_id, '_trek_faqs', $essential );
            }
        } else {
            delete_post_meta( $post_id, '_trek_essential_list' );
            delete_post_meta( $post_id, '_trek_faqs' );
        }

        // 10. Curated Reviews (Sync dual keys: _trek_curated_reviews & _trek_reviews_list)
        if ( isset( $_POST['rev_name'] ) && is_array( $_POST['rev_name'] ) ) {
            $reviews = array();
            $names   = $_POST['rev_name'];
            $locs    = $_POST['rev_loc'] ?? array();
            $ratings = $_POST['rev_rating'] ?? array();
            $quotes  = $_POST['rev_quote'] ?? array();

            for ( $i = 0; $i < count( $names ); $i++ ) {
                if ( ! empty( trim( $names[ $i ] ) ) ) {
                    $reviews[] = array(
                        'name'     => sanitize_text_field( $names[ $i ] ),
                        'location' => sanitize_text_field( $locs[ $i ] ?? '' ),
                        'rating'   => sanitize_text_field( $ratings[ $i ] ?? '5' ),
                        'quote'    => sanitize_textarea_field( $quotes[ $i ] ?? '' )
                    );
                }
            }
            if ( empty( $reviews ) ) {
                delete_post_meta( $post_id, '_trek_curated_reviews' );
                delete_post_meta( $post_id, '_trek_reviews_list' );
            } else {
                update_post_meta( $post_id, '_trek_curated_reviews', $reviews );
                update_post_meta( $post_id, '_trek_reviews_list', $reviews );
            }
        } else {
            delete_post_meta( $post_id, '_trek_curated_reviews' );
            delete_post_meta( $post_id, '_trek_reviews_list' );
        }

        // 10b. Fixed Departure Dates Repeater (_trek_departure_dates)
        if ( isset( $_POST['dep_date'] ) && is_array( $_POST['dep_date'] ) ) {
            $departure_dates = array();
            $dep_dates_arr   = $_POST['dep_date'];
            $dep_prices      = $_POST['dep_price'] ?? array();
            $dep_seats       = $_POST['dep_seats'] ?? array();
            $dep_statuses    = $_POST['dep_status'] ?? array();
            $dep_notes       = $_POST['dep_note'] ?? array();

            for ( $i = 0; $i < count( $dep_dates_arr ); $i++ ) {
                $d = sanitize_text_field( $dep_dates_arr[ $i ] ?? '' );
                if ( ! empty( $d ) ) {
                    $departure_dates[] = array(
                        'date'   => $d,
                        'price'  => sanitize_text_field( $dep_prices[ $i ] ?? '' ),
                        'seats'  => sanitize_text_field( $dep_seats[ $i ] ?? '' ),
                        'status' => sanitize_text_field( $dep_statuses[ $i ] ?? 'open' ),
                        'note'   => sanitize_text_field( $dep_notes[ $i ] ?? '' ),
                    );
                }
            }

            if ( empty( $departure_dates ) ) {
                delete_post_meta( $post_id, '_trek_departure_dates' );
            } else {
                update_post_meta( $post_id, '_trek_departure_dates', $departure_dates );
            }
        } else {
            delete_post_meta( $post_id, '_trek_departure_dates' );
        }

        // 11. Sync Quick Facts Array (_trek_quick_facts)
        $quick_facts = array(
            'duration'    => get_post_meta( $post_id, '_trek_hero_days', true ),
            'high_point'  => get_post_meta( $post_id, '_trek_fact_high_point', true ),
            'low_point'   => get_post_meta( $post_id, '_trek_fact_low_point', true ),
            'status'      => get_post_meta( $post_id, '_trek_trip_status', true ) ?: 'open',
            'region'      => get_post_meta( $post_id, '_trek_fact_region', true ),
            'season'      => get_post_meta( $post_id, '_trek_fact_season', true ),
            'group_size'  => get_post_meta( $post_id, '_trek_fact_group_size', true ),
            'trekking'    => get_post_meta( $post_id, '_trek_fact_trekking', true ),
            'accom'       => get_post_meta( $post_id, '_trek_fact_accom', true ),
            'style'       => get_post_meta( $post_id, '_trek_fact_style', true ),
            'distance'    => get_post_meta( $post_id, '_trek_fact_distance', true ),
            'start'       => get_post_meta( $post_id, '_trek_fact_start', true ),
            'end'         => get_post_meta( $post_id, '_trek_fact_end', true ),
            'difficulty'  => get_post_meta( $post_id, '_trek_difficulty', true ),
            'price'       => get_post_meta( $post_id, '_trek_price', true ),
            'currency'    => get_post_meta( $post_id, '_trek_currency', true ),
        );
        update_post_meta( $post_id, '_trek_quick_facts', $quick_facts );

        // 12. Compile & Save Master JSON Payload (_trek_structured_data)
        if ( class_exists( 'Trek_Renderer' ) ) {
            $full_data = Trek_Renderer::get_full_trek_data( $post_id );
            if ( ! empty( $full_data ) ) {
                update_post_meta( $post_id, '_trek_structured_data', wp_slash( wp_json_encode( $full_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ) );
            }
        }
    }

    /**
     * Get structured, fallback-safe card data for a given destination post
     */
    public static function get_card_data( $post_id, $index = 1 ) {
        $post_obj = get_post( $post_id );
        if ( ! $post_obj ) {
            return null;
        }

        $hero_days = get_post_meta( $post_id, '_trek_hero_days', true );
        $breadcrumb_region = get_post_meta( $post_id, '_trek_breadcrumb_region', true );

        // 1. Title
        $card_title = get_post_meta( $post_id, '_trek_card_title', true );
        if ( empty( $card_title ) ) {
            $card_title = get_post_meta( $post_id, '_trek_hero_title', true ) ?: get_the_title( $post_id );
        }

        // 2. Duration / Meta Badge
        $card_meta = get_post_meta( $post_id, '_trek_card_meta', true );
        if ( empty( $card_meta ) ) {
            $card_meta = $hero_days . ' days';
        }

        // 3. Category / Tag line (e.g. "Nepal & India · Spiritual" or "Everest · Challenging")
        $card_tag = get_post_meta( $post_id, '_trek_card_tag', true );
        if ( empty( $card_tag ) ) {
            $regions = get_the_terms( $post_id, 'trek_region' );
            $region_name = ( ! empty( $regions ) && ! is_wp_error( $regions ) ) ? $regions[0]->name : '';
            $cats = get_the_terms( $post_id, 'trek_category' );
            $cat_name = '';
            if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
                $cat_names = wp_list_pluck( $cats, 'name' );
                $cat_name = implode( ' & ', array_slice( $cat_names, 0, 2 ) );
            }
            if ( ! empty( $region_name ) && ! empty( $cat_name ) ) {
                $card_tag = $region_name . ' · ' . $cat_name;
            } elseif ( ! empty( $cat_name ) ) {
                $card_tag = $cat_name;
            } elseif ( ! empty( $region_name ) ) {
                $card_tag = $region_name;
            }
        }

        // 4. Card Description (<p>)
        $card_excerpt = get_post_meta( $post_id, '_trek_card_excerpt', true );
        if ( empty( $card_excerpt ) ) {
            $card_excerpt = get_post_meta( $post_id, '_trek_hero_subtitle', true );
        }
        if ( empty( $card_excerpt ) ) {
            $card_excerpt = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( $post_obj->post_content, 22, '…' );
        }

        // 5. Image
        $card_image = get_post_meta( $post_id, '_trek_card_image_url', true );
        if ( empty( $card_image ) ) {
            $card_image = get_the_post_thumbnail_url( $post_id, 'large' );
        }
        if ( empty( $card_image ) ) {
            $card_image = get_post_meta( $post_id, '_trek_hero_bg_img', true );
        }

        // 6. Featured
        $featured = get_post_meta( $post_id, '_trek_featured_home', true );
        if ( '' === $featured && ! metadata_exists( 'post', $post_id, '_trek_featured_home' ) ) {
            $featured = '1'; // Default to featured
        }

        // 7. Order
        $order = intval( get_post_meta( $post_id, '_trek_card_order', true ) ?: ( $post_obj->menu_order ?: $index ) );

        return array(
            'id'          => $post_id,
            'number'      => sprintf( '%02d', $index ),
            'title'       => $card_title,
            'meta'        => $card_meta,
            'tag'         => $card_tag,
            'excerpt'     => $card_excerpt,
            'image'       => $card_image,
            'permalink'   => get_permalink( $post_id ),
            'featured'    => $featured,
            'order'       => $order,
        );
    }

    /**
     * Query and return formatted trek cards for home slider or listing
     */
    public static function get_trek_cards( $args = array() ) {
        $defaults = array(
            'posts_per_page' => -1,
            'featured_only'  => false,
            'category'       => '',
            'region'         => '',
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        );
        $parsed_args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => intval( $parsed_args['posts_per_page'] ),
            'orderby'        => $parsed_args['orderby'],
            'order'          => $parsed_args['order'],
        );

        if ( ! empty( $parsed_args['category'] ) ) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'trek_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $parsed_args['category'] ),
            );
        }

        if ( ! empty( $parsed_args['region'] ) ) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'trek_region',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $parsed_args['region'] ),
            );
        }

        $trips_query = new WP_Query( $query_args );
        $cards = array();
        $idx = 1;

        if ( $trips_query->have_posts() ) {
            while ( $trips_query->have_posts() ) {
                $trips_query->the_post();
                $pid = get_the_ID();
                $card = self::get_card_data( $pid, $idx );
                if ( $card ) {
                    if ( $parsed_args['featured_only'] && '0' === $card['featured'] ) {
                        $idx++;
                        continue;
                    }
                    $card['number'] = sprintf( '%02d', $idx );
                    $cards[] = $card;
                    $idx++;
                }
            }
            wp_reset_postdata();
        }

        if ( empty( $cards ) ) {
            $cards = array(
                array(
                    'id'        => 0,
                    'number'    => '01',
                    'meta'      => '14 days',
                    'tag'       => 'Nepal & India · Spiritual',
                    'title'     => 'The Buddhist Circuit',
                    'excerpt'   => 'Follow the sacred footsteps of Buddha from Kathmandu and Lumbini to Bodhgaya, Varanasi and Sarnath.',
                    'permalink' => home_url( '/all-trips/the-buddhist-circuit/' ),
                    'image'     => 'https://images.unsplash.com/photo-1662563059758-9c7b2c5ad2f2?auto=format&fit=crop&fm=jpg&q=88&w=2400',
                    'featured'  => '1',
                ),
                array(
                    'id'        => 0,
                    'number'    => '02',
                    'meta'      => '12 days',
                    'tag'       => 'India · Special Interest',
                    'title'     => 'Taj, Tiger & Tea',
                    'excerpt'   => 'Journey from Delhi and the Taj Mahal through Ranthambhore and Jaipur to the tea-covered hills of Darjeeling.',
                    'permalink' => home_url( '/all-trips/taj-tiger-tea/' ),
                    'image'     => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&q=92&w=2400',
                    'featured'  => '1',
                ),
                array(
                    'id'        => 0,
                    'number'    => '03',
                    'meta'      => '12 days',
                    'tag'       => 'Mustang · Remote',
                    'title'     => 'Sky Caves & Ancient Trails',
                    'excerpt'   => 'Cross an arid trans-Himalayan landscape where cliff villages, monasteries and old trade routes still shape daily life.',
                    'permalink' => home_url( '/all-trips/upper-mustang/' ),
                    'image'     => 'https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/Jomsom2-1024x583-1.jpg',
                    'featured'  => '1',
                ),
                array(
                    'id'        => 0,
                    'number'    => '04',
                    'meta'      => '13 days',
                    'tag'       => 'Annapurna · Moderate',
                    'title'     => 'A Himalayan Sanctuary',
                    'excerpt'   => 'Forest paths, mountain villages and the dramatic amphitheatre of Annapurna Sanctuary in one beautifully paced journey.',
                    'permalink' => home_url( '/all-trips/annapurna-sanctuary/' ),
                    'image'     => 'https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/20191210_1600152-1024x768-1.jpg',
                    'featured'  => '1',
                ),
                array(
                    'id'        => 0,
                    'number'    => '05',
                    'meta'      => '15 days',
                    'tag'       => 'Everest · Challenging',
                    'title'     => 'The Ultimate Everest Base Camp Trek',
                    'excerpt'   => 'Walk into the heart of the Khumbu with enough time to acclimatise, absorb the landscape and experience life along the trail.',
                    'permalink' => home_url( '/all-trips/everest-basecamp-trek/' ),
                    'image'     => 'https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/AERIAL-VIEW-OF-THE-EVEREST-BASE-CAMP.jpg',
                    'featured'  => '1',
                ),
            );
        }

        return $cards;
    }

    public static function render_guide_page() {
        ?>
        <div class="wrap">
            <h1>🏔️ <?php esc_html_e( 'Trek Content Manager - Form Fields Guide', 'trek-content-manager' ); ?></h1>
            <div style="background:#fff; padding:24px; border:1px solid #ccd0d4; border-radius:8px; max-width:900px; margin-top:16px;">
                <h2><?php esc_html_e( 'How to Create a Dynamic Destination Page', 'trek-content-manager' ); ?></h2>
                <ol style="font-size:14px; line-height:1.8;">
                    <li>Go to <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_destination' ) ); ?>"><strong>Trek Manager &rarr; Add New Destination</strong></a>.</li>
                    <li>Enter the trek title and fill the visual metaboxes (Hero, Quick Facts, Overview WYSIWYG, Itinerary Days, Inclusions &amp; Exclusions, Photo Gallery, Reviews). Use the <strong>JSON Auto-Fill</strong> tool at the top of the edit page to instantly populate all fields from a JSON data file.</li>
                    <li>Use the <strong>Upload Image</strong> buttons to easily select images from your WordPress Media Library!</li>
                    <li>All submitted reviews from visitors are saved in the database under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_review' ) ); ?>"><strong>Trek Manager &rarr; Reviews</strong></a> where you can approve, moderate or edit them.</li>
                    <li>Click <strong>Publish</strong>. That's it!</li>
                </ol>
            </div>
        </div>
        <?php
    }
}
