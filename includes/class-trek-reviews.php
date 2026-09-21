<?php
/**
 * Trek Manager - Reviews Controller & Custom CRUD
 *
 * Provides a dedicated, professional management interface for Trek Reviews.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Reviews {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 12 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_trek_approve_review', array( __CLASS__, 'ajax_approve_review' ) );
        add_action( 'wp_ajax_trek_reject_review', array( __CLASS__, 'ajax_reject_review' ) );
        add_action( 'wp_ajax_trek_delete_review', array( __CLASS__, 'ajax_delete_review' ) );
    }

    public static function register_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'All Reviews', 'trek-content-manager' ),
            __( 'All Reviews', 'trek-content-manager' ),
            'edit_posts',
            'trek-reviews',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( 'trek_destination_page_trek-reviews' !== $hook && ( ! isset( $_GET['page'] ) || 'trek-reviews' !== $_GET['page'] ) ) {
            return;
        }

        wp_enqueue_style(
            'trek-admin-reviews-css',
            TREK_MANAGER_PLUGIN_URL . 'assets/css/trek-admin-reviews.css',
            array(),
            TREK_MANAGER_VERSION
        );

        wp_enqueue_script(
            'trek-admin-reviews-js',
            TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-admin-reviews.js',
            array( 'jquery' ),
            TREK_MANAGER_VERSION,
            true
        );

        wp_localize_script(
            'trek-admin-reviews-js',
            'TrekReviewsData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'trek_reviews_nonce' ),
                'i18n'    => array(
                    'confirmDelete' => __( 'Are you sure you want to delete this review?', 'trek-content-manager' ),
                    'error'         => __( 'An error occurred. Please try again.', 'trek-content-manager' ),
                ),
            )
        );
    }

    /**
     * AJAX: Approve review
     */
    public static function ajax_approve_review() {
        check_ajax_referer( 'trek_reviews_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['review_id'] ?? 0 );
        if ( ! $post_id || 'trek_review' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid review ID.', 'trek-content-manager' ) ) );
        }

        update_post_meta( $post_id, '_trek_approved', '1' );
        wp_update_post( array(
            'ID'          => $post_id,
            'post_status' => 'publish',
        ) );

        wp_send_json_success( array(
            'message' => __( 'Review approved and published.', 'trek-content-manager' ),
            'status'  => 'Published',
        ) );
    }

    /**
     * AJAX: Reject / Unpublish review
     */
    public static function ajax_reject_review() {
        check_ajax_referer( 'trek_reviews_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['review_id'] ?? 0 );
        if ( ! $post_id || 'trek_review' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid review ID.', 'trek-content-manager' ) ) );
        }

        update_post_meta( $post_id, '_trek_approved', '0' );
        wp_update_post( array(
            'ID'          => $post_id,
            'post_status' => 'pending',
        ) );

        wp_send_json_success( array(
            'message' => __( 'Review moved to pending moderation.', 'trek-content-manager' ),
            'status'  => 'Pending',
        ) );
    }

    /**
     * AJAX: Delete review
     */
    public static function ajax_delete_review() {
        check_ajax_referer( 'trek_reviews_nonce', 'nonce' );

        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $post_id = absint( $_POST['review_id'] ?? 0 );
        if ( ! $post_id || 'trek_review' !== get_post_type( $post_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid review ID.', 'trek-content-manager' ) ) );
        }

        wp_trash_post( $post_id );

        wp_send_json_success( array(
            'message' => __( 'Review moved to trash.', 'trek-content-manager' ),
        ) );
    }

    /**
     * Render the custom All Reviews management page
     */
    public static function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        // Query params
        $status_filter = sanitize_text_field( $_GET['status'] ?? 'all' );
        $rating_filter = absint( $_GET['rating'] ?? 0 );
        $search_query  = sanitize_text_field( $_GET['s'] ?? '' );
        $paged         = max( 1, absint( $_GET['paged'] ?? 1 ) );

        $args = array(
            'post_type'      => 'trek_review',
            'posts_per_page' => 20,
            'paged'          => $paged,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( 'published' === $status_filter ) {
            $args['post_status'] = 'publish';
            $args['meta_query'] = array(
                array(
                    'key'   => '_trek_approved',
                    'value' => '1',
                ),
            );
        } elseif ( 'pending' === $status_filter ) {
            $args['post_status'] = array( 'pending', 'publish', 'draft' );
            $args['meta_query'] = array(
                array(
                    'key'     => '_trek_approved',
                    'value'   => '1',
                    'compare' => '!=',
                ),
            );
        } else {
            $args['post_status'] = array( 'publish', 'pending', 'draft' );
        }

        if ( $rating_filter > 0 ) {
            $args['meta_query'][] = array(
                'key'   => '_trek_rating',
                'value' => $rating_filter,
            );
        }

        if ( ! empty( $search_query ) ) {
            $args['s'] = $search_query;
        }

        $query = new WP_Query( $args );

        // Counts for tabs
        $counts = wp_count_posts( 'trek_review' );
        $total_count = intval( $counts->publish ?? 0 ) + intval( $counts->pending ?? 0 ) + intval( $counts->draft ?? 0 );
        $published_count = intval( $counts->publish ?? 0 );
        $pending_count = intval( $counts->pending ?? 0 );

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-reviews.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Reviews template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }
}
