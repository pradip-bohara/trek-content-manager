<?php
/**
 * Trek Content Manager - Secure REST API Endpoints & Review Management
 * 
 * Provides:
 * - GET  /wp-json/trek-manager/v1/destinations/{id} (Filtered by role)
 * - POST /wp-json/trek-manager/v1/destinations/{id} (Save draft / publish)
 * - POST /wp-json/trek-manager/v1/enquiries          (Public booking submission)
 * - POST /wp-json/trek-manager/v1/reviews            (Public review submission saved to DB)
 * - GET  /wp-json/trek-manager/v1/reviews/{trek_id}  (Fetch approved reviews for a destination)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_REST_API {

    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
        add_action( 'wp_ajax_ya_submit_trek_review', array( __CLASS__, 'ajax_submit_review' ) );
        add_action( 'wp_ajax_nopriv_ya_submit_trek_review', array( __CLASS__, 'ajax_submit_review' ) );
    }

    public static function register_routes() {
        $namespace = 'trek-manager/v1';

        register_rest_route( $namespace, '/destinations/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( __CLASS__, 'get_destination' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $namespace, '/destinations/(?P<id>\d+)', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( __CLASS__, 'save_destination' ),
            'permission_callback' => array( __CLASS__, 'check_edit_permission' ),
        ) );

        register_rest_route( $namespace, '/enquiries', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( __CLASS__, 'submit_enquiry' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $namespace, '/reviews', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array( __CLASS__, 'submit_review' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $namespace, '/reviews/(?P<trek_id>\d+)', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array( __CLASS__, 'get_trek_reviews' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public static function check_edit_permission( $request ) {
        $post_id = $request->get_param( 'id' );
        return current_user_can( 'edit_post', $post_id );
    }

    public static function get_destination( $request ) {
        $post_id = $request->get_param( 'id' );
        $post = get_post( $post_id );

        if ( ! $post || $post->post_type !== 'trek_destination' ) {
            return new WP_Error( 'not_found', __( 'Trek destination not found', 'trek-content-manager' ), array( 'status' => 404 ) );
        }

        if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
            return new WP_Error( 'not_found', __( 'Trek destination not found', 'trek-content-manager' ), array( 'status' => 404 ) );
        }

        $raw_json = get_post_meta( $post_id, '_trek_structured_data', true );
        $data = json_decode( $raw_json, true );

        if ( ! is_array( $data ) ) {
            $data = Trek_Renderer::get_full_trek_data( $post );
        }

        if ( empty( $data ) ) {
            return new WP_Error( 'invalid_data', __( 'Destination data is unavailable', 'trek-content-manager' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( $data );
    }

    public static function save_destination( $request ) {
        $post_id = absint( $request->get_param( 'id' ) );
        $post    = get_post( $post_id );

        if ( ! $post || 'trek_destination' !== $post->post_type ) {
            return new WP_Error( 'invalid_destination', __( 'Trek destination not found.', 'trek-content-manager' ), array( 'status' => 404 ) );
        }

        $body = $request->get_json_params();

        if ( empty( $body ) || ! is_array( $body ) ) {
            return new WP_Error( 'invalid_payload', __( 'Invalid JSON payload', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        $safe_body = array();
        if ( isset( $body['title'] ) ) {
            $safe_body['title'] = sanitize_text_field( $body['title'] );
        }

        if ( isset( $body['overview'] ) && is_array( $body['overview'] ) ) {
            $safe_overview = array();
            foreach ( $body['overview'] as $key => $value ) {
                $safe_overview[ sanitize_key( $key ) ] = is_string( $value ) ? wp_kses_post( $value ) : $value;
            }
            $safe_body['overview'] = $safe_overview;
        }

        if ( isset( $body['hero'] ) && is_array( $body['hero'] ) ) {
            $safe_hero = array();
            foreach ( $body['hero'] as $key => $value ) {
                $safe_hero[ sanitize_key( $key ) ] = is_string( $value ) ? sanitize_text_field( $value ) : $value;
            }
            $safe_body['hero'] = $safe_hero;
        }

        if ( isset( $body['highlights'] ) && is_array( $body['highlights'] ) ) {
            $safe_body['highlights'] = array_map( 'sanitize_text_field', array_values( $body['highlights'] ) );
        }

        if ( isset( $body['itinerary'] ) && is_array( $body['itinerary'] ) ) {
            $safe_body['itinerary'] = array_values( array_filter( array_map( function( $day ) {
                if ( ! is_array( $day ) ) {
                    return null;
                }
                return array(
                    'day'        => sanitize_text_field( $day['day'] ?? '' ),
                    'stage'      => sanitize_text_field( $day['stage'] ?? '' ),
                    'title'      => sanitize_text_field( $day['title'] ?? '' ),
                    'description'=> wp_kses_post( $day['description'] ?? '' ),
                    'pills'      => sanitize_text_field( $day['pills'] ?? '' ),
                );
            }, $body['itinerary'] ) ) );
        }

        update_post_meta( $post_id, '_trek_structured_data', wp_json_encode( $safe_body ) );

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Trek destination saved successfully', 'trek-content-manager' )
        ) );
    }

    public static function submit_enquiry( $request ) {
        $params = $request->get_params();

        if ( ! empty( $params['website_hp'] ) ) {
            return new WP_Error( 'spam_detected', __( 'Spam detected', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        $email = sanitize_email( $params['email'] ?? '' );
        if ( ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', __( 'Valid email is required', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        $name    = sanitize_text_field( $params['name'] ?? '' );
        $message = sanitize_textarea_field( $params['message'] ?? '' );
        $trek_id = absint( $params['trek_id'] ?? 0 );

        if ( empty( $name ) || empty( $message ) ) {
            return new WP_Error( 'missing_fields', __( 'Please provide your name and message.', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        $post_id = wp_insert_post( array(
            'post_type'    => 'trek_enquiry',
            'post_title'   => $name,
            'post_content' => $message,
            'post_status'  => 'private',
        ), true );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( 'db_error', __( 'Failed to store the enquiry.', 'trek-content-manager' ), array( 'status' => 500 ) );
        }

        update_post_meta( $post_id, '_trek_enquiry_email', $email );
        update_post_meta( $post_id, '_trek_enquiry_trek_id', $trek_id );
        update_post_meta( $post_id, '_trek_enquiry_source', sanitize_text_field( $params['source'] ?? 'frontend' ) );

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Your enquiry has been received.', 'trek-content-manager' )
        ) );
    }

    /**
     * Submit Review Handler (Stores directly in WordPress Database as `trek_review`)
     */
    public static function submit_review( $request ) {
        $params = $request->get_params();

        $name       = sanitize_text_field( $params['name'] ?? '' );
        $email      = sanitize_email( $params['email'] ?? '' );
        $city       = sanitize_text_field( $params['city'] ?? '' );
        $country    = sanitize_text_field( $params['country'] ?? '' );
        $tour_date  = sanitize_text_field( $params['tour_date'] ?? '' );
        $rating     = intval( $params['rating'] ?? 5 );
        $comment    = sanitize_textarea_field( $params['comment'] ?? '' );
        $moment     = sanitize_textarea_field( $params['moment'] ?? '' );
        $leader     = sanitize_textarea_field( $params['leader'] ?? '' );
        $advice     = sanitize_textarea_field( $params['advice'] ?? '' );
        $trek_id    = intval( $params['trek_id'] ?? 0 );

        if ( empty( $name ) || empty( $comment ) ) {
            return new WP_Error( 'missing_fields', __( 'Please provide your name and review comment.', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        if ( ! $trek_id || 'trek_destination' !== get_post_type( $trek_id ) || 'publish' !== get_post_status( $trek_id ) ) {
            return new WP_Error( 'invalid_trek', __( 'Please select a valid published trek.', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        if ( ! empty( $email ) && ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', __( 'Please provide a valid email address.', 'trek-content-manager' ), array( 'status' => 400 ) );
        }

        if ( $rating < 1 || $rating > 5 ) {
            $rating = 5;
        }

        $review_post_id = wp_insert_post( array(
            'post_type'    => 'trek_review',
            'post_title'   => $name,
            'post_content' => $comment,
            'post_status'  => 'publish',
        ) );

        if ( is_wp_error( $review_post_id ) ) {
            return new WP_Error( 'db_error', __( 'Failed to save review in database.', 'trek-content-manager' ), array( 'status' => 500 ) );
        }

        update_post_meta( $review_post_id, '_trek_id', $trek_id );
        update_post_meta( $review_post_id, '_trek_rating', $rating );
        update_post_meta( $review_post_id, '_trek_reviewer_email', $email );
        update_post_meta( $review_post_id, '_trek_city', $city );
        update_post_meta( $review_post_id, '_trek_country', $country );
        update_post_meta( $review_post_id, '_trek_tour_date', $tour_date );
        update_post_meta( $review_post_id, '_trek_moment', $moment );
        update_post_meta( $review_post_id, '_trek_leader', $leader );
        update_post_meta( $review_post_id, '_trek_advice', $advice );
        update_post_meta( $review_post_id, '_trek_approved', '1' );

        $trek_title = $trek_id ? get_the_title( $trek_id ) : 'Trek Destination';
        $location_str = array_filter( array( $city, $country ) );
        $loc_text = ! empty( $location_str ) ? ' (' . implode( ', ', $location_str ) . ')' : '';

        $whatsapp_text = "New Trek Review for {$trek_title}:\n"
            . "Name: {$name}{$loc_text}\n"
            . "Rating: {$rating}/5 Stars\n"
            . "Tour Date: {$tour_date}\n\n"
            . "Comment: {$comment}\n"
            . (! empty( $moment ) ? "\nInspirational Moment: {$moment}" : "")
            . (! empty( $leader ) ? "\nTrek Leader: {$leader}" : "")
            . (! empty( $advice ) ? "\nAdvice for Travellers: {$advice}" : "");

        $whatsapp_number = $trek_id ? ( get_post_meta( $trek_id, '_trek_whatsapp_number', true ) ?: '9779803197006' ) : '9779803197006';
        $whatsapp_url = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $whatsapp_number ) . '?text=' . rawurlencode( $whatsapp_text );

        return rest_ensure_response( array(
            'success'      => true,
            'review_id'    => $review_post_id,
            'message'      => __( 'Thank you! Your review has been submitted successfully.', 'trek-content-manager' ),
            'whatsapp_url' => $whatsapp_url,
            'review'       => array(
                'name'     => $name,
                'location' => implode( ', ', $location_str ),
                'rating'   => $rating,
                'quote'    => $comment,
            )
        ) );
    }

    /**
     * AJAX Fallback for Submitting Reviews
     */
    public static function ajax_submit_review() {
        check_ajax_referer( 'trek_review_nonce', 'security' );
        $request = new WP_REST_Request( 'POST' );
        $request->set_body_params( $_POST );
        $response = self::submit_review( $request );
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        } else {
            wp_send_json_success( $response->get_data() );
        }
    }

    /**
     * Fetch approved reviews for a given destination
     */
    public static function get_trek_reviews( $request ) {
        $trek_id = $request->get_param( 'trek_id' );

        $query = new WP_Query( array(
            'post_type'      => 'trek_review',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_trek_id',
                    'value'   => $trek_id,
                    'compare' => '='
                ),
                array(
                    'key'     => '_trek_approved',
                    'value'   => '1',
                    'compare' => '='
                )
            ),
            'orderby'        => 'date',
            'order'          => 'DESC'
        ) );

        $reviews = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $rid = get_the_ID();
                $city = get_post_meta( $rid, '_trek_city', true );
                $country = get_post_meta( $rid, '_trek_country', true );
                $loc = array_filter( array( $city, $country ) );

                $reviews[] = array(
                    'id'       => $rid,
                    'name'     => get_the_title(),
                    'location' => implode( ', ', $loc ),
                    'rating'   => intval( get_post_meta( $rid, '_trek_rating', true ) ?: 5 ),
                    'quote'    => get_the_content(),
                    'date'     => get_the_date(),
                );
            }
            wp_reset_postdata();
        }

        return rest_ensure_response( array(
            'success' => true,
            'count'   => count( $reviews ),
            'reviews' => $reviews
        ) );
    }
}
