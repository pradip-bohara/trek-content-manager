<?php
/**
 * Trek Manager - Enquiries Controller & Custom CRUD
 *
 * Provides a dedicated, professional management interface for Trek Enquiries.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Enquiries {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 13 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_trek_update_enquiry_status', array( __CLASS__, 'ajax_update_status' ) );
        add_action( 'wp_ajax_trek_delete_enquiry', array( __CLASS__, 'ajax_delete_enquiry' ) );
        add_action( 'wp_ajax_trek_get_enquiry_detail', array( __CLASS__, 'ajax_get_enquiry_detail' ) );
    }

    public static function register_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'All Enquiries', 'trek-content-manager' ),
            __( 'All Enquiries', 'trek-content-manager' ),
            'edit_posts',
            'trek-enquiries',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( 'trek_destination_page_trek-enquiries' !== $hook && ( ! isset( $_GET['page'] ) || 'trek-enquiries' !== $_GET['page'] ) ) {
            return;
        }

        wp_enqueue_style(
            'trek-admin-enquiries-css',
            TREK_MANAGER_PLUGIN_URL . 'assets/css/trek-admin-enquiries.css',
            array(),
            TREK_MANAGER_VERSION
        );

        wp_enqueue_script(
            'trek-admin-enquiries-js',
            TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-admin-enquiries.js',
            array( 'jquery' ),
            TREK_MANAGER_VERSION,
            true
        );

        wp_localize_script(
            'trek-admin-enquiries-js',
            'TrekEnquiriesData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'trek_enquiries_nonce' ),
                'i18n'    => array(
                    'confirmDelete' => __( 'Are you sure you want to delete this enquiry?', 'trek-content-manager' ),
                    'error'         => __( 'An error occurred. Please try again.', 'trek-content-manager' ),
                ),
            )
        );
    }

    /**
     * AJAX: Update enquiry status
     */
    public static function ajax_update_status() {
        check_ajax_referer( 'trek_enquiries_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['enquiry_id'] ?? 0 );
        $status  = sanitize_text_field( $_POST['status'] ?? 'New' );

        $allowed_statuses = array( 'New', 'Contacted', 'Follow Up', 'Closed' );
        if ( ! in_array( $status, $allowed_statuses, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status.', 'trek-content-manager' ) ) );
        }

        if ( ! $post_id || 'trek_enquiry' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid enquiry ID.', 'trek-content-manager' ) ) );
        }

        update_post_meta( $post_id, '_trek_enquiry_status', $status );

        wp_send_json_success( array(
            'message' => __( 'Status updated.', 'trek-content-manager' ),
            'status'  => $status,
        ) );
    }

    /**
     * AJAX: Delete enquiry
     */
    public static function ajax_delete_enquiry() {
        check_ajax_referer( 'trek_enquiries_nonce', 'nonce' );

        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['enquiry_id'] ?? 0 );
        if ( ! $post_id || 'trek_enquiry' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid enquiry ID.', 'trek-content-manager' ) ) );
        }

        wp_trash_post( $post_id );

        wp_send_json_success( array(
            'message' => __( 'Enquiry moved to trash.', 'trek-content-manager' ),
        ) );
    }

    /**
     * AJAX: Get enquiry details for slide-over panel
     */
    public static function ajax_get_enquiry_detail() {
        check_ajax_referer( 'trek_enquiries_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['enquiry_id'] ?? 0 );
        $post    = get_post( $post_id );

        if ( ! $post || 'trek_enquiry' !== $post->post_type ) {
            wp_send_json_error( array( 'message' => __( 'Enquiry not found.', 'trek-content-manager' ) ) );
        }

        $email      = get_post_meta( $post_id, '_trek_enquiry_email', true );
        $phone      = get_post_meta( $post_id, '_trek_enquiry_phone', true );
        $travellers = get_post_meta( $post_id, '_trek_enquiry_travellers', true ) ?: '1';
        $source     = get_post_meta( $post_id, '_trek_enquiry_source', true ) ?: 'Website Enquiry';
        $status     = get_post_meta( $post_id, '_trek_enquiry_status', true ) ?: 'New';
        
        $trek_id    = get_post_meta( $post_id, '_trek_enquiry_trek_id', true );
        $trip_title = get_post_meta( $post_id, '_trek_enquiry_trek_title', true );
        if ( ! $trip_title && $trek_id ) {
            $trip_title = get_the_title( $trek_id );
        }

        wp_send_json_success( array(
            'id'         => $post_id,
            'name'       => $post->post_title ?: __( 'Guest Traveller', 'trek-content-manager' ),
            'email'      => $email ?: '—',
            'phone'      => $phone ?: '—',
            'trip'       => $trip_title ?: __( 'General Enquiry', 'trek-content-manager' ),
            'travellers' => $travellers,
            'source'     => $source,
            'status'     => $status,
            'date'       => get_the_date( 'F j, Y g:i a', $post_id ),
            'message'    => wpautop( $post->post_content ) ?: '<p><em>' . esc_html__( 'No message provided.', 'trek-content-manager' ) . '</em></p>',
            'edit_url'   => get_edit_post_link( $post_id ),
        ) );
    }

    /**
     * Render the custom All Enquiries management page
     */
    public static function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        // Query params
        $status_filter = sanitize_text_field( $_GET['status'] ?? 'all' );
        $search_query  = sanitize_text_field( $_GET['s'] ?? '' );
        $paged         = max( 1, absint( $_GET['paged'] ?? 1 ) );

        $args = array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => 20,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( 'all' !== $status_filter && ! empty( $status_filter ) ) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_trek_enquiry_status',
                    'value'   => ucfirst( $status_filter ),
                    'compare' => '=',
                ),
            );
        }

        if ( ! empty( $search_query ) ) {
            $args['s'] = $search_query;
        }

        $query = new WP_Query( $args );

        // Counts
        $counts = wp_count_posts( 'trek_enquiry' );
        $total_count = intval( $counts->publish ?? 0 ) + intval( $counts->private ?? 0 ) + intval( $counts->draft ?? 0 );

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-enquiries.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Enquiries template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }
}
