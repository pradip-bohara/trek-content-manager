<?php
/**
 * Trek Content Manager - Custom Post Type & Complete Dynamic Form Builder
 * 
 * Features:
 * - Native WordPress Media Uploader (Upload / Select from Media Library)
 * - WYSIWYG WordPress Editors (wp_editor) for Overview, Extensions, Essential Info, etc.
 * - Secondary Big Heading support for Hero section
 * - Repeaters for: Itinerary Days, Inclusions/Exclusions, Gallery Images, Extensions, Essential Info, Side Points
 * - Review Custom Post Type & Moderation for storing submitted reviews
 * - Dedicated Form Fields for Quick Facts, Hero, Route Map, WhatsApp & Contact settings
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
            'show_in_menu'          => 'edit.php?post_type=trek_destination',
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
            'show_in_menu'          => 'edit.php?post_type=trek_destination',
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
            array( __CLASS__, 'render_guide_page' )
        );
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'Export Enquiries', 'trek-content-manager' ),
            __( 'Export Enquiries', 'trek-content-manager' ),
            'edit_posts',
            'trek-export-enquiries',
            array( __CLASS__, 'render_enquiry_export_page' )
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
        $new_columns['trek_name'] = __( 'Trek Destination', 'trek-content-manager' );
        $new_columns['trek_rating'] = __( 'Rating', 'trek-content-manager' );
        $new_columns['trek_location'] = __( 'Location', 'trek-content-manager' );
        $new_columns['trek_status'] = __( 'Status', 'trek-content-manager' );
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    public static function render_review_column( $column, $post_id ) {
        switch ( $column ) {
            case 'trek_name':
                $trek_id = get_post_meta( $post_id, '_trek_id', true );
                if ( $trek_id && get_post( $trek_id ) ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $trek_id ) ) . '"><strong>' . esc_html( get_the_title( $trek_id ) ) . '</strong></a>';
                } else {
                    echo '<em>' . esc_html__( 'General / Unlinked', 'trek-content-manager' ) . '</em>';
                }
                break;
            case 'trek_rating':
                $rating = intval( get_post_meta( $post_id, '_trek_rating', true ) ?: 5 );
                echo '<span style="color:#c6a45f; font-size:16px;">' . str_repeat( '★', $rating ) . '</span> (' . $rating . '/5)';
                break;
            case 'trek_location':
                $city = get_post_meta( $post_id, '_trek_city', true );
                $country = get_post_meta( $post_id, '_trek_country', true );
                $loc = array_filter( array( $city, $country ) );
                echo esc_html( ! empty( $loc ) ? implode( ', ', $loc ) : '—' );
                break;
            case 'trek_status':
                $approved = get_post_meta( $post_id, '_trek_approved', true );
                if ( '1' === $approved && 'publish' === get_post_status( $post_id ) ) {
                    echo '<span style="color:#0a7a3b; font-weight:700; background:#e6f6ec; padding:3px 8px; border-radius:4px;">' . esc_html__( 'Approved', 'trek-content-manager' ) . '</span>';
                } else {
                    echo '<span style="color:#b26b00; font-weight:700; background:#fff8e6; padding:3px 8px; border-radius:4px;">' . esc_html__( 'Pending Moderation', 'trek-content-manager' ) . '</span>';
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
                echo esc_html( get_post_meta( $post_id, '_trek_enquiry_phone', true ) ?: '—' );
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

    public static function add_meta_boxes() {
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
            __( '📅 4. Day-by-Day Itinerary Builder (Repeater with Side Card & Keywords)', 'trek-content-manager' ),
            array( __CLASS__, 'render_itinerary_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 5. Inclusions & Exclusions Repeater
        add_meta_box(
            'ya_trek_inex_metabox',
            __( '✅ 5. What’s Included & What’s Not Included (Repeaters)', 'trek-content-manager' ),
            array( __CLASS__, 'render_inex_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 6. Photo Gallery Repeater (WP Media Uploader)
        add_meta_box(
            'ya_trek_gallery_metabox',
            __( '📷 6. Photo Gallery (WordPress Media Uploader Repeater with Lightbox)', 'trek-content-manager' ),
            array( __CLASS__, 'render_gallery_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 7. Extensions / Add-ons Repeater
        add_meta_box(
            'ya_trek_extensions_metabox',
            __( '➕ 7. Extensions & Add-on Cards (Top Headings + WYSIWYG & Cards Repeater)', 'trek-content-manager' ),
            array( __CLASS__, 'render_extensions_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 8. Essential Information Accordions
        add_meta_box(
            'ya_trek_essential_metabox',
            __( 'ℹ️ 8. Essential Information Accordions (Top Headings + WYSIWYG & Accordion Items)', 'trek-content-manager' ),
            array( __CLASS__, 'render_essential_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 9. Reviews Settings & Curated Reviews
        add_meta_box(
            'ya_trek_reviews_metabox',
            __( '⭐ 9. Traveller Reviews & Curated Testimonials', 'trek-content-manager' ),
            array( __CLASS__, 'render_reviews_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 10. Contact, WhatsApp & Booking Settings
        add_meta_box(
            'ya_trek_contact_metabox',
            __( '💬 10. Join Fixed Departure, WhatsApp & Booking Settings', 'trek-content-manager' ),
            array( __CLASS__, 'render_contact_metabox' ),
            'trek_destination',
            'normal',
            'high'
        );

        // 11. Structured JSON Payload & API Data
        add_meta_box(
            'ya_trek_json_metabox',
            __( '📦 11. Structured JSON Payload & REST API Data', 'trek-content-manager' ),
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
        </style>

        <script>
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
        ?>
        <div class="ya-admin-box" style="background:#fdfcf9; border:1px solid #dcd7cc; border-radius:8px; padding:18px; margin-bottom:14px;">
            <!-- Row 1: Titles -->
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label for="trek_script_title"><?php esc_html_e( 'Script Eyebrow Title', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_script_title" id="trek_script_title" value="<?php echo esc_attr( $script_title ); ?>" placeholder="e.g. Everest">
                    <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Cursive script tag above the main hero heading.', 'trek-content-manager' ); ?></span>
                </div>
                <div class="ya-admin-field">
                    <label for="trek_card_title"><?php esc_html_e( 'Card Title (h3)', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_card_title" id="trek_card_title" value="<?php echo esc_attr( $card_title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post->ID ) ?: 'e.g. The Buddhist Circuit' ); ?>">
                    <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Title on cards and sliders. Defaults to post title.', 'trek-content-manager' ); ?></span>
                </div>
                <div class="ya-admin-field">
                    <label for="trek_hero_title"><?php esc_html_e( 'Hero Big Heading (Hero h1 Title)', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_hero_title" id="trek_hero_title" value="<?php echo esc_attr( $hero_title ); ?>" placeholder="<?php echo esc_attr( get_the_title( $post->ID ) ?: 'e.g. The Ultimate Trek: Everest Base Camp Experience' ); ?>">
                    <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Large headline on view page. Defaults to post title.', 'trek-content-manager' ); ?></span>
                </div>
            </div>

            <!-- Row 2: Categories & Regions Multi-Select -->
            <div class="ya-admin-row" style="margin-top:10px;">
                <div class="ya-admin-field">
                    <label style="font-weight:600; display:block; margin-bottom:5px;"><?php esc_html_e( 'Trip Category (Multiple Select)', 'trek-content-manager' ); ?></label>
                    <div style="max-height:115px; overflow-y:auto; border:1px solid #ccd0d4; background:#fff; padding:8px 12px; border-radius:4px;">
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $cat ) : ?>
                                <label style="display:block; margin-bottom:4px; font-size:13px; font-weight:normal; cursor:pointer;">
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

                <div class="ya-admin-field">
                    <label style="font-weight:600; display:block; margin-bottom:5px;"><?php esc_html_e( 'Destination Region (Multiple Select)', 'trek-content-manager' ); ?></label>
                    <div style="max-height:115px; overflow-y:auto; border:1px solid #ccd0d4; background:#fff; padding:8px 12px; border-radius:4px;">
                        <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                            <?php foreach ( $regions as $reg ) : ?>
                                <label style="display:block; margin-bottom:4px; font-size:13px; font-weight:normal; cursor:pointer;">
                                    <input type="checkbox" name="trek_region_ids[]" value="<?php echo esc_attr( $reg->term_id ); ?>" <?php checked( in_array( $reg->term_id, $assigned_regs ) ); ?>>
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
            </div>

            <!-- Row 2b: Taxonomies, Difficulty & Interactive Map Settings -->
            <div class="ya-admin-row" style="margin-top:10px; background:#f4f7f6; padding:14px; border-radius:6px; border:1px solid #d8e6e3; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="ya-admin-field">
                    <label for="trek_primary_category" style="font-weight:700; color:#022f2e; display:block; margin-bottom:5px;">
                        <?php esc_html_e( '⭐ Primary Category', 'trek-content-manager' ); ?>
                    </label>
                    <select name="trek_primary_category" id="trek_primary_category" style="width:100%; max-width:100%;">
                        <option value=""><?php esc_html_e( '— Auto (First Selected) —', 'trek-content-manager' ); ?></option>
                        <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                            <?php foreach ( $categories as $cat ) : ?>
                                <option value="<?php echo esc_attr( $cat->slug ); ?>" <?php selected( $primary_cat, $cat->slug ); ?>>
                                    <?php echo esc_html( $cat->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="ya-admin-field">
                    <label for="trek_difficulty" style="font-weight:700; color:#022f2e; display:block; margin-bottom:5px;">
                        <?php esc_html_e( '⚡ Trek Difficulty', 'trek-content-manager' ); ?>
                    </label>
                    <select name="trek_difficulty" id="trek_difficulty" style="width:100%; max-width:100%;">
                        <option value=""><?php esc_html_e( '— Select Difficulty —', 'trek-content-manager' ); ?></option>
                        <option value="Easy" <?php selected( $current_diff, 'Easy' ); ?>>Easy (Gentle trails & low elevation)</option>
                        <option value="Moderate" <?php selected( $current_diff, 'Moderate' ); ?>>Moderate (Standard mountain walking)</option>
                        <option value="Demanding" <?php selected( $current_diff, 'Demanding' ); ?>>Demanding (High passes & altitude)</option>
                        <option value="Strenuous" <?php selected( $current_diff, 'Strenuous' ); ?>>Strenuous (Long days, rough terrain)</option>
                        <option value="Challenging" <?php selected( $current_diff, 'Challenging' ); ?>>Challenging (Remote wilderness & high altitude)</option>
                        <option value="Technical" <?php selected( $current_diff, 'Technical' ); ?>>Technical (Glacial travel & equipment)</option>
                    </select>
                </div>

                <div class="ya-admin-field">
                    <label for="trek_card_tag" style="font-weight:700; color:#022f2e; display:block; margin-bottom:5px;">
                        <?php esc_html_e( '🏷️ Card Subtitle / Tag', 'trek-content-manager' ); ?>
                    </label>
                    <input type="text" name="trek_card_tag" id="trek_card_tag" value="<?php echo esc_attr( $card_tag ); ?>" placeholder="e.g. Nepal · High-Altitude Trek">
                </div>

                <div class="ya-admin-field">
                    <label for="trek_map_region" style="font-weight:700; color:#022f2e; display:block; margin-bottom:5px;">
                        <?php esc_html_e( '🗺️ Interactive Map Region', 'trek-content-manager' ); ?>
                    </label>
                    <select name="trek_map_region" id="trek_map_region" style="width:100%; max-width:100%;">
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
                    <span class="description" style="font-size:11px; color:#556c6a; display:block; margin-top:4px;">
                        <?php esc_html_e( 'Binds this trek to the interactive Nepal tourism map for instant click-through.', 'trek-content-manager' ); ?>
                    </span>
                </div>

                <div class="ya-admin-field">
                    <label for="trek_map_district" style="font-weight:700; color:#022f2e; display:block; margin-bottom:5px;">
                        <?php esc_html_e( '📍 Map District', 'trek-content-manager' ); ?>
                    </label>
                    <select name="trek_map_district" id="trek_map_district" style="width:100%; max-width:100%;">
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
                    <span class="description" style="font-size:11px; color:#556c6a; display:block; margin-top:4px;">
                        <?php esc_html_e( 'Places the destination point on this exact district.', 'trek-content-manager' ); ?>
                    </span>
                </div>
            </div>

            <!-- Row 3: Badges, Duration & Breadcrumb Region -->
            <div class="ya-admin-row ya-admin-row--4" style="margin-top:10px;">
                <div class="ya-admin-field">
                    <label for="trek_hero_days"><?php esc_html_e( 'Hero Days Badge Number', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_hero_days" id="trek_hero_days" value="<?php echo esc_attr( $hero_days ); ?>" placeholder="15">
                </div>
                <div class="ya-admin-field">
                    <label for="trek_hero_badge_sub"><?php esc_html_e( 'Hero Badge Subtitle', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_hero_badge_sub" id="trek_hero_badge_sub" value="<?php echo esc_attr( $hero_badge_sub ); ?>" placeholder="Days in Nepal">
                </div>
                <div class="ya-admin-field">
                    <label for="trek_card_meta"><?php esc_html_e( 'Card Duration Badge (Pill)', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_card_meta" id="trek_card_meta" value="<?php echo esc_attr( $card_meta ); ?>" placeholder="<?php echo esc_attr( $default_meta ); ?>">
                </div>
                <div class="ya-admin-field">
                    <label for="trek_breadcrumb_region"><?php esc_html_e( 'Breadcrumb Region / Country', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_breadcrumb_region" id="trek_breadcrumb_region" list="trek_breadcrumb_region_list" value="<?php echo esc_attr( $breadcrumb_reg ); ?>" placeholder="e.g. Nepal">
                    <datalist id="trek_breadcrumb_region_list">
                        <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                            <?php foreach ( $regions as $reg ) : ?>
                                <option value="<?php echo esc_attr( $reg->name ); ?>"></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                    <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Select from created regions or type custom text.', 'trek-content-manager' ); ?></span>
                </div>
            </div>

            <!-- Row 4: Page Description above Card Description -->
            <div class="ya-admin-field" style="margin-top:14px; margin-bottom:12px;">
                <label for="trek_hero_subtitle" style="font-weight:600; display:block; margin-bottom:4px;"><?php esc_html_e( 'Page Description (Hero Subtitle - Single Destination Page)', 'trek-content-manager' ); ?></label>
                <textarea name="trek_hero_subtitle" id="trek_hero_subtitle" rows="3" placeholder="A carefully paced 15-day journey through Sherpa country, high Himalayan valleys and the legendary Khumbu landscape to Everest Base Camp."><?php echo esc_textarea( $hero_subtitle ); ?></textarea>
                <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( 'Detailed narrative description displayed under the large heading on the single destination page.', 'trek-content-manager' ); ?></span>
            </div>

            <div class="ya-admin-field" style="margin-bottom:14px;">
                <label for="trek_card_excerpt" style="font-weight:600; display:block; margin-bottom:4px;"><?php esc_html_e( 'Card Description (Home Slider, Listing Cards & Search)', 'trek-content-manager' ); ?></label>
                <textarea name="trek_card_excerpt" id="trek_card_excerpt" rows="2" placeholder="Follow the sacred footsteps of Buddha from Kathmandu and Lumbini to Bodhgaya, Varanasi and Sarnath."><?php echo esc_textarea( $card_excerpt ); ?></textarea>
                <span class="description" style="font-size:11px; color:#666;"><?php esc_html_e( '1–2 sentences shown directly inside the card body snippet across listings and sliders.', 'trek-content-manager' ); ?></span>
            </div>

            <!-- Row 5: Side-by-Side Images (Card Image Left, Hero Fullscreen Image Right) -->
            <div class="ya-admin-row" style="margin-top:14px; margin-bottom:14px;">
                <div class="ya-admin-field" style="background:#fff; border:1px solid #e2ded5; border-radius:6px; padding:12px;">
                    <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:6px;">
                        <?php esc_html_e( '🎴 Card Background Image (Left Side - Tall / Vertical format)', 'trek-content-manager' ); ?>
                    </label>
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                        <input type="text" name="trek_card_image_url" id="trek_card_image_url" value="<?php echo esc_attr( $card_image ); ?>" style="flex-grow:1;" placeholder="<?php echo esc_attr( $default_card_img ); ?>">
                        <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_card_image_url', 'trek_card_preview')"><?php esc_html_e( 'Upload Card Image', 'trek-content-manager' ); ?></button>
                    </div>
                    <img id="trek_card_preview" class="ya-img-preview" src="<?php echo esc_url( $card_image ?: $default_card_img ); ?>" style="width:100%; max-height:160px; object-fit:cover; border-radius:4px; <?php echo empty( $card_image ) && empty( $default_card_img ) ? 'display:none;' : ''; ?>">
                    <span class="description" style="font-size:11px; color:#666; display:block; margin-top:4px;"><?php esc_html_e( 'Optimized for Signature Journeys card slider & trip grid listings.', 'trek-content-manager' ); ?></span>
                </div>

                <div class="ya-admin-field" style="background:#fff; border:1px solid #e2ded5; border-radius:6px; padding:12px;">
                    <label style="font-weight:700; color:#022f2e; display:block; margin-bottom:6px;">
                        <?php esc_html_e( '🏔️ Hero Fullscreen Background Image (Right Side - Wide Landscape format)', 'trek-content-manager' ); ?>
                    </label>
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                        <input type="text" name="trek_hero_bg_img" id="trek_hero_bg_img" value="<?php echo esc_attr( $hero_bg_img ); ?>" style="flex-grow:1;" placeholder="Upload or enter hero image URL">
                        <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_hero_bg_img', 'trek_hero_preview')"><?php esc_html_e( 'Upload Hero Image', 'trek-content-manager' ); ?></button>
                    </div>
                    <img id="trek_hero_preview" class="ya-img-preview" src="<?php echo esc_url( $hero_bg_img ); ?>" style="width:100%; max-height:160px; object-fit:cover; border-radius:4px; <?php echo empty( $hero_bg_img ) ? 'display:none;' : ''; ?>">
                    <span class="description" style="font-size:11px; color:#666; display:block; margin-top:4px;"><?php esc_html_e( 'Full-width cinematic background on single destination page hero.', 'trek-content-manager' ); ?></span>
                </div>
            </div>

            <!-- Row 6: Dedicated Hero Slider Gallery -->
            <div style="background:#f4f8f7; border:1px solid #cfe1dc; border-radius:6px; padding:14px; margin:14px 0;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px;">
                    <div>
                        <strong style="color:#022f2e;"><?php esc_html_e( '🎞️ Hero Slider Gallery', 'trek-content-manager' ); ?></strong>
                        <span class="description" style="display:block; margin-top:3px;"><?php esc_html_e( 'Add multiple wide images for the single destination hero. The first image remains the main hero image; these slides follow every 3 seconds.', 'trek-content-manager' ); ?></span>
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

            <!-- Row 7: Feature in Home Page Signature Journeys Slider -->
            <div style="background:#eef6f5; padding:12px 14px; border-radius:6px; border:1px solid #c2e2de;">
                <label style="font-weight:700; color:#022f2e; display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="trek_featured_home" value="1" <?php checked( $featured_home, '1' ); ?>>
                    <?php esc_html_e( 'Feature in Home Page Signature Journeys Slider [trek_home] & [trek_signature_journeys]', 'trek-content-manager' ); ?>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * 2. Quick Facts Metabox
     */
    public static function render_facts_metabox( $post ) {
        $fact_high_point = get_post_meta( $post->ID, '_trek_fact_high_point', true );
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
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '1. Highest Point / Altitude', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_high_point" value="<?php echo esc_attr( $fact_high_point ); ?>" placeholder="5,550m · Kala Patthar">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '2. Destination / Region Fact', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_region" value="<?php echo esc_attr( $fact_region ); ?>" placeholder="Everest Base Camp">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '3. Daily Walking Time', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_trekking" value="<?php echo esc_attr( $fact_trekking ); ?>" placeholder="Usually 5–6 hours">
            </div>
        </div>
        <div class="ya-admin-row ya-admin-row--4">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '4. Accommodation Style', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_accom" value="<?php echo esc_attr( $fact_accom ); ?>" placeholder="Hotel & local lodges">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '5. Trip Style Fact', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_style" value="<?php echo esc_attr( $fact_style ); ?>" placeholder="High-altitude trek">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '6. Best Season', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_season" value="<?php echo esc_attr( $fact_season ); ?>" placeholder="Mar–May & Sep–Nov">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '7. Group Size', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_group_size" value="<?php echo esc_attr( $fact_group_size ); ?>" placeholder="2–12 Travellers">
            </div>
        </div>
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label><?php esc_html_e( '8. Total Distance', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_distance" value="<?php echo esc_attr( $fact_distance ); ?>" placeholder="e.g. 130 km">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '9. Starting Point', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_start" value="<?php echo esc_attr( $fact_start ); ?>" placeholder="e.g. Kathmandu">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( '10. Ending Point', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_fact_end" value="<?php echo esc_attr( $fact_end ); ?>" placeholder="e.g. Lukla">
            </div>
        </div>

        <!-- Pricing & Commercial Details -->
        <div class="ya-admin-row" style="margin-top:12px; background:#f0fdf4; border:1px solid #bbf7d0; padding:14px; border-radius:6px;">
            <div class="ya-admin-field">
                <label style="font-weight:700; color:#166534;"><?php esc_html_e( '💵 Starting Price / Person (Numeric)', 'trek-content-manager' ); ?></label>
                <input type="number" step="any" name="trek_price" value="<?php echo esc_attr( $price ); ?>" placeholder="e.g. 2450">
                <span class="description" style="font-size:11px; color:#15803d;"><?php esc_html_e( 'Used in Schema.org Product Offer & booking quotes.', 'trek-content-manager' ); ?></span>
            </div>
            <div class="ya-admin-field">
                <label style="font-weight:700; color:#166534;"><?php esc_html_e( '💱 Currency Code', 'trek-content-manager' ); ?></label>
                <select name="trek_currency" style="width:100%;">
                    <option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($ - United States Dollar)</option>
                    <option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€ - Euro)</option>
                    <option value="GBP" <?php selected( $currency, 'GBP' ); ?>>GBP (£ - British Pound)</option>
                    <option value="AUD" <?php selected( $currency, 'AUD' ); ?>>AUD ($ - Australian Dollar)</option>
                    <option value="CAD" <?php selected( $currency, 'CAD' ); ?>>CAD ($ - Canadian Dollar)</option>
                    <option value="NPR" <?php selected( $currency, 'NPR' ); ?>>NPR (Rs - Nepalese Rupee)</option>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * 3. Overview Narrative (WYSIWYG), Highlights & Route Visual Map
     */
    public static function render_overview_metabox( $post ) {
        $kicker          = get_post_meta( $post->ID, '_trek_overview_kicker', true );
        $script          = get_post_meta( $post->ID, '_trek_overview_script', true );
        $heading         = get_post_meta( $post->ID, '_trek_overview_heading', true );
        $subheading      = get_post_meta( $post->ID, '_trek_overview_subheading', true );
        
        $content         = get_post_meta( $post->ID, '_trek_overview_content', true );
        $highlight_box   = get_post_meta( $post->ID, '_trek_overview_highlight', true );

        $inclusions = get_post_meta( $post->ID, '_trek_inclusions_list', true );
        if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
            $inclusions = array();
        }

        $route_img         = get_post_meta( $post->ID, '_trek_route_img', true );
        $route_caption_sub = get_post_meta( $post->ID, '_trek_route_caption_sub', true );
        $route_caption_h3  = get_post_meta( $post->ID, '_trek_route_caption_h3', true );
        ?>
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label><?php esc_html_e( 'Section Kicker', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_overview_kicker" value="<?php echo esc_attr( $kicker ); ?>" placeholder="Everest Experience · 15 Days">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( 'Script Eyebrow Heading', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_overview_script" value="<?php echo esc_attr( $script ); ?>" placeholder="The Ultimate Trek">
            </div>
            <div class="ya-admin-field">
                <label><?php esc_html_e( 'Main Heading (h2)', 'trek-content-manager' ); ?></label>
                <input type="text" name="trek_overview_heading" value="<?php echo esc_attr( $heading ); ?>" placeholder="To the Foot of the World’s Highest Mountain">
            </div>
        </div>

        <div class="ya-admin-field" style="margin-bottom:14px;">
            <label><?php esc_html_e( 'Overview Sub-Headline (h3)', 'trek-content-manager' ); ?></label>
            <input type="text" name="trek_overview_subheading" value="<?php echo esc_attr( $subheading ); ?>" placeholder="Everest Base Camp through the Sherpa homelands">
        </div>

        <div class="ya-admin-field" style="margin-bottom:16px;">
            <label><strong><?php esc_html_e( 'Overview Narrative (WYSIWYG Editor)', 'trek-content-manager' ); ?></strong></label>
            <?php
            wp_editor( $content, 'trek_overview_content', array(
                'textarea_name' => 'trek_overview_content',
                'textarea_rows' => 7,
                'media_buttons' => false,
                'teeny'         => false,
                'quicktags'     => true,
            ) );
            ?>
        </div>

        <div class="ya-admin-field" style="margin-bottom:16px;">
            <label><strong><?php esc_html_e( 'Gold Highlight Quote Box (WYSIWYG / Rich Content)', 'trek-content-manager' ); ?></strong></label>
            <?php
            wp_editor( $highlight_box, 'trek_overview_highlight', array(
                'textarea_name' => 'trek_overview_highlight',
                'textarea_rows' => 3,
                'media_buttons' => false,
                'teeny'         => true,
                'quicktags'     => true,
            ) );
            ?>
        </div>

        <div class="ya-admin-subhead">
            <span><?php esc_html_e( 'Key Highlight Inclusions / Bullet Points', 'trek-content-manager' ); ?></span>
            <button type="button" class="button button-secondary button-small" onclick="yaAddSimpleRow('ya-ov-inc-rows', 'inc_item[]')">+ Add Bullet Point</button>
        </div>
        <div id="ya-ov-inc-rows" style="margin-bottom:18px;">
            <?php foreach ( $inclusions as $inc ) : ?>
                <div style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                    <span style="color:#045857; font-weight:bold;">✔</span>
                    <input type="text" name="inc_item[]" value="<?php echo esc_attr( $inc ); ?>" style="flex-grow:1;" placeholder="e.g. Airport welcome and hotel assistance in Kathmandu">
                    <button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="background:#f4f0e7; padding:16px; border-radius:8px; border:1px solid #dfd8cc;">
            <strong style="font-size:14px; color:#022f2e;"><?php esc_html_e( '🗺️ Route Visual Map & Card', 'trek-content-manager' ); ?></strong>
            <div class="ya-admin-field" style="margin-top:10px;">
                <label><?php esc_html_e( 'Route Map / Visual Image', 'trek-content-manager' ); ?></label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_route_img" id="trek_route_img" value="<?php echo esc_attr( $route_img ); ?>" style="flex-grow:1;" placeholder="Upload Route Map Image URL">
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_route_img', 'trek_route_preview')"><?php esc_html_e( 'Upload Route Map', 'trek-content-manager' ); ?></button>
                </div>
                <img id="trek_route_preview" class="ya-img-preview" src="<?php echo esc_url( $route_img ); ?>" style="<?php echo empty( $route_img ) ? 'display:none;' : ''; ?>">
            </div>
            <div class="ya-admin-row" style="margin-top:12px;">
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Route Map Short Kicker', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_route_caption_sub" value="<?php echo esc_attr( $route_caption_sub ); ?>" placeholder="e.g. Route Map">
                </div>
                <div class="ya-admin-field">
                    <label><?php esc_html_e( 'Route Map Main Title (h3)', 'trek-content-manager' ); ?></label>
                    <input type="text" name="trek_route_caption_h3" value="<?php echo esc_attr( $route_caption_h3 ); ?>" placeholder="e.g. Lukla to Everest Base Camp">
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * 4. Day-by-Day Itinerary Builder (Repeater with Side Card & Keywords)
     */
    public static function render_itinerary_metabox( $post ) {
        $itin_kicker     = get_post_meta( $post->ID, '_trek_itin_kicker', true );
        $itin_script     = get_post_meta( $post->ID, '_trek_itin_script', true );
        $itin_heading    = get_post_meta( $post->ID, '_trek_itin_heading', true );
        $itin_subtext    = get_post_meta( $post->ID, '_trek_itin_subtext', true );

        // Side Card Data
        $side_script     = get_post_meta( $post->ID, '_trek_itin_side_script', true );
        $side_title      = get_post_meta( $post->ID, '_trek_itin_side_title', true );
        $side_desc       = get_post_meta( $post->ID, '_trek_itin_side_desc', true );
        $side_points     = get_post_meta( $post->ID, '_trek_itin_side_points', true );
        if ( empty( $side_points ) || ! is_array( $side_points ) ) {
            $side_points = array();
        }

        $itinerary = get_post_meta( $post->ID, '_trek_itinerary_list', true );
        if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
            $itinerary = array();
        }
        ?>
        <div class="ya-admin-box">
            <h4 style="margin-top:0; color:#022f2e;">Top Section Headings</h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label>Kicker</label>
                    <input type="text" name="trek_itin_kicker" value="<?php echo esc_attr( $itin_kicker ); ?>" placeholder="e.g. Day by Day">
                </div>
                <div class="ya-admin-field">
                    <label>Script Title</label>
                    <input type="text" name="trek_itin_script" value="<?php echo esc_attr( $itin_script ); ?>" placeholder="e.g. Your Journey">
                </div>
                <div class="ya-admin-field">
                    <label>Main Heading</label>
                    <input type="text" name="trek_itin_heading" value="<?php echo esc_attr( $itin_heading ); ?>" placeholder="e.g. 15 Days Through the Khumbu">
                </div>
            </div>
            <div class="ya-admin-field">
                <label>Support Subtitle Text</label>
                <input type="text" name="trek_itin_subtext" value="<?php echo esc_attr( $itin_subtext ); ?>" placeholder="e.g. Open each day to view the walking plan, meals, accommodation and approximate duration.">
            </div>
        </div>

        <div class="ya-admin-box" style="background:#f7f5f0;">
            <h4 style="margin-top:0; color:#022f2e;">📌 Sticky Itinerary Side Card</h4>
            <div class="ya-admin-row ya-admin-row--3">
                <div class="ya-admin-field">
                    <label>Side Script</label>
                    <input type="text" name="trek_itin_side_script" value="<?php echo esc_attr( $side_script ); ?>" placeholder="e.g. 15 Days">
                </div>
                <div class="ya-admin-field">
                    <label>Side Heading</label>
                    <input type="text" name="trek_itin_side_title" value="<?php echo esc_attr( $side_title ); ?>" placeholder="e.g. Kathmandu to Base Camp and Back">
                </div>
                <div class="ya-admin-field">
                    <label>Side Support Paragraph</label>
                    <input type="text" name="trek_itin_side_desc" value="<?php echo esc_attr( $side_desc ); ?>" placeholder="e.g. The route includes two acclimatisation days and a gradual ascent...">
                </div>
            </div>
            <div class="ya-admin-subhead">
                <span>Side Card Highlights / Points</span>
                <button type="button" class="button button-secondary button-small" onclick="yaAddSimpleRow('ya-side-point-rows', 'itin_side_point[]')">+ Add Side Point</button>
            </div>
            <div id="ya-side-point-rows">
                <?php foreach ( $side_points as $pt ) : ?>
                    <div style="display:flex; gap:8px; margin-bottom:6px;">
                        <input type="text" name="itin_side_point[]" value="<?php echo esc_attr( $pt ); ?>" style="flex-grow:1;" placeholder="e.g. Acclimatisation in Namche and Dingboche">
                        <button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ya-admin-subhead" style="margin-top:20px;">
            <span>Day-by-Day Journey Builder</span>
            <button type="button" class="button button-primary" id="ya-add-itin-btn">+ Add Another Day</button>
        </div>

        <div id="ya-itin-rows">
            <?php foreach ( $itinerary as $idx => $item ) : ?>
                <div class="ya-rep-item">
                    <div class="ya-rep-head">
                        <span>📅 Day <?php echo esc_html( $item['day'] ?? sprintf( '%02d', $idx + 1 ) ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove Day</button>
                    </div>
                    <div class="ya-admin-row ya-admin-row--3">
                        <div class="ya-admin-field">
                            <label>Day Number (e.g. 01)</label>
                            <input type="text" name="itin_day[]" value="<?php echo esc_attr( $item['day'] ?? sprintf( '%02d', $idx + 1 ) ); ?>" placeholder="01">
                        </div>
                        <div class="ya-admin-field">
                            <label>Stage / Elevation / Location</label>
                            <input type="text" name="itin_stage[]" value="<?php echo esc_attr( $item['stage'] ?? '' ); ?>" placeholder="e.g. 3,440m or Kathmandu">
                        </div>
                        <div class="ya-admin-field">
                            <label>Day Main Title</label>
                            <input type="text" name="itin_title[]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" placeholder="e.g. Trek to Namche Bazaar">
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Day Narrative (Supports Rich Text)</label>
                        <textarea name="itin_desc[]" rows="3" placeholder="Day trail narrative describing the route, landmarks, suspension bridges and mountain views..."><?php echo esc_textarea( $item['desc'] ?? '' ); ?></textarea>
                    </div>
                    <div class="ya-admin-field">
                        <label>Meta Pills / Keywords (Comma-separated: Meals, Hotel, Walking duration)</label>
                        <input type="text" name="itin_pills[]" value="<?php echo esc_attr( $item['pills'] ?? '' ); ?>" placeholder="e.g. Breakfast, Lunch & Dinner, Local Lodge, Approx. 5–6 hrs">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.getElementById('ya-add-itin-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-itin-rows');
                const count = String(container.children.length + 1).padStart(2, '0');
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.innerHTML = `
                    <div class="ya-rep-head">
                        <span>📅 Day ${count}</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove Day</button>
                    </div>
                    <div class="ya-admin-row ya-admin-row--3">
                        <div class="ya-admin-field">
                            <label>Day Number</label>
                            <input type="text" name="itin_day[]" value="${count}" placeholder="${count}">
                        </div>
                        <div class="ya-admin-field">
                            <label>Stage / Elevation / Location</label>
                            <input type="text" name="itin_stage[]" placeholder="e.g. 3,870m or Namche">
                        </div>
                        <div class="ya-admin-field">
                            <label>Day Main Title</label>
                            <input type="text" name="itin_title[]" placeholder="e.g. Trek to Tengboche">
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Day Narrative</label>
                        <textarea name="itin_desc[]" rows="3" placeholder="Describe the day's route, trail conditions, scenery and highlights..."></textarea>
                    </div>
                    <div class="ya-admin-field">
                        <label>Meta Pills / Keywords (Comma-separated)</label>
                        <input type="text" name="itin_pills[]" value="Breakfast, Lunch & Dinner, Local Lodge, Approx. 5 hrs" placeholder="e.g. Breakfast, Lunch & Dinner, Local Lodge, Approx. 5 hrs">
                    </div>
                `;
                container.appendChild(div);
            });
        </script>
        <?php
    }

    /**
     * 5. Inclusions & Exclusions Repeater
     */
    public static function render_inex_metabox( $post ) {
        $inclusions = get_post_meta( $post->ID, '_trek_inclusions_full', true );
        if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
            $inclusions = array();
        }

        $exclusions = get_post_meta( $post->ID, '_trek_exclusions_full', true );
        if ( empty( $exclusions ) || ! is_array( $exclusions ) ) {
            $exclusions = array();
        }
        ?>
        <div class="ya-admin-row">
            <div>
                <div class="ya-admin-subhead" style="border-bottom-color:#045857;">
                    <span style="color:#045857;">Included In The Trip</span>
                    <button type="button" class="button button-secondary button-small" onclick="yaAddSimpleRow('ya-inc-full-rows', 'inc_full_item[]')">+ Add Included</button>
                </div>
                <div id="ya-inc-full-rows">
                    <?php foreach ( $inclusions as $inc ) : ?>
                        <div style="display:flex; gap:6px; margin-bottom:6px; align-items:center;">
                            <span style="color:#045857;">✔</span>
                            <input type="text" name="inc_full_item[]" value="<?php echo esc_attr( $inc ); ?>" style="flex-grow:1;" placeholder="e.g. Return domestic flights (Kathmandu–Lukla–Kathmandu)">
                            <button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="ya-admin-subhead" style="border-bottom-color:#8a2424;">
                    <span style="color:#8a2424;">Excluded / Not Included</span>
                    <button type="button" class="button button-secondary button-small" onclick="yaAddSimpleRow('ya-exc-full-rows', 'exc_full_item[]')">+ Add Excluded</button>
                </div>
                <div id="ya-exc-full-rows">
                    <?php foreach ( $exclusions as $exc ) : ?>
                        <div style="display:flex; gap:6px; margin-bottom:6px; align-items:center;">
                            <span style="color:#8a2424;">✖</span>
                            <input type="text" name="exc_full_item[]" value="<?php echo esc_attr( $exc ); ?>" style="flex-grow:1;" placeholder="e.g. International flights and Nepal entry visa fees">
                            <button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            function yaAddSimpleRow(containerId, inputName) {
                const c = document.getElementById(containerId);
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; gap:6px; margin-bottom:6px; align-items:center;';
                div.innerHTML = `<span style="color:#666;">•</span><input type="text" name="${inputName}" style="flex-grow:1;" placeholder="e.g. Enter inclusion or exclusion detail..."><button type="button" class="ya-btn-delete" onclick="this.parentElement.remove()">✕</button>`;
                c.appendChild(div);
            }
        </script>
        <?php
    }

    /**
     * 6. Gallery Repeater
     */
    public static function render_gallery_metabox( $post ) {
        $gal_kicker  = get_post_meta( $post->ID, '_trek_gallery_kicker', true );
        $gal_script  = get_post_meta( $post->ID, '_trek_gallery_script', true );
        $gal_heading = get_post_meta( $post->ID, '_trek_gallery_heading', true );
        $gal_subtext = get_post_meta( $post->ID, '_trek_gallery_subtext', true );

        $gallery = get_post_meta( $post->ID, '_trek_gallery_list', true );
        if ( empty( $gallery ) || ! is_array( $gallery ) ) {
            $gallery = array();
        }
        ?>
        <div class="ya-admin-row ya-admin-row--3">
            <div class="ya-admin-field">
                <label>Gallery Kicker</label>
                <input type="text" name="trek_gallery_kicker" value="<?php echo esc_attr( $gal_kicker ); ?>" placeholder="e.g. Visual Journey">
            </div>
            <div class="ya-admin-field">
                <label>Gallery Script Title</label>
                <input type="text" name="trek_gallery_script" value="<?php echo esc_attr( $gal_script ); ?>" placeholder="e.g. Above the Clouds">
            </div>
            <div class="ya-admin-field">
                <label>Gallery Main Heading</label>
                <input type="text" name="trek_gallery_heading" value="<?php echo esc_attr( $gal_heading ); ?>" placeholder="e.g. Landscapes of the Everest Trail">
            </div>
        </div>

        <div class="ya-admin-subhead">
            <span>Photo Gallery List (WP Media Uploader)</span>
            <button type="button" class="button button-primary button-small" id="ya-add-gallery-btn">+ Add Image</button>
        </div>

        <div id="ya-gallery-rows">
            <?php foreach ( $gallery as $idx => $g ) : ?>
                <div class="ya-rep-item" style="display:flex; gap:14px; align-items:center;">
                    <img id="ya_gal_prev_<?php echo $idx; ?>" class="ya-img-preview" src="<?php echo esc_url( $g['url'] ); ?>" style="width:100px; height:70px; margin:0;">
                    <div style="flex-grow:1;">
                        <input type="text" name="gal_url[]" id="ya_gal_url_<?php echo $idx; ?>" value="<?php echo esc_attr( $g['url'] ); ?>" placeholder="Upload or enter image URL" style="margin-bottom:6px;">
                        <input type="text" name="gal_label[]" value="<?php echo esc_attr( $g['label'] ); ?>" placeholder="e.g. Namche Bazaar or Ama Dablam">
                    </div>
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'ya_gal_url_<?php echo $idx; ?>', 'ya_gal_prev_<?php echo $idx; ?>')">Choose Image</button>
                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">✕</button>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.getElementById('ya-add-gallery-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-gallery-rows');
                const idx = Date.now();
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.style.cssText = 'display:flex; gap:14px; align-items:center;';
                div.innerHTML = `
                    <img id="ya_gal_prev_${idx}" class="ya-img-preview" style="width:100px; height:70px; margin:0; display:none;">
                    <div style="flex-grow:1;">
                        <input type="text" name="gal_url[]" id="ya_gal_url_${idx}" placeholder="Upload or enter image URL" style="margin-bottom:6px;">
                        <input type="text" name="gal_label[]" placeholder="e.g. Namche Bazaar or Ama Dablam">
                    </div>
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'ya_gal_url_${idx}', 'ya_gal_prev_${idx}')">Choose Image</button>
                    <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">✕</button>
                `;
                container.appendChild(div);
            });
        </script>
        <?php
    }

    /**
     * 7. Extensions & Add-ons (Top Headings + WYSIWYG & Cards Repeater)
     */
    public static function render_extensions_metabox( $post ) {
        $ext_photo    = get_post_meta( $post->ID, '_trek_ext_photo', true );
        $ext_kicker   = get_post_meta( $post->ID, '_trek_ext_kicker', true );
        $ext_script   = get_post_meta( $post->ID, '_trek_ext_script', true );
        $ext_heading  = get_post_meta( $post->ID, '_trek_ext_heading', true );
        $ext_intro    = get_post_meta( $post->ID, '_trek_ext_intro', true );
        $ext_btn_text = get_post_meta( $post->ID, '_trek_ext_btn_text', true );
        $ext_btn_url  = get_post_meta( $post->ID, '_trek_ext_btn_url', true );

        $extensions = get_post_meta( $post->ID, '_trek_extensions_list', true );
        if ( empty( $extensions ) || ! is_array( $extensions ) ) {
            $extensions = array();
        }
        ?>
        <div class="ya-admin-box">
            <h4 style="margin-top:0; color:#022f2e;">Top Intro Section (Banner Photo, 2 Headings & WYSIWYG Narrative)</h4>
            <div class="ya-admin-field">
                <label>Intro Photo Banner</label>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="text" name="trek_ext_photo" id="trek_ext_photo" value="<?php echo esc_attr( $ext_photo ); ?>" style="flex-grow:1;" placeholder="Upload or enter extension banner photo URL">
                    <button type="button" class="button button-secondary" onclick="yaOpenMediaUploader(this, 'trek_ext_photo', 'trek_ext_preview')">Upload Photo</button>
                </div>
                <img id="trek_ext_preview" class="ya-img-preview" src="<?php echo esc_url( $ext_photo ); ?>" style="<?php echo empty( $ext_photo ) ? 'display:none;' : ''; ?>">
            </div>
            <div class="ya-admin-row ya-admin-row--3" style="margin-top:10px;">
                <div class="ya-admin-field">
                    <label>Kicker</label>
                    <input type="text" name="trek_ext_kicker" value="<?php echo esc_attr( $ext_kicker ); ?>" placeholder="e.g. Make It Yours">
                </div>
                <div class="ya-admin-field">
                    <label>Script Title (Heading 1)</label>
                    <input type="text" name="trek_ext_script" value="<?php echo esc_attr( $ext_script ); ?>" placeholder="e.g. Stay a Little Longer">
                </div>
                <div class="ya-admin-field">
                    <label>Main Heading (Heading 2)</label>
                    <input type="text" name="trek_ext_heading" value="<?php echo esc_attr( $ext_heading ); ?>" placeholder="e.g. Add another side of Nepal to your trek">
                </div>
            </div>
            <div class="ya-admin-field" style="margin-top:10px;">
                <label><strong>Intro Text (WYSIWYG Editor)</strong></label>
                <?php
                wp_editor( $ext_intro, 'trek_ext_intro', array(
                    'textarea_name' => 'trek_ext_intro',
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                    'teeny'         => false,
                    'quicktags'     => true,
                ) );
                ?>
            </div>
            <div class="ya-admin-row" style="margin-top:12px;">
                <div class="ya-admin-field">
                    <label>Button Text</label>
                    <input type="text" name="trek_ext_btn_text" value="<?php echo esc_attr( $ext_btn_text ); ?>" placeholder="e.g. Plan a Bespoke Extension">
                </div>
                <div class="ya-admin-field">
                    <label>Button URL</label>
                    <input type="text" name="trek_ext_btn_url" value="<?php echo esc_attr( $ext_btn_url ); ?>" placeholder="e.g. /bespoke-trips/ or https://...">
                </div>
            </div>
        </div>

        <div class="ya-admin-subhead">
            <span>Extension & Add-on Cards Repeater</span>
            <button type="button" class="button button-primary button-small" id="ya-add-ext-btn">+ Add Extension Card</button>
        </div>

        <div id="ya-ext-rows">
            <?php foreach ( $extensions as $idx => $ext ) : ?>
                <div class="ya-rep-item">
                    <div class="ya-rep-head">
                        <span>Card: <?php echo esc_html( $ext['title'] ?? 'Extension' ); ?></span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-row">
                        <div class="ya-admin-field">
                            <label>Extension Card Title</label>
                            <input type="text" name="ext_title[]" value="<?php echo esc_attr( $ext['title'] ?? '' ); ?>" placeholder="e.g. Kathmandu Heritage">
                        </div>
                        <div class="ya-admin-field">
                            <label>Suggested Duration Subtitle</label>
                            <input type="text" name="ext_suggested[]" value="<?php echo esc_attr( $ext['suggested'] ?? 'Suggested: 1–2 extra days' ); ?>" placeholder="e.g. Suggested: 1–2 extra days">
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Description</label>
                        <textarea name="ext_desc[]" rows="2" placeholder="e.g. Add guided visits to the Kathmandu Valley’s historic squares, temples, stupas and artisan neighbourhoods."><?php echo esc_textarea( $ext['desc'] ?? '' ); ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
            document.getElementById('ya-add-ext-btn')?.addEventListener('click', function() {
                const container = document.getElementById('ya-ext-rows');
                const div = document.createElement('div');
                div.className = 'ya-rep-item';
                div.innerHTML = `
                    <div class="ya-rep-head">
                        <span>New Extension Card</span>
                        <button type="button" class="ya-btn-delete" onclick="this.closest('.ya-rep-item').remove()">Remove</button>
                    </div>
                    <div class="ya-admin-row">
                        <div class="ya-admin-field">
                            <label>Extension Card Title</label>
                            <input type="text" name="ext_title[]" placeholder="e.g. Chitwan Wildlife">
                        </div>
                        <div class="ya-admin-field">
                            <label>Suggested Duration Subtitle</label>
                            <input type="text" name="ext_suggested[]" value="Suggested: 2–3 extra days" placeholder="e.g. Suggested: 2–3 extra days">
                        </div>
                    </div>
                    <div class="ya-admin-field">
                        <label>Description</label>
                        <textarea name="ext_desc[]" rows="2" placeholder="e.g. Exchange the high mountains for Nepal’s subtropical lowlands, forest landscapes and wildlife activities."></textarea>
                    </div>
                `;
                container.appendChild(div);
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
        $trek_id    = absint( get_post_meta( $post->ID, '_trek_enquiry_trek_id', true ) );
        $trek_title = get_post_meta( $post->ID, '_trek_enquiry_trek_title', true );
        $email      = get_post_meta( $post->ID, '_trek_enquiry_email', true );
        $phone      = get_post_meta( $post->ID, '_trek_enquiry_phone', true );
        $travellers = get_post_meta( $post->ID, '_trek_enquiry_travellers', true );
        $source     = get_post_meta( $post->ID, '_trek_enquiry_source', true );
        $region     = get_post_meta( $post->ID, '_trek_enquiry_region', true );
        $feeling    = get_post_meta( $post->ID, '_trek_enquiry_feeling', true );
        $pace       = get_post_meta( $post->ID, '_trek_enquiry_pace', true );
        ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;background:#fcfcfc;padding:16px;border-radius:8px;border:1px solid #e2e4e7;">
            <div>
                <strong style="color:#2c3338;"><?php esc_html_e( 'Matched Journey / Trip', 'trek-content-manager' ); ?></strong><br>
                <?php
                if ( $trek_id && get_post( $trek_id ) ) {
                    echo '<a href="' . esc_url( get_edit_post_link( $trek_id ) ) . '"><strong>' . esc_html( get_the_title( $trek_id ) ) . '</strong></a>';
                } elseif ( ! empty( $trek_title ) ) {
                    echo '<strong style="color:#022f2e;">' . esc_html( $trek_title ) . '</strong>';
                } else {
                    echo '—';
                }
                ?>
            </div>
            <div>
                <strong style="color:#2c3338;"><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></strong><br>
                <?php echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—'; ?>
            </div>
            <div>
                <strong style="color:#2c3338;"><?php esc_html_e( 'Source', 'trek-content-manager' ); ?></strong><br>
                <?php
                if ( 'journey_finder' === $source || 'Journey Selector' === $source ) {
                    echo '<span style="display:inline-block;color:#022f2e;background:#e2f2e9;padding:3px 9px;border-radius:6px;font-weight:600;font-size:11px;border:1px solid #b7dfc8;">🗺️ ' . esc_html__( 'Journey Selector', 'trek-content-manager' ) . '</span>';
                } else {
                    echo esc_html( $source ?: 'frontend' );
                }
                ?>
            </div>
            <?php if ( ! empty( $phone ) ) : ?>
                <div>
                    <strong style="color:#2c3338;"><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?></strong><br>
                    <?php echo esc_html( $phone ); ?>
                </div>
            <?php endif; ?>
            <?php if ( ! empty( $travellers ) ) : ?>
                <div>
                    <strong style="color:#2c3338;"><?php esc_html_e( 'Travellers', 'trek-content-manager' ); ?></strong><br>
                    <?php echo esc_html( $travellers ); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $region ) || ! empty( $feeling ) || ! empty( $pace ) ) : ?>
            <div style="margin-top:16px;padding:14px;background:#f0f7f6;border-radius:8px;border:1px solid #cce5e3;">
                <strong style="color:#022f2e;display:block;margin-bottom:8px;">🧭 <?php esc_html_e( 'Selected Travel Preferences', 'trek-content-manager' ); ?>:</strong>
                <div style="display:flex;gap:15px;flex-wrap:wrap;">
                    <?php if ( ! empty( $region ) ) : ?>
                        <div><span style="color:#646970;font-size:11px;text-transform:uppercase;font-weight:700;"><?php esc_html_e( 'Region', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $region ) ) ); ?></strong></div>
                    <?php endif; ?>
                    <?php if ( ! empty( $feeling ) ) : ?>
                        <div><span style="color:#646970;font-size:11px;text-transform:uppercase;font-weight:700;"><?php esc_html_e( 'Feeling', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( $feeling ) ); ?></strong></div>
                    <?php endif; ?>
                    <?php if ( ! empty( $pace ) ) : ?>
                        <div><span style="color:#646970;font-size:11px;text-transform:uppercase;font-weight:700;"><?php esc_html_e( 'Pace', 'trek-content-manager' ); ?>:</span> <strong><?php echo esc_html( ucwords( $pace ) ); ?></strong></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <p style="margin:18px 0 0;color:#646970;"><?php esc_html_e( 'The traveller’s itinerary request details are stored in the main WordPress editor above.', 'trek-content-manager' ); ?></p>
        <?php
    }

    /**
     * Metabox for `trek_review` Post Type (Admin View & Edit Review Submissions)
     */
    public static function render_review_detail_metabox( $post ) {
        wp_nonce_field( 'save_review_details_action', 'review_details_nonce' );

        $trek_id  = get_post_meta( $post->ID, '_trek_id', true );
        $rating   = get_post_meta( $post->ID, '_trek_rating', true ) ?: 5;
        $email    = get_post_meta( $post->ID, '_trek_reviewer_email', true );
        $city     = get_post_meta( $post->ID, '_trek_city', true );
        $country  = get_post_meta( $post->ID, '_trek_country', true );
        $tour_date= get_post_meta( $post->ID, '_trek_tour_date', true );
        $moment   = get_post_meta( $post->ID, '_trek_moment', true );
        $leader   = get_post_meta( $post->ID, '_trek_leader', true );
        $advice   = get_post_meta( $post->ID, '_trek_advice', true );
        $approved = get_post_meta( $post->ID, '_trek_approved', true ) ?: '1';

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
                    <option value="4" <?php selected( $rating, 4 ); ?>>★★★★☆ (4 Stars)</option>
                    <option value="3" <?php selected( $rating, 3 ); ?>>★★★☆☆ (3 Stars)</option>
                    <option value="2" <?php selected( $rating, 2 ); ?>>★★☆☆☆ (2 Stars)</option>
                    <option value="1" <?php selected( $rating, 1 ); ?>>★☆☆☆☆ (1 Star)</option>
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
            // Note: trek_hero_subtitle is handled separately as textarea below
            'trek_hero_bg_img',
            'trek_fact_high_point',
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
            'trek_route_img',
            'trek_route_caption_sub',
            'trek_route_caption_h3',
            'trek_itin_kicker',
            'trek_itin_script',
            'trek_itin_heading',
            'trek_itin_subtext',
            'trek_itin_side_script',
            'trek_itin_side_title',
            'trek_itin_side_desc',
            'trek_gallery_kicker',
            'trek_gallery_script',
            'trek_gallery_heading',
            'trek_gallery_subtext',
            'trek_ext_photo',
            'trek_ext_kicker',
            'trek_ext_script',
            'trek_ext_heading',
            'trek_ext_btn_text',
            'trek_ext_btn_url',
            'trek_ess_kicker',
            'trek_ess_script',
            'trek_ess_heading',
            'trek_ess_card_img',
            'trek_ess_card_kicker',
            'trek_ess_card_title',
            'trek_ess_card_desc',
            'trek_ess_card_btn',
            'trek_ess_card_email',
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
            if ( isset( $_POST[ $f ] ) ) {
                $value = sanitize_text_field( $_POST[ $f ] );
                if ( '' === trim( $value ) ) {
                    delete_post_meta( $post_id, '_' . $f );
                } else {
                    update_post_meta( $post_id, '_' . $f, $value );
                }
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
            $value = sanitize_textarea_field( $_POST['trek_hero_subtitle'] );
            if ( '' === trim( $value ) ) {
                delete_post_meta( $post_id, '_trek_hero_subtitle' );
            } else {
                update_post_meta( $post_id, '_trek_hero_subtitle', $value );
            }
        }

        // 2. WYSIWYG Editor HTML Fields (Sanitized with wp_kses_post)
        $wysiwyg_fields = array(
            'trek_overview_content',
            'trek_overview_highlight',
            'trek_ext_intro',
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

        // 4. Side Card Highlights Points
        if ( isset( $_POST['itin_side_point'] ) && is_array( $_POST['itin_side_point'] ) ) {
            $pts = array_map( 'sanitize_text_field', array_filter( $_POST['itin_side_point'] ) );
            $pts = array_values( $pts );
            empty( $pts ) ? delete_post_meta( $post_id, '_trek_itin_side_points' ) : update_post_meta( $post_id, '_trek_itin_side_points', $pts );
        } else {
            delete_post_meta( $post_id, '_trek_itin_side_points' );
        }

        // 5. Itinerary Repeater (Sync dual keys: _trek_itinerary_list & _trek_itinerary)
        if ( isset( $_POST['itin_title'] ) && is_array( $_POST['itin_title'] ) ) {
            $itinerary = array();
            $days   = $_POST['itin_day'] ?? array();
            $stages = $_POST['itin_stage'] ?? array();
            $titles = $_POST['itin_title'];
            $pills  = $_POST['itin_pills'] ?? array();
            $descs  = $_POST['itin_desc'] ?? array();

            for ( $i = 0; $i < count( $titles ); $i++ ) {
                if ( ! empty( trim( $titles[ $i ] ) ) ) {
                    $itinerary[] = array(
                        'day'   => sanitize_text_field( $days[ $i ] ?? sprintf( '%02d', $i + 1 ) ),
                        'stage' => sanitize_text_field( $stages[ $i ] ?? '' ),
                        'title' => sanitize_text_field( $titles[ $i ] ),
                        'pills' => sanitize_text_field( $pills[ $i ] ?? '' ),
                        'desc'  => wp_kses_post( $descs[ $i ] ?? '' )
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

        // 6. Full Inclusions & Exclusions (Sync dual keys: _trek_inclusions_full & _trek_inclusions)
        if ( isset( $_POST['inc_full_item'] ) && is_array( $_POST['inc_full_item'] ) ) {
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
        if ( isset( $_POST['exc_full_item'] ) && is_array( $_POST['exc_full_item'] ) ) {
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

        // 7. Dedicated Hero Slider Gallery
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

        // 8. Gallery Repeater (Sync dual keys: _trek_gallery_list & _trek_gallery_images)
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

        // 8. Extensions Repeater
        if ( isset( $_POST['ext_title'] ) && is_array( $_POST['ext_title'] ) ) {
            $extensions = array();
            $titles    = $_POST['ext_title'];
            $suggested = $_POST['ext_suggested'] ?? array();
            $descs     = $_POST['ext_desc'] ?? array();

            for ( $i = 0; $i < count( $titles ); $i++ ) {
                if ( ! empty( trim( $titles[ $i ] ) ) ) {
                    $extensions[] = array(
                        'title'     => sanitize_text_field( $titles[ $i ] ),
                        'suggested' => sanitize_text_field( $suggested[ $i ] ?? 'Suggested: 1–2 extra days' ),
                        'desc'      => sanitize_textarea_field( $descs[ $i ] ?? '' )
                    );
                }
            }
            empty( $extensions ) ? delete_post_meta( $post_id, '_trek_extensions_list' ) : update_post_meta( $post_id, '_trek_extensions_list', $extensions );
        } else {
            delete_post_meta( $post_id, '_trek_extensions_list' );
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

        // 11. Sync Quick Facts Array (_trek_quick_facts)
        $quick_facts = array(
            'duration'    => get_post_meta( $post_id, '_trek_hero_days', true ),
            'high_point'  => get_post_meta( $post_id, '_trek_fact_high_point', true ),
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
                    <li>Enter the trek title and fill the visual metaboxes (Hero, Quick Facts, Overview WYSIWYG, Itinerary Days, Photo Gallery, Extensions, Reviews).</li>
                    <li>Use the <strong>Upload Image</strong> buttons to easily select images from your WordPress Media Library!</li>
                    <li>All submitted reviews from visitors are saved in the database under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_review' ) ); ?>"><strong>Trek Manager &rarr; Reviews</strong></a> where you can approve, moderate or edit them.</li>
                    <li>Click <strong>Publish</strong>. That's it!</li>
                </ol>
            </div>
        </div>
        <?php
    }
}
