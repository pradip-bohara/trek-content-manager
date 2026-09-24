<?php
/**
 * Trek Content Manager - Production-Ready Dynamic Shortcodes
 *
 * Provides secure, fully dynamic shortcodes for:
 * 1. [ya_country_destinations country="..."]: Country/Region destination card slider
 * 2. [ya_all_trips_filterable]: Dynamic AJAX/Frontend filterable trips directory with live search
 * 3. [ya_category_treks category="..."]: Category/Type specific trek card grid or carousel
 *
 * Strictly adheres to WordPress Coding Standards & Security Best Practices:
 * - Strict input sanitization (sanitize_text_field, absint, sanitize_key)
 * - Safe contextual output escaping (esc_html, esc_url, esc_attr, wp_kses_post)
 * - Output Buffering (ob_start / ob_get_clean)
 * - Safe WP_Query parameterized requests with postdata resets
 * - Null and empty state guards to prevent PHP notices
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Shortcodes {

    /**
     * Track rendered shortcode instances for unique DOM IDs
     */
    private static $instance_counter = 0;

    /**
     * Register all shortcodes
     */
    public static function init() {
        // Core shortcodes
        add_shortcode( 'ya_country_destinations',   array( __CLASS__, 'render_country_destinations' ) );
        add_shortcode( 'ya_all_trips_filterable',   array( __CLASS__, 'render_all_trips_filterable' ) );
        add_shortcode( 'ya_category_treks',         array( __CLASS__, 'render_category_treks' ) );
        add_shortcode( 'ya_signature_journeys',     array( __CLASS__, 'render_signature_journeys' ) );
        add_shortcode( 'ya_journey_finder',         array( __CLASS__, 'render_journey_finder' ) );
        add_shortcode( 'ya_nepal_map',              array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'nepal_tourism_map',          array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'ya_journal_posts',           array( __CLASS__, 'render_journal_posts' ) );
        add_shortcode( 'ya_home_notes',              array( __CLASS__, 'render_home_notes' ) );
        add_shortcode( 'ya_trip_detail',             array( __CLASS__, 'render_trip_detail' ) );

        // Canonical aliases for full flexibility across themes and pages
        add_shortcode( 'trek_country_destinations', array( __CLASS__, 'render_country_destinations' ) );
        add_shortcode( 'trek_all_trips_filterable', array( __CLASS__, 'render_all_trips_filterable' ) );
        add_shortcode( 'trek_listing',              array( __CLASS__, 'render_all_trips_filterable' ) );
        add_shortcode( 'trek_category_treks',       array( __CLASS__, 'render_category_treks' ) );
        add_shortcode( 'trek_journey_cards',        array( __CLASS__, 'render_category_treks' ) );
        add_shortcode( 'trek_signature_journeys',   array( __CLASS__, 'render_signature_journeys' ) );
        add_shortcode( 'trek_home',                 array( __CLASS__, 'render_signature_journeys' ) );
        add_shortcode( 'trek_journey_finder',       array( __CLASS__, 'render_journey_finder' ) );
        add_shortcode( 'journey_finder',            array( __CLASS__, 'render_journey_finder' ) );
        add_shortcode( 'trek_nepal_map',            array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'ya_tourism_map',            array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'trek_tourism_map',          array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'trek_journal_posts',         array( __CLASS__, 'render_journal_posts' ) );
        add_shortcode( 'trek_home_notes',            array( __CLASS__, 'render_home_notes' ) );
        add_shortcode( 'trek_trip_detail',           array( __CLASS__, 'render_trip_detail' ) );

        // Granular Field Shortcode: [trek_field id="123" field="price"]
        add_shortcode( 'trek_field',                 array( __CLASS__, 'render_trek_field' ) );

        // Traveller Matcher Discovery Experience
        add_shortcode( 'ya_traveller_matcher',       array( __CLASS__, 'render_traveller_matcher' ) );
        add_shortcode( 'trek_traveller_matcher',     array( __CLASS__, 'render_traveller_matcher' ) );
        add_shortcode( 'traveller_matcher',          array( __CLASS__, 'render_traveller_matcher' ) );

        // Modular Section Shortcodes
        add_shortcode( 'trek_hero',                  array( __CLASS__, 'render_trek_hero_section' ) );
        add_shortcode( 'trek_facts',                 array( __CLASS__, 'render_trek_facts_section' ) );
        add_shortcode( 'trek_quick_facts',           array( __CLASS__, 'render_trek_facts_section' ) );
        add_shortcode( 'trek_overview',              array( __CLASS__, 'render_trek_overview_section' ) );
        add_shortcode( 'trek_itinerary',             array( __CLASS__, 'render_trek_itinerary_section' ) );
        add_shortcode( 'trek_inclusions',            array( __CLASS__, 'render_trek_inclusions_section' ) );
        add_shortcode( 'trek_inex',                  array( __CLASS__, 'render_trek_inclusions_section' ) );
        add_shortcode( 'trek_gallery',               array( __CLASS__, 'render_trek_gallery_section' ) );
        add_shortcode( 'trek_essentials',            array( __CLASS__, 'render_trek_essentials_section' ) );
        add_shortcode( 'trek_faqs',                  array( __CLASS__, 'render_trek_essentials_section' ) );
        add_shortcode( 'trek_reviews',               array( __CLASS__, 'render_trek_reviews_section' ) );
        add_shortcode( 'trek_departures',            array( __CLASS__, 'render_trek_departures_section' ) );
        add_shortcode( 'trek_contact',               array( __CLASS__, 'render_trek_departures_section' ) );
        add_shortcode( 'trek_map',                   array( __CLASS__, 'render_trek_map_section' ) );
        add_shortcode( 'trek_card',                  array( __CLASS__, 'render_trek_card_shortcode' ) );

        add_action( 'rest_api_init', array( __CLASS__, 'register_journal_comment_routes' ) );
        add_action( 'wp_ajax_ya_match_journey',               array( __CLASS__, 'ajax_match_journey' ) );
        add_action( 'wp_ajax_nopriv_ya_match_journey',        array( __CLASS__, 'ajax_match_journey' ) );
        add_action( 'wp_ajax_ya_submit_journey_lead',        array( __CLASS__, 'ajax_submit_journey_lead' ) );
        add_action( 'wp_ajax_nopriv_ya_submit_journey_lead', array( __CLASS__, 'ajax_submit_journey_lead' ) );
        add_action( 'wp_ajax_ya_submit_traveller_matcher',        array( __CLASS__, 'ajax_submit_traveller_matcher' ) );
        add_action( 'wp_ajax_nopriv_ya_submit_traveller_matcher', array( __CLASS__, 'ajax_submit_traveller_matcher' ) );
    }

    /**
     * Render the same data-driven destination detail template inside a page.
     *
     * Usage: [trek_trip_detail id="123"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_trip_detail( $atts ) {
        $atts = shortcode_atts(
            array( 'id' => 0 ),
            $atts,
            'trek_trip_detail'
        );

        $post_id = absint( $atts['id'] );
        $post    = $post_id ? get_post( $post_id ) : null;

        if ( ! $post || 'trek_destination' !== $post->post_type ) {
            return '';
        }

        if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
            return '';
        }

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/single-trek_destination.php';
        if ( ! file_exists( $template ) ) {
            return '';
        }

        ob_start();
        include $template;
        return ob_get_clean();
    }

    /**
     * Register guest comment and comment-like endpoints.
     */
    public static function register_journal_comment_routes() {
        register_rest_route(
            'trek/v1',
            '/comments',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'create_guest_comment' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'trek/v1',
            '/comments/(?P<id>\d+)/like',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'like_journal_comment' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'trek/v1',
            '/posts/(?P<id>\d+)/like',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'like_journal_post' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Save a guest comment through WordPress's normal comment pipeline.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public static function create_guest_comment( WP_REST_Request $request ) {
        $post_id = absint( $request->get_param( 'post_id' ) );
        $name    = sanitize_text_field( $request->get_param( 'name' ) );
        $email   = sanitize_email( $request->get_param( 'email' ) );
        $content = trim( wp_kses_post( $request->get_param( 'content' ) ) );
        $website  = trim( sanitize_text_field( $request->get_param( 'website' ) ) );

        if ( $website ) {
            return new WP_Error( 'spam_detected', 'Your comment could not be submitted.', array( 'status' => 400 ) );
        }

        if ( ! $post_id || 'post' !== get_post_type( $post_id ) || ! comments_open( $post_id ) ) {
            return new WP_Error( 'comments_closed', 'Comments are closed for this post.', array( 'status' => 400 ) );
        }

        if ( empty( $name ) || ! is_email( $email ) || empty( $content ) ) {
            return new WP_Error( 'invalid_comment', 'Please provide your name, a valid email, and a comment.', array( 'status' => 422 ) );
        }

        $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
        $rate_key   = 'trek_comment_rate_' . md5( $ip_address . '|' . $post_id );
        $rate_count = (int) get_transient( $rate_key );
        if ( $rate_count >= 5 ) {
            return new WP_Error( 'rate_limited', 'Please wait before submitting another comment.', array( 'status' => 429 ) );
        }
        set_transient( $rate_key, $rate_count + 1, HOUR_IN_SECONDS );

        $comment_id = wp_new_comment(
            array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $name,
                'comment_author_email' => $email,
                'comment_content'      => $content,
                'comment_type'         => '',
                'comment_author_IP'    => $ip_address,
                'user_id'              => get_current_user_id(),
            ),
            true
        );

        if ( is_wp_error( $comment_id ) ) {
            return $comment_id;
        }

        $comment = get_comment( $comment_id );

        return new WP_REST_Response(
            array(
                'id'       => $comment_id,
                'status'   => $comment ? $comment->comment_approved : '0',
                'message'  => 'Your comment was submitted and may need approval before it appears.',
                'author'   => $name,
                'date'     => $comment ? get_comment_date( 'F j, Y', $comment ) : current_time( 'F j, Y' ),
                'content'  => wpautop( wp_kses_post( $content ) ),
                'avatar'   => get_avatar_url( $email, array( 'size' => 48 ) ),
            ),
            201
        );
    }

    /**
     * Increment a persisted like count for an approved comment.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public static function like_journal_comment( WP_REST_Request $request ) {
        $comment_id = absint( $request['id'] );
        $comment    = get_comment( $comment_id );

        if ( ! $comment || '1' !== (string) $comment->comment_approved ) {
            return new WP_Error( 'invalid_comment', 'This comment is not available.', array( 'status' => 404 ) );
        }

        $likes = (int) get_comment_meta( $comment_id, '_trek_like_count', true );
        update_comment_meta( $comment_id, '_trek_like_count', $likes + 1 );

        return rest_ensure_response( array( 'likes' => $likes + 1 ) );
    }

    /**
     * Increment a persisted like count for a published WordPress post.
     *
     * @param WP_REST_Request $request REST request.
     * @return WP_REST_Response|WP_Error
     */
    public static function like_journal_post( WP_REST_Request $request ) {
        $post_id = absint( $request['id'] );
        if ( 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
            return new WP_Error( 'invalid_post', 'This post is not available.', array( 'status' => 404 ) );
        }

        $likes = (int) get_post_meta( $post_id, '_trek_like_count', true );
        update_post_meta( $post_id, '_trek_like_count', $likes + 1 );

        return rest_ensure_response( array( 'likes' => $likes + 1 ) );
    }

    /**
     * Extract structured, sanitized card data from a WP_Post object or ID
     *
     * @param int|WP_Post $post_item Post ID or WP_Post
     * @param int         $index     Current loop index
     * @return array
     */
    public static function get_formatted_trip_data( $post_item, $index = 1 ) {
        $post = is_object( $post_item ) ? $post_item : get_post( $post_item );
        if ( ! ( $post instanceof WP_Post ) ) {
            return array();
        }

        $post_id = $post->ID;

        // 1. Title & Heading
        $card_title = get_post_meta( $post_id, '_trek_card_title', true );
        if ( empty( $card_title ) ) {
            $card_title = get_post_meta( $post_id, '_trek_hero_title', true );
        }
        if ( empty( $card_title ) ) {
            $card_title = get_the_title( $post_id );
        }

        // 2. Excerpt / Short Description (Admin Metabox "Card Description" - _trek_card_excerpt)
        $card_excerpt = get_post_meta( $post_id, '_trek_card_excerpt', true );
        if ( empty( $card_excerpt ) ) {
            $card_excerpt = get_post_meta( $post_id, '_trek_hero_subtitle', true );
        }
        if ( empty( $card_excerpt ) && has_excerpt( $post_id ) ) {
            $card_excerpt = get_the_excerpt( $post_id );
        }
        if ( empty( $card_excerpt ) ) {
            $card_excerpt = wp_trim_words( $post->post_content, 22, '...' );
        }
        $card_excerpt = wp_strip_all_tags( $card_excerpt );

        // 3. Featured Image URL
        $card_image = get_post_meta( $post_id, '_trek_card_image_url', true );
        if ( empty( $card_image ) && has_post_thumbnail( $post_id ) ) {
            $card_image = get_the_post_thumbnail_url( $post_id, 'large' );
        }
        if ( empty( $card_image ) ) {
            $card_image = get_post_meta( $post_id, '_trek_hero_bg_img', true );
        }
        if ( empty( $card_image ) ) {
            $card_image = '';
        }

        // 4. Duration & Days Meta
        $hero_days = get_post_meta( $post_id, '_trek_hero_days', true );
        $card_meta = get_post_meta( $post_id, '_trek_card_meta', true );
        if ( empty( $card_meta ) ) {
            $card_meta = ! empty( $hero_days ) ? trim( $hero_days ) . ' Days' : '';
        }

        $duration_val = ! empty( $hero_days ) ? preg_replace( '/[^0-9]/', '', $hero_days ) : '';

        // 5. Taxonomy Terms (Categories & Regions)
        $cat_terms = get_the_terms( $post_id, 'trek_category' );
        $cat_slugs = array();
        $cat_names = array();
        if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
            foreach ( $cat_terms as $ct ) {
                $cat_slugs[] = sanitize_html_class( $ct->slug );
                $cat_names[] = $ct->name;
            }
        }

        $region_terms = get_the_terms( $post_id, 'trek_region' );
        $region_slugs = array();
        $region_names = array();
        if ( ! empty( $region_terms ) && ! is_wp_error( $region_terms ) ) {
            foreach ( $region_terms as $rt ) {
                $region_slugs[] = sanitize_html_class( $rt->slug );
                $region_names[] = $rt->name;
            }
        }

        // 6. Country & Tag formatting
        $country_meta = get_post_meta( $post_id, '_trek_breadcrumb_region', true );
        if ( empty( $country_meta ) && ! empty( $region_names ) ) {
            $country_meta = $region_names[0];
        }
        if ( empty( $country_meta ) ) {
            $country_meta = get_post_meta( $post_id, '_trek_country', true );
        }

        // 6. Primary Category & Tag formatting
        $primary_cat_slug = get_post_meta( $post_id, '_trek_primary_category', true );
        $primary_cat_name = '';
        if ( ! empty( $primary_cat_slug ) ) {
            $term_obj = get_term_by( 'slug', $primary_cat_slug, 'trek_category' );
            if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                $primary_cat_name = $term_obj->name;
            }
        }
        if ( empty( $primary_cat_name ) && ! empty( $cat_names ) ) {
            $primary_cat_name = $cat_names[0];
        }

        $tag_label = get_post_meta( $post_id, '_trek_card_tag', true );
        if ( empty( $tag_label ) ) {
            $tag_label = get_post_meta( $post_id, '_trek_tag_label', true );
        }
        if ( empty( $tag_label ) ) {
            if ( ! empty( $country_meta ) && ! empty( $primary_cat_name ) ) {
                $tag_label = $country_meta . ' · ' . $primary_cat_name;
            } elseif ( ! empty( $primary_cat_name ) ) {
                $tag_label = $primary_cat_name;
            } elseif ( ! empty( $country_meta ) ) {
                $tag_label = $country_meta . ' · Signature Journey';
            } else {
                $tag_label = 'Signature Journey';
            }
        }

        $primary_tag   = ! empty( $primary_cat_name ) ? $primary_cat_name : ( ! empty( $cat_names[0] ) ? $cat_names[0] : '' );
        $secondary_tag = ! empty( $region_names[0] ) ? $region_names[0] : ( ! empty( $cat_names[1] ) ? $cat_names[1] : '' );

        $trip_city = get_post_meta( $post_id, '_trek_city', true );
        $trip_country = get_post_meta( $post_id, '_trek_country', true );
        $trip_address = implode( ', ', array_filter( array( $trip_city, $trip_country, $country_meta ) ) );

        return array(
            'id'             => $post_id,
            'number'         => sprintf( '%02d', $index ),
            'title'          => $card_title,
            'excerpt'        => $card_excerpt,
            'image'          => $card_image,
            'permalink'      => get_permalink( $post_id ),
            'duration_val'   => $duration_val,
            'duration_meta'  => $card_meta,
            'duration_label' => strpos( $card_meta, 'Trip' ) !== false ? $card_meta : $duration_val . '-Day Trip',
            'country'        => $country_meta,
            'country_slug'   => ! empty( $region_slugs[0] ) ? $region_slugs[0] : sanitize_title( $country_meta ),
            'tag'            => $tag_label,
            'tag_primary'    => $primary_tag,
            'tag_secondary'  => $secondary_tag,
            'category_slugs' => ! empty( $cat_slugs ) ? implode( ' ', $cat_slugs ) : 'all',
            'all_slugs'      => implode( ' ', array_unique( array_merge( $cat_slugs, $region_slugs ) ) ),
            'address'        => $trip_address,
            'location'       => $trip_address,
        );
    }



    /**
     * =========================================================================
     * SHORTCODE 1: [ya_country_destinations country="nepal" layout="feature" count="-1"]
     * =========================================================================
     * Accepts a country attribute, queries the database securely for destinations
     * belonging to that specific country, and outputs them matching the editorial card layout.
     * Supports both 'feature' (editorial inspiration carousel) and 'slider' layouts.
     */
    public static function render_country_destinations( $atts ) {
        self::$instance_counter++;
        $uid = 'ya_country_' . self::$instance_counter;

        $atts = shortcode_atts( array(
            'country'    => 'nepal',
            'count'      => -1,
            'orderby'    => 'menu_order title',
            'order'      => 'ASC',
            'title'      => '',
            'subtitle'   => '',
            'kicker'     => '',
            'script'     => '',
            'layout'     => 'feature', // 'feature' (carousel in alltrips.html) or 'slider' (in destination pages)
        ), $atts, 'ya_country_destinations' );

        $country_raw  = sanitize_text_field( $atts['country'] );
        $country_slug = sanitize_title( $country_raw );
        $posts_count  = intval( $atts['count'] );
        if ( $posts_count === 0 ) {
            $posts_count = -1;
        }

        // Build secure WP_Query with sanitized tax_query
        $query_args = array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_count,
            'orderby'        => sanitize_sql_orderby( $atts['orderby'] ) ?: 'menu_order title',
            'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
            'no_found_rows'  => true,
            'tax_query'      => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'trek_region',
                    'field'    => 'slug',
                    'terms'    => array( $country_slug, $country_raw ),
                ),
                array(
                    'taxonomy' => 'trek_category',
                    'field'    => 'slug',
                    'terms'    => array( $country_slug ),
                ),
            ),
        );

        $query = new WP_Query( $query_args );
        $cards = array();
        $idx   = 1;

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $trip = self::get_formatted_trip_data( get_the_ID(), $idx );
                if ( ! empty( $trip ) ) {
                    $cards[] = $trip;
                    $idx++;
                }
            }
            wp_reset_postdata();
        }

        // Fallback to meta query if taxonomy had no matches
        if ( empty( $cards ) ) {
            $meta_query_args = array(
                'post_type'      => 'trek_destination',
                'post_status'    => 'publish',
                'posts_per_page' => $posts_count,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_trek_breadcrumb_region',
                        'value'   => $country_raw,
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => '_trek_card_tag',
                        'value'   => $country_raw,
                        'compare' => 'LIKE',
                    ),
                ),
            );
            $meta_query = new WP_Query( $meta_query_args );
            if ( $meta_query->have_posts() ) {
                while ( $meta_query->have_posts() ) {
                    $meta_query->the_post();
                    $trip = self::get_formatted_trip_data( get_the_ID(), $idx );
                    if ( ! empty( $trip ) ) {
                        $cards[] = $trip;
                        $idx++;
                    }
                }
                wp_reset_postdata();
            }
        }

        if ( empty( $cards ) ) {
            return '<div class="ya-empty" style="text-align:center; padding:48px 20px; color:#666; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        ob_start();

        if ( 'cards_only' === $atts['layout'] || 'cards' === $atts['layout'] ) :
            foreach ( $cards as $c ) : ?>
                <a class="ya-trip-card" href="<?php echo esc_url( $c['permalink'] ); ?>" style="--trip-image:url('<?php echo esc_url( $c['image'] ); ?>')">
                    <span class="ya-trip-duration"><?php echo esc_html( $c['duration_label'] ); ?></span>
                    <div class="ya-trip-content">
                        <small><?php echo esc_html( $c['tag'] ); ?></small>
                        <h3><?php echo esc_html( $c['title'] ); ?></h3>
                        <p><?php echo esc_html( $c['excerpt'] ); ?></p>
                        <div class="ya-trip-link">
                            <span><?php esc_html_e( 'View Journey', 'trek-content-manager' ); ?></span>
                            <span>↗</span>
                        </div>
                    </div>
                </a>
            <?php endforeach;
        elseif ( 'feature' === $atts['layout'] || 'carousel' === $atts['layout'] ) :
        ?>
            <!-- FEATURE CAROUSEL (DYNAMIC) -->
            <div class="ya-feature-carousel" id="<?php echo esc_attr( $uid ); ?>" tabindex="0" aria-label="<?php echo esc_attr( $country_raw ); ?> journeys carousel">
                <div class="ya-feature-viewport">
                    <div class="ya-feature-track">
                        <?php foreach ( $cards as $c ) : ?>
                            <a class="ya-feature-card" href="<?php echo esc_url( $c['permalink'] ); ?>" style="--ya-feature-image:url('<?php echo esc_url( $c['image'] ); ?>')">
                                <span class="ya-feature-duration">
                                    <strong><?php echo esc_html( $c['duration_val'] ); ?></strong>
                                    <small><?php esc_html_e( 'Days', 'trek-content-manager' ); ?></small>
                                </span>
                                <div class="ya-feature-content">
                                    <span class="ya-feature-label"><?php echo esc_html( $c['tag'] ); ?></span>
                                    <h3><?php echo esc_html( $c['title'] ); ?></h3>
                                    <p><?php echo esc_html( $c['excerpt'] ); ?></p>
                                    <span class="ya-feature-open" aria-hidden="true">↗</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="ya-feature-controls">
                    <button class="ya-feature-prev" type="button" aria-label="<?php esc_attr_e( 'Previous journey', 'trek-content-manager' ); ?>">
                        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6" /></svg>
                    </button>
                    <button class="ya-feature-next" type="button" aria-label="<?php esc_attr_e( 'Next journey', 'trek-content-manager' ); ?>">
                        <svg viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" /></svg>
                    </button>
                </div>
            </div>

            <script>
                (function() {
                    const carousel = document.getElementById('<?php echo esc_js( $uid ); ?>');
                    if (!carousel || carousel.dataset.ready === 'true') return;
                    carousel.dataset.ready = 'true';

                    const viewport = carousel.querySelector('.ya-feature-viewport');
                    const track = carousel.querySelector('.ya-feature-track');
                    const previous = carousel.querySelector('.ya-feature-prev');
                    const next = carousel.querySelector('.ya-feature-next');
                    if (!track || !viewport) return;

                    const originals = Array.from(track.querySelectorAll('.ya-feature-card'));
                    const originalCount = originals.length;
                    const cloneCount = 2;
                    if (originalCount < 3) return;

                    const before = originals.slice(-cloneCount).map(card => {
                        const clone = card.cloneNode(true);
                        clone.classList.add('ya-feature-clone');
                        clone.setAttribute('aria-hidden', 'true');
                        return clone;
                    });
                    const after = originals.slice(0, cloneCount).map(card => {
                        const clone = card.cloneNode(true);
                        clone.classList.add('ya-feature-clone');
                        clone.setAttribute('aria-hidden', 'true');
                        return clone;
                    });

                    track.prepend(...before);
                    track.append(...after);

                    let cards = Array.from(track.querySelectorAll('.ya-feature-card'));
                    let current = cloneCount;
                    let moving = false;

                    const visibleCount = () => {
                        if (window.innerWidth <= 480) return 1.12;
                        if (window.innerWidth <= 700) return 1.22;
                        if (window.innerWidth <= 900) return 2;
                        if (window.innerWidth <= 1180) return 2.35;
                        return 3;
                    };

                    const sizeCards = () => {
                        const style = window.getComputedStyle(track);
                        const gap = parseFloat(style.gap) || 18;
                        const count = visibleCount();
                        const width = (viewport.clientWidth - gap * (count - 1)) / count;
                        carousel.style.setProperty('--ya-feature-width', `${Math.max(width, 250)}px`);
                    };

                    const classes = () => {
                        cards.forEach((card, index) => {
                            card.classList.toggle('ya-feature-active', index === current);
                            card.classList.toggle('ya-feature-near', index === current - 1 || index === current + 1);
                            if (index === current) card.setAttribute('aria-current', 'true');
                            else card.removeAttribute('aria-current');
                        });
                    };

                    const centre = (animate = true) => {
                        const active = cards[current];
                        if (!active) return;
                        track.classList.toggle('ya-feature-no-transition', !animate);
                        const offset = viewport.clientWidth / 2 - (active.offsetLeft + active.offsetWidth / 2);
                        track.style.transform = `translate3d(${offset}px,0,0)`;
                        classes();
                        if (!animate) requestAnimationFrame(() => requestAnimationFrame(() => track.classList.remove('ya-feature-no-transition')));
                    };

                    const move = direction => {
                        if (moving) return;
                        moving = true;
                        current += direction;
                        centre(true);
                    };

                    if (previous) previous.addEventListener('click', () => move(-1));
                    if (next) next.addEventListener('click', () => move(1));

                    cards.forEach((card, index) => card.addEventListener('click', event => {
                        if (index === current) return;
                        event.preventDefault();
                        if (moving) return;
                        moving = true;
                        current = index;
                        centre(true);
                    }));

                    track.addEventListener('transitionend', event => {
                        if (event.propertyName !== 'transform') return;
                        if (current === cloneCount - 1) {
                            current = cloneCount + originalCount - 1;
                            centre(false);
                        } else if (current === cloneCount + originalCount) {
                            current = cloneCount;
                            centre(false);
                        }
                        moving = false;
                    });

                    carousel.addEventListener('keydown', event => {
                        if (event.key === 'ArrowLeft') { event.preventDefault(); move(-1); }
                        if (event.key === 'ArrowRight') { event.preventDefault(); move(1); }
                    });

                    let startX = 0;
                    viewport.addEventListener('touchstart', event => { startX = event.changedTouches[0].clientX; }, { passive: true });
                    viewport.addEventListener('touchend', event => {
                        const distance = startX - event.changedTouches[0].clientX;
                        if (Math.abs(distance) > 45) move(distance > 0 ? 1 : -1);
                    }, { passive: true });

                    let timer;
                    window.addEventListener('resize', () => {
                        clearTimeout(timer);
                        timer = setTimeout(() => { sizeCards(); centre(false); }, 120);
                    });

                    sizeCards();
                    requestAnimationFrame(() => centre(false));
                })();
            </script>
        <?php
        else :
        ?>
            <!-- SLIDER SHELL (DYNAMIC) -->
            <div class="ya-country-destinations-wrap ya-country-<?php echo esc_attr( $country_slug ); ?>" id="<?php echo esc_attr( $uid ); ?>">
                <div class="ya-country-inner">
                    <div class="ya-slider-shell">
                        <button class="ya-slider-btn ya-slider-prev" type="button" aria-label="<?php esc_attr_e( 'Previous journey', 'trek-content-manager' ); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>

                        <div class="ya-slider" id="<?php echo esc_attr( $uid ); ?>_track">
                            <?php foreach ( $cards as $c ) : ?>
                                <a class="ya-trip-card" href="<?php echo esc_url( $c['permalink'] ); ?>" style="--trip-image:url('<?php echo esc_url( $c['image'] ); ?>')">
                                    <span class="ya-trip-duration"><?php echo esc_html( $c['duration_label'] ); ?></span>
                                    <div class="ya-trip-content">
                                        <small><?php echo esc_html( $c['tag'] ); ?></small>
                                        <h3><?php echo esc_html( $c['title'] ); ?></h3>
                                        <p><?php echo esc_html( $c['excerpt'] ); ?></p>
                                        <div class="ya-trip-link">
                                            <span><?php esc_html_e( 'View Journey', 'trek-content-manager' ); ?></span>
                                            <span>↗</span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <button class="ya-slider-btn ya-slider-next" type="button" aria-label="<?php esc_attr_e( 'Next journey', 'trek-content-manager' ); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>

                <script>
                    (function() {
                        const wrap = document.getElementById('<?php echo esc_js( $uid ); ?>');
                        if (!wrap) return;
                        const track = wrap.querySelector('#<?php echo esc_js( $uid ); ?>_track');
                        const btnPrev = wrap.querySelector('.ya-slider-prev');
                        const btnNext = wrap.querySelector('.ya-slider-next');
                        const getScrollAmount = () => (track ? Math.max(track.clientWidth * 0.75, 300) : 320);

                        if (btnPrev && track) {
                            btnPrev.addEventListener('click', function() {
                                track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
                            });
                        }
                        if (btnNext && track) {
                            btnNext.addEventListener('click', function() {
                                track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
                            });
                        }
                    })();
                </script>
            </div>
        <?php
        endif;

        return ob_get_clean();
    }

    /**
     * Render the Journal page's featured post and post grid.
     * Usage: [ya_journal_posts count="7"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_journal_posts( $atts = array() ) {
        $atts = shortcode_atts(
            array(
                'count' => 7,
            ),
            $atts,
            'ya_journal_posts'
        );

        $count = max( 1, min( 30, absint( $atts['count'] ) ) );
        $query = new WP_Query(
            array(
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => $count,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'update_post_meta_cache' => true,
                'update_post_term_cache' => true,
            )
        );

        if ( ! $query->have_posts() ) {
            return '<div class="ya-empty" style="text-align:center; padding:48px 20px; color:#666; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        $posts = $query->posts;

        $get_data = static function ( $post, $index ) {
            $categories = get_the_category( $post->ID );
            $category   = ! empty( $categories ) ? $categories[0]->name : 'Journal';
            $excerpt    = has_excerpt( $post->ID )
                ? get_the_excerpt( $post->ID )
                : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '...' );

            return array(
                'id'       => $post->ID,
                'number'   => sprintf( '%02d', $index ),
                'title'    => get_the_title( $post->ID ),
                'excerpt'  => wp_strip_all_tags( $excerpt ),
                'category' => $category,
                'date'     => get_the_date( 'F j, Y', $post->ID ),
                'url'      => get_permalink( $post->ID ),
                'image'    => get_the_post_thumbnail_url( $post->ID, 'large' ),
            );
        };

        $featured = $get_data( $posts[0], 1 );

        ob_start();
        ?>
        <article class="ya-featured-post ya-reveal">
            <a class="ya-featured-image" href="<?php echo esc_url( $featured['url'] ); ?>" aria-label="<?php echo esc_attr( 'Read ' . $featured['title'] ); ?>">
                <?php if ( $featured['image'] ) : ?>
                    <img src="<?php echo esc_url( $featured['image'] ); ?>" alt="<?php echo esc_attr( $featured['title'] ); ?>" loading="lazy">
                <?php endif; ?>
                <span class="ya-featured-badge">Featured Story</span>
            </a>
            <div class="ya-featured-copy">
                <div class="ya-post-meta"><span><?php echo esc_html( $featured['date'] ); ?></span><span><?php echo esc_html( $featured['category'] ); ?></span></div>
                <h3><?php echo esc_html( $featured['title'] ); ?></h3>
                <p><?php echo esc_html( $featured['excerpt'] ); ?></p>
                <a class="ya-read-more" href="<?php echo esc_url( $featured['url'] ); ?>">Read the Story</a>
            </div>
        </article>

        <?php if ( count( $posts ) > 1 ) : ?>
            <div class="ya-post-grid">
                <?php foreach ( array_slice( $posts, 1 ) as $index => $post ) :
                    $item = $get_data( $post, $index + 2 );
                    ?>
                    <article class="ya-post-card ya-reveal">
                        <a class="ya-post-image" href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( 'Read ' . $item['title'] ); ?>">
                            <?php if ( $item['image'] ) : ?>
                                <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
                            <?php endif; ?>
                            <span class="ya-post-number"><?php echo esc_html( $item['number'] ); ?></span>
                        </a>
                        <div class="ya-post-body">
                            <div class="ya-post-meta"><span><?php echo esc_html( $item['date'] ); ?></span><span><?php echo esc_html( $item['category'] ); ?></span></div>
                            <h3><?php echo esc_html( $item['title'] ); ?></h3>
                            <p><?php echo esc_html( $item['excerpt'] ); ?></p>
                            <a class="ya-read-more" href="<?php echo esc_url( $item['url'] ); ?>">Read More</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render dynamic inspiration cards for the homepage.
     * Usage: [ya_home_notes count="6" view_all_url="/journal/"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_home_notes( $atts = array() ) {
        self::$instance_counter++;
        $uid = 'ya_home_notes_' . self::$instance_counter;
        $atts = shortcode_atts(
            array(
                'count'          => 4,
                'view_all_url'   => '',
                'view_all_label' => 'View All Inspiration',
            ),
            $atts,
            'ya_home_notes'
        );
        $count = max( 1, min( 30, absint( $atts['count'] ) ) );

        if ( empty( $atts['view_all_url'] ) ) {
            $blog_page_id = get_option( 'page_for_posts' );
            if ( $blog_page_id ) {
                $atts['view_all_url'] = get_permalink( $blog_page_id );
            } else {
                $journal_page = get_page_by_path( 'journal' ) ?: get_page_by_path( 'blog' ) ?: get_page_by_path( 'articles' );
                $atts['view_all_url'] = $journal_page ? get_permalink( $journal_page->ID ) : home_url( '/journal/' );
            }
        }

        $query = new WP_Query(
            array(
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => $count,
                'ignore_sticky_posts'    => true,
                'no_found_rows'          => true,
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'update_post_meta_cache' => true,
                'update_post_term_cache' => true,
            )
        );

        if ( ! $query->have_posts() ) {
            return '<div class="ya-empty" style="text-align:center; padding:48px 20px; color:#666; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $uid ); ?>" class="ya-inspiration-cards">
            <style>
                #<?php echo esc_attr( $uid ); ?>,
                #<?php echo esc_attr( $uid ); ?> * { box-sizing: border-box; }
                #<?php echo esc_attr( $uid ); ?> {
                    color: #333;
                    font-family: var(--te-font-body, "Open Sans", Arial, sans-serif);
                    width: 100%;
                }

                /* 4-Card Desktop Grid */
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 28px;
                    width: 100%;
                }

                /* Sharp, non-curved modern card styling */
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card {
                    display: flex;
                    min-width: 0;
                    flex-direction: column;
                    padding: 0;
                    border: none;
                    border-radius: 0 !important;
                    background: transparent;
                    transition: transform 0.3s ease;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-image {
                    display: block;
                    aspect-ratio: 1.48 / 1;
                    overflow: hidden;
                    background: #e9e5de;
                    border-radius: 0 !important;
                    position: relative;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-image img {
                    display: block;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 0 !important;
                    transition: transform 0.55s cubic-bezier(0.22, 0.61, 0.36, 1);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card:hover .ya-inspiration-image img {
                    transform: scale(1.06);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-body {
                    display: flex;
                    flex: 1;
                    flex-direction: column;
                    padding: 18px 0 0;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-meta {
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 0;
                    margin: 0 0 12px;
                    color: #8c8c8c;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.8px;
                    line-height: 1.35;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-meta span + span::before {
                    padding: 0 6px;
                    color: #617654;
                    content: "•";
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-title {
                    margin: 0;
                    color: #111111;
                    font-family: var(--te-font-display, "Newsreader", Georgia, serif);
                    font-size: clamp(18px, 1.35vw, 22px);
                    font-weight: 500;
                    line-height: 1.25;
                    letter-spacing: -0.02em;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-title a {
                    color: inherit;
                    text-decoration: none;
                    transition: color 0.2s ease;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-title a:hover {
                    color: #d1ad54;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-excerpt {
                    display: -webkit-box;
                    margin: 10px 0 0;
                    overflow: hidden;
                    color: #222222;
                    font-size: 13px;
                    line-height: 1.55;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 3;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-top: auto;
                    padding-top: 18px;
                    color: #d1ad54;
                    font-size: 13px;
                    font-weight: 600;
                    line-height: 1.2;
                    text-decoration: none;
                    transition: color 0.2s ease, transform 0.2s ease;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link:hover {
                    color: #617654;
                    transform: translateX(3px);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link span {
                    transition: transform 0.2s ease;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link:hover span {
                    transform: translateX(2px);
                }

                /* Center Wrap for View All Button */
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-cta-wrap {
                    text-align: center;
                    margin-top: 50px;
                }

                /* View All Button matching Hero Section CTA style (solid color, no gradient) */
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 16px;
                    min-height: 54px;
                    padding: 6px 8px 6px 30px;
                    border-radius: 0 !important; /* Sharp / non-curved border */
                    background: #d1ad54 !important;
                    color: #ffffff;
                    border: 1px solid #d1ad54;
                    box-shadow: 0 10px 28px rgba(209, 173, 84, 0.25), 0 2px 6px rgba(0, 0, 0, 0.12);
                    font-family: var(--te-font-body, "Open Sans", Arial, sans-serif);
                    font-size: 11.5px;
                    font-weight: 700;
                    line-height: 1;
                    letter-spacing: 2.2px;
                    text-decoration: none;
                    text-transform: uppercase;
                    overflow: hidden;
                    cursor: pointer;
                    transition: transform 0.4s cubic-bezier(.22, .61, .36, 1), background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s cubic-bezier(.22, .61, .36, 1);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn:hover {
                    background: #617654 !important;
                    border-color: #617654 !important;
                    box-shadow: 0 16px 36px rgba(97, 118, 84, 0.35), 0 4px 10px rgba(0, 0, 0, 0.2);
                    transform: translateY(-3px);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn__text {
                    position: relative;
                    z-index: 1;
                    color: #ffffff;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn__icon {
                    position: relative;
                    z-index: 1;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    background: transparent;
                    backdrop-filter: none;
                    -webkit-backdrop-filter: none;
                    color: #ffffff;
                    transition: transform 0.4s cubic-bezier(.22, .61, .36, 1), background-color 0.35s ease;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn__icon svg {
                    width: 16px;
                    height: 16px;
                    stroke: currentColor;
                    transition: transform 0.35s cubic-bezier(.22, .61, .36, 1);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn:hover .ya-inspiration-btn__icon {
                    background: transparent;
                    transform: scale(1.06);
                }

                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-btn:hover .ya-inspiration-btn__icon svg {
                    transform: translateX(3px);
                }

                /* Mobile & Tablet: Horizontal Scrollable Strip (Snap Scroll) */
                @media (max-width: 1023px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-inspiration-grid {
                        display: flex;
                        overflow-x: auto;
                        scroll-snap-type: x mandatory;
                        -webkit-overflow-scrolling: touch;
                        gap: 20px;
                        padding: 0 4px 20px;
                        margin: 0 -16px;
                        padding-left: 16px;
                        padding-right: 16px;
                        scrollbar-width: none;
                    }

                    #<?php echo esc_attr( $uid ); ?> .ya-inspiration-grid::-webkit-scrollbar {
                        display: none;
                    }

                    #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card {
                        flex: 0 0 280px;
                        max-width: 80vw;
                        scroll-snap-align: start;
                    }

                    #<?php echo esc_attr( $uid ); ?> .ya-inspiration-cta-wrap {
                        margin-top: 36px;
                    }
                }
            </style>
            <div class="ya-inspiration-grid">
                <?php foreach ( $query->posts as $post ) :
                    $categories = get_the_category( $post->ID );
                    $category   = ! empty( $categories ) ? $categories[0]->name : 'Journal';
                    $content    = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : $post->post_content;
                    $excerpt    = wp_trim_words( wp_strip_all_tags( $content ), 24, '...' );
                    $image      = get_the_post_thumbnail_url( $post->ID, 'large' );
                    ?>
                    <article class="ya-inspiration-card ya-reveal">
                        <a class="ya-inspiration-image" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read %s', 'trek-content-manager' ), get_the_title( $post->ID ) ) ); ?>">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>" loading="lazy">
                            <?php endif; ?>
                        </a>
                        <div class="ya-inspiration-body">
                            <div class="ya-inspiration-meta">
                                <span><?php echo esc_html( get_the_date( 'M j, Y', $post->ID ) ); ?></span>
                                <span><?php echo esc_html( $category ); ?></span>
                            </div>
                            <h3 class="ya-inspiration-title">
                                <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                                    <?php echo esc_html( get_the_title( $post->ID ) ); ?>
                                </a>
                            </h3>
                            <p class="ya-inspiration-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                            <a class="ya-inspiration-link" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                                <?php esc_html_e( 'Read More', 'trek-content-manager' ); ?>
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="ya-inspiration-cta-wrap">
                <a class="ya-inspiration-btn" href="<?php echo esc_url( $atts['view_all_url'] ); ?>">
                    <span class="ya-inspiration-btn__text"><?php echo esc_html( $atts['view_all_label'] ); ?></span>
                    <span class="ya-inspiration-btn__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M13 6l6 6-6 6"></path>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * =========================================================================
     * SHORTCODE 2: [ya_all_trips_filterable]
     * =========================================================================
     * Generates dynamic trips directory matching alltrips.html card design & animations.
     * Features:
     * - Dynamic category tabs fetched directly from database (trek_category taxonomy)
     * - Region/Country filter on the left side of search input (trek_region taxonomy, defaults to Nepal)
     * - Live multi-field search (title, excerpt, tags, region, country)
     * - Real-time counter and empty state
     */
    public static function render_all_trips_filterable( $atts ) {
        self::$instance_counter++;
        $uid = 'ya_filterable_' . self::$instance_counter;

        $atts = shortcode_atts( array(
            'eyebrow'        => 'Choose Your Journey',
            'title'          => 'Himalayan Adventures',
            'count'          => -1,
            'default_region' => 'nepal',
            'region'         => '',
            'category'       => '',
            'show_search'    => 'true',
            'show_filter'    => 'true',
            'show_region'    => 'true',
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ), $atts, 'ya_all_trips_filterable' );

        $posts_count = intval( $atts['count'] );
        if ( $posts_count === 0 ) {
            $posts_count = -1;
        }

        // Query all published trips
        $query_args = array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_count,
            'orderby'        => sanitize_sql_orderby( $atts['orderby'] ) ?: 'menu_order title',
            'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
            'no_found_rows'  => true,
        );

        $tax_query = array();
        if ( ! empty( $atts['region'] ) && 'all' !== $atts['region'] ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_region',
                'field'    => 'slug',
                'terms'    => sanitize_title( $atts['region'] ),
            );
        }
        if ( ! empty( $atts['category'] ) && 'all' !== $atts['category'] ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_category',
                'field'    => 'slug',
                'terms'    => sanitize_title( $atts['category'] ),
            );
        }
        if ( ! empty( $tax_query ) ) {
            if ( count( $tax_query ) > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            $query_args['tax_query'] = $tax_query;
        }

        $trips_query = new WP_Query( $query_args );
        $trips = array();
        $idx   = 1;

        if ( $trips_query->have_posts() ) {
            while ( $trips_query->have_posts() ) {
                $trips_query->the_post();
                $trip = self::get_formatted_trip_data( get_the_ID(), $idx );
                if ( ! empty( $trip ) ) {
                    $trips[] = $trip;
                    $idx++;
                }
            }
            wp_reset_postdata();
        }

        if ( empty( $trips ) ) {
            return '<div class="ya-empty" style="text-align:center; padding:48px 20px; color:#666; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        // Fetch dynamic categories from the database — only show categories that
        // have at least one published trip so filters are not shown empty.
        $categories = get_terms( array(
            'taxonomy'   => 'trek_category',
            'hide_empty' => true,
        ) );

        ob_start();
        ?>
        <section class="ya-trips-listing" id="<?php echo esc_attr( $uid ); ?>">
            <div class="ya-inner">
                <div class="ya-listing-head ya-reveal">
                    <div>
                        <span class="ya-script"><?php echo esc_html( $atts['eyebrow'] ); ?></span>
                        <h2><?php echo esc_html( $atts['title'] ); ?></h2>
                    </div>
                    <p class="ya-result-count" aria-live="polite">
                        <span id="<?php echo esc_attr( $uid ); ?>_count"><?php echo count( $trips ); ?></span> <?php esc_html_e( 'journeys displayed', 'trek-content-manager' ); ?>
                    </p>
                </div>

                <div class="ya-toolbar ya-reveal">
                    <?php if ( 'true' === $atts['show_filter'] ) : ?>
                        <div class="ya-filters" role="group" aria-label="<?php esc_attr_e( 'Filter journeys by category', 'trek-content-manager' ); ?>">
                            <button class="ya-filter is-active" type="button" data-filter="all"><?php esc_html_e( 'All Trips', 'trek-content-manager' ); ?></button>
                            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                                <?php foreach ( $categories as $cat ) : ?>
                                    <button class="ya-filter" type="button" data-filter="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></button>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <button class="ya-filter" type="button" data-filter="trekking"><?php esc_html_e( 'Trekking', 'trek-content-manager' ); ?></button>
                                <button class="ya-filter" type="button" data-filter="culture"><?php esc_html_e( 'Culture', 'trek-content-manager' ); ?></button>
                                <button class="ya-filter" type="button" data-filter="wildlife"><?php esc_html_e( 'Wildlife', 'trek-content-manager' ); ?></button>
                                <button class="ya-filter" type="button" data-filter="luxury"><?php esc_html_e( 'Luxury', 'trek-content-manager' ); ?></button>
                                <button class="ya-filter" type="button" data-filter="soft"><?php esc_html_e( 'Soft Adventure', 'trek-content-manager' ); ?></button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( 'true' === $atts['show_search'] ) : ?>
                        <label class="ya-search">
                            <span style="position:absolute;clip:rect(0 0 0 0);clip-path:inset(50%);width:1px;height:1px;overflow:hidden"><?php esc_html_e( 'Search trips', 'trek-content-manager' ); ?></span>
                            <input id="<?php echo esc_attr( $uid ); ?>_search" type="search" placeholder="<?php esc_attr_e( 'Search a journey...', 'trek-content-manager' ); ?>" autocomplete="off">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-4-4" />
                            </svg>
                        </label>
                    <?php endif; ?>
                </div>

                <div class="ya-trip-grid" id="<?php echo esc_attr( $uid ); ?>_grid">
                    <?php foreach ( $trips as $t ) : ?>
                        <article class="ya-trip-card ya-reveal"
                                 data-category="<?php echo esc_attr( $t['category_slugs'] ); ?>"
                                 data-region="<?php echo esc_attr( $t['country_slug'] ); ?>"
                                 data-all="<?php echo esc_attr( $t['all_slugs'] ); ?>"
                                 data-title="<?php echo esc_attr( strtolower( $t['title'] ) ); ?>"
                                 data-excerpt="<?php echo esc_attr( strtolower( $t['excerpt'] ) ); ?>"
                                 data-country="<?php echo esc_attr( strtolower( $t['country'] ) ); ?>">
                            <a class="ya-trip-link" href="<?php echo esc_url( $t['permalink'] ); ?>">
                                <div class="ya-trip-image">
                                    <img src="<?php echo esc_url( $t['image'] ); ?>" alt="<?php echo esc_attr( $t['title'] ); ?>" loading="lazy">
                                    <span class="ya-trip-number"><?php echo esc_html( $t['number'] ); ?></span>
                                    <div class="ya-trip-tags">
                                        <span><?php echo esc_html( $t['tag_primary'] ); ?></span>
                                        <?php if ( ! empty( $t['tag_secondary'] ) ) : ?>
                                            <span><?php echo esc_html( $t['tag_secondary'] ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ya-trip-content">
                                    <div class="ya-trip-meta">
                                        <span><?php echo esc_html( $t['country'] ); ?></span>
                                        <span><?php echo esc_html( $t['duration_meta'] ); ?></span>
                                    </div>
                                    <h3><?php echo esc_html( $t['title'] ); ?></h3>
                                    <p><?php echo esc_html( $t['excerpt'] ); ?></p>
                                    <div class="ya-trip-action">
                                        <span><?php esc_html_e( 'Explore trip', 'trek-content-manager' ); ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="ya-empty" id="<?php echo esc_attr( $uid ); ?>_empty">
                    <h3><?php esc_html_e( 'No Journeys Found', 'trek-content-manager' ); ?></h3>
                    <p><?php esc_html_e( 'We could not find any journeys matching your current filters and search criteria. Try selecting "All Trips" or searching for a different keyword.', 'trek-content-manager' ); ?></p>
                </div>
            </div>

            <script>
                (function() {
                    const root = document.getElementById('<?php echo esc_js( $uid ); ?>');
                    if (!root) return;

                    const buttons = Array.from(root.querySelectorAll('.ya-filter'));
                    const cards = Array.from(root.querySelectorAll('.ya-trip-card'));
                    const search = root.querySelector('#<?php echo esc_js( $uid ); ?>_search');
                    const count = root.querySelector('#<?php echo esc_js( $uid ); ?>_count');
                    const empty = root.querySelector('#<?php echo esc_js( $uid ); ?>_empty');

                    let activeFilter = 'all';

                    function applyFilters() {
                        const query = (search ? search.value : '').trim().toLowerCase();
                        let visible = 0;

                        cards.forEach(card => {
                            const catAttr = (card.getAttribute('data-category') || '').toLowerCase();
                            const allAttr = (card.getAttribute('data-all') || '').toLowerCase();
                            const categories = (catAttr + ' ' + allAttr).split(/\s+/).filter(Boolean);
                            const title = (card.getAttribute('data-title') || '').toLowerCase();
                            const excerpt = (card.getAttribute('data-excerpt') || '').toLowerCase();
                            const cardCountry = (card.getAttribute('data-country') || '').toLowerCase();

                            const matchesCategory = (activeFilter === 'all') || categories.includes(activeFilter.toLowerCase());
                            const matchesSearch = !query || title.includes(query) || excerpt.includes(query) || cardCountry.includes(query) || categories.some(c => c.includes(query));

                            const show = matchesCategory && matchesSearch;
                            card.classList.toggle('is-hidden', !show);
                            if (show) visible++;
                        });

                        if (count) count.textContent = visible;
                        if (empty) empty.classList.toggle('is-visible', visible === 0);
                    }

                    buttons.forEach(button => {
                        button.addEventListener('click', function() {
                            activeFilter = this.getAttribute('data-filter') || 'all';
                            buttons.forEach(b => b.classList.toggle('is-active', b === this));
                            applyFilters();
                        });
                    });

                    if (search) {
                        search.addEventListener('input', applyFilters);
                    }

                    // Apply filters on initial load
                    applyFilters();
                })();
            </script>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * =========================================================================
     * SHORTCODE 3: [ya_category_treks category="himalayan" layout="grid" count="6"]
     * =========================================================================
     * Accepts a category attribute (e.g. 'himalayan', 'comfort', 'heli', 'trekking', 'luxury')
     * and returns related destination or trek cards dynamically.
     */
    public static function render_category_treks( $atts ) {
        self::$instance_counter++;
        $uid = 'ya_cat_' . self::$instance_counter;

        $atts = shortcode_atts( array(
            'category'   => 'trekking',
            'layout'     => 'grid', // 'grid' or 'slider'
            'count'      => 6,
            'columns'    => 3,
            'region'     => '',
            'title'      => '',
            'subtitle'   => '',
            'orderby'    => 'menu_order title',
            'order'      => 'ASC',
        ), $atts, 'ya_category_treks' );

        $category_slug = sanitize_title( $atts['category'] );
        $posts_count   = intval( $atts['count'] );
        if ( $posts_count === 0 ) {
            $posts_count = 6;
        }

        // Build query
        $query_args = array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => $posts_count,
            'orderby'        => sanitize_sql_orderby( $atts['orderby'] ) ?: 'menu_order title',
            'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
            'no_found_rows'  => true,
        );

        $tax_query = array();
        if ( ! empty( $category_slug ) && 'all' !== $category_slug ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_category',
                'field'    => 'slug',
                'terms'    => $category_slug,
            );
        }

        if ( ! empty( $atts['region'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_region',
                'field'    => 'slug',
                'terms'    => sanitize_title( $atts['region'] ),
            );
        }

        if ( ! empty( $tax_query ) ) {
            if ( count( $tax_query ) > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            $query_args['tax_query'] = $tax_query;
        }

        $query = new WP_Query( $query_args );
        $cards = array();
        $idx   = 1;

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $trip = self::get_formatted_trip_data( get_the_ID(), $idx );
                if ( ! empty( $trip ) ) {
                    $cards[] = $trip;
                    $idx++;
                }
            }
            wp_reset_postdata();
        }

        if ( empty( $cards ) ) {
            return '<div class="ya-empty" style="text-align:center; padding:48px 20px; color:#666; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        $cols = max( 1, min( 4, intval( $atts['columns'] ) ) );
        $layout_type = in_array( $atts['layout'], array( 'slider', 'grid' ), true ) ? $atts['layout'] : 'grid';

        ob_start();
        ?>
        <div class="ya-category-treks-wrap ya-layout-<?php echo esc_attr( $layout_type ); ?>" id="<?php echo esc_attr( $uid ); ?>">
            <style>
                #<?php echo esc_attr( $uid ); ?> {
                    --c-green: #022f2e;
                    --c-gold: #c7a767;
                    width: 100%;
                    margin: 30px 0;
                    font-family: "Plus Jakarta Sans", "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
                    box-sizing: border-box;
                }
                #<?php echo esc_attr( $uid ); ?> * {
                    box-sizing: border-box;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-head {
                    margin-bottom: 35px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-head h2 {
                    margin: 0 0 10px;
                    font-family: "Cormorant Garamond", Georgia, serif;
                    font-size: clamp(34px, 4.5vw, 56px);
                    color: var(--c-green);
                    line-height: 1;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-head p {
                    margin: 0;
                    color: #556c6a;
                    font-size: 15px;
                    max-width: 600px;
                    line-height: 1.6;
                }
                /* Grid Layout */
                #<?php echo esc_attr( $uid ); ?> .ya-cat-grid {
                    display: grid;
                    grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, minmax(0, 1fr));
                    gap: 26px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat {
                    position: relative;
                    min-height: 520px;
                    overflow: hidden;
                    border-radius: 3px 3px 80px 3px;
                    isolation: isolate;
                    text-decoration: none;
                    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
                    transition: transform 0.4s ease, box-shadow 0.4s ease;
                    display: block;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 30px 75px rgba(2, 47, 46, 0.32);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat:before {
                    content: "";
                    position: absolute;
                    inset: 0;
                    z-index: 1;
                    background: linear-gradient(180deg, rgba(2,47,46,0.05) 15%, rgba(2,47,46,0.3) 45%, rgba(2,47,46,0.95) 100%);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat:after {
                    content: "";
                    position: absolute;
                    inset: 0;
                    z-index: 0;
                    background: var(--cat-img, #022f2e) center/cover no-repeat;
                    transition: transform 0.8s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat:hover:after {
                    transform: scale(1.07);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-number {
                    position: absolute;
                    top: 22px;
                    right: 22px;
                    z-index: 2;
                    font-family: "Cormorant Garamond", Georgia, serif;
                    font-size: 26px;
                    color: #fff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-meta {
                    position: absolute;
                    top: 22px;
                    left: 22px;
                    z-index: 2;
                    padding: 8px 14px;
                    border-radius: 999px;
                    background: rgba(255, 255, 255, 0.94);
                    color: #111;
                    font: 700 10px/1 "Plus Jakarta Sans", sans-serif;
                    letter-spacing: 1.5px;
                    text-transform: uppercase;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-body {
                    position: absolute;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 2;
                    padding: 28px;
                    color: #fff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-body small {
                    color: var(--c-gold);
                    font: 700 10px/1 "Plus Jakarta Sans", sans-serif;
                    letter-spacing: 1.8px;
                    text-transform: uppercase;
                    display: block;
                    margin-bottom: 8px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-body h3 {
                    margin: 0 0 10px;
                    font-family: "Cormorant Garamond", Georgia, serif;
                    font-size: 32px;
                    line-height: 1.05;
                    font-weight: 600;
                    color: #fff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-body p {
                    margin: 0;
                    color: rgba(255, 255, 255, 0.78);
                    font-size: 13.5px;
                    line-height: 1.65;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-link {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 18px;
                    padding-top: 15px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                    font: 700 10px/1 "Plus Jakarta Sans", sans-serif;
                    letter-spacing: 1.4px;
                    text-transform: uppercase;
                    color: #fff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-cat-card-link b {
                    width: 36px;
                    height: 36px;
                    border: 1px solid rgba(255, 255, 255, 0.6);
                    border-radius: 50%;
                    display: grid;
                    place-items: center;
                    color: #fff;
                    transition: 0.3s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-journey-card-cat:hover .ya-cat-card-link b {
                    background: #fff;
                    color: #111;
                    transform: rotate(-35deg);
                }
                @media (max-width: 992px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-cat-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }
                @media (max-width: 600px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-cat-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>

            <?php if ( ! empty( $atts['title'] ) ) : ?>
                <div class="ya-cat-head">
                    <h2><?php echo esc_html( $atts['title'] ); ?></h2>
                    <?php if ( ! empty( $atts['subtitle'] ) ) : ?>
                        <p><?php echo esc_html( $atts['subtitle'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="ya-cat-grid">
                <?php foreach ( $cards as $c ) : ?>
                    <a class="ya-journey-card-cat" href="<?php echo esc_url( $c['permalink'] ); ?>" style="--cat-img:url('<?php echo esc_url( $c['image'] ); ?>')">
                        <span class="ya-cat-card-meta"><?php echo esc_html( $c['duration_meta'] ); ?></span>
                        <span class="ya-cat-card-number"><?php echo esc_html( $c['number'] ); ?></span>
                        <div class="ya-cat-card-body">
                            <small><?php echo esc_html( $c['tag'] ); ?></small>
                            <h3><?php echo esc_html( $c['title'] ); ?></h3>
                            <p><?php echo esc_html( $c['excerpt'] ); ?></p>
                            <div class="ya-cat-card-link">
                                <span><?php esc_html_e( 'View journey', 'trek-content-manager' ); ?></span>
                                <b><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M9 7h8v8"/></svg></b>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * =========================================================================
     * SHORTCODE 4: [ya_signature_journeys]
     * =========================================================================
     */
    public static function render_signature_journeys( $atts ) {
        self::$instance_counter++;
        $uid = 'ya_sig_' . self::$instance_counter;

        $atts = shortcode_atts(
            array(
                'default_country' => 'nepal',
                'orderby'         => 'menu_order title',
                'order'           => 'ASC',
            ),
            $atts,
            'ya_signature_journeys'
        );

        /*
         * ---------------------------------------------------------
         * FETCH ALL PUBLISHED DESTINATIONS
         * ---------------------------------------------------------
         */
        $query_args = array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => sanitize_sql_orderby( $atts['orderby'] ) ?: 'menu_order title',
            'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
            'no_found_rows'  => true,
        );

        $query = new WP_Query( $query_args );

        $cards = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $p_id = get_the_ID();

                // Country slug identification
                $reg_slugs = wp_get_post_terms( $p_id, 'trek_region', array( 'fields' => 'slugs' ) );
                $country_slug = '';
                if ( ! empty( $reg_slugs ) && ! is_wp_error( $reg_slugs ) ) {
                    foreach ( array( 'nepal', 'bhutan', 'india', 'tibet' ) as $c_cand ) {
                        if ( in_array( $c_cand, $reg_slugs, true ) ) {
                            $country_slug = $c_cand;
                            break;
                        }
                    }
                }

                // Check meta if slug wasn't matched in terms
                if ( empty( $country_slug ) ) {
                    $meta_country = strtolower( trim( (string) get_post_meta( $p_id, '_trek_country', true ) ) );
                    foreach ( array( 'nepal', 'bhutan', 'india', 'tibet' ) as $c_cand ) {
                        if ( strpos( $meta_country, $c_cand ) !== false ) {
                            $country_slug = $c_cand;
                            break;
                        }
                    }
                }

                // Fallback check in title / fact region if still empty
                if ( empty( $country_slug ) ) {
                    $haystack = strtolower( get_the_title( $p_id ) . ' ' . get_post_meta( $p_id, '_trek_fact_region', true ) . ' ' . get_post_meta( $p_id, '_trek_breadcrumb_region', true ) );
                    if ( strpos( $haystack, 'bhutan' ) !== false ) {
                        $country_slug = 'bhutan';
                    } elseif ( strpos( $haystack, 'india' ) !== false || strpos( $haystack, 'rajasthan' ) !== false || strpos( $haystack, 'kerala' ) !== false || strpos( $haystack, 'varanasi' ) !== false || strpos( $haystack, 'ladakh' ) !== false ) {
                        $country_slug = 'india';
                    } elseif ( strpos( $haystack, 'tibet' ) !== false || strpos( $haystack, 'lhasa' ) !== false || strpos( $haystack, 'kailash' ) !== false ) {
                        $country_slug = 'tibet';
                    } elseif ( strpos( $haystack, 'nepal' ) !== false ) {
                        $country_slug = 'nepal';
                    }
                }

                // Title & URL
                $c_title = get_the_title( $p_id );
                $c_url   = get_permalink( $p_id );

                // Image - strictly from database
                $c_img = get_the_post_thumbnail_url( $p_id, 'large' );
                if ( empty( $c_img ) ) {
                    $c_img = get_post_meta( $p_id, '_trek_card_image_url', true );
                }
                if ( empty( $c_img ) ) {
                    $c_img = get_post_meta( $p_id, '_trek_hero_bg_img', true );
                }
                if ( empty( $c_img ) ) {
                    $c_img = '';
                }

                // Category badge - strictly from database
                $cat_terms = get_the_terms( $p_id, 'trek_category' );
                $badge     = ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->name : '';

                // Region / Country Display - strictly from database
                $c_region = get_post_meta( $p_id, '_trek_fact_region', true );
                if ( empty( $c_region ) ) {
                    $c_region = get_post_meta( $p_id, '_trek_breadcrumb_region', true );
                }
                if ( empty( $c_region ) ) {
                    $reg_terms = get_the_terms( $p_id, 'trek_region' );
                    if ( ! empty( $reg_terms ) && ! is_wp_error( $reg_terms ) ) {
                        $c_region = $reg_terms[0]->name;
                    }
                }
                if ( empty( $c_region ) ) {
                    $c_region = get_post_meta( $p_id, '_trek_country', true );
                }

                // Difficulty - strictly from database
                $diff_terms = get_the_terms( $p_id, 'trek_difficulty' );
                $c_diff     = ( ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->name : get_post_meta( $p_id, '_trek_difficulty', true );
                $c_diff     = ! empty( $c_diff ) ? preg_replace( '/\s*trek$/i', '', trim( $c_diff ) ) : '';

                // Duration - strictly from database
                $days_raw = get_post_meta( $p_id, '_trek_hero_days', true );
                if ( empty( $days_raw ) ) {
                    $days_raw = get_post_meta( $p_id, '_trek_fact_duration', true );
                }
                $c_days = ! empty( $days_raw ) ? preg_replace( '/[^0-9]/', '', (string) $days_raw ) : '';

                // Best Season - strictly from database
                $c_season = get_post_meta( $p_id, '_trek_fact_season', true );
                if ( empty( $c_season ) ) {
                    $c_season = get_post_meta( $p_id, '_trek_fact_best_season', true );
                }
                $c_season = ! empty( $c_season ) ? trim( (string) $c_season ) : '';

                // Location / Region display string - strictly from database
                $country_name     = ! empty( $country_slug ) ? ucfirst( $country_slug ) : '';
                $location_display = $c_region;
                if ( ! empty( $c_region ) && ! empty( $country_name ) && strtolower( $c_region ) !== strtolower( $country_name ) && stripos( $c_region, $country_name ) === false ) {
                    $location_display = $c_region . ', ' . $country_name;
                } elseif ( empty( $location_display ) ) {
                    $location_display = $country_name;
                }

                $cards[] = array(
                    'id'               => $p_id,
                    'country_slug'     => $country_slug,
                    'url'              => $c_url,
                    'img'              => $c_img,
                    'alt'              => $c_title,
                    'badge'            => $badge,
                    'region'           => $c_region,
                    'location_display' => $location_display,
                    'diff'             => $c_diff,
                    'title'            => $c_title,
                    'days'             => $c_days,
                    'season'           => $c_season,
                );
            }
            wp_reset_postdata();
        }

        if ( empty( $cards ) ) {
            return '<div class="ya-empty" style="text-align:center; padding:60px 20px; color:#617579; font-family:\'Open Sans\',Arial,sans-serif; font-size:16px;"><p>' . esc_html__( 'No data found.', 'trek-content-manager' ) . '</p></div>';
        }

        $default_country = sanitize_title( $atts['default_country'] );
        if ( ! in_array( $default_country, array( 'nepal', 'bhutan', 'india', 'tibet' ), true ) ) {
            $default_country = 'nepal';
        }

        $yeti_logo_url = 'https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/yeti_logo_active.gif';

        ob_start();
        ?>
        <div class="ya-collection-shell" id="<?php echo esc_attr( $uid ); ?>" data-default-country="<?php echo esc_attr( $default_country ); ?>">
            <style>
                #<?php echo esc_attr( $uid ); ?> {
                    width: 100%;
                    box-sizing: border-box;
                    position: relative;
                }
                #<?php echo esc_attr( $uid ); ?> * {
                    box-sizing: border-box;
                }

                /* =========================================================================
                   COUNTRY FILTER TABS (Pure white, 0 border radius, 1px border)
                   ========================================================================= */
                #<?php echo esc_attr( $uid ); ?> .ya-col-tabs {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0;
                    flex-wrap: wrap;
                    margin: 0 auto clamp(24px, 3.5vw, 44px);
                    max-width: 900px;
                    padding: 0 16px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-tab {
                    appearance: none;
                    -webkit-appearance: none;
                    background: #ffffff !important;
                    border-radius: 0 !important;
                    border: 1px solid rgba(17, 17, 17, 0.16) !important;
                    box-shadow: none !important;
                    height: 46px;
                    padding: 0 clamp(22px, 2.5vw, 34px);
                    font-family: "Open Sans", Arial, sans-serif;
                    font-size: 13px;
                    font-weight: 700;
                    letter-spacing: 1.4px;
                    text-transform: uppercase;
                    color: #102f35;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0px;
                    transition: all .25s ease;
                    position: relative;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-tab:hover {
                    border-color: #d1ad54 !important;
                    color: #d1ad54;
                    transform: translateY(-1px);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-tab.is-active {
                    background: #ffffff !important;
                    border-color: #d1ad54 !important;
                    color: #d1ad54;
                    font-weight: 800;
                    box-shadow: inset 0 -3px 0 #d1ad54, 0 3px 12px rgba(209, 173, 84, 0.12) !important;
                }
               

                /* =========================================================================
                   CAROUSEL CONTAINER & ARROWS
                   ========================================================================= */
                #<?php echo esc_attr( $uid ); ?> .ya-col-carousel-wrap {
                    position: relative;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn {
                    position: absolute;
                    top: 45%;
                    transform: translateY(-50%);
                    width: 48px;
                    height: 48px;
                    border-radius: 50%;
                    background: #ffffff;
                    border: 1px solid rgba(17, 17, 17, 0.14);
                    box-shadow: 0 6px 24px rgba(16, 47, 53, 0.14);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #102f35;
                    cursor: pointer;
                    z-index: 10;
                    transition: all .25s cubic-bezier(.22, 1, .36, 1);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn:hover {
                    background: #617654;
                    border-color: #617654;
                    color: #ffffff;
                    transform: translateY(-50%) scale(1.08);
                    box-shadow: 0 10px 28px rgba(97, 118, 84, 0.28);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn:active {
                    transform: translateY(-50%) scale(0.96);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn.is-disabled {
                    opacity: 0.35;
                    cursor: not-allowed;
                    pointer-events: none;
                    box-shadow: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-prev {
                    left: -22px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-next {
                    right: -22px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn svg {
                    width: 20px;
                    height: 20px;
                }

                /* =========================================================================
                   1-ROW SCROLLABLE TRACK
                   ========================================================================= */
                #<?php echo esc_attr( $uid ); ?> .ya-col-track {
                    display: flex;
                    flex-direction: row;
                    flex-wrap: nowrap;
                    gap: 28px;
                    overflow-x: auto;
                    overflow-y: hidden;
                    scroll-snap-type: x mandatory;
                    scroll-behavior: smooth;
                    -webkit-overflow-scrolling: touch;
                    padding: 12px 12px 28px;
                    margin: 0;
                    scrollbar-width: none; /* Firefox */
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-track::-webkit-scrollbar {
                    display: none; /* Safari & Chrome */
                }

                /* =========================================================================
                   DOMINANT LUXURY CARD (6px Border Radius, Modern Layout)
                   ========================================================================= */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card {
                    flex: 0 0 350px;
                    min-width: 280px;
                    max-width: 350px;
                    scroll-snap-align: start;
                    display: flex;
                    flex-direction: column;
                    border-radius: 6px;
                    overflow: hidden;
                    background: #ffffff;
                    border: 2px solid rgba(97, 118, 84, 0.2);
                    box-shadow: 0 4px 20px rgba(7, 94, 84, 0.05);
                    transition: transform .35s cubic-bezier(.22, 1, .36, 1), box-shadow .35s ease, border-color .35s ease;
                    text-decoration: none;
                    color: inherit;
                    position: relative;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover {
                    transform: translateY(-6px);
                    border-color: #d1ad54;
                    box-shadow: 0 20px 52px rgba(7, 94, 84, 0.14);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card.is-hidden {
                    display: none !important;
                }

                /* Card Media */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__media {
                    position: relative;
                    height: 236px;
                    overflow: hidden;
                    flex-shrink: 0;
                    background: #102f35;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__img {
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                    display: block !important;
                    transition: transform .65s cubic-bezier(.22, 1, .36, 1);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__img {
                    transform: scale(1.06);
                }

                /* Top-Right Badge: { Yeti Logo | Category } with Transparent/Glassy Background */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-badge {
                    position: absolute;
                    top: 14px;
                    right: 14px;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    border-radius: 1px;
                    padding: 5px 12px 5px 8px;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
                    z-index: 3;
                    transition: background .25s ease, border-color .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__top-badge {
                    background: #cfab55;
                    border-color: #617654;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-logo {
                    width: 24px !important;
                    height: 24px !important;
                    max-width: 24px !important;
                    max-height: 24px !important;
                    object-fit: contain !important;
                    display: block !important;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-sep {
                    color: rgba(255, 255, 255, 0.4);
                    font-size: 11px;
                    font-weight: 300;
                    line-height: 1;
                    user-select: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-cat {
                    font-family: "Open Sans", Arial, sans-serif;
                    font-size: 10.5px;
                    font-weight: 700;
                    letter-spacing: .8px;
                    text-transform: uppercase;
                    color: #ffffff;
                    line-height: 1;
                }

                /* Card Body */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__body {
                    padding: 22px 20px 20px;
                    display: flex;
                    flex-direction: column;
                    flex-grow: 1;
                    position: relative;
                    background: #ffffff;
                }

                /* Location / Region Line (Below image, no background) */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-bottom: 8px;
                    background: transparent !important;
                    padding: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                    position: static !important;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc-icon {
                    width: 13px;
                    height: 13px;
                    color: #617654;
                    flex-shrink: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc-text {
                    font-family: "Open Sans", Arial, sans-serif;
                    font-size: 11px;
                    font-weight: 600;
                    letter-spacing: .9px;
                    text-transform: uppercase;
                    color: #617654;
                }

                /* Title Heading (Below location) */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__title {
                    margin: 0 0 18px;
                    font-family: "Newsreader", Georgia, serif;
                    font-size: 20px;
                    font-weight: 600;
                    line-height: 1.28;
                    color: #111111;
                    transition: color .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__title {
                    color: #d1ad54;
                }

                /* 3-Column Meta Info Row (Wrappable) */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 12px 14px;
                    margin-top: auto;
                    padding-top: 14px;
                    border-top: 1px solid #eef5f2;
                    position: relative;
                    z-index: 1;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-item {
                    flex: 1 1 calc(33.333% - 10px);
                    min-width: 80px;
                    display: flex;
                    flex-direction: column;
                    gap: 3px;
                    padding: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-head {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-meta-icon {
                    width: 12px;
                    height: 12px;
                    color: #617654;
                    flex-shrink: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-label {
                    font-family: "Open Sans", Arial, sans-serif;
                    font-size: 9.5px;
                    font-weight: 600;
                    letter-spacing: .6px;
                    text-transform: uppercase;
                    color: #617654;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-val {
                    font-family: "Open Sans", Arial, sans-serif;
                    font-size: 12.5px;
                    font-weight: 600;
                    color: #111111;
                    white-space: normal;
                    word-break: break-word;
                    line-height: 1.35;
                }

                /* Subtle contour art behind content */
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__body::after {
                    content: "";
                    position: absolute;
                    bottom: 0; left: 0; right: 0;
                    height: 50px;
                    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 60' preserveAspectRatio='none'%3E%3Cpath d='M0 40 Q40 20 80 35 Q120 50 160 30 Q200 10 240 28 Q280 46 320 32 Q360 18 400 38 L400 60 L0 60Z' fill='%23eaf5f0' opacity='0.4'/%3E%3C/svg%3E") bottom / cover no-repeat;
                    pointer-events: none;
                }

                /* =========================================================================
                   BOTTOM PAGINATION DOTS
                   ========================================================================= */
                #<?php echo esc_attr( $uid ); ?> .ya-col-dots {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    margin: 28px auto 0;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-dot {
                    appearance: none;
                    -webkit-appearance: none;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: rgba(16, 47, 53, 0.2);
                    border: none;
                    padding: 0;
                    cursor: pointer;
                    transition: all .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-dot:hover {
                    background: #d1ad54;
                    transform: scale(1.2);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-dot.is-active,
                #<?php echo esc_attr( $uid ); ?> .ya-col-dot.is-active:hover,
                #<?php echo esc_attr( $uid ); ?> .ya-col-dot.is-active:focus {
                    background: #d1ad54;
                    width: 24px;
                    border-radius: 12px;
                    transform: scale(1);
                    box-shadow: 0 0 10px rgba(209, 173, 84, 0.5);
                }

                /* =========================================================================
                   RESPONSIVE ADJUSTMENTS
                   ========================================================================= */
                @media (max-width: 1200px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-col-card {
                        flex: 0 0 330px;
                        max-width: 350px;
                        min-width: 280px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-prev {
                        left: -12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-next {
                        right: -12px;
                    }
                }
                @media (max-width: 768px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-col-card {
                        flex: 0 0 85vw;
                        max-width: 340px;
                        min-width: 280px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-track {
                        gap: 16px;
                        padding: 8px 4px 20px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-tabs {
                        gap: 8px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-tab {
                        height: 42px;
                        padding: 0 18px;
                        font-size: 12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-nav-btn {
                        width: 40px;
                        height: 40px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-prev {
                        left: -6px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-col-next {
                        right: -6px;
                    }
                }
            </style>

            <!-- 4 Country Filter Buttons (Pure white, 0 border radius, 1px border) -->
            <div class="ya-col-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Filter Journeys by Country', 'trek-content-manager' ); ?>">
                <?php
                $countries = array(
                    'nepal'  => __( 'Nepal', 'trek-content-manager' ),
                    'bhutan' => __( 'Bhutan', 'trek-content-manager' ),
                    'india'  => __( 'India', 'trek-content-manager' ),
                    'tibet'  => __( 'Tibet', 'trek-content-manager' ),
                );
                foreach ( $countries as $c_slug => $c_name ) :
                    $is_tab_active = ( $c_slug === $default_country );
                    $count_for_c = 0;
                    foreach ( $cards as $c ) {
                        if ( $c['country_slug'] === $c_slug ) {
                            $count_for_c++;
                        }
                    }
                ?>
                    <button type="button"
                            class="ya-col-tab<?php echo $is_tab_active ? ' is-active' : ''; ?>"
                            data-country="<?php echo esc_attr( $c_slug ); ?>"
                            role="tab"
                            aria-selected="<?php echo $is_tab_active ? 'true' : 'false'; ?>">
                        <span><?php echo esc_html( $c_name ); ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Carousel Area -->
            <div class="ya-col-carousel-wrap">
                <!-- Navigation Arrows -->
                <button type="button" class="ya-col-nav-btn ya-col-prev" aria-label="<?php esc_attr_e( 'Previous Journeys', 'trek-content-manager' ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" class="ya-col-nav-btn ya-col-next" aria-label="<?php esc_attr_e( 'Next Journeys', 'trek-content-manager' ); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>

                <!-- 1-Row Scrollable Track -->
                <div class="ya-col-track" id="<?php echo esc_attr( $uid ); ?>_track">
                    <?php foreach ( $cards as $i => $fc ) :
                        $is_initial_hidden = ( $fc['country_slug'] !== $default_country );
                    ?>
                        <a class="ya-col-card<?php echo $is_initial_hidden ? ' is-hidden' : ''; ?>"
                           href="<?php echo esc_url( $fc['url'] ); ?>"
                           data-country="<?php echo esc_attr( $fc['country_slug'] ); ?>"
                           data-card-index="<?php echo esc_attr( $i ); ?>">
                            <div class="ya-col-card__media">
                                <?php if ( ! empty( $fc['img'] ) ) : ?>
                                    <img src="<?php echo esc_url( $fc['img'] ); ?>" alt="<?php echo esc_attr( $fc['alt'] ); ?>" loading="lazy" class="ya-col-card__img">
                                <?php else : ?>
                                    <div class="ya-col-card__img-placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #102f35 0%, #1d4d57 100%);color:rgba(255,255,255,0.4);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                    </div>
                                <?php endif; ?>

                                <!-- Top-Right: { Yeti Logo | Category } with Transparent Background -->
                                <div class="ya-col-card__top-badge">
                                    <img src="<?php echo esc_url( $yeti_logo_url ); ?>" alt="Yeti Adventure" class="ya-col-card__top-logo">
                                    <?php if ( ! empty( $fc['badge'] ) ) : ?>
                                        <span class="ya-col-card__top-sep" aria-hidden="true">|</span>
                                        <span class="ya-col-card__top-cat"><?php echo esc_html( $fc['badge'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ya-col-card__body">
                                <!-- Location & Region (below image, no background) -->
                                <?php if ( ! empty( $fc['location_display'] ) ) : ?>
                                    <div class="ya-col-card__loc">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-card__loc-icon"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                        <span class="ya-col-card__loc-text"><?php echo esc_html( $fc['location_display'] ); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Name Heading -->
                                <h3 class="ya-col-card__title"><?php echo esc_html( $fc['title'] ); ?></h3>

                                <!-- 3 Columns: Duration, Difficulty, Best Season -->
                                <div class="ya-col-card__meta">
                                    <div class="ya-col-card__meta-item">
                                        <div class="ya-col-card__meta-head">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            <span class="ya-col-card__meta-label"><?php esc_html_e( 'Duration', 'trek-content-manager' ); ?></span>
                                        </div>
                                        <span class="ya-col-card__meta-val"><?php echo ! empty( $fc['days'] ) ? esc_html( $fc['days'] ) . ' ' . esc_html__( 'Days', 'trek-content-manager' ) : '—'; ?></span>
                                    </div>
                                    <div class="ya-col-card__meta-item">
                                        <div class="ya-col-card__meta-head">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                            <span class="ya-col-card__meta-label"><?php esc_html_e( 'Difficulty', 'trek-content-manager' ); ?></span>
                                        </div>
                                        <span class="ya-col-card__meta-val"><?php echo ! empty( $fc['diff'] ) ? esc_html( $fc['diff'] ) : '—'; ?></span>
                                    </div>
                                    <div class="ya-col-card__meta-item">
                                        <div class="ya-col-card__meta-head">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                            <span class="ya-col-card__meta-label"><?php esc_html_e( 'Best Season', 'trek-content-manager' ); ?></span>
                                        </div>
                                        <span class="ya-col-card__meta-val"><?php echo ! empty( $fc['season'] ) ? esc_html( $fc['season'] ) : '—'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Empty state when a specific country tab has 0 trips -->
                <div class="ya-col-empty" id="<?php echo esc_attr( $uid ); ?>_empty" style="display: none; text-align: center; padding: 60px 20px; color: #617654; font-family: 'Open Sans', Arial, sans-serif; font-size: 15px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 12px; color: #617654; display: block; opacity: 0.8;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p style="margin: 0; font-weight: 600;"><?php esc_html_e( 'No data found.', 'trek-content-manager' ); ?></p>
                </div>

                <!-- Bottom Pagination Dots -->
                <div class="ya-col-dots" id="<?php echo esc_attr( $uid ); ?>_dots" role="tablist" aria-label="<?php esc_attr_e( 'Journey slides', 'trek-content-manager' ); ?>"></div>
            </div>

            <script>
                (function() {
                    const shell = document.getElementById('<?php echo esc_js( $uid ); ?>');
                    if (!shell) return;

                    const track = shell.querySelector('#<?php echo esc_js( $uid ); ?>_track');
                    const dotsWrap = shell.querySelector('#<?php echo esc_js( $uid ); ?>_dots');
                    const prevBtn = shell.querySelector('.ya-col-prev');
                    const nextBtn = shell.querySelector('.ya-col-next');
                    const tabs = Array.from(shell.querySelectorAll('.ya-col-tab'));
                    const cards = Array.from(shell.querySelectorAll('.ya-col-card'));
                    const emptyEl = shell.querySelector('#<?php echo esc_js( $uid ); ?>_empty');

                    let activeCountry = shell.getAttribute('data-default-country') || 'nepal';

                    function getVisibleCards() {
                        return cards.filter(c => c.getAttribute('data-country') === activeCountry);
                    }

                    function updateNavArrows() {
                        if (!track) return;
                        const sl = track.scrollLeft;
                        const maxScroll = track.scrollWidth - track.clientWidth - 4;
                        if (prevBtn) {
                            if (sl <= 4) {
                                prevBtn.classList.add('is-disabled');
                            } else {
                                prevBtn.classList.remove('is-disabled');
                            }
                        }
                        if (nextBtn) {
                            if (sl >= maxScroll) {
                                nextBtn.classList.add('is-disabled');
                            } else {
                                nextBtn.classList.remove('is-disabled');
                            }
                        }
                    }

                    function buildDots() {
                        if (!dotsWrap) return;
                        dotsWrap.innerHTML = '';
                        const visible = getVisibleCards();
                        if (visible.length <= 1) {
                            dotsWrap.style.display = 'none';
                            return;
                        }
                        dotsWrap.style.display = 'flex';

                        visible.forEach((card, idx) => {
                            const dot = document.createElement('button');
                            dot.type = 'button';
                            dot.className = 'ya-col-dot' + (idx === 0 ? ' is-active' : '');
                            dot.setAttribute('aria-label', 'Slide ' + (idx + 1));
                            dot.addEventListener('click', () => {
                                const cardCenter = (card.offsetLeft - track.offsetLeft) - Math.max(0, (track.clientWidth - card.offsetWidth) / 2);
                                track.scrollTo({ left: Math.max(0, cardCenter), behavior: 'smooth' });
                            });
                            dotsWrap.appendChild(dot);
                        });
                    }

                    function syncDotsOnScroll() {
                        if (!dotsWrap || !track) return;
                        const visible = getVisibleCards();
                        if (visible.length === 0) return;
                        const dots = Array.from(dotsWrap.querySelectorAll('.ya-col-dot'));

                        // Check if scrolled to the very end
                        const maxScroll = track.scrollWidth - track.clientWidth - 10;
                        if (track.scrollLeft >= maxScroll) {
                            dots.forEach((d, idx) => d.classList.toggle('is-active', idx === visible.length - 1));
                            return;
                        }

                        // Determine which card is closest to the center of the visible track viewport
                        const viewCenter = track.scrollLeft + (track.clientWidth / 2);
                        let closestIdx = 0;
                        let minDistance = Infinity;

                        visible.forEach((card, idx) => {
                            const cardCenter = (card.offsetLeft - track.offsetLeft) + (card.offsetWidth / 2);
                            const dist = Math.abs(viewCenter - cardCenter);
                            if (dist < minDistance) {
                                minDistance = dist;
                                closestIdx = idx;
                            }
                        });

                        dots.forEach((d, idx) => {
                            d.classList.toggle('is-active', idx === closestIdx);
                        });
                    }

                    function filterByCountry(countrySlug) {
                        activeCountry = countrySlug;

                        // Update tabs
                        tabs.forEach(t => {
                            const isCurrent = t.getAttribute('data-country') === countrySlug;
                            t.classList.toggle('is-active', isCurrent);
                            t.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
                        });

                        const visible = getVisibleCards();

                        if (visible.length === 0) {
                            if (emptyEl) emptyEl.style.display = 'block';
                            if (track) track.style.display = 'none';
                            if (prevBtn) prevBtn.style.display = 'none';
                            if (nextBtn) nextBtn.style.display = 'none';
                            if (dotsWrap) dotsWrap.style.display = 'none';
                        } else {
                            if (emptyEl) emptyEl.style.display = 'none';
                            if (track) track.style.display = 'flex';
                            if (prevBtn) prevBtn.style.display = 'flex';
                            if (nextBtn) nextBtn.style.display = 'flex';

                            // Show/Hide cards with subtle animation
                            cards.forEach(card => {
                                if (card.getAttribute('data-country') === countrySlug) {
                                    card.classList.remove('is-hidden');
                                    card.style.opacity = '0';
                                    card.style.transform = 'translateY(12px)';
                                    card.style.transition = 'opacity .35s ease, transform .35s ease';
                                    requestAnimationFrame(() => {
                                        card.style.opacity = '1';
                                        card.style.transform = 'translateY(0)';
                                    });
                                } else {
                                    card.classList.add('is-hidden');
                                }
                            });

                            // Scroll track back to start
                            if (track) {
                                track.scrollTo({ left: 0, behavior: 'smooth' });
                            }

                            // Rebuild dots & nav state
                            setTimeout(() => {
                                buildDots();
                                updateNavArrows();
                            }, 50);
                        }
                    }

                    // Tab clicks
                    tabs.forEach(tab => {
                        tab.addEventListener('click', function() {
                            const country = this.getAttribute('data-country');
                            if (country && country !== activeCountry) {
                                filterByCountry(country);
                            }
                        });
                    });

                    // Arrow Navigation
                    if (prevBtn && track) {
                        prevBtn.addEventListener('click', () => {
                            const card = track.querySelector('.ya-col-card:not(.is-hidden)');
                            const scrollAmount = card ? (card.offsetWidth + 28) : 340;
                            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                        });
                    }
                    if (nextBtn && track) {
                        nextBtn.addEventListener('click', () => {
                            const card = track.querySelector('.ya-col-card:not(.is-hidden)');
                            const scrollAmount = card ? (card.offsetWidth + 28) : 340;
                            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                        });
                    }

                    // Scroll event throttling
                    if (track) {
                        let isScrolling = false;
                        track.addEventListener('scroll', () => {
                            if (!isScrolling) {
                                window.requestAnimationFrame(() => {
                                    updateNavArrows();
                                    syncDotsOnScroll();
                                    isScrolling = false;
                                });
                                isScrolling = true;
                            }
                        }, { passive: true });
                    }

                    // Initialize
                    const initialVisible = getVisibleCards();
                    if (initialVisible.length === 0) {
                        if (emptyEl) emptyEl.style.display = 'block';
                        if (track) track.style.display = 'none';
                        if (prevBtn) prevBtn.style.display = 'none';
                        if (nextBtn) nextBtn.style.display = 'none';
                        if (dotsWrap) dotsWrap.style.display = 'none';
                    } else {
                        buildDots();
                        updateNavArrows();
                    }
                })();
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * =========================================================================
     * SHORTCODE 5: [trek_journey_finder] / [ya_journey_finder]
     * =========================================================================
     * Interactive trip selector widget based on region, feeling, and pace.
     */
    public static function render_journey_finder( $atts ) {
        self::$instance_counter++;
        $instance_id = self::$instance_counter;
        $uid         = 'ya_finder_' . $instance_id;

        $atts = shortcode_atts(
            array(
                'id'       => ( 1 === $instance_id ) ? 'journey-finder' : 'journey-finder-' . $instance_id,
                'title'    => "Let's choose your next story.",
                'subtitle' => 'Pick the landscape, feeling and pace that draw you in. We will point you toward a journey worth considering—then shape it properly around you.',
                'class'    => '',
            ),
            $atts,
            'trek_journey_finder'
        );

        $section_id = sanitize_html_class( $atts['id'] );
        $ajax_url   = admin_url( 'admin-ajax.php' );
        $nonce      = wp_create_nonce( 'trek_journey_lead_nonce' );

        $categories = class_exists( 'Trek_Journey_Matcher' ) ? Trek_Journey_Matcher::get_categories() : array();

        // Calculate initial match (default fallback or first published journey)
        $initial_match = class_exists( 'Trek_Journey_Matcher' ) ? Trek_Journey_Matcher::match( array() ) : array();
        $initial_dest  = ( ! empty( $initial_match['destination_id'] ) && class_exists( 'Trek_Journey_Matcher' ) )
            ? Trek_Journey_Matcher::get_destination_display_data( $initial_match['destination_id'], $initial_match )
            : array();

        ob_start();
        ?>
        <section class="ya-selector <?php echo esc_attr( $atts['class'] ); ?>" id="<?php echo esc_attr( $section_id ); ?>" data-uid="<?php echo esc_attr( $uid ); ?>">
            <style>
                #<?php echo esc_attr( $section_id ); ?>,
                #<?php echo esc_attr( $section_id ); ?> * {
                    box-sizing: border-box;
                }

                #<?php echo esc_attr( $section_id ); ?> {
                    --gold: #d1ad54;
                    --gold-hover: #b8933f;
                    --forest: #617654;
                    --forest-dark: #4e5e44;
                    --paper: #ffffff;
                    --card-bg: #ffffff;
                    --text: #111111;
                    --muted: #444444;
                    --line: rgba(17, 17, 17, .09);
                    --font-display: var(--te-font-heading, "Newsreader", "Cormorant Garamond", Georgia, serif);
                    --font-sans: var(--te-font-body, "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);

                    width: 100%;
                    padding: clamp(60px, 6vw, 95px) 0;
                    background: #ffffff;
                    color: var(--text);
                    font-family: var(--font-sans);
                    overflow: visible;
                    position: relative;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-inner {
                    position: relative;
                    width: min(1280px, calc(100% - 56px));
                    margin: 0 auto;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-selector-grid {
                    display: grid;
                    grid-template-columns: .85fr 1.15fr;
                    gap: clamp(40px, 5vw, 70px);
                    align-items: center;
                }

                #<?php echo esc_attr( $section_id ); ?> h2 {
                    margin: 0;
                    color: var(--text);
                    font-family: var(--font-display);
                    font-size: clamp(44px, 5.5vw, 76px);
                    font-weight: 400;
                    line-height: 1.04;
                    letter-spacing: -.03em;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-header-accent {
                    color: var(--gold);
                    font-style: italic;
                    font-weight: 500;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-selector-subtitle {
                    margin: 18px 0 0;
                    font-size: clamp(14.5px, 1.2vw, 16px);
                    line-height: 1.75;
                    color: var(--muted);
                    max-width: 520px;
                    font-weight: 400;
                }

                /* Clear All Toolbar & Actions */
                #<?php echo esc_attr( $section_id ); ?> .ya-clear-bar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 12px;
                    flex-wrap: wrap;
                    padding: 10px 4px 4px;
                    margin-top: 6px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-selected-count {
                    font-size: 11.5px;
                    font-weight: 700;
                    color: var(--forest);
                    letter-spacing: 0.8px;
                    text-transform: uppercase;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-clear-all-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 14px;
                    border-radius: 999px;
                    border: 1px solid var(--forest);
                    background: transparent;
                    color: var(--forest);
                    font-family: var(--font-sans);
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.8px;
                    text-transform: uppercase;
                    cursor: pointer;
                    transition: all .22s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-clear-all-btn:hover {
                    background: var(--forest);
                    color: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__menu-top {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 6px 10px 8px;
                    border-bottom: 1px solid rgba(17, 17, 17, 0.08);
                    margin-bottom: 4px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__menu-title {
                    font-size: 10.5px;
                    font-weight: 700;
                    letter-spacing: 1.2px;
                    text-transform: uppercase;
                    color: var(--forest);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__clear-btn {
                    border: none;
                    background: transparent;
                    color: var(--gold);
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                    cursor: pointer;
                    padding: 2px 6px;
                    border-radius: 4px;
                    transition: color .2s ease, background-color .2s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__clear-btn:hover {
                    color: var(--forest);
                    background: rgba(97, 118, 84, 0.08);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-box {
                    padding: 0;
                    border-radius: 0;
                    background: transparent;
                    box-shadow: none;
                    border: none;
                    position: relative;
                }

                /* Horizontal Multi-Select Dropdowns */
                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown-group {
                    display: flex;
                    flex-direction: column;
                    gap: 0;
                    position: relative;
                    border-top: 1px solid rgba(17, 17, 17, .12);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown {
                    position: relative;
                    border-bottom: 1px solid rgba(17, 17, 17, .12);
                    transition: border-color .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown[data-category="explore"] {
                    z-index: 35;
                }
                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown[data-category="feel"] {
                    z-index: 30;
                }
                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown[data-category="pace"] {
                    z-index: 25;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown:hover {
                    border-bottom-color: var(--forest);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown.is-open {
                    border-bottom-color: var(--gold);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__control {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 18px;
                    padding: 20px 4px;
                    background: transparent;
                    border: none;
                    border-radius: 0;
                    box-shadow: none;
                    cursor: pointer;
                    user-select: none;
                    transition: all .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__control:hover {
                    background: transparent;
                    box-shadow: none;
                    transform: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown.is-open .ya-dropdown__control {
                    background: transparent;
                    box-shadow: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__label {
                    display: inline-block;
                    font-size: 12px;
                    font-weight: 700;
                    letter-spacing: 1.6px;
                    text-transform: uppercase;
                    color: var(--text);
                    margin: 0;
                    min-width: 175px;
                    flex-shrink: 0;
                    transition: color .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown:hover .ya-dropdown__label {
                    color: var(--forest);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown.is-open .ya-dropdown__label {
                    color: var(--gold);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__text-wrap {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    overflow: hidden;
                    flex: 1;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__placeholder {
                    font-size: 14.5px;
                    font-weight: 400;
                    color: var(--forest);
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    transition: color .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__value {
                    font-size: 14.5px;
                    font-weight: 600;
                    color: var(--text);
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 20px;
                    height: 20px;
                    padding: 0 6px;
                    border-radius: 999px;
                    background: var(--forest);
                    color: #ffffff;
                    font-size: 11px;
                    font-weight: 700;
                    flex-shrink: 0;
                    line-height: 1;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__arrow {
                    width: 18px;
                    height: 18px;
                    stroke: var(--forest);
                    transition: transform .3s cubic-bezier(.22, .61, .36, 1), stroke .25s ease;
                    flex-shrink: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown:hover .ya-dropdown__arrow {
                    stroke: var(--forest);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown.is-open .ya-dropdown__arrow {
                    transform: rotate(180deg);
                    stroke: var(--gold);
                }

                /* Dropdown Menu (Floating Options Panel) */
                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__menu {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: #ffffff;
                    border-radius: 8px;
                    border: 1px solid rgba(17, 17, 17, .12);
                    box-shadow: 0 16px 36px rgba(0, 0, 0, .10);
                    max-height: 240px;
                    overflow-y: auto;
                    padding: 6px;
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-6px) scale(.99);
                    pointer-events: none;
                    transition: opacity .22s ease, transform .22s ease, visibility .22s ease;
                    scrollbar-width: thin;
                    scrollbar-color: rgba(17, 17, 17, .2) transparent;
                    z-index: 50;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__menu::-webkit-scrollbar {
                    width: 5px;
                }
                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__menu::-webkit-scrollbar-thumb {
                    background: rgba(17, 17, 17, .2);
                    border-radius: 4px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown.is-open .ya-dropdown__menu {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0) scale(1);
                    pointer-events: auto;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__option {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px 14px;
                    border-radius: 6px;
                    cursor: pointer;
                    user-select: none;
                    font-size: 13.5px;
                    color: var(--text);
                    transition: background-color .18s ease, color .18s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__option:hover {
                    background: rgba(209, 173, 84, .10);
                    color: var(--text);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__option.is-checked {
                    background: rgba(97, 118, 84, .10);
                    font-weight: 600;
                    color: var(--forest-dark);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__checkbox {
                    display: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__check-custom {
                    width: 18px;
                    height: 18px;
                    border-radius: 4px;
                    border: 1.5px solid rgba(17, 17, 17, .25);
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    transition: all .2s ease;
                    flex-shrink: 0;
                    background: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__check-custom svg {
                    width: 12px;
                    height: 12px;
                    stroke: #ffffff;
                    opacity: 0;
                    transform: scale(.6);
                    transition: all .2s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__option.is-checked .ya-dropdown__check-custom {
                    background: var(--forest);
                    border-color: var(--forest);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__option.is-checked .ya-dropdown__check-custom svg {
                    opacity: 1;
                    transform: scale(1);
                }

                /* Matched Result Area */
                #<?php echo esc_attr( $section_id ); ?> .ya-match-result {
                    margin-top: 24px;
                    padding-top: 0;
                    border: none;
                    background: transparent;
                    transition: opacity .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-result.is-loading {
                    opacity: 0.5;
                    pointer-events: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-preview {
                    display: flex;
                    align-items: center;
                    gap: 18px;
                    padding: 6px 0 14px;
                    background: transparent;
                    border: none;
                    border-radius: 0;
                    box-shadow: none;
                    margin-bottom: 6px;
                    animation: yaFadeSlideIn .4s ease;
                }

                @keyframes yaFadeSlideIn {
                    from { opacity: 0; transform: translateY(8px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-thumb {
                    position: relative;
                    width: 110px;
                    height: 82px;
                    border-radius: 4px;
                    overflow: hidden;
                    flex-shrink: 0;
                    background: transparent;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-thumb img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    display: block;
                    border-radius: 4px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-tag {
                    position: absolute;
                    bottom: 4px;
                    left: 4px;
                    right: 4px;
                    background: rgba(24, 35, 26, 0.88);
                    backdrop-filter: blur(4px);
                    color: var(--gold);
                    font-size: 8px;
                    font-weight: 700;
                    letter-spacing: 1px;
                    text-align: center;
                    padding: 2px 4px;
                    border-radius: 2px;
                    text-transform: uppercase;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-details {
                    flex: 1;
                    min-width: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-title {
                    margin: 0 0 6px;
                    font-family: var(--font-display);
                    font-size: clamp(21px, 2.3vw, 27px);
                    font-weight: 500;
                    line-height: 1.25;
                    color: var(--text);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-meta-line {
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 16px;
                    margin-top: 4px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-meta-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 0;
                    border-radius: 0;
                    background: transparent;
                    color: var(--muted);
                    font-size: 13px;
                    font-weight: 500;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-meta-pill svg {
                    color: var(--forest);
                    flex-shrink: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-eyebrow {
                    display: block;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    color: var(--gold);
                    margin-bottom: 6px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-prompt-title {
                    margin: 0 0 8px;
                    font-family: var(--font-display);
                    font-size: clamp(23px, 2.5vw, 29px);
                    font-weight: 500;
                    line-height: 1.22;
                    color: var(--text);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-message-box {
                    display: block;
                    padding: 8px 0 8px 16px;
                    background: transparent;
                    border: none;
                    border-left: 2.5px solid var(--gold);
                    border-radius: 0;
                    margin: 16px 0 20px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-callout {
                    margin: 0;
                    font-size: 13.5px;
                    line-height: 1.65;
                    color: var(--muted);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match-callout strong {
                    color: var(--forest);
                    font-weight: 600;
                }

                /* Email Collection Box & Button */
                #<?php echo esc_attr( $section_id ); ?> .ya-email-collect {
                    position: relative;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-group {
                    display: flex;
                    gap: 12px;
                    align-items: stretch;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-input {
                    flex: 1;
                    height: 58px;
                    padding: 0 24px;
                    border: 1.5px solid rgba(17, 17, 17, .14);
                    border-radius: 999px;
                    background: rgba(255, 255, 255, 0.95);
                    color: var(--text);
                    font-family: var(--font-sans);
                    font-size: 14.5px;
                    outline: none;
                    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
                    transition: border-color .25s ease, box-shadow .25s ease, background-color .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-input:focus {
                    border-color: var(--gold);
                    box-shadow: 0 0 0 3px rgba(207, 171, 85, .20);
                    background: #ffffff;
                }

                /* Modern Luxury Button with Reverse Hover Effect */
                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn {
                    position: relative;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 14px;
                    min-height: 58px;
                    height: 58px;
                    margin: 0;
                    padding: 6px 8px 6px 28px;
                    border-radius: 999px;
                    background: #d1ad54;
                    color: #ffffff;
                    border: 1.5px solid #d1ad54;
                    box-shadow: 0 8px 24px rgba(209, 173, 84, 0.35);
                    font-family: var(--font-sans);
                    font-size: 11.5px;
                    font-weight: 700;
                    line-height: 1;
                    letter-spacing: 1.8px;
                    text-decoration: none;
                    text-transform: uppercase;
                    overflow: hidden;
                    cursor: pointer;
                    white-space: nowrap;
                    flex-shrink: 0;
                    transition: all 0.35s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn .ya-hero-cta__text {
                    position: relative;
                    z-index: 1;
                    color: #ffffff;
                    transition: color 0.35s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn .ya-hero-cta__icon {
                    position: relative;
                    z-index: 1;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 44px;
                    height: 44px;
                    border-radius: 50%;
                    background: transparent;
                    color: #ffffff;
                    transition: all 0.35s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn .ya-hero-cta__icon svg {
                    width: 17px;
                    height: 17px;
                    stroke: currentColor;
                    transition: transform 0.35s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn:hover {
                    background: #617654;
                    border-color: #617654;
                    box-shadow: 0 12px 30px rgba(97, 118, 84, 0.45);
                    transform: translateY(-2px);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn:hover .ya-hero-cta__text {
                    color: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn:hover .ya-hero-cta__icon {
                    background: transparent;
                    color: #ffffff;
                    transform: scale(1.06);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn:hover .ya-hero-cta__icon svg {
                    transform: translateX(3px);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn:disabled {
                    opacity: 0.65;
                    cursor: not-allowed;
                    transform: none;
                    box-shadow: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-btn-loader {
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255, 255, 255, .35);
                    border-top-color: #ffffff;
                    border-radius: 50%;
                    animation: yaFinderSpin 0.7s infinite linear;
                }

                @keyframes yaFinderSpin {
                    to { transform: rotate(360deg); }
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-feedback {
                    margin-top: 14px;
                    padding: 12px 18px;
                    border-radius: 8px;
                    font-size: 13.5px;
                    line-height: 1.5;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-feedback.is-success {
                    background: #eef8f2;
                    color: #0b5d38;
                    border: 1px solid #c3e7d1;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-feedback.is-error {
                    background: #fdf2f2;
                    color: #a51d24;
                    border: 1px solid #f8d7da;
                }

                /* Reveal animations */
                #<?php echo esc_attr( $section_id ); ?> .ya-reveal {
                    opacity: 0;
                    transform: translateY(28px);
                    transition: opacity .85s cubic-bezier(.2, .7, .2, 1), transform .85s cubic-bezier(.2, .7, .2, 1);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-reveal.is-visible {
                    opacity: 1;
                    transform: none;
                }

                @media (max-width: 1100px) {
                    #<?php echo esc_attr( $section_id ); ?> .ya-selector-grid {
                        grid-template-columns: 1fr;
                        gap: 45px;
                    }
                }

                @media (max-width: 820px) {
                    #<?php echo esc_attr( $section_id ); ?> {
                        padding: 60px 0;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-inner {
                        width: min(100% - 32px, 1280px);
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-selector-copy {
                        text-align: center;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-selector-subtitle {
                        margin-left: auto;
                        margin-right: auto;
                    }
                }

                @media (max-width: 600px) {
                    #<?php echo esc_attr( $section_id ); ?> .ya-choice-box {
                        padding: 0;
                    }
                    #<?php echo esc_attr( $section_id ); ?> h2 {
                        font-size: clamp(34px, 8.5vw, 46px);
                        line-height: 1.1;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-email-group {
                        flex-direction: column;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-hero-cta.ya-email-btn {
                        width: 100%;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-match-preview {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 14px;
                        padding: 6px 0 12px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-match-thumb {
                        width: 100%;
                        height: 140px;
                        border-radius: 4px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-match-message-box {
                        margin: 14px 0 18px;
                        padding: 6px 0 6px 12px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-clear-bar {
                        gap: 8px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__control {
                        padding: 16px 0;
                        gap: 12px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__label {
                        min-width: 135px;
                        font-size: 11px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__placeholder,
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__value {
                        font-size: 13.5px;
                    }
                }

                @media (max-width: 440px) {
                    #<?php echo esc_attr( $section_id ); ?> h2 {
                        font-size: clamp(28px, 9vw, 36px);
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__control {
                        flex-wrap: wrap;
                        gap: 6px 10px;
                        padding: 14px 0;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-dropdown__label {
                        min-width: 100%;
                        margin-bottom: 2px;
                    }
                }

                @media (prefers-reduced-motion: reduce) {
                    #<?php echo esc_attr( $section_id ); ?> *,
                    #<?php echo esc_attr( $section_id ); ?> .ya-reveal {
                        transition-duration: .01ms !important;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-reveal {
                        opacity: 1;
                        transform: none;
                    }
                }
            </style>

            <div class="ya-inner ya-selector-grid">
                <div class="ya-selector-copy ya-reveal">
                    <h2>
                        <span><?php esc_html_e( "Let's choose", 'trek-content-manager' ); ?></span><br />
                        <span><?php esc_html_e( 'your story', 'trek-content-manager' ); ?></span><br />
                        <span class="ya-header-accent"><?php esc_html_e( 'from here.', 'trek-content-manager' ); ?></span>
                    </h2>
                    <?php if ( ! empty( $atts['subtitle'] ) ) : ?>
                        <p class="ya-selector-subtitle">
                            <?php echo esc_html( $atts['subtitle'] ); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="ya-choice-box ya-reveal">
                    <!-- 3 MULTI-SELECT DROPDOWNS -->
                    <div class="ya-dropdown-group">
                        <!-- CATEGORY 1: EXPLORE -->
                        <?php if ( ! empty( $categories['explore'] ) ) : ?>
                            <div class="ya-dropdown" data-category="explore">
                                <div class="ya-dropdown__control" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                    <span class="ya-dropdown__label"><?php echo esc_html( $categories['explore']['label'] ?? __( 'I Want to Explore', 'trek-content-manager' ) ); ?></span>
                                    <div class="ya-dropdown__text-wrap">
                                        <span class="ya-dropdown__placeholder"><?php esc_html_e( 'Select interest...', 'trek-content-manager' ); ?></span>
                                        <span class="ya-dropdown__value" style="display:none;"></span>
                                        <span class="ya-dropdown__badge" style="display:none;">0</span>
                                    </div>
                                    <svg class="ya-dropdown__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                                <div class="ya-dropdown__menu" role="listbox">
                                    <div class="ya-dropdown__menu-top">
                                        <span class="ya-dropdown__menu-title"><?php echo esc_html( $categories['explore']['label'] ?? __( 'I Want to Explore', 'trek-content-manager' ) ); ?></span>
                                        <button type="button" class="ya-dropdown__clear-btn" data-category="explore">
                                            <?php esc_html_e( 'Clear', 'trek-content-manager' ); ?>
                                        </button>
                                    </div>
                                    <?php foreach ( $categories['explore']['options'] as $key => $label ) : ?>
                                        <label class="ya-dropdown__option" data-key="<?php echo esc_attr( $key ); ?>">
                                            <input type="checkbox" class="ya-dropdown__checkbox" value="<?php echo esc_attr( $key ); ?>" />
                                            <span class="ya-dropdown__check-custom">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </span>
                                            <span class="ya-dropdown__opt-text"><?php echo esc_html( $label ); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- CATEGORY 2: FEEL -->
                        <?php if ( ! empty( $categories['feel'] ) ) : ?>
                            <div class="ya-dropdown" data-category="feel">
                                <div class="ya-dropdown__control" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                    <span class="ya-dropdown__label"><?php echo esc_html( $categories['feel']['label'] ?? __( 'I Want to Feel', 'trek-content-manager' ) ); ?></span>
                                    <div class="ya-dropdown__text-wrap">
                                        <span class="ya-dropdown__placeholder"><?php esc_html_e( 'Select feeling...', 'trek-content-manager' ); ?></span>
                                        <span class="ya-dropdown__value" style="display:none;"></span>
                                        <span class="ya-dropdown__badge" style="display:none;">0</span>
                                    </div>
                                    <svg class="ya-dropdown__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                                <div class="ya-dropdown__menu" role="listbox">
                                    <div class="ya-dropdown__menu-top">
                                        <span class="ya-dropdown__menu-title"><?php echo esc_html( $categories['feel']['label'] ?? __( 'I Want to Feel', 'trek-content-manager' ) ); ?></span>
                                        <button type="button" class="ya-dropdown__clear-btn" data-category="feel">
                                            <?php esc_html_e( 'Clear', 'trek-content-manager' ); ?>
                                        </button>
                                    </div>
                                    <?php foreach ( $categories['feel']['options'] as $key => $label ) : ?>
                                        <label class="ya-dropdown__option" data-key="<?php echo esc_attr( $key ); ?>">
                                            <input type="checkbox" class="ya-dropdown__checkbox" value="<?php echo esc_attr( $key ); ?>" />
                                            <span class="ya-dropdown__check-custom">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </span>
                                            <span class="ya-dropdown__opt-text"><?php echo esc_html( $label ); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- CATEGORY 3: PACE -->
                        <?php if ( ! empty( $categories['pace'] ) ) : ?>
                            <div class="ya-dropdown" data-category="pace">
                                <div class="ya-dropdown__control" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                                    <span class="ya-dropdown__label"><?php echo esc_html( $categories['pace']['label'] ?? __( 'My Ideal Pace', 'trek-content-manager' ) ); ?></span>
                                    <div class="ya-dropdown__text-wrap">
                                        <span class="ya-dropdown__placeholder"><?php esc_html_e( 'Select your pace...', 'trek-content-manager' ); ?></span>
                                        <span class="ya-dropdown__value" style="display:none;"></span>
                                        <span class="ya-dropdown__badge" style="display:none;">0</span>
                                    </div>
                                    <svg class="ya-dropdown__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                                <div class="ya-dropdown__menu" role="listbox">
                                    <div class="ya-dropdown__menu-top">
                                        <span class="ya-dropdown__menu-title"><?php echo esc_html( $categories['pace']['label'] ?? __( 'My Ideal Pace', 'trek-content-manager' ) ); ?></span>
                                        <button type="button" class="ya-dropdown__clear-btn" data-category="pace">
                                            <?php esc_html_e( 'Clear', 'trek-content-manager' ); ?>
                                        </button>
                                    </div>
                                    <?php foreach ( $categories['pace']['options'] as $key => $label ) : ?>
                                        <label class="ya-dropdown__option" data-key="<?php echo esc_attr( $key ); ?>">
                                            <input type="checkbox" class="ya-dropdown__checkbox" value="<?php echo esc_attr( $key ); ?>" />
                                            <span class="ya-dropdown__check-custom">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </span>
                                            <span class="ya-dropdown__opt-text"><?php echo esc_html( $label ); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Clear All Toolbar (appears when any options are selected) -->
                    <div class="ya-clear-bar" id="<?php echo esc_attr( $uid ); ?>_clear_bar" style="display: none;">
                        <span class="ya-selected-count" id="<?php echo esc_attr( $uid ); ?>_selected_count"></span>
                        <button type="button" class="ya-clear-all-btn" id="<?php echo esc_attr( $uid ); ?>_clear_all_btn" aria-label="<?php esc_attr_e( 'Clear all selections', 'trek-content-manager' ); ?>">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span><?php esc_html_e( 'Clear All Options', 'trek-content-manager' ); ?></span>
                        </button>
                    </div>

                    <!-- YOUR JOURNEY MATCH RESULT -->
                    <div class="ya-match-result" id="<?php echo esc_attr( $uid ); ?>_match_card" role="region" aria-live="polite" style="display: none;">
                        <!-- Preview Card for Matched Journey (Image, Title & Meta Pills) -->
                        <div class="ya-match-preview" id="<?php echo esc_attr( $uid ); ?>_preview_box" style="display: none;">
                            <div class="ya-match-thumb" id="<?php echo esc_attr( $uid ); ?>_match_thumb">
                                <img id="<?php echo esc_attr( $uid ); ?>_match_img" src="" alt="" loading="lazy" />
                                <span class="ya-match-tag"><?php esc_html_e( 'RECOMMENDED', 'trek-content-manager' ); ?></span>
                            </div>
                            <div class="ya-match-details">
                                <h3 class="ya-match-title" id="<?php echo esc_attr( $uid ); ?>_match_title"></h3>
                                <div class="ya-match-meta-line" id="<?php echo esc_attr( $uid ); ?>_match_meta">
                                    <span class="ya-match-meta-pill">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <span id="<?php echo esc_attr( $uid ); ?>_region_text"></span>
                                    </span>
                                    <span class="ya-match-meta-pill">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span id="<?php echo esc_attr( $uid ); ?>_duration_text"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Default Prompt State (before all 3 categories selected) -->
                        <div class="ya-match-prompt" id="<?php echo esc_attr( $uid ); ?>_match_prompt" style="display: none;">
                            <span class="ya-match-eyebrow" id="<?php echo esc_attr( $uid ); ?>_match_eyebrow">
                                <?php esc_html_e( 'DISCOVER YOUR BESPOKE JOURNEY', 'trek-content-manager' ); ?>
                            </span>
                            <h3 class="ya-match-prompt-title" id="<?php echo esc_attr( $uid ); ?>_prompt_title">
                                <?php esc_html_e( 'Choose Your Travel Style', 'trek-content-manager' ); ?>
                            </h3>
                        </div>

                        <!-- Reassuring Message Box -->
                        <div class="ya-match-message-box">
                            <p class="ya-match-callout" id="<?php echo esc_attr( $uid ); ?>_match_callout">
                                <?php esc_html_e( 'Select your preferences in all 3 categories above (Explore, Feel, and Pace) to reveal your personalized Himalayan match.', 'trek-content-manager' ); ?>
                            </p>
                        </div>
                    </div>

                    <!-- EMAIL COLLECTION: TAKE YOUR JOURNEY WITH YOU -->
                    <div class="ya-email-collect" id="<?php echo esc_attr( $uid ); ?>_email_box" style="display: none;">
                        <form class="ya-journey-email-form" id="<?php echo esc_attr( $uid ); ?>_email_form" data-ajax-url="<?php echo esc_url( $ajax_url ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" onsubmit="return false;">
                            <input type="hidden" id="<?php echo esc_attr( $uid ); ?>_hidden_dest_id" name="destination_id" value="0" />
                            <div class="ya-email-group">
                                <input type="email" id="<?php echo esc_attr( $uid ); ?>_email_input" name="email" class="ya-email-input" placeholder="<?php esc_attr_e( 'Enter your email address...', 'trek-content-manager' ); ?>" required autocomplete="email" />
                                <button type="submit" id="<?php echo esc_attr( $uid ); ?>_submit_btn" class="ya-hero-cta ya-email-btn" aria-label="<?php esc_attr_e( 'Get Full Journey Guide', 'trek-content-manager' ); ?>">
                                    <span class="ya-hero-cta__text ya-btn-text"><?php esc_html_e( 'GET FULL JOURNEY GUIDE', 'trek-content-manager' ); ?></span>
                                    <span class="ya-hero-cta__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14M13 6l6 6-6 6"></path>
                                        </svg>
                                    </span>
                                    <span class="ya-btn-loader" style="display:none;" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div id="<?php echo esc_attr( $uid ); ?>_feedback" class="ya-email-feedback" style="display:none;" role="alert"></div>
                        </form>
                    </div>

                </div>
            </div>

            <script>
                (function() {
                    const section = document.getElementById('<?php echo esc_js( $section_id ); ?>');
                    if (!section) return;

                    const uid = '<?php echo esc_js( $uid ); ?>';
                    const ajaxUrl = '<?php echo esc_url( $ajax_url ); ?>';
                    const nonce = '<?php echo esc_js( $nonce ); ?>';

                    const resultCard = document.getElementById(uid + '_match_card');
                    const emailBox = document.getElementById(uid + '_email_box');
                    const previewBox = document.getElementById(uid + '_preview_box');
                    const promptBox = document.getElementById(uid + '_match_prompt');
                    const thumbEl = document.getElementById(uid + '_match_thumb');
                    const imgEl = document.getElementById(uid + '_match_img');
                    const eyebrowEl = document.getElementById(uid + '_match_eyebrow');
                    const titleEl = document.getElementById(uid + '_match_title');
                    const promptTitleEl = document.getElementById(uid + '_prompt_title');
                    const metaLineEl = document.getElementById(uid + '_match_meta');
                    const regionEl = document.getElementById(uid + '_region_text');
                    const durationEl = document.getElementById(uid + '_duration_text');
                    const calloutEl = document.getElementById(uid + '_match_callout');
                    const hiddenDestId = document.getElementById(uid + '_hidden_dest_id');

                    const clearBar = document.getElementById(uid + '_clear_bar');
                    const selectedCountEl = document.getElementById(uid + '_selected_count');
                    const clearAllBtn = document.getElementById(uid + '_clear_all_btn');
                    const matchClearBtn = document.getElementById(uid + '_match_clear_btn');

                    const formEl = document.getElementById(uid + '_email_form');
                    const emailInput = document.getElementById(uid + '_email_input');
                    const submitBtn = document.getElementById(uid + '_submit_btn');
                    const btnText = submitBtn ? submitBtn.querySelector('.ya-btn-text') : null;
                    const btnLoader = submitBtn ? submitBtn.querySelector('.ya-btn-loader') : null;
                    const feedbackEl = document.getElementById(uid + '_feedback');

                    let debounceTimer = null;

                    const catLabels = {
                        explore: '<?php echo esc_js( __( 'Explore', 'trek-content-manager' ) ); ?>',
                        feel: '<?php echo esc_js( __( 'Feel', 'trek-content-manager' ) ); ?>',
                        pace: '<?php echo esc_js( __( 'Pace', 'trek-content-manager' ) ); ?>'
                    };

                    function updateClearBar() {
                        const totalChecked = section.querySelectorAll('.ya-dropdown__checkbox:checked').length;
                        const selections = getSelectedKeys();
                        const activeCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length > 0);
                        const missingCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length === 0);

                        if (clearBar) {
                            if (totalChecked > 0) {
                                clearBar.style.display = 'flex';
                                if (selectedCountEl) {
                                    if (activeCats.length < 3) {
                                        const missingNames = missingCats.map(c => catLabels[c]).join(' & ');
                                        selectedCountEl.textContent = totalChecked + ' ' + (totalChecked === 1 ? '<?php echo esc_js( __( 'preference selected', 'trek-content-manager' ) ); ?>' : '<?php echo esc_js( __( 'preferences selected', 'trek-content-manager' ) ); ?>') + ' • <?php echo esc_js( __( 'Select', 'trek-content-manager' ) ); ?> ' + missingNames + ' <?php echo esc_js( __( 'to reveal match', 'trek-content-manager' ) ); ?>';
                                    } else {
                                        selectedCountEl.textContent = totalChecked + ' <?php echo esc_js( __( 'preferences selected • Journey matched', 'trek-content-manager' ) ); ?>';
                                    }
                                }
                            } else {
                                clearBar.style.display = 'none';
                            }
                        }
                    }

                    function clearAllSelections() {
                        section.querySelectorAll('.ya-dropdown__checkbox').forEach(cb => {
                            cb.checked = false;
                        });
                        section.querySelectorAll('.ya-dropdown__option').forEach(opt => {
                            opt.classList.remove('is-checked');
                        });
                        dropdowns.forEach(dropdown => {
                            updateDropdownHeader(dropdown);
                        });
                        updateClearBar();
                        updateMatch();
                    }

                    if (clearAllBtn) {
                        clearAllBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            clearAllSelections();
                        });
                    }

                    if (matchClearBtn) {
                        matchClearBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            clearAllSelections();
                        });
                    }

                    // Category clear buttons inside dropdown menus
                    section.querySelectorAll('.ya-dropdown__clear-btn').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const cat = btn.getAttribute('data-category');
                            const dropdown = section.querySelector('.ya-dropdown[data-category="' + cat + '"]');
                            if (dropdown) {
                                dropdown.querySelectorAll('.ya-dropdown__checkbox').forEach(cb => {
                                    cb.checked = false;
                                });
                                dropdown.querySelectorAll('.ya-dropdown__option').forEach(opt => {
                                    opt.classList.remove('is-checked');
                                });
                                updateDropdownHeader(dropdown);
                                updateClearBar();
                                clearTimeout(debounceTimer);
                                debounceTimer = setTimeout(updateMatch, 150);
                            }
                        });
                    });

                    // Dropdown interaction logic
                    const dropdowns = section.querySelectorAll('.ya-dropdown');

                    dropdowns.forEach(dropdown => {
                        const control = dropdown.querySelector('.ya-dropdown__control');
                        const placeholder = dropdown.querySelector('.ya-dropdown__placeholder');
                        const valEl = dropdown.querySelector('.ya-dropdown__value');
                        const badge = dropdown.querySelector('.ya-dropdown__badge');
                        const checkboxes = dropdown.querySelectorAll('.ya-dropdown__checkbox');
                        const options = dropdown.querySelectorAll('.ya-dropdown__option');

                        // Toggle open/close
                        control.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const isOpen = dropdown.classList.contains('is-open');

                            // Close all other dropdowns
                            dropdowns.forEach(d => {
                                if (d !== dropdown) {
                                    d.classList.remove('is-open');
                                    d.querySelector('.ya-dropdown__control').setAttribute('aria-expanded', 'false');
                                }
                            });

                            dropdown.classList.toggle('is-open', !isOpen);
                            control.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
                        });

                        // Checkbox changes
                        options.forEach(option => {
                            option.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const checkbox = option.querySelector('.ya-dropdown__checkbox');
                                checkbox.checked = !checkbox.checked;
                                option.classList.toggle('is-checked', checkbox.checked);

                                updateDropdownHeader(dropdown);
                                updateClearBar();

                                clearTimeout(debounceTimer);
                                debounceTimer = setTimeout(updateMatch, 200);
                            });
                        });
                    });

                    function updateDropdownHeader(dropdown) {
                        const placeholder = dropdown.querySelector('.ya-dropdown__placeholder');
                        const valEl = dropdown.querySelector('.ya-dropdown__value');
                        const badge = dropdown.querySelector('.ya-dropdown__badge');
                        const checkedOptions = dropdown.querySelectorAll('.ya-dropdown__option.is-checked');

                        if (checkedOptions.length === 0) {
                            placeholder.style.display = 'inline';
                            valEl.style.display = 'none';
                            badge.style.display = 'none';
                        } else if (checkedOptions.length === 1) {
                            const firstLabel = checkedOptions[0].querySelector('.ya-dropdown__opt-text').textContent.trim();
                            placeholder.style.display = 'none';
                            valEl.textContent = firstLabel;
                            valEl.style.display = 'inline';
                            badge.style.display = 'none';
                        } else {
                            const firstLabel = checkedOptions[0].querySelector('.ya-dropdown__opt-text').textContent.trim();
                            placeholder.style.display = 'none';
                            valEl.textContent = firstLabel;
                            valEl.style.display = 'inline';
                            badge.textContent = '+' + (checkedOptions.length - 1);
                            badge.style.display = 'inline-flex';
                        }
                    }

                    // Auto-close any dropdown when clicking outside of that specific dropdown box
                    document.addEventListener('click', function(e) {
                        dropdowns.forEach(d => {
                            if (!d.contains(e.target)) {
                                d.classList.remove('is-open');
                                d.querySelector('.ya-dropdown__control').setAttribute('aria-expanded', 'false');
                            }
                        });
                    });

                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            dropdowns.forEach(d => {
                                d.classList.remove('is-open');
                                d.querySelector('.ya-dropdown__control').setAttribute('aria-expanded', 'false');
                            });
                        }
                    });

                    function getSelectedKeys() {
                        const selections = {
                            explore: [],
                            feel: [],
                            pace: []
                        };

                        section.querySelectorAll('.ya-dropdown').forEach(dropdown => {
                            const cat = dropdown.getAttribute('data-category');
                            if (cat && selections[cat]) {
                                dropdown.querySelectorAll('.ya-dropdown__checkbox:checked').forEach(cb => {
                                    selections[cat].push(cb.value);
                                });
                            }
                        });

                        return selections;
                    }

                    function updateMatch() {
                        const selections = getSelectedKeys();
                        const activeCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length > 0);
                        const missingCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length === 0);

                        // If not all 3 categories are selected -> hide results & email box
                        if (activeCats.length < 3) {
                            if (resultCard) resultCard.style.display = 'none';
                            if (emailBox) emailBox.style.display = 'none';
                            if (previewBox) previewBox.style.display = 'none';
                            if (promptBox) promptBox.style.display = 'none';
                            if (hiddenDestId) hiddenDestId.value = '0';
                            return;
                        }

                        // All 3 categories are selected -> show both result card and email collection box
                        if (resultCard) {
                            resultCard.style.display = 'block';
                            resultCard.classList.add('is-loading');
                        }
                        if (emailBox) {
                            emailBox.style.display = 'block';
                        }

                        const formData = new FormData();
                        formData.append('action', 'ya_match_journey');
                        formData.append('nonce', nonce);
                        formData.append('explore', selections.explore.join(','));
                        formData.append('feel', selections.feel.join(','));
                        formData.append('pace', selections.pace.join(','));

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(res => {
                            if (resultCard) {
                                resultCard.classList.remove('is-loading');
                            }
                            if (res && res.success && res.data) {
                                const d = res.data;
                                if (promptBox) promptBox.style.display = 'none';
                                if (previewBox) previewBox.style.display = 'flex';
                                if (titleEl && d.title) titleEl.textContent = d.title;
                                if (regionEl && d.region) regionEl.textContent = d.region;
                                if (durationEl && d.duration) durationEl.textContent = d.duration;
                                if (imgEl && thumbEl) {
                                    if (d.image_url) {
                                        imgEl.src = d.image_url;
                                        imgEl.alt = d.title || '';
                                        thumbEl.style.display = 'block';
                                    } else {
                                        thumbEl.style.display = 'none';
                                    }
                                }
                                if (calloutEl) {
                                    calloutEl.textContent = '<?php echo esc_js( __( "We have matched your private journey. Enter your email below to receive the complete day-by-day itinerary, lodge portfolio, and bespoke details.", 'trek-content-manager' ) ); ?>';
                                }
                                if (hiddenDestId && d.destination_id) hiddenDestId.value = d.destination_id;
                            }
                        })
                        .catch(() => {
                            if (resultCard) resultCard.classList.remove('is-loading');
                        });
                    }

                    // Email Form Submission
                    if (formEl && submitBtn && emailInput) {
                        formEl.addEventListener('submit', function(e) {
                            e.preventDefault();

                            const selections = getSelectedKeys();
                            const activeCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length > 0);

                            if (activeCats.length < 3) {
                                const missingCats = ['explore', 'feel', 'pace'].filter(c => selections[c].length === 0);
                                const missingNames = missingCats.map(c => catLabels[c]).join(' & ');
                                showFeedback('<?php echo esc_js( __( 'Please select your preferred', 'trek-content-manager' ) ); ?> ' + missingNames + ' <?php echo esc_js( __( 'above before requesting your guide.', 'trek-content-manager' ) ); ?>', 'error');
                                return;
                            }

                            const email = emailInput.value.trim();
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!email || !emailRegex.test(email)) {
                                showFeedback('<?php echo esc_js( __( 'Please enter a valid email address.', 'trek-content-manager' ) ); ?>', 'error');
                                emailInput.focus();
                                return;
                            }

                            setBtnLoading(true);
                            if (feedbackEl) feedbackEl.style.display = 'none';

                            const formData = new FormData();
                            formData.append('action', 'ya_submit_journey_lead');
                            formData.append('nonce', nonce);
                            formData.append('email', email);
                            formData.append('explore', selections.explore.join(','));
                            formData.append('feel', selections.feel.join(','));
                            formData.append('pace', selections.pace.join(','));

                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(res => {
                                setBtnLoading(false);
                                if (res && res.success) {
                                    showFeedback('<?php echo esc_js( __( 'Your journey dossier has been prepared! Our Himalayan specialist will email you the full itinerary breakdown shortly.', 'trek-content-manager' ) ); ?>', 'success');
                                    emailInput.value = '';
                                } else {
                                    showFeedback(res.data?.message || '<?php echo esc_js( __( 'Unable to process your request. Please try again.', 'trek-content-manager' ) ); ?>', 'error');
                                }
                            })
                            .catch(() => {
                                setBtnLoading(false);
                                showFeedback('<?php echo esc_js( __( 'Network error occurred. Please try again.', 'trek-content-manager' ) ); ?>', 'error');
                            });
                        });
                    }

                    function setBtnLoading(isLoading) {
                        if (!submitBtn) return;
                        submitBtn.disabled = isLoading;
                        if (btnText) btnText.style.display = isLoading ? 'none' : 'inline';
                        if (btnLoader) btnLoader.style.display = isLoading ? 'inline-block' : 'none';
                    }

                    function showFeedback(msg, type) {
                        if (!feedbackEl) return;
                        feedbackEl.textContent = msg;
                        feedbackEl.className = 'ya-email-feedback is-' + type;
                        feedbackEl.style.display = 'block';
                    }

                    // Reveal on scroll
                    const revealItems = section.querySelectorAll('.ya-reveal');
                    if (!('IntersectionObserver' in window)) {
                        revealItems.forEach(item => item.classList.add('is-visible'));
                    } else {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('is-visible');
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, { threshold: 0.1, rootMargin: '0px 0px -35px' });
                        revealItems.forEach(item => observer.observe(item));
                    }
                })();
            </script>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handler: Calculate and return the best destination match dynamically
     */
    public static function ajax_match_journey() {
        check_ajax_referer( 'trek_journey_lead_nonce', 'nonce' );

        if ( ! class_exists( 'Trek_Journey_Matcher' ) ) {
            wp_send_json_error( array( 'message' => __( 'Journey matcher engine unavailable.', 'trek-content-manager' ) ) );
        }

        $explore_raw = sanitize_text_field( $_POST['explore'] ?? '' );
        $feel_raw    = sanitize_text_field( $_POST['feel'] ?? '' );
        $pace_raw    = sanitize_text_field( $_POST['pace'] ?? '' );

        $selections = array(
            'explore' => ! empty( $explore_raw ) ? explode( ',', $explore_raw ) : array(),
            'feel'    => ! empty( $feel_raw ) ? explode( ',', $feel_raw ) : array(),
            'pace'    => ! empty( $pace_raw ) ? explode( ',', $pace_raw ) : array(),
        );

        $result       = Trek_Journey_Matcher::match( $selections );
        $display_data = Trek_Journey_Matcher::get_destination_display_data( $result['destination_id'], $result );

        wp_send_json_success( $display_data );
    }

    public static function ajax_submit_journey_lead() {
        check_ajax_referer( 'trek_journey_lead_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'trek-content-manager' ) ) );
        }

        $explore_raw = sanitize_text_field( $_POST['explore'] ?? '' );
        $feel_raw    = sanitize_text_field( $_POST['feel'] ?? '' );
        $pace_raw    = sanitize_text_field( $_POST['pace'] ?? '' );

        $selections = array(
            'explore' => ! empty( $explore_raw ) ? explode( ',', $explore_raw ) : array(),
            'feel'    => ! empty( $feel_raw ) ? explode( ',', $feel_raw ) : array(),
            'pace'    => ! empty( $pace_raw ) ? explode( ',', $pace_raw ) : array(),
        );

        // Security: NEVER trust client-side score or destination ID. Recalculate match server-side!
        if ( ! class_exists( 'Trek_Journey_Matcher' ) ) {
            wp_send_json_error( array( 'message' => __( 'Journey matcher engine unavailable.', 'trek-content-manager' ) ) );
        }

        $match_result = Trek_Journey_Matcher::match( $selections );
        $dest_data    = Trek_Journey_Matcher::get_destination_display_data( $match_result['destination_id'], $match_result );

        if ( empty( $dest_data ) || empty( $dest_data['destination_id'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Unable to find matching destination.', 'trek-content-manager' ) ) );
        }

        $destination_id    = $dest_data['destination_id'];
        $destination_title = $dest_data['title'];
        $destination_url   = $dest_data['permalink'];
        $destination_img   = $dest_data['image_url'];
        $region_name       = $dest_data['region'];
        $duration          = $dest_data['duration'];
        $destination_intro = $dest_data['excerpt'];
        $score             = $dest_data['score'];

        $all_matched_labels = array_merge(
            $dest_data['explore_matches'] ?? array(),
            $dest_data['feel_matches'] ?? array(),
            $dest_data['pace_matches'] ?? array()
        );
        $matched_tags_str = ! empty( $all_matched_labels ) ? implode( ' · ', $all_matched_labels ) : __( 'Curated Himalayan Journey', 'trek-content-manager' );

        // 1. Store Lead in WordPress Database as `trek_enquiry`
        $post_title = sprintf( 'Journey Lead: %s (%s)', $destination_title, $email );
        $post_content = sprintf(
            "Traveller Email: %s\nMatched Journey: %s\nDestination ID: %d\nMatch Score: %d\nRegion: %s\nDuration: %s\nExplore: %s\nFeel: %s\nPace: %s\nSource: Journey Selector\nTimestamp: %s\nIP: %s",
            $email,
            $destination_title,
            $destination_id,
            $score,
            $region_name,
            $duration,
            implode( ', ', $selections['explore'] ),
            implode( ', ', $selections['feel'] ),
            implode( ', ', $selections['pace'] ),
            current_time( 'mysql' ),
            sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' )
        );

        $enquiry_id = wp_insert_post( array(
            'post_type'    => 'trek_enquiry',
            'post_title'   => $post_title,
            'post_content' => $post_content,
            'post_status'  => 'private',
        ), true );

        if ( ! is_wp_error( $enquiry_id ) ) {
            update_post_meta( $enquiry_id, '_trek_enquiry_email', $email );
            update_post_meta( $enquiry_id, '_trek_enquiry_trek_title', $destination_title );
            update_post_meta( $enquiry_id, '_trek_enquiry_trek_id', $destination_id );
            update_post_meta( $enquiry_id, '_trek_enquiry_source', 'Journey Selector' );
            update_post_meta( $enquiry_id, '_trek_enquiry_match_score', $score );
            update_post_meta( $enquiry_id, '_trek_enquiry_explore', $selections['explore'] );
            update_post_meta( $enquiry_id, '_trek_enquiry_feeling', $selections['feel'] );
            update_post_meta( $enquiry_id, '_trek_enquiry_pace', $selections['pace'] );
            update_post_meta( $enquiry_id, '_trek_enquiry_date', current_time( 'mysql' ) );
        }

        // 2. Prepare and Send Customer Itinerary Email via wp_mail()
        $site_name   = get_bloginfo( 'name' ) ?: 'Yeti Adventure';
        $admin_email = get_option( 'admin_email' );

        $email_subject = sprintf( __( 'Your journey match: %s | %s', 'trek-content-manager' ), $destination_title, $site_name );

        $email_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $site_name, $admin_email ),
        );

        // Build HTML Email
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html( $email_subject ); ?></title>
        </head>
        <body style="margin:0;padding:0;background-color:#fbf9f4;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#111111;">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#fbf9f4;padding:30px 15px;">
                <tr>
                    <td align="center">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 10px 40px rgba(2,47,46,0.08);border:1px solid #e7e5e0;">
                            <!-- Brand Header -->
                            <tr>
                                <td style="background-color:#022f2e;padding:35px 35px 30px;text-align:center;">
                                    <span style="display:inline-block;color:#c7a767;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;margin-bottom:8px;">HIMALAYAN EXPEDITIONS &amp; BESPOKE TRAVEL</span>
                                    <h1 style="margin:0;color:#ffffff;font-family:Georgia,serif;font-size:28px;font-weight:400;letter-spacing:-0.5px;"><?php echo esc_html( $site_name ); ?></h1>
                                </td>
                            </tr>
                            <?php if ( ! empty( $destination_img ) ) : ?>
                            <!-- Destination Featured Image -->
                            <tr>
                                <td style="padding:0;line-height:0;">
                                    <img src="<?php echo esc_url( $destination_img ); ?>" alt="<?php echo esc_attr( $destination_title ); ?>" style="width:100%;max-height:280px;object-fit:cover;display:block;" />
                                </td>
                            </tr>
                            <?php endif; ?>
                            <!-- Intro Copy -->
                            <tr>
                                <td style="padding:35px 35px 25px;">
                                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#333333;">Dear Explorer,</p>
                                    <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#555555;">
                                        Thank you for exploring with our interactive Journey Matcher. Based on your personal travel preferences, our Himalayan specialists have matched you with:
                                    </p>

                                    <!-- Recommended Trek Card -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f5f8f7;border-left:4px solid #c7a767;border-radius:8px;margin-bottom:28px;">
                                        <tr>
                                            <td style="padding:22px 24px;">
                                                <span style="display:block;color:#c7a767;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;">YOUR JOURNEY MATCH</span>
                                                <h2 style="margin:0 0 10px;font-family:Georgia,serif;font-size:24px;color:#022f2e;line-height:1.25;"><?php echo esc_html( $destination_title ); ?></h2>
                                                
                                                <p style="margin:0 0 14px;font-size:13.5px;color:#044b49;font-weight:600;">
                                                    📍 <?php echo esc_html( $region_name ); ?> &nbsp;•&nbsp; ⏱️ <?php echo esc_html( $duration ); ?>
                                                </p>

                                                <p style="margin:0 0 16px;font-size:14.5px;line-height:1.7;color:#444444;"><?php echo esc_html( $destination_intro ); ?></p>
                                                
                                                <!-- Selected Preferences -->
                                                <div style="background:#ffffff;padding:10px 14px;border-radius:8px;border:1px solid #d8e5e2;font-size:12px;color:#022f2e;">
                                                    <strong>You selected:</strong> <?php echo esc_html( $matched_tags_str ); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- CTA Button -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:30px;">
                                        <tr>
                                            <td align="center">
                                                <a href="<?php echo esc_url( $destination_url ); ?>" style="display:inline-block;background-color:#022f2e;color:#ffffff;text-decoration:none;padding:16px 36px;border-radius:999px;font-size:14px;font-weight:700;letter-spacing:0.8px;">
                                                    VIEW YOUR JOURNEY &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#666666;">
                                        Every journey with Yeti Adventure is completely private and bespoke. We adapt routes, acclimatization days, and boutique lodge reservations around your rhythm.
                                    </p>
                                    <p style="margin:0;font-size:14px;line-height:1.7;color:#333333;">
                                        Warm regards,<br>
                                        <strong>The Mountain Expedition Team</strong><br>
                                        <span style="color:#777777;font-size:13px;"><?php echo esc_html( $site_name ); ?></span>
                                    </p>
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td style="background-color:#faf8f5;padding:20px 35px;border-top:1px solid #ece8e1;text-align:center;">
                                    <p style="margin:0;font-size:12px;color:#888888;line-height:1.5;">
                                        You received this email because you requested a personalized journey guide on <?php echo esc_html( home_url() ); ?>.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        $customer_email_body = ob_get_clean();

        // Dispatch email to customer
        wp_mail( $email, $email_subject, $customer_email_body, $email_headers );

        // 3. Dispatch Notification to Site Admin
        $admin_subject = sprintf( '[New Journey Lead] %s - %s', $destination_title, $email );
        $admin_body = sprintf(
            "A new traveller has completed the Journey Matcher:\n\n" .
            "Email: %s\n" .
            "Matched Destination: %s (ID: %d)\n" .
            "Match Score: %d\n" .
            "Region: %s\n" .
            "Duration: %s\n" .
            "Selected Preferences: %s\n" .
            "Date: %s\n\n" .
            "View Enquiry in WordPress Dashboard:\n%s\n",
            $email,
            $destination_title,
            $destination_id,
            $score,
            $region_name,
            $duration,
            $matched_tags_str,
            current_time( 'mysql' ),
            admin_url( 'post.php?post=' . $enquiry_id . '&action=edit' )
        );
        wp_mail( $admin_email, $admin_subject, $admin_body );

        wp_send_json_success( array(
            'message' => sprintf( __( '✓ Thank you! We have sent your personalized journey guide for %s to %s. Please check your inbox.', 'trek-content-manager' ), $destination_title, $email ),
        ) );
    }

    /**
     * =========================================================================
     * SHORTCODE: [ya_traveller_matcher] / [trek_traveller_matcher]
     * =========================================================================
     * 9-Step Traveller Matcher & Journey Discovery Experience.
     */
    public static function render_traveller_matcher( $atts = array() ) {
        self::$instance_counter++;
        $uid = 'ya_matcher_' . self::$instance_counter;

        $atts = shortcode_atts( array(
            'class' => '',
        ), $atts, 'ya_traveller_matcher' );

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'trek_traveller_matcher_nonce' );

        ob_start();
        ?>
        <section class="ya-matcher-section <?php echo esc_attr( $atts['class'] ); ?>" id="<?php echo esc_attr( $uid ); ?>" data-ajax-url="<?php echo esc_url( $ajax_url ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Jost:wght@400;500;600;700&display=swap');

                #<?php echo esc_attr( $uid ); ?> {
                    --ym-peach: #d1ad54;
                    --ym-peach-hover: #617654;
                    --ym-dark-card: #f9f5f0;
                    --ym-forest: #204c2b;
                    --ym-forest-light: #617654;
                    --ym-text-light: #111111;
                    --ym-text-muted: #555555;
                    --ym-gold: #d1ad54;
                    --ym-gold-hover: #617654;
                    --ym-font-serif: "Cormorant Garamond", "Newsreader", Georgia, serif;
                    --ym-font-sans: "Open Sans", "Jost", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

                    width: 100%;
                    min-height: 80vh;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    padding: clamp(28px, 4vw, 64px) 0;
                    background: #fff;
                    color: var(--ym-text-light);
                    font-family: var(--ym-font-sans);
                    box-sizing: border-box;
                    position: relative;
                    overflow-x: clip;
                }
                #<?php echo esc_attr( $uid ); ?> * {
                    box-sizing: border-box;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-inner {
                    max-width: 1400px;
                    width: 100%;
                    margin: 0 auto;
                    padding: 0 clamp(14px, 3vw, 40px);
                }

                /* Master Rounded Container (#f9f5f0 background, 700px min-height desktop, 10px radius) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-shell-card {
                    background-color: var(--ym-dark-card);
                    border-radius: 10px;
                    padding: clamp(38px, 5.5vw, 72px) clamp(20px, 4.5vw, 64px);
                    position: relative;
                    overflow: hidden;
                    min-height: 700px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    border: 1px solid rgba(0, 0, 0, 0.06);
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
                    transition: min-height .3s ease;
                }

                /* Screen Transitions */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-screen {
                    display: none;
                    opacity: 0;
                    transform: translateY(14px);
                    transition: opacity .35s ease, transform .35s ease;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-screen.is-active {
                    display: block;
                    opacity: 1;
                    transform: translateY(0);
                }

                /* ==========================================================
                   STEP 1: 2-COLUMN HERO & BROCHURE COLLAGE
                   ========================================================== */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-grid {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
                    gap: clamp(32px, 4.5vw, 68px);
                    align-items: center;
                    width: 100%;
                }

                /* Left: Text Block */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-text {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    max-width: 520px;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-title {
                    font-family: var(--ym-font-serif);
                    font-size: clamp(30px, 3vw, 56px);
                    font-weight: 500;
                    line-height: 1.15;
                    letter-spacing: -0.015em;
                    color: #111111;
                    margin: 0 0 clamp(16px, 2vw, 22px) 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-subtitle {
                    font-family: var(--ym-font-sans);
                    font-size: clamp(15px, 1.2vw, 18px);
                    font-weight: 400;
                    line-height: 1.65;
                    color: var(--ym-text-muted);
                    margin: 0 0 clamp(24px, 3vw, 36px) 0;
                    max-width: 460px;
                }

                /* Primary CTA Button (Default: #d1ad54, Hover: #617654) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach {
                    appearance: none;
                    background: var(--ym-peach);
                    border: 1px solid var(--ym-peach);
                    border-radius: 4px;
                    height: 52px;
                    padding: 0 36px;
                    font-family: var(--ym-font-sans);
                    font-size: 13.5px;
                    font-weight: 700;
                    letter-spacing: 1.8px;
                    text-transform: uppercase;
                    color: #ffffff;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    box-shadow: 0 4px 14px rgba(209, 173, 84, 0.28);
                    transition: background .25s ease, border-color .25s ease, transform .2s ease, box-shadow .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach:hover {
                    background: var(--ym-peach-hover);
                    border-color: var(--ym-peach-hover);
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(97, 118, 84, 0.32);
                    color: #ffffff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach:active {
                    transform: translateY(0);
                }

                /* Right: Tilted Overlapping Collage Stage */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-collage {
                    position: relative;
                    width: 100%;
                    max-width: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    perspective: 1000px;
                    padding: 10px 0;
                    overflow: hidden;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                    position: relative;
                    width: 530px;
                    height: 390px;
                    transform-origin: center center;
                    transition: transform .3s ease;
                }

                /* Shared Brochure Card Attributes */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure {
                    position: absolute;
                    border: 5px solid #ffffff;
                    border-radius: 6px;
                    overflow: hidden;
                    background: #ffffff;
                    box-shadow: 0 14px 34px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
                    transition: transform .35s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow .35s ease;
                    user-select: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure:hover {
                    box-shadow: 0 20px 44px rgba(0, 0, 0, 0.18);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure__inner {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                }

                /* Brochure Card Background Image */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure__user-img {
                    position: absolute;
                    inset: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    z-index: 1;
                    transition: transform .45s cubic-bezier(0.2, 0.8, 0.2, 1);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure:hover .ya-mat-brochure__user-img {
                    transform: scale(1.06);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure__user-img:not([src]),
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure__user-img[src=""] {
                    display: none !important;
                }

                /* Card 1: Front Left "TRAVEL" Brochure */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card1 {
                    width: 224px;
                    height: 300px;
                    left: 8px;
                    top: 48px;
                    transform: rotate(-7deg);
                    z-index: 4;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card1:hover {
                    transform: rotate(-5deg) translateY(-6px) scale(1.03);
                    z-index: 8;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-art {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    z-index: 2;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                    background: linear-gradient(180deg, rgba(16, 47, 53, 0.5) 0%, rgba(16, 47, 53, 0.15) 30%, rgba(16, 47, 53, 0.45) 65%, rgba(16, 47, 53, 0.88) 100%);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-top {
                    padding: 16px 14px 0;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-brand {
                    font-family: var(--ym-font-sans);
                    font-size: 8.5px;
                    font-weight: 700;
                    letter-spacing: 1.8px;
                    color: #ffffff;
                    text-transform: uppercase;
                    background: rgba(0, 0, 0, 0.35);
                    backdrop-filter: blur(4px);
                    -webkit-backdrop-filter: blur(4px);
                    padding: 4px 10px;
                    border-radius: 20px;
                    display: inline-block;
                    border: 1px solid rgba(255, 255, 255, 0.28);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-mid {
                    text-align: center;
                    position: relative;
                    padding: 10px 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-mountain {
                    display: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-title {
                    font-family: var(--ym-font-serif);
                    font-size: 34px;
                    font-weight: 700;
                    letter-spacing: 2.8px;
                    color: #ffffff;
                    position: relative;
                    z-index: 2;
                    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.75);
                    margin: 0;
                    line-height: 1;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-bottom {
                    padding: 10px 14px 12px;
                    text-align: center;
                    background: rgba(32, 76, 43, 0.9);
                    backdrop-filter: blur(4px);
                    -webkit-backdrop-filter: blur(4px);
                    border-top: 1px solid rgba(255, 255, 255, 0.15);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card1-url {
                    font-family: var(--ym-font-sans);
                    font-size: 8.5px;
                    letter-spacing: 1px;
                    color: rgba(255, 255, 255, 0.95);
                    display: block;
                    font-weight: 600;
                }

                /* Card 2: Top Center Scenic Brochure (Behind) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card2 {
                    width: 200px;
                    height: 255px;
                    left: 172px;
                    top: 4px;
                    transform: rotate(4deg);
                    z-index: 2;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card2:hover {
                    transform: rotate(3deg) translateY(-6px) scale(1.03);
                    z-index: 8;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card2-art {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    z-index: 2;
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-end;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card2-visual {
                    flex-grow: 1;
                    background: transparent;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card2-body {
                    background: rgba(255, 255, 255, 0.92);
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                    padding: 14px 14px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    border-top: 1px solid rgba(0, 0, 0, 0.06);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card2-body p {
                    font-family: var(--ym-font-serif);
                    font-size: 11.5px;
                    font-weight: 600;
                    line-height: 1.4;
                    color: #111111;
                    margin: 0;
                }

                /* Card 3: Right "Which Traveller Are You?" Brochure */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card3 {
                    width: 218px;
                    height: 295px;
                    left: 288px;
                    top: 50px;
                    transform: rotate(12deg);
                    z-index: 5;
                    background: #1c4b2a;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card3:hover {
                    transform: rotate(10deg) translateY(-6px) scale(1.03);
                    z-index: 8;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card3-content {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    z-index: 2;
                    background: rgba(28, 75, 42, 0.15);
                 
                    padding: 22px 18px 18px;
                    display: flex;
                    flex-direction: column;
                    box-sizing: border-box;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card3-title {
                    font-family: var(--ym-font-serif);
                    font-size: 18px;
                    font-weight: 700;
                    line-height: 1.25;
                    color: #ffffff;
                    margin: 0 0 10px 0;
                    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card3-desc,
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure__which-desc {
                    font-family: var(--ym-font-sans);
                    font-size: 9.5px;
                    line-height: 1.5;
                    color: rgba(255, 255, 255, 0.88);
                    margin: 0 0 auto 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card3-footer {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                    padding-top: 14px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card3-footer span {
                    font-family: var(--ym-font-sans);
                    font-size: 8.5px;
                    line-height: 1.5;
                    color: rgba(255, 255, 255, 0.78);
                    font-weight: 500;
                }

                /* Card 4: Route Itinerary Card (Bottom Behind) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card4 {
                    width: 195px;
                    height: 225px;
                    left: 145px;
                    top: 175px;
                    transform: rotate(-4deg);
                    z-index: 1;
                    background: #ffffff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-brochure--card4:hover {
                    transform: rotate(-3deg) translateY(-6px) scale(1.03);
                    z-index: 8;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-content {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    z-index: 2;
                    padding: 24px 18px 18px;
                    display: flex;
                    flex-direction: column;
                    box-sizing: border-box;
              
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-lines {
                    display: flex;
                    flex-direction: column;
                    gap: 7px;
                    margin-bottom: 24px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-line {
                    height: 5px;
                    background: rgba(32, 76, 43, 0.18);
                    border-radius: 3px;
                    display: block;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-line.short { width: 45%; }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-line.medium { width: 75%; }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-card4-svg {
                    width: 100%;
                    height: auto;
                    margin-top: auto;
                }

                /* Header / Progress Bar */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-nav-bar {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    max-width: 760px;
                    width: 100%;
                    margin: 0 auto clamp(18px, 2.5vw, 28px);
                    padding-bottom: 14px;
                    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-back-btn {
                    appearance: none;
                    background: transparent;
                    border: none;
                    padding: 8px 12px;
                    font-family: var(--ym-font-sans);
                    font-size: 13.5px;
                    font-weight: 700;
                    letter-spacing: .9px;
                    text-transform: uppercase;
                    color: #111111;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    transition: color .2s ease, transform .2s ease;
                    border-radius: 4px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-back-btn:hover {
                    color: #617654;
                    transform: translateX(-3px);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-progress-text {
                    font-size: 13.5px;
                    font-weight: 700;
                    letter-spacing: 1.8px;
                    color: #666666;
                    font-family: var(--ym-font-sans);
                    text-align: center;
                }

                /* Content Box (Centered) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-content-box {
                    max-width: 860px;
                    width: 100%;
                    margin: 0 auto;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-eyebrow {
                    display: block;
                    font-size: clamp(12px, 1vw, 13px);
                    font-weight: 700;
                    letter-spacing: 2.5px;
                    text-transform: uppercase;
                    color: #617654;
                    margin-bottom: 12px;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-title {
                    font-family: var(--ym-font-serif);
                    font-size: clamp(23px, 3.2vw, 42px);
                    font-weight: 500;
                    line-height: 1.25;
                    color: #111111 !important;
                    margin: 0 auto clamp(12px, 1.8vw, 16px);
                    max-width: 780px;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-subtitle {
                    font-size: clamp(14.5px, 1.15vw, 17.5px);
                    line-height: 1.6;
                    color: var(--ym-text-muted);
                    margin: 0 auto clamp(20px, 3vw, 36px);
                    max-width: 640px;
                    text-align: center;
                }

                /* 4-Option Grid (2x2 on desktop, single column centered on mobile) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-options {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 16px;
                    margin: 0 auto;
                    max-width: 760px;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option {
                    appearance: none;
                    background: #ffffff;
                    border: 1.5px solid rgba(0, 0, 0, 0.1);
                    border-radius: 8px;
                    padding: clamp(14px, 1.8vw, 20px) clamp(16px, 2vw, 24px);
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    cursor: pointer;
                    text-align: left;
                    transition: border-color .25s ease, transform .25s ease, box-shadow .25s ease, background .25s ease;
                    font-family: var(--ym-font-sans);
                    font-size: clamp(14.5px, 1.1vw, 18px);
                    font-weight: 600;
                    color: #111111;
                    min-height: 64px;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option:hover {
                    border-color: #d1ad54;
                    background: #ffffff;
                    transform: translateY(-2px);
                    box-shadow: 0 8px 24px rgba(209, 173, 84, 0.16);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option.is-selected {
                    border-color: #d1ad54;
                    box-shadow: 0 0 0 2px #d1ad54, 0 8px 24px rgba(209, 173, 84, 0.18);
                    background: #ffffff;
                    color: #111111;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option-indicator {
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    border: 1.5px solid rgba(0, 0, 0, 0.25);
                    background: #fcfcfc;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    margin-left: 14px;
                    transition: border-color .2s, background .2s;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option.is-selected .ya-mat-option-indicator {
                    border-color: #d1ad54;
                    background: #d1ad54;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option-indicator svg {
                    width: 14px;
                    height: 14px;
                    color: #ffffff;
                    opacity: 0;
                    transition: opacity .2s;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-option.is-selected .ya-mat-option-indicator svg {
                    opacity: 1;
                }

                /* Primary Generic Button (Step 8 & 9) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn {
                    appearance: none;
                    background: var(--ym-peach);
                    border: 1px solid var(--ym-peach);
                    border-radius: 4px;
                    height: 52px;
                    padding: 0 36px;
                    font-family: var(--ym-font-sans);
                    font-size: 13.5px;
                    font-weight: 700;
                    letter-spacing: 1.5px;
                    text-transform: uppercase;
                    color: #ffffff;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    text-decoration: none;
                    box-shadow: 0 4px 14px rgba(209, 173, 84, 0.28);
                    transition: background .25s ease, border-color .25s ease, transform .2s ease, box-shadow .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn:hover {
                    background: var(--ym-peach-hover);
                    border-color: var(--ym-peach-hover);
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(97, 118, 84, 0.32);
                    color: #ffffff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn:active {
                    transform: translateY(0);
                }

                /* Email Screen (Centered) */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-email-form {
                    max-width: 460px;
                    width: 100%;
                    margin: 0 auto;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-input-group {
                    position: relative;
                    margin-bottom: 14px;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-email-input {
                    width: 100%;
                    height: 54px;
                    padding: 0 20px;
                    font-family: var(--ym-font-sans);
                    font-size: 16px;
                    color: #111111;
                    background: #ffffff;
                    border: 1.5px solid rgba(0, 0, 0, 0.15);
                    border-radius: 6px;
                    outline: none;
                    transition: border-color .25s, box-shadow .25s;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-email-input:focus {
                    border-color: #d1ad54;
                    box-shadow: 0 0 0 3px rgba(209, 173, 84, 0.2);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-error-msg {
                    color: #b32d2e;
                    font-size: 13px;
                    font-weight: 600;
                    text-align: center;
                    margin-top: 6px;
                    display: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-privacy {
                    font-size: 13px;
                    color: var(--ym-text-muted);
                    line-height: 1.5;
                    margin-top: 16px;
                    text-align: center;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-loading {
                    display: none;
                    text-align: center;
                    padding: 24px 0;
                    color: #d1ad54;
                    font-weight: 600;
                    font-size: 16px;
                }

                /* Step 9: Results Carousel / Slider */
                #<?php echo esc_attr( $uid ); ?> .ya-mat-results-wrap {
                    max-width: 1240px;
                    width: 100%;
                    margin: 0 auto;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-wrapper {
                    position: relative;
                    margin: clamp(24px, 3.5vw, 36px) auto clamp(28px, 4vw, 40px);
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel {
                    display: flex;
                    gap: clamp(14px, 2vw, 24px);
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    scroll-behavior: smooth;
                    padding: 10px 4px 24px;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                    scrollbar-color: var(--ym-forest) rgba(16, 47, 53, 0.08);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel::-webkit-scrollbar {
                    height: 6px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel::-webkit-scrollbar-thumb {
                    background: var(--ym-forest);
                    border-radius: 999px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel::-webkit-scrollbar-track {
                    background: rgba(16, 47, 53, 0.08);
                    border-radius: 999px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel .ya-col-card {
                    flex: 0 0 350px;
                    min-width: 280px;
                    max-width: 350px;
                    scroll-snap-align: start;
                    scroll-snap-stop: always;
                    display: flex;
                    flex-direction: column;
                    border-radius: 8px;
                    overflow: hidden;
                    background: #ffffff;
                    border: 2px solid rgba(97, 118, 84, 0.2);
                    box-shadow: 0 4px 20px rgba(7, 94, 84, 0.05);
                    transition: transform .35s cubic-bezier(.22, 1, .36, 1), box-shadow .35s ease, border-color .35s ease;
                    text-decoration: none;
                    color: inherit;
                    position: relative;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel .ya-col-card:hover {
                    transform: translateY(-6px);
                    border-color: #d1ad54;
                    box-shadow: 0 20px 52px rgba(7, 94, 84, 0.14);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 46px;
                    height: 46px;
                    border-radius: 50%;
                    background: #ffffff;
                    border: 1px solid rgba(16, 47, 53, 0.16);
                    box-shadow: 0 6px 20px rgba(16, 47, 53, 0.12);
                    color: #111111;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    z-index: 10;
                    transition: all .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn svg {
                    width: 20px;
                    height: 20px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn:hover {
                    background: var(--ym-forest);
                    color: #ffffff;
                    border-color: var(--ym-forest);
                    transform: translateY(-50%) scale(1.08);
                    box-shadow: 0 8px 24px rgba(97, 117, 85, 0.3);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn--prev {
                    left: -22px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn--next {
                    right: -22px;
                }

                #<?php echo esc_attr( $uid ); ?> .ya-col-card__media {
                    position: relative;
                    height: 236px;
                    overflow: hidden;
                    flex-shrink: 0;
                    background: #102f35;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__img {
                    width: 100% !important;
                    height: 100% !important;
                    object-fit: cover !important;
                    display: block !important;
                    transition: transform .65s cubic-bezier(.22, 1, .36, 1);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__img {
                    transform: scale(1.06);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-badge {
                    position: absolute;
                    top: 14px;
                    right: 14px;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 5px 12px 5px 8px;
                    background: #cfab55;
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                    border: 1px solid rgba(255, 255, 255, 0.28);
                    border-radius: 50px;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
                    z-index: 3;
                    transition: background .25s ease, border-color .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__top-badge {
                    background: #cfab55;
                    border-color: rgba(255, 255, 255, 0.45);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-logo {
                    width: 15px !important;
                    height: 15px !important;
                    max-width: 15px !important;
                    max-height: 15px !important;
                    object-fit: contain !important;
                    display: block !important;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-sep {
                    color: rgba(255, 255, 255, 0.4);
                    font-size: 11px;
                    font-weight: 300;
                    line-height: 1;
                    user-select: none;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__top-cat {
                    font-family: var(--ym-font-sans);
                    font-size: 10.5px;
                    font-weight: 700;
                    letter-spacing: .8px;
                    text-transform: uppercase;
                    color: #ffffff;
                    line-height: 1;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__body {
                    padding: 22px 20px 20px;
                    display: flex;
                    flex-direction: column;
                    flex-grow: 1;
                    position: relative;
                    background: #ffffff;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-bottom: 8px;
                    background: transparent !important;
                    padding: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                    position: static !important;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc-icon {
                    width: 13px;
                    height: 13px;
                    color: #617654;
                    flex-shrink: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__loc-text {
                    font-family: var(--ym-font-sans);
                    font-size: 11px;
                    font-weight: 600;
                    letter-spacing: .9px;
                    text-transform: uppercase;
                    color: #617654;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__title {
                    margin: 0 0 18px;
                    font-family: var(--ym-font-serif);
                    font-size: 21px;
                    font-weight: 600;
                    line-height: 1.28;
                    color: #111111;
                    transition: color .25s ease;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card:hover .ya-col-card__title {
                    color: #d1ad54;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 12px 14px;
                    margin-top: auto;
                    padding-top: 14px;
                    border-top: 1px solid #eef5f2;
                    position: relative;
                    z-index: 1;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-item {
                    flex: 1 1 calc(33.333% - 10px);
                    min-width: 80px;
                    display: flex;
                    flex-direction: column;
                    gap: 3px;
                    padding: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-head {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-meta-icon {
                    width: 12px;
                    height: 12px;
                    color: #617654;
                    flex-shrink: 0;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-label {
                    font-family: var(--ym-font-sans);
                    font-size: 9.5px;
                    font-weight: 600;
                    letter-spacing: .6px;
                    text-transform: uppercase;
                    color: #617654;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-col-card__meta-val {
                    font-family: var(--ym-font-sans);
                    font-size: 13px;
                    font-weight: 600;
                    color: #111111;
                    white-space: normal;
                    word-break: break-word;
                    line-height: 1.35;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-actions {
                    text-align: center;
                    margin-top: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 16px;
                    flex-wrap: wrap;
                    width: 100%;
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--outline {
                    background: transparent;
                    border: 1.5px solid var(--ym-peach);
                    color: var(--ym-peach);
                }
                #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--outline:hover {
                    background: var(--ym-peach-hover);
                    border-color: var(--ym-peach-hover);
                    color: #ffffff;
                }

                /* ==========================================================
                   RESPONSIVE BREAKPOINTS (MOBILE-FIRST CENTERED)
                   ========================================================== */
                @media (max-width: 1280px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn--prev { left: 4px; }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn--next { right: 4px; }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                        transform: scale(0.92);
                    }
                }

                /* Tablet / Small Laptop: Stack Hero, Center Text, Scale Collage */
                @media (max-width: 1024px) {
                    #<?php echo esc_attr( $uid ); ?> {
                        min-height: auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-shell-card {
                        min-height: auto;
                        padding: 48px 32px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-grid {
                        grid-template-columns: 1fr;
                        gap: 36px;
                        text-align: center;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-text {
                        align-items: center;
                        text-align: center;
                        max-width: 620px;
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-title {
                        text-align: center;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-subtitle {
                        text-align: center;
                        margin-left: auto;
                        margin-right: auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach {
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-collage {
                        height: 350px;
                        min-height: 350px;
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                        transform: scale(0.85);
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel .ya-col-card {
                        flex: 0 0 calc(50% - 12px);
                        min-width: 270px;
                    }
                }

                /* Mobile Landscape / Small Tablet (<= 768px): Single column, centered options */
                @media (max-width: 768px) {
                    #<?php echo esc_attr( $uid ); ?> {
                        padding: 24px 0;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-shell-card {
                        min-height: auto;
                        padding: 38px 22px;
                        border-radius: 10px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-nav-bar {
                        max-width: 460px;
                        margin: 0 auto 20px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-options {
                        grid-template-columns: 1fr;
                        gap: 12px;
                        max-width: 460px;
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-option {
                        min-height: 58px;
                        padding: 14px 18px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-collage {
                        height: 290px;
                        min-height: 290px;
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                        transform: scale(0.70);
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach {
                        max-width: 320px;
                        width: 100%;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel {
                        padding: 6px 2px 18px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-cards-carousel .ya-col-card {
                        flex: 0 0 85%;
                        min-width: 250px;
                        max-width: 330px;
                        scroll-snap-align: center;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-carousel-btn {
                        display: none;
                    }
                }

                /* Mobile Portrait (<= 540px): Full mobile-first vertical stack & centering */
                @media (max-width: 540px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-actions {
                        flex-direction: column;
                        max-width: 320px;
                        margin: 24px auto 0;
                        gap: 12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-actions .ya-mat-btn {
                        width: 100%;
                        justify-content: center;
                    }
                }

                /* Mobile Small (<= 480px) */
                @media (max-width: 480px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-inner {
                        padding: 0 12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-shell-card {
                        min-height: auto;
                        padding: 28px 16px;
                        border-radius: 10px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-grid {
                        gap: 22px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-title {
                        font-size: clamp(24px, 7vw, 30px);
                        line-height: 1.2;
                        margin-bottom: 12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-subtitle {
                        font-size: 14.5px;
                        line-height: 1.55;
                        margin-bottom: 22px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-btn--peach {
                        height: 50px;
                        font-size: 13px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-collage {
                        height: 230px;
                        min-height: 230px;
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                        transform: scale(0.55);
                        margin: 0 auto;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-title {
                        font-size: clamp(21px, 6vw, 26px);
                        line-height: 1.25;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-subtitle {
                        font-size: 14px;
                        margin-bottom: 20px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-nav-bar {
                        max-width: 100%;
                        margin-bottom: 16px;
                        padding-bottom: 10px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-options {
                        max-width: 100%;
                        gap: 10px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-option {
                        min-height: 54px;
                        padding: 12px 16px;
                        font-size: 14.5px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-email-input {
                        height: 50px;
                        font-size: 15px;
                    }
                }

                /* Extra Narrow Phones (<= 360px) */
                @media (max-width: 360px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-shell-card {
                        padding: 24px 12px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-title {
                        font-size: 22px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-hero-collage {
                        height: 195px;
                        min-height: 195px;
                    }
                    #<?php echo esc_attr( $uid ); ?> .ya-mat-collage-stage {
                        transform: scale(0.46);
                    }
                }
            </style>

            <div class="ya-mat-inner">
                <div class="ya-mat-shell-card">

                <!-- STEP 1: INTRODUCTION (HERO 2-COLUMN WITH BROCHURE COLLAGE) -->
                <div class="ya-mat-screen is-active ya-mat-screen--intro" data-step="1">
                    <div class="ya-mat-hero-grid">
                        <!-- LEFT SIDE: Vertically centered text & primary button -->
                        <div class="ya-mat-hero-text">
                            <h2 class="ya-mat-hero-title">
                                <?php echo wp_kses( __( 'Not Sure What Kind of<br>Traveller, Are You?', 'trek-content-manager' ), array( 'br' => array() ) ); ?>
                            </h2>
                            <p class="ya-mat-hero-subtitle">
                                <?php esc_html_e( "Let's find out Together, what kind of journey right for you.", 'trek-content-manager' ); ?>
                            </p>
                            <button type="button" class="ya-mat-btn ya-mat-start-btn ya-mat-btn--peach" data-next="2">
                                <span><?php esc_html_e( 'START EXPLORING', 'trek-content-manager' ); ?></span>
                            </button>
                        </div>

                        <!-- RIGHT SIDE: Tilted overlapping collage of cards / brochures -->
                        <div class="ya-mat-hero-collage" aria-hidden="true">
                            <div class="ya-mat-collage-stage">
                                <!-- Card 2: Top Center Scenic Brochure (Behind) -->
                                <div class="ya-mat-brochure ya-mat-brochure--card2" data-card="2">
                                    <div class="ya-mat-brochure__inner">
                                        <!-- USER IMAGE PLACEHOLDER: Paste image URL below or set via Customizer 'te_matcher_card2_img' -->
                                        <?php $c2_img = get_theme_mod( 'te_matcher_card2_img', 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>
                                        <img src="<?php echo esc_url( $c2_img ?: 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>" alt="" class="ya-mat-brochure__user-img" />
                                        <div class="ya-mat-card2-art">
                                            <div class="ya-mat-card2-visual"></div>
                                            <div class="ya-mat-card2-body">
                                                <p>yati shaped around</p>
                                                <p>you to travel.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 4: Route Itinerary Card (Bottom Behind) -->
                                <div class="ya-mat-brochure ya-mat-brochure--card4" data-card="4">
                                    <div class="ya-mat-brochure__inner">
                                        <!-- USER IMAGE PLACEHOLDER: Paste image URL below or set via Customizer 'te_matcher_card4_img' -->
                                        <?php $c4_img = get_theme_mod( 'te_matcher_card4_img', 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>
                                        <img src="<?php echo esc_url( $c4_img ?: 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>" alt="" class="ya-mat-brochure__user-img" />
                                        <div class="ya-mat-card4-content">
                                            <div class="ya-mat-card4-lines">
                                                <span class="ya-mat-card4-line short"></span>
                                                <span class="ya-mat-card4-line medium"></span>
                                            </div>
                                            <svg class="ya-mat-card4-svg" viewBox="0 0 160 55" fill="none">
                                                <path d="M 12 40 C 45 15, 80 42, 148 18" stroke="#3d6c38" stroke-width="2.5" stroke-linecap="round"/>
                                                <circle cx="16" cy="38" r="4.5" fill="#ffffff" stroke="#3d6c38" stroke-width="2.5"/>
                                                <circle cx="82" cy="33" r="4.5" fill="#ffffff" stroke="#3d6c38" stroke-width="2.5"/>
                                                <circle cx="144" cy="20" r="4.5" fill="#ffffff" stroke="#3d6c38" stroke-width="2.5"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 1: Main Front Travel Brochure -->
                                <div class="ya-mat-brochure ya-mat-brochure--card1" data-card="1">
                                    <div class="ya-mat-brochure__inner">
                                        <!-- USER IMAGE PLACEHOLDER: Paste image URL below or set via Customizer 'te_matcher_card1_img' -->
                                        <?php $c1_img = get_theme_mod( 'te_matcher_card1_img', 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>
                                        <img src="<?php echo esc_url( $c1_img ?: 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>" alt="" class="ya-mat-brochure__user-img" />
                                        <div class="ya-mat-card1-art">
                                            <div class="ya-mat-card1-top">
                                                <span class="ya-mat-card1-brand"><?php esc_html_e( 'YOUR TRAVEL CO.', 'trek-content-manager' ); ?></span>
                                            </div>
                                            <div class="ya-mat-card1-mid">
                                                <div class="ya-mat-card1-mountain"></div>
                                                <h3 class="ya-mat-card1-title">TRAVEL</h3>
                                            </div>
                                            <div class="ya-mat-card1-bottom">
                                                <span class="ya-mat-card1-url">www.yetiadventure.com</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card 3: Which Traveller Are You? (Forest Green Right) -->
                                <div class="ya-mat-brochure ya-mat-brochure--card3" data-card="3">
                                    <div class="ya-mat-brochure__inner">
                                        <!-- USER IMAGE PLACEHOLDER: Paste image URL below or set via Customizer 'te_matcher_card3_img' -->
                                        <?php $c3_img = get_theme_mod( 'te_matcher_card3_img', 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>
                                        <img src="<?php echo esc_url( $c3_img ?: 'http://localhost/travling/wp-content/uploads/2026/09/sylwia-bartyzel-FuuEdB1XN4M-unsplash-scaled.jpg' ); ?>" alt="" class="ya-mat-brochure__user-img" />
                                        <div class="ya-mat-card3-content">
                                            <h3 class="ya-mat-card3-title">
                                                <?php echo wp_kses( __( 'Which Traveller<br>Are You?', 'trek-content-manager' ), array( 'br' => array() ) ); ?>
                                            </h3>
                                            <p class="ya-mat-brochure__which-desc">
                                                <?php esc_html_e( 'Adventurer, relaxer, culture seeker or family explorer. Start with a few questions.', 'trek-content-manager' ); ?>
                                            </p>
                                            <div class="ya-mat-card3-footer">
                                                <span>sales@yetiadventure.com</span>
                                                <span>+977 1 4371516</span>
                                                <span>www.yetiadventure.com</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: QUESTION 1 -->
                <div class="ya-mat-screen" data-step="2">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">01 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'What would you most like to explore?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q1">
                            <button type="button" class="ya-mat-option" data-value="culture_heritage">
                                <span><?php esc_html_e( 'Culture & Heritage', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="nature_wildlife">
                                <span><?php esc_html_e( 'Nature & Wildlife', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="mountains_adventure">
                                <span><?php esc_html_e( 'Mountains & Adventure', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="people_local_life">
                                <span><?php esc_html_e( 'People & Local Life', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: QUESTION 2 -->
                <div class="ya-mat-screen" data-step="3">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">02 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'How do you want your journey to feel?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q2">
                            <button type="button" class="ya-mat-option" data-value="feel_inspired">
                                <span><?php esc_html_e( 'Inspired', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="feel_free">
                                <span><?php esc_html_e( 'Free', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="feel_connected">
                                <span><?php esc_html_e( 'Connected', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="feel_excited">
                                <span><?php esc_html_e( 'Excited', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: QUESTION 3 -->
                <div class="ya-mat-screen" data-step="4">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="3">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">03 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'What kind of places draw you in?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q3">
                            <button type="button" class="ya-mat-option" data-value="place_remote_wild">
                                <span><?php esc_html_e( 'Remote & Wild', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="place_ancient_cultural">
                                <span><?php esc_html_e( 'Ancient & Cultural', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="place_peaceful_scenic">
                                <span><?php esc_html_e( 'Peaceful & Scenic', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="place_vibrant_local">
                                <span><?php esc_html_e( 'Vibrant & Local', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: QUESTION 4 -->
                <div class="ya-mat-screen" data-step="5">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="4">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">04 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'What’s your ideal travel pace?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q4">
                            <button type="button" class="ya-mat-option" data-value="pace_slow_easy">
                                <span><?php esc_html_e( 'Slow & Easy', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="pace_balanced">
                                <span><?php esc_html_e( 'Balanced', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="pace_active_exploring">
                                <span><?php esc_html_e( 'Active & Exploring', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="pace_challenging">
                                <span><?php esc_html_e( 'Challenging', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 6: QUESTION 5 -->
                <div class="ya-mat-screen" data-step="6">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">05 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'How do you like to experience a destination?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q5">
                            <button type="button" class="ya-mat-option" data-value="exp_walk_discover">
                                <span><?php esc_html_e( 'Walk & Discover', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="exp_meet_connect">
                                <span><?php esc_html_e( 'Meet & Connect', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="exp_explore_photograph">
                                <span><?php esc_html_e( 'Explore & Photograph', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="exp_challenge_myself">
                                <span><?php esc_html_e( 'Challenge Myself', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 7: QUESTION 6 -->
                <div class="ya-mat-screen" data-step="7">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="6">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text">06 / 06</span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'What would make this journey unforgettable?', 'trek-content-manager' ); ?></h2>
                        <div class="ya-mat-options" data-question="q6">
                            <button type="button" class="ya-mat-option" data-value="mem_breathtaking_view">
                                <span><?php esc_html_e( 'A breathtaking view', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="mem_meaningful_encounter">
                                <span><?php esc_html_e( 'A meaningful encounter', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="mem_new_adventure">
                                <span><?php esc_html_e( 'A new adventure', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                            <button type="button" class="ya-mat-option" data-value="mem_story_to_remember">
                                <span><?php esc_html_e( 'A story to remember', 'trek-content-manager' ); ?></span>
                                <span class="ya-mat-option-indicator"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 8: EMAIL COLLECTION -->
                <div class="ya-mat-screen" data-step="8">
                    <div class="ya-mat-nav-bar">
                        <button type="button" class="ya-mat-back-btn" data-back="7">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            <span><?php esc_html_e( 'Back', 'trek-content-manager' ); ?></span>
                        </button>
                        <span class="ya-mat-progress-text"><?php esc_html_e( 'Almost there', 'trek-content-manager' ); ?></span>
                        <span style="width:50px;"></span>
                    </div>
                    <div class="ya-mat-content-box">
                        <h2 class="ya-mat-title"><?php esc_html_e( 'Your journey is Ready..', 'trek-content-manager' ); ?></h2>
                        <p class="ya-mat-subtitle"><?php esc_html_e( 'Enter your email and we’ll reveal the destinations that match the way you love to travel.', 'trek-content-manager' ); ?></p>
                        
                        <form class="ya-mat-email-form" novalidate>
                            <div class="ya-mat-input-group">
                                <input type="email" class="ya-mat-email-input" placeholder="<?php esc_attr_e( 'Your email address', 'trek-content-manager' ); ?>" required />
                                <span class="ya-mat-error-msg"><?php esc_html_e( 'Please enter a valid email address.', 'trek-content-manager' ); ?></span>
                            </div>
                            <button type="submit" class="ya-mat-btn" style="width:100%;">
                                <span><?php esc_html_e( 'Gets My Matches', 'trek-content-manager' ); ?></span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                            <p class="ya-mat-privacy"><?php esc_html_e( 'We’ll only use your email to send your travel matches and related journey information.', 'trek-content-manager' ); ?></p>
                            
                            <div class="ya-mat-loading">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="animation: spin 1s linear infinite; display:inline-block; vertical-align:middle; margin-right:8px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                                <span><?php esc_html_e( 'Finding journeys that match you...', 'trek-content-manager' ); ?></span>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- STEP 9: PERSONALIZED RESULTS -->
                <div class="ya-mat-screen" data-step="9">
                    <div class="ya-mat-results-wrap">
                        <div class="ya-mat-content-box">
                            <span class="ya-mat-eyebrow"><?php esc_html_e( 'Curated For You', 'trek-content-manager' ); ?></span>
                            <h2 class="ya-mat-title"><?php esc_html_e( 'Your Journey Matches', 'trek-content-manager' ); ?></h2>
                            <p class="ya-mat-subtitle"><?php esc_html_e( 'Destinations that fit the way you want to travel. Swipe or use arrows to discover more.', 'trek-content-manager' ); ?></p>
                        </div>

                        <!-- 6 Destination Cards Carousel / Slider ("sursull") -->
                        <div class="ya-mat-carousel-wrapper">
                            <button type="button" class="ya-mat-carousel-btn ya-mat-carousel-btn--prev" aria-label="<?php esc_attr_e( 'Previous', 'trek-content-manager' ); ?>" id="<?php echo esc_attr( $uid ); ?>_prev">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                            </button>

                            <div class="ya-mat-cards-carousel" id="<?php echo esc_attr( $uid ); ?>_cards">
                                <!-- Injected via JavaScript (up to 6 cards) -->
                            </div>

                            <button type="button" class="ya-mat-carousel-btn ya-mat-carousel-btn--next" aria-label="<?php esc_attr_e( 'Next', 'trek-content-manager' ); ?>" id="<?php echo esc_attr( $uid ); ?>_next">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                            </button>
                        </div>

                        <div class="ya-mat-actions">
                            <button type="button" class="ya-mat-btn ya-mat-btn--outline ya-mat-restart-btn">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span><?php esc_html_e( 'Retake Discovery', 'trek-content-manager' ); ?></span>
                            </button>
                            <a href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>" class="ya-mat-btn">
                                <span><?php esc_html_e( 'Discover More Journeys', 'trek-content-manager' ); ?></span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                </div><!-- /.ya-mat-shell-card -->
            </div><!-- /.ya-mat-inner -->

            <script>
                (function() {
                    const shell = document.getElementById('<?php echo esc_js( $uid ); ?>');
                    if (!shell) return;

                    const ajaxUrl = shell.getAttribute('data-ajax-url');
                    const nonce   = shell.getAttribute('data-nonce');
                    const cardsContainer = shell.querySelector('#<?php echo esc_js( $uid ); ?>_cards');
                    const prevBtn = shell.querySelector('#<?php echo esc_js( $uid ); ?>_prev');
                    const nextBtn = shell.querySelector('#<?php echo esc_js( $uid ); ?>_next');
                    const screens = Array.from(shell.querySelectorAll('.ya-mat-screen'));
                    const emailForm = shell.querySelector('.ya-mat-email-form');
                    const emailInput = shell.querySelector('.ya-mat-email-input');
                    const errorMsg = shell.querySelector('.ya-mat-error-msg');
                    const loadingIndicator = shell.querySelector('.ya-mat-loading');
                    const submitBtn = emailForm ? emailForm.querySelector('button[type="submit"]') : null;

                    const state = {
                        step: 1,
                        answers: {
                            q1: '',
                            q2: '',
                            q3: '',
                            q4: '',
                            q5: '',
                            q6: ''
                        },
                        email: ''
                    };

                    function showStep(stepNum) {
                        state.step = stepNum;
                        screens.forEach(s => {
                            const sNum = parseInt(s.getAttribute('data-step'), 10);
                            if (sNum === stepNum) {
                                s.classList.add('is-active');
                                s.style.opacity = '0';
                                s.style.transform = 'translateY(12px)';
                                requestAnimationFrame(() => {
                                    s.style.opacity = '1';
                                    s.style.transform = 'translateY(0)';
                                });
                            } else {
                                s.classList.remove('is-active');
                            }
                        });

                        // Scroll smoothly to top of matcher container if user is lower down
                        const shellTop = shell.getBoundingClientRect().top + window.pageYOffset - 50;
                        if (window.pageYOffset > shellTop + 200) {
                            window.scrollTo({ top: shellTop, behavior: 'smooth' });
                        }
                    }

                    // Start Exploring Button
                    const startBtn = shell.querySelector('.ya-mat-start-btn');
                    if (startBtn) {
                        startBtn.addEventListener('click', () => showStep(2));
                    }

                    // Back Buttons
                    shell.querySelectorAll('.ya-mat-back-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const backStep = parseInt(this.getAttribute('data-back'), 10);
                            if (backStep >= 1) {
                                showStep(backStep);
                            }
                        });
                    });

                    // Option Buttons
                    shell.querySelectorAll('.ya-mat-option').forEach(opt => {
                        opt.addEventListener('click', function() {
                            const parent = this.closest('.ya-mat-options');
                            if (!parent) return;

                            const questionKey = parent.getAttribute('data-question');
                            const val = this.getAttribute('data-value');

                            // Mark active in DOM
                            parent.querySelectorAll('.ya-mat-option').forEach(o => o.classList.remove('is-selected'));
                            this.classList.add('is-selected');

                            // Store in state
                            state.answers[questionKey] = val;

                            // Proceed to next screen after brief visual confirmation
                            setTimeout(() => {
                                const currentStep = state.step;
                                showStep(currentStep + 1);
                            }, 220);
                        });
                    });

                    // Carousel Navigation Buttons
                    if (prevBtn && cardsContainer) {
                        prevBtn.addEventListener('click', () => {
                            const card = cardsContainer.querySelector('.ya-col-card');
                            const scrollAmount = card ? (card.offsetWidth + 24) : 340;
                            cardsContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                        });
                    }
                    if (nextBtn && cardsContainer) {
                        nextBtn.addEventListener('click', () => {
                            const card = cardsContainer.querySelector('.ya-col-card');
                            const scrollAmount = card ? (card.offsetWidth + 24) : 340;
                            cardsContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                        });
                    }

                    // Email Validation
                    function isValidEmail(email) {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email).toLowerCase());
                    }

                    if (emailInput) {
                        emailInput.addEventListener('input', function() {
                            if (errorMsg) errorMsg.style.display = 'none';
                            this.style.borderColor = 'rgba(16, 47, 53, 0.2)';
                        });
                    }

                    // Email Submission
                    if (emailForm) {
                        emailForm.addEventListener('submit', function(e) {
                            e.preventDefault();

                            const emailVal = emailInput ? emailInput.value.trim() : '';

                            if (!isValidEmail(emailVal)) {
                                if (errorMsg) errorMsg.style.display = 'block';
                                if (emailInput) {
                                    emailInput.style.borderColor = '#b32d2e';
                                    emailInput.focus();
                                }
                                return;
                            }

                            state.email = emailVal;

                            // Loading state
                            if (submitBtn) submitBtn.style.display = 'none';
                            if (loadingIndicator) loadingIndicator.style.display = 'block';

                            const formData = new FormData();
                            formData.append('action', 'ya_submit_traveller_matcher');
                            formData.append('nonce', nonce);
                            formData.append('email', state.email);
                            formData.append('q1', state.answers.q1);
                            formData.append('q2', state.answers.q2);
                            formData.append('q3', state.answers.q3);
                            formData.append('q4', state.answers.q4);
                            formData.append('q5', state.answers.q5);
                            formData.append('q6', state.answers.q6);

                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (submitBtn) submitBtn.style.display = 'inline-flex';
                                if (loadingIndicator) loadingIndicator.style.display = 'none';

                                if (data.success && data.data && data.data.cards) {
                                    renderResults(data.data.cards);
                                    showStep(9);
                                } else {
                                    alert(data.data && data.data.message ? data.data.message : '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'trek-content-manager' ) ); ?>');
                                }
                            })
                            .catch(() => {
                                if (submitBtn) submitBtn.style.display = 'inline-flex';
                                if (loadingIndicator) loadingIndicator.style.display = 'none';
                                alert('<?php echo esc_js( __( 'Something went wrong. Please try again.', 'trek-content-manager' ) ); ?>');
                            });
                        });
                    }

                    // Render Result Cards using exact [ya_signature_journeys] style
                    function renderResults(cards) {
                        if (!cardsContainer) return;
                        cardsContainer.innerHTML = '';

                        if (!cards || cards.length === 0) {
                            cardsContainer.innerHTML = '<div style="flex:1; text-align:center; padding:48px 20px; color:#617579;"><p><?php echo esc_js( __( 'We could not find an exact match yet. Explore all journeys to discover more possibilities.', 'trek-content-manager' ) ); ?></p></div>';
                            return;
                        }

                        const yetiLogo = 'https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/yeti_logo_active.gif';

                        cards.forEach(card => {
                            const a = document.createElement('a');
                            a.className = 'ya-col-card';
                            a.href = card.url || '#';

                            let mediaHtml = '';
                            if (card.img) {
                                mediaHtml = '<img src="' + card.img + '" alt="' + (card.alt || card.title) + '" loading="lazy" class="ya-col-card__img">';
                            } else {
                                mediaHtml = '<div class="ya-col-card__img-placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #102f35 0%, #1d4d57 100%);color:rgba(255,255,255,0.4);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg></div>';
                            }

                            let badgeHtml = '';
                            if (card.badge) {
                                badgeHtml = '<span class="ya-col-card__top-sep" aria-hidden="true">|</span><span class="ya-col-card__top-cat">' + card.badge + '</span>';
                            }

                            let locHtml = '';
                            if (card.location_display) {
                                locHtml = '<div class="ya-col-card__loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-card__loc-icon"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg><span class="ya-col-card__loc-text">' + card.location_display + '</span></div>';
                            }

                            const daysStr = card.days ? card.days + ' <?php echo esc_js( __( 'Days', 'trek-content-manager' ) ); ?>' : '—';
                            const diffStr = card.diff ? card.diff : '—';
                            const seasonStr = card.season ? card.season : '—';

                            a.innerHTML = `
                                <div class="ya-col-card__media">
                                    ${mediaHtml}
                                    <div class="ya-col-card__top-badge">
                                        <img src="${yetiLogo}" alt="Yeti Adventure" class="ya-col-card__top-logo">
                                        ${badgeHtml}
                                    </div>
                                </div>
                                <div class="ya-col-card__body">
                                    ${locHtml}
                                    <h3 class="ya-col-card__title">${card.title}</h3>
                                    <div class="ya-col-card__meta">
                                        <div class="ya-col-card__meta-item">
                                            <div class="ya-col-card__meta-head">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                <span class="ya-col-card__meta-label"><?php echo esc_js( __( 'Duration', 'trek-content-manager' ) ); ?></span>
                                            </div>
                                            <span class="ya-col-card__meta-val">${daysStr}</span>
                                        </div>
                                        <div class="ya-col-card__meta-item">
                                            <div class="ya-col-card__meta-head">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                                <span class="ya-col-card__meta-label"><?php echo esc_js( __( 'Difficulty', 'trek-content-manager' ) ); ?></span>
                                            </div>
                                            <span class="ya-col-card__meta-val">${diffStr}</span>
                                        </div>
                                        <div class="ya-col-card__meta-item">
                                            <div class="ya-col-card__meta-head">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ya-col-meta-icon"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                                <span class="ya-col-card__meta-label"><?php echo esc_js( __( 'Best Season', 'trek-content-manager' ) ); ?></span>
                                            </div>
                                            <span class="ya-col-card__meta-val">${seasonStr}</span>
                                        </div>
                                    </div>
                                </div>
                            `;

                            cardsContainer.appendChild(a);
                        });
                    }

                    // Restart / Retake Discovery
                    const restartBtn = shell.querySelector('.ya-mat-restart-btn');
                    if (restartBtn) {
                        restartBtn.addEventListener('click', () => {
                            state.answers = { q1: '', q2: '', q3: '', q4: '', q5: '', q6: '' };
                            state.email = '';
                            shell.querySelectorAll('.ya-mat-option').forEach(o => o.classList.remove('is-selected'));
                            if (emailInput) emailInput.value = '';
                            showStep(1);
                        });
                    }
                })();
            </script>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handler: Process Traveller Matcher Answers & Save Lead
     */
    public static function ajax_submit_traveller_matcher() {
        check_ajax_referer( 'trek_traveller_matcher_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'trek-content-manager' ) ) );
        }

        if ( ! class_exists( 'Trek_Journey_Matcher' ) ) {
            wp_send_json_error( array( 'message' => __( 'Journey matcher engine unavailable.', 'trek-content-manager' ) ) );
        }

        $q1 = sanitize_key( $_POST['q1'] ?? '' );
        $q2 = sanitize_key( $_POST['q2'] ?? '' );
        $q3 = sanitize_key( $_POST['q3'] ?? '' );
        $q4 = sanitize_key( $_POST['q4'] ?? '' );
        $q5 = sanitize_key( $_POST['q5'] ?? '' );
        $q6 = sanitize_key( $_POST['q6'] ?? '' );

        // Centralized Answer -> Keyword Mapping Layer
        $mapping = array(
            // Question 1: What would you most like to explore?
            'culture_heritage'       => array( 'explore' => array( 'culture_heritage', 'history' ) ),
            'nature_wildlife'        => array( 'explore' => array( 'nature_landscapes', 'wildlife' ) ),
            'mountains_adventure'    => array( 'explore' => array( 'mountains', 'adventure' ) ),
            'people_local_life'      => array( 'explore' => array( 'local_life', 'food_traditions' ) ),

            // Question 2: How do you want your journey to feel?
            'feel_inspired'          => array( 'feel' => array( 'inspired' ) ),
            'feel_free'              => array( 'feel' => array( 'free' ) ),
            'feel_connected'         => array( 'feel' => array( 'connected' ) ),
            'feel_excited'           => array( 'feel' => array( 'adventurous', 'alive' ) ),

            // Question 3: What kind of places draw you in?
            'place_remote_wild'      => array( 'explore' => array( 'remote_places' ), 'pace' => array( 'wild_remote' ) ),
            'place_ancient_cultural' => array( 'explore' => array( 'history', 'culture_heritage' ) ),
            'place_peaceful_scenic'  => array( 'feel' => array( 'peaceful' ), 'pace' => array( 'slow_scenic' ) ),
            'place_vibrant_local'    => array( 'explore' => array( 'local_life' ), 'feel' => array( 'immersed' ) ),

            // Question 4: What’s your ideal travel pace?
            'pace_slow_easy'         => array( 'pace' => array( 'slow_scenic', 'relaxed' ) ),
            'pace_balanced'          => array( 'pace' => array( 'balanced' ) ),
            'pace_active_exploring'  => array( 'pace' => array( 'active', 'adventure' ) ),
            'pace_challenging'       => array( 'pace' => array( 'challenging', 'trekking', 'expedition' ) ),

            // Question 5: How do you like to experience a destination?
            'exp_walk_discover'      => array( 'pace' => array( 'culture_discovery', 'slow_scenic' ) ),
            'exp_meet_connect'       => array( 'feel' => array( 'connected' ), 'explore' => array( 'local_life' ) ),
            'exp_explore_photograph' => array( 'explore' => array( 'nature_landscapes' ), 'feel' => array( 'inspired' ) ),
            'exp_challenge_myself'   => array( 'feel' => array( 'challenged', 'accomplished' ) ),

            // Question 6: What would make this journey unforgettable?
            'mem_breathtaking_view'  => array( 'feel' => array( 'inspired' ), 'explore' => array( 'mountains' ) ),
            'mem_meaningful_encounter'=> array( 'feel' => array( 'connected', 'spirituality' ), 'explore' => array( 'local_life' ) ),
            'mem_new_adventure'      => array( 'explore' => array( 'adventure' ), 'feel' => array( 'adventurous' ) ),
            'mem_story_to_remember'  => array( 'feel' => array( 'far_from_ordinary', 'accomplished' ) ),
        );

        $selections = array(
            'explore' => array(),
            'feel'    => array(),
            'pace'    => array(),
        );

        foreach ( array( $q1, $q2, $q3, $q4, $q5, $q6 ) as $ans ) {
            if ( isset( $mapping[ $ans ] ) ) {
                foreach ( array( 'explore', 'feel', 'pace' ) as $cat ) {
                    if ( ! empty( $mapping[ $ans ][ $cat ] ) ) {
                        $selections[ $cat ] = array_merge( $selections[ $cat ], $mapping[ $ans ][ $cat ] );
                    }
                }
            }
        }

        // De-duplicate
        $selections['explore'] = array_unique( $selections['explore'] );
        $selections['feel']    = array_unique( $selections['feel'] );
        $selections['pace']    = array_unique( $selections['pace'] );

        // Run multi-destination matching (top 6)
        $matches = Trek_Journey_Matcher::match_multiple( $selections, 6 );

        $cards = array();
        $matched_titles = array();
        $primary_dest_id = 0;
        $primary_score   = 0;

        foreach ( $matches as $idx => $m ) {
            $p_id = $m['destination_id'];
            if ( ! $p_id ) {
                continue;
            }
            if ( 0 === $idx ) {
                $primary_dest_id = $p_id;
                $primary_score   = $m['score'];
            }

            $c_title = get_the_title( $p_id );
            $matched_titles[] = $c_title;

            // Image
            $c_img = get_the_post_thumbnail_url( $p_id, 'large' );
            if ( empty( $c_img ) ) {
                $c_img = get_post_meta( $p_id, '_trek_card_image_url', true );
            }
            if ( empty( $c_img ) ) {
                $c_img = get_post_meta( $p_id, '_trek_hero_bg_img', true );
            }
            if ( empty( $c_img ) ) {
                $c_img = '';
            }

            // Category badge
            $cat_terms = get_the_terms( $p_id, 'trek_category' );
            $badge     = ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) ? $cat_terms[0]->name : '';

            // Region / Country
            $c_region = get_post_meta( $p_id, '_trek_fact_region', true );
            if ( empty( $c_region ) ) {
                $c_region = get_post_meta( $p_id, '_trek_breadcrumb_region', true );
            }
            if ( empty( $c_region ) ) {
                $reg_terms = get_the_terms( $p_id, 'trek_region' );
                if ( ! empty( $reg_terms ) && ! is_wp_error( $reg_terms ) ) {
                    $c_region = $reg_terms[0]->name;
                }
            }
            if ( empty( $c_region ) ) {
                $c_region = get_post_meta( $p_id, '_trek_country', true );
            }

            // Difficulty
            $diff_terms = get_the_terms( $p_id, 'trek_difficulty' );
            $c_diff     = ( ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->name : get_post_meta( $p_id, '_trek_difficulty', true );
            $c_diff     = ! empty( $c_diff ) ? preg_replace( '/\s*trek$/i', '', trim( $c_diff ) ) : '';

            // Duration
            $days_raw = get_post_meta( $p_id, '_trek_hero_days', true );
            if ( empty( $days_raw ) ) {
                $days_raw = get_post_meta( $p_id, '_trek_fact_duration', true );
            }
            $c_days = ! empty( $days_raw ) ? preg_replace( '/[^0-9]/', '', (string) $days_raw ) : '';

            // Best Season
            $c_season = get_post_meta( $p_id, '_trek_fact_season', true );
            if ( empty( $c_season ) ) {
                $c_season = get_post_meta( $p_id, '_trek_fact_best_season', true );
            }
            $c_season = ! empty( $c_season ) ? trim( (string) $c_season ) : '';

            $cards[] = array(
                'id'               => $p_id,
                'title'            => $c_title,
                'url'              => get_permalink( $p_id ),
                'img'              => $c_img,
                'alt'              => $c_title,
                'badge'            => $badge,
                'location_display' => $c_region,
                'diff'             => $c_diff,
                'days'             => $c_days,
                'season'           => $c_season,
                'score'            => $m['score'],
            );
        }

        // Check if an enquiry with this email already exists (optimized lightweight query)
        $existing_enquiries = get_posts( array(
            'post_type'              => 'trek_enquiry',
            'post_status'            => 'any',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'meta_query'             => array(
                array(
                    'key'     => '_trek_enquiry_email',
                    'value'   => $email,
                    'compare' => '=',
                ),
            ),
        ) );

        if ( ! empty( $existing_enquiries ) ) {
            // Existing email found: do NOT insert duplicate post in database.
            // Increment lookup count and record latest timestamp and recommendations.
            $enquiry_id   = $existing_enquiries[0];
            $lookup_count = (int) get_post_meta( $enquiry_id, '_trek_enquiry_lookup_count', true );
            if ( $lookup_count < 1 ) {
                $lookup_count = 1;
            }
            $lookup_count++;
            update_post_meta( $enquiry_id, '_trek_enquiry_lookup_count', $lookup_count );
            update_post_meta( $enquiry_id, '_trek_enquiry_last_lookup', current_time( 'mysql' ) );

            if ( ! empty( $matched_titles ) ) {
                update_post_meta( $enquiry_id, '_trek_enquiry_trek_title', implode( ', ', $matched_titles ) );
            }
            if ( $primary_dest_id ) {
                update_post_meta( $enquiry_id, '_trek_enquiry_trek_id', $primary_dest_id );
                update_post_meta( $enquiry_id, '_trek_enquiry_match_score', $primary_score );
            }
            update_post_meta( $enquiry_id, '_trek_enquiry_explore', $selections['explore'] );
            update_post_meta( $enquiry_id, '_trek_enquiry_feeling', $selections['feel'] );
            update_post_meta( $enquiry_id, '_trek_enquiry_pace', $selections['pace'] );

            // Append re-lookup log to the existing record content
            $existing_post = get_post( $enquiry_id );
            if ( $existing_post ) {
                $append_note = sprintf(
                    "\n--- Re-lookup #%d on %s ---\nMatched Journeys: %s\nPrimary Destination ID: %d (Score: %d)\nAnswers: Q1=%s, Q2=%s, Q3=%s, Q4=%s, Q5=%s, Q6=%s",
                    $lookup_count,
                    current_time( 'mysql' ),
                    implode( ', ', $matched_titles ),
                    $primary_dest_id,
                    $primary_score,
                    $q1, $q2, $q3, $q4, $q5, $q6
                );
                wp_update_post( array(
                    'ID'           => $enquiry_id,
                    'post_content' => $existing_post->post_content . $append_note,
                ) );
            }
        } else {
            // New email: Store Lead in `trek_enquiry` CPT
            $enquiry_title = sprintf( 'Traveller Matcher Lead: %s (%s)', implode( ', ', $matched_titles ), $email );
            $enquiry_content = sprintf(
                "Traveller Email: %s\nMatched Journeys: %s\nPrimary Destination ID: %d\nPrimary Score: %d\nExplore Keys: %s\nFeel Keys: %s\nPace Keys: %s\nRaw Answers: Q1=%s, Q2=%s, Q3=%s, Q4=%s, Q5=%s, Q6=%s\nSource: Traveller Matcher\nTimestamp: %s\nIP: %s",
                $email,
                implode( ', ', $matched_titles ),
                $primary_dest_id,
                $primary_score,
                implode( ', ', $selections['explore'] ),
                implode( ', ', $selections['feel'] ),
                implode( ', ', $selections['pace'] ),
                $q1, $q2, $q3, $q4, $q5, $q6,
                current_time( 'mysql' ),
                sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' )
            );

            $enquiry_id = wp_insert_post( array(
                'post_type'    => 'trek_enquiry',
                'post_title'   => $enquiry_title,
                'post_content' => $enquiry_content,
                'post_status'  => 'private',
            ), true );

            if ( ! is_wp_error( $enquiry_id ) ) {
                update_post_meta( $enquiry_id, '_trek_enquiry_email', $email );
                update_post_meta( $enquiry_id, '_trek_enquiry_lookup_count', 1 );
                update_post_meta( $enquiry_id, '_trek_enquiry_trek_title', implode( ', ', $matched_titles ) );
                update_post_meta( $enquiry_id, '_trek_enquiry_trek_id', $primary_dest_id );
                update_post_meta( $enquiry_id, '_trek_enquiry_source', 'Traveller Matcher' );
                update_post_meta( $enquiry_id, '_trek_enquiry_match_score', $primary_score );
                update_post_meta( $enquiry_id, '_trek_enquiry_explore', $selections['explore'] );
                update_post_meta( $enquiry_id, '_trek_enquiry_feeling', $selections['feel'] );
                update_post_meta( $enquiry_id, '_trek_enquiry_pace', $selections['pace'] );
                update_post_meta( $enquiry_id, '_trek_enquiry_date', current_time( 'mysql' ) );
            }
        }

        wp_send_json_success( array(
            'cards'   => $cards,
            'message' => __( 'Matches retrieved successfully.', 'trek-content-manager' ),
        ) );
    }

    /**
     * Render Interactive Nepal Tourism Map dynamically bound to CPT Data
     * Shortcode: [ya_nepal_map] / [trek_nepal_map]
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render_nepal_map( $atts = array() ) {
        $atts = shortcode_atts( array(
            'country' => 'nepal',
            'class'   => '',
        ), $atts, 'ya_nepal_map' );

        // Fetch all published trek destinations
        $trips = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ) );

        $region_data = array();

        // Standard map regions mapping
        $known_regions = array(
            'everest-region'         => array( 'title' => 'Everest Region (Khumbu)', 'keys' => array( 'everest', 'ebc', 'khumbu', 'gokyo', 'kala patthar', 'sagarmatha' ) ),
            'annapurna'              => array( 'title' => 'Annapurna Region', 'keys' => array( 'annapurna', 'abc', 'sanctuary', 'poon hill', 'mardi', 'ghorepani' ) ),
            'mustang'                => array( 'title' => 'Mustang (Kingdom of Lo)', 'keys' => array( 'mustang', 'lo manthang', 'upper mustang' ) ),
            'manaslu'                => array( 'title' => 'Manaslu Region', 'keys' => array( 'manaslu', 'tsum', 'larke' ) ),
            'ganesh-himal'           => array( 'title' => 'Ganesh Himal', 'keys' => array( 'ganesh', 'ruby valley' ) ),
            'dhaulagiri'             => array( 'title' => 'Dhaulagiri Region', 'keys' => array( 'dhaulagiri' ) ),
            'kanchenjunga'           => array( 'title' => 'Kanchenjunga', 'keys' => array( 'kanchenjunga', 'kangchenjunga' ) ),
            'api-himal'              => array( 'title' => 'Api Himal', 'keys' => array( 'api', 'api himal', 'saipal' ) ),
            'chitwan-national-park'  => array( 'title' => 'Chitwan National Park', 'keys' => array( 'chitwan', 'safari', 'jungle' ) ),
            'bardia-national-park'   => array( 'title' => 'Bardia National Park', 'keys' => array( 'bardia' ) ),
            'koshi-tappu-wildlife'   => array( 'title' => 'Koshi Tappu Wildlife', 'keys' => array( 'koshi', 'tappu' ) ),
            'lumbini'                => array( 'title' => 'Lumbini (Buddha Circuit)', 'keys' => array( 'lumbini', 'buddha' ) ),
            'kathmandu'              => array( 'title' => 'Kathmandu Valley', 'keys' => array( 'kathmandu', 'paten', 'bhaktapur' ) ),
            'pokhara'                => array( 'title' => 'Pokhara & Lakes', 'keys' => array( 'pokhara', 'fewa' ) ),
        );

        // Key map data by actual GeoJSON district names, not broad regions.
        $known_districts = array(
            'solukhumbu', 'sankhuwasabha', 'taplejung', 'ilam', 'panchthar',
            'jhapa', 'morang', 'sunsari', 'udayapur', 'khotang', 'okhaldhunga',
            'dolakha', 'ramechhap', 'sindhuli', 'makwanpur', 'chitwan',
            'kathmandu', 'bhaktapur', 'lalitpur', 'nuwakot', 'rasuwa',
            'dhading', 'gorkha', 'lamjung', 'tanahu', 'kaski', 'manang',
            'mustang', 'myagdi', 'parbat', 'baglung', 'syangja', 'gulmi',
            'palpa', 'nawalpur', 'parasi', 'rupandehi', 'kapilvastu',
            'arghakhanchi', 'pyuthan', 'rolpa', 'rukum east', 'rukum west',
            'salyan', 'dang', 'banke', 'bardiya', 'surkhet', 'dailekh',
            'jajarkot', 'jumla', 'kalikot', 'mugu', 'dolpa', 'humla',
            'bajura', 'bajhang', 'achham', 'doti', 'kailali', 'kanchanpur',
            'darchula', 'baitadi', 'dadeldhura', 'dhanusha', 'mahottari',
            'sarlahi', 'rautahat', 'bara', 'parsa', 'saptari', 'siraha',
            'sindhupalchok', 'kavrepalanchok',
        );

        $region_districts = array(
            'everest-region'        => 'solukhumbu',
            'annapurna'             => 'kaski',
            'mustang'               => 'mustang',
            'manaslu'               => 'gorkha',
            'ganesh-himal'          => 'dhading',
            'dhaulagiri'            => 'myagdi',
            'kanchenjunga'          => 'taplejung',
            'api-himal'             => 'darchula',
            'chitwan-national-park' => 'chitwan',
            'bardia-national-park'  => 'bardiya',
            'koshi-tappu-wildlife'  => 'sunsari',
            'lumbini'               => 'rupandehi',
            'kathmandu'             => 'kathmandu',
            'pokhara'               => 'kaski',
        );

        // Process trips and bind to regions
        foreach ( $trips as $trip ) {
            $post_id = $trip->ID;
            $data = self::get_formatted_trip_data( $post_id );
            if ( empty( $data ) ) continue;

            $assigned_map_region = get_post_meta( $post_id, '_trek_map_region', true );
            $target_slug = '';

            if ( ! empty( $assigned_map_region ) && isset( $known_regions[ $assigned_map_region ] ) ) {
                $target_slug = $assigned_map_region;
            } else {
                // Auto match from region terms or title
                $haystack = strtolower( $trip->post_title . ' ' . $data['tag'] . ' ' . $data['all_slugs'] );
                foreach ( $known_regions as $r_slug => $r_info ) {
                    foreach ( $r_info['keys'] as $k ) {
                        if ( strpos( $haystack, $k ) !== false ) {
                            $target_slug = $r_slug;
                            break 2;
                        }
                    }
                }
            }

            if ( $target_slug ) {
                $map_district = get_post_meta( $post_id, '_trek_map_district', true );
                $map_district = $map_district ? strtolower( trim( $map_district ) ) : '';

                if ( ! $map_district ) {
                    foreach ( $known_districts as $district ) {
                        if ( false !== strpos( $haystack, $district ) ) {
                            $map_district = $district;
                            break;
                        }
                    }
                }

                if ( ! $map_district && isset( $region_districts[ $target_slug ] ) ) {
                    $map_district = $region_districts[ $target_slug ];
                }

                if ( ! $map_district ) {
                    continue;
                }

                $map_district = str_replace( ' ', '-', $map_district );
                $location_address = ! empty( $data['address'] ) ? $data['address'] : ( ! empty( $data['location'] ) ? $data['location'] : 'Nepal' );

                if ( ! isset( $region_data[ $map_district ] ) ) {
                    $region_data[ $map_district ] = array(
                        'district'  => $map_district,
                        'title'     => $data['title'],
                        'days'      => $data['duration_label'],
                        'image'     => $data['image'],
                        'permalink' => $data['permalink'],
                        'address'   => $location_address,
                        'count'     => 1,
                        'has_trips' => true,
                        'destinations' => array( array(
                            'title'     => $data['title'],
                            'days'      => $data['duration_label'],
                            'image'     => $data['image'],
                            'permalink' => $data['permalink'],
                            'address'   => $location_address,
                        ) ),
                    );
                } else {
                    $region_data[ $map_district ]['count']++;
                    $region_data[ $map_district ]['destinations'][] = array(
                        'title'     => $data['title'],
                        'days'      => $data['duration_label'],
                        'image'     => $data['image'],
                        'permalink' => $data['permalink'],
                        'address'   => $location_address,
                    );
                }
            }
        }

        ob_start();
        $template_file = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/nepal-map-template.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo '<p style="color:#d4af37; text-align:center; padding:30px;">' . esc_html__( 'Nepal Tourism Map template file not found.', 'trek-content-manager' ) . '</p>';
        }
        return ob_get_clean();
    }

    /**
     * Render individual field value via shortcode
     * Usage: [trek_field id="123" field="price"]
     */
    public static function render_trek_field( $atts ) {
        $atts = shortcode_atts( array(
            'id'    => 0,
            'key'   => '',
            'field' => '',
            'raw'   => '0',
        ), $atts, 'trek_field' );

        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) {
            return '';
        }

        $key = ! empty( $atts['field'] ) ? sanitize_key( $atts['field'] ) : sanitize_key( $atts['key'] );
        if ( empty( $key ) ) {
            $key = 'title';
        }

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) {
            return '';
        }

        $output = '';
        switch ( $key ) {
            case 'title':
            case 'name':
                $output = $data['title'] ?? get_the_title( $post_id );
                break;
            case 'hero_title':
                $output = $data['hero']['title'] ?? ( $data['title'] ?? '' );
                break;
            case 'script_title':
                $output = $data['hero']['script_title'] ?? '';
                break;
            case 'subtitle':
            case 'hero_subtitle':
                $output = $data['hero']['subtitle'] ?? '';
                break;
            case 'days':
            case 'duration':
            case 'hero_days':
                $output = $data['hero']['days'] ?? '';
                break;
            case 'price':
                $output = $data['pricing']['price'] ?? '';
                break;
            case 'formatted_price':
                $output = $data['pricing']['formatted'] ?? ( $data['pricing']['price'] ?? '' );
                break;
            case 'currency':
                $output = $data['pricing']['currency'] ?? 'USD';
                break;
            case 'difficulty':
                $output = $data['difficulty'] ?? '';
                break;
            case 'high_point':
            case 'highest_point':
            case 'max_altitude':
                $output = $data['quick_facts']['high_point'] ?? '';
                break;
            case 'low_point':
                $output = $data['quick_facts']['low_point'] ?? '';
                break;
            case 'region':
            case 'breadcrumb_region':
                $output = $data['breadcrumb_region'] ?? ( ! empty( $data['regions'] ) ? $data['regions'][0] : '' );
                break;
            case 'season':
            case 'best_season':
                $output = $data['quick_facts']['season'] ?? '';
                break;
            case 'group_size':
                $output = $data['quick_facts']['group_size'] ?? '';
                break;
            case 'accommodation':
            case 'accom':
                $output = $data['quick_facts']['accom'] ?? '';
                break;
            case 'trekking':
            case 'daily_walking':
                $output = $data['quick_facts']['trekking'] ?? '';
                break;
            case 'style':
                $output = $data['quick_facts']['style'] ?? '';
                break;
            case 'status':
                $output = $data['status'] ?? 'open';
                break;
            case 'whatsapp':
            case 'whatsapp_number':
                $output = $data['contact']['whatsapp'] ?? '';
                break;
            case 'email':
            case 'sales_email':
                $output = $data['contact']['email'] ?? '';
                break;
            case 'overview':
            case 'overview_content':
                $output = $data['overview']['content'] ?? '';
                break;
            case 'highlight':
            case 'overview_highlight':
                $output = $data['overview']['highlight'] ?? '';
                break;
            case 'hero_image':
            case 'image':
                $output = $data['hero']['image'] ?? '';
                break;
            case 'card_image':
                $output = $data['card']['image'] ?? '';
                break;
            case 'route_img':
                $output = $data['overview']['route_img'] ?? '';
                break;
            case 'map_file_url':
                $output = $data['overview']['map_file_url'] ?? '';
                break;
            case 'map_region':
                $output = $data['map_region'] ?? '';
                break;
            case 'map_district':
                $output = $data['map_district'] ?? '';
                break;
            default:
                $meta_val = get_post_meta( $post_id, '_trek_' . $key, true );
                if ( empty( $meta_val ) ) {
                    $meta_val = get_post_meta( $post_id, $key, true );
                }
                $output = is_array( $meta_val ) ? implode( ', ', array_map( 'strval', $meta_val ) ) : (string) $meta_val;
                break;
        }

        if ( '1' === $atts['raw'] ) {
            return $output;
        }
        return wp_kses_post( $output );
    }

    /**
     * Render Quick Facts Grid section via shortcode
     * Usage: [trek_facts id="123"]
     */
    public static function render_trek_facts_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_facts' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $facts = $data['quick_facts'] ?? array();
        ob_start();
        ?>
        <div class="ya-trek-shortcode-facts" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin:24px 0;">
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Duration</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $data['hero']['days'] ?? '—' ); ?></strong>
            </div>
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Max Elevation</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $facts['high_point'] ?? '—' ); ?></strong>
            </div>
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Difficulty</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $data['difficulty'] ?? '—' ); ?></strong>
            </div>
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Trip Style</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $facts['style'] ?? '—' ); ?></strong>
            </div>
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Accommodation</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $facts['accom'] ?? '—' ); ?></strong>
            </div>
            <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px;">
                <span style="display:block; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#64748b;">Best Season</span>
                <strong style="color:#022f2e; font-size:15px;"><?php echo esc_html( $facts['season'] ?? '—' ); ?></strong>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Itinerary Accordions section via shortcode
     * Usage: [trek_itinerary id="123"]
     */
    public static function render_trek_itinerary_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_itinerary' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) || empty( $data['itinerary'] ) ) return '';

        $itin = $data['itinerary'];
        ob_start();
        ?>
        <div class="ya-trek-shortcode-itinerary" style="margin:24px 0;">
            <?php if ( ! empty( $data['itinerary_section']['heading'] ) ) : ?>
                <h3 style="color:#022f2e; font-size:22px; margin-bottom:16px;"><?php echo esc_html( $data['itinerary_section']['heading'] ); ?></h3>
            <?php endif; ?>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ( $itin as $day ) : ?>
                    <details style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px 18px; cursor:pointer;">
                        <summary style="font-weight:700; color:#022f2e; display:flex; justify-content:space-between; align-items:center;">
                            <span><span style="color:#c6a45f; margin-right:8px;">Day <?php echo esc_html( $day['day'] ?? '' ); ?>:</span> <?php echo esc_html( $day['title'] ?? '' ); ?></span>
                            <?php if ( ! empty( $day['stage'] ) ) : ?>
                                <span style="font-size:12px; color:#64748b; font-weight:normal;"><?php echo esc_html( $day['stage'] ); ?></span>
                            <?php endif; ?>
                        </summary>
                        <div style="margin-top:10px; color:#334155; font-size:14px; line-height:1.6;">
                            <?php if ( ! empty( $day['pills'] ) ) : ?>
                                <p style="font-size:12px; color:#045857; margin-bottom:8px;"><strong>Tags:</strong> <?php echo esc_html( $day['pills'] ); ?></p>
                            <?php endif; ?>
                            <div><?php echo wp_kses_post( $day['description'] ?? '' ); ?></div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Inclusions & Exclusions section via shortcode
     * Usage: [trek_inclusions id="123"]
     */
    public static function render_trek_inclusions_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_inclusions' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $inc = $data['inex']['inclusions'] ?? array();
        $exc = $data['inex']['exclusions'] ?? array();
        ob_start();
        ?>
        <div class="ya-trek-shortcode-inex" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px; margin:24px 0;">
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px;">
                <h4 style="color:#166534; margin-top:0; font-size:16px;">✔ <?php esc_html_e( 'Included In The Trip', 'trek-content-manager' ); ?></h4>
                <ul style="list-style:none; padding:0; margin:12px 0 0 0;">
                    <?php foreach ( $inc as $item ) : ?>
                        <li style="margin-bottom:12px; font-size:13px; color:#14532d;">
                            <strong>✔ <?php echo esc_html( $item['title'] ?? (string) $item ); ?></strong>
                            <?php if ( ! empty( $item['description'] ) ) : ?>
                                <p style="margin:4px 0 0 16px; font-size:12px; color:#15803d;"><?php echo esc_html( $item['description'] ); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:20px;">
                <h4 style="color:#991b1b; margin-top:0; font-size:16px;">✖ <?php esc_html_e( 'Not Included / Excluded', 'trek-content-manager' ); ?></h4>
                <ul style="list-style:none; padding:0; margin:12px 0 0 0;">
                    <?php foreach ( $exc as $item ) : ?>
                        <li style="margin-bottom:12px; font-size:13px; color:#7f1d1d;">
                            <strong>✖ <?php echo esc_html( $item['title'] ?? (string) $item ); ?></strong>
                            <?php if ( ! empty( $item['description'] ) ) : ?>
                                <p style="margin:4px 0 0 16px; font-size:12px; color:#b91c1c;"><?php echo esc_html( $item['description'] ); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Hero Section via shortcode
     * Usage: [trek_hero id="123"]
     */
    public static function render_trek_hero_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_hero' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $hero = $data['hero'] ?? array();
        $bg   = ! empty( $hero['image'] ) ? esc_url( $hero['image'] ) : '';
        ob_start();
        ?>
        <div class="ya-trek-shortcode-hero" style="position:relative; min-height:360px; background:#022f2e url('<?php echo $bg; ?>') center/cover no-repeat; border-radius:12px; overflow:hidden; display:flex; align-items:flex-end; padding:32px; color:#fff; margin:24px 0;">
            <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(2,47,46,0.92) 0%, rgba(2,47,46,0.3) 100%);"></div>
            <div style="position:relative; z-index:2; max-width:800px;">
                <?php if ( ! empty( $hero['script_title'] ) ) : ?>
                    <span style="font-family:'Playfair Display',serif; font-style:italic; color:#c6a45f; font-size:20px;"><?php echo esc_html( $hero['script_title'] ); ?></span>
                <?php endif; ?>
                <h1 style="font-size:32px; margin:6px 0 12px 0; color:#fff;"><?php echo esc_html( $hero['title'] ?? $data['title'] ); ?></h1>
                <?php if ( ! empty( $hero['subtitle'] ) ) : ?>
                    <p style="font-size:15px; color:#e2e8f0; line-height:1.5; margin:0;"><?php echo esc_html( $hero['subtitle'] ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Overview & Highlights via shortcode
     * Usage: [trek_overview id="123"]
     */
    public static function render_trek_overview_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_overview' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $overview = $data['overview'] ?? array();
        ob_start();
        ?>
        <div class="ya-trek-shortcode-overview" style="margin:24px 0; font-size:15px; line-height:1.7; color:#334155;">
            <?php if ( ! empty( $overview['heading'] ) ) : ?>
                <h3 style="color:#022f2e; font-size:24px; margin-bottom:14px;"><?php echo esc_html( $overview['heading'] ); ?></h3>
            <?php endif; ?>
            <div style="margin-bottom:16px;"><?php echo wp_kses_post( $overview['content'] ?? '' ); ?></div>
            <?php if ( ! empty( $overview['highlight'] ) ) : ?>
                <blockquote style="background:#f8f9fa; border-left:4px solid #c6a45f; padding:14px 18px; margin:16px 0; font-style:italic; color:#022f2e;">
                    <?php echo wp_kses_post( $overview['highlight'] ); ?>
                </blockquote>
            <?php endif; ?>
            <?php if ( ! empty( $overview['highlights'] ) && is_array( $overview['highlights'] ) ) : ?>
                <h4 style="color:#022f2e; font-size:16px; margin:16px 0 8px 0;"><?php esc_html_e( 'Key Highlights', 'trek-content-manager' ); ?></h4>
                <ul style="padding-left:20px;">
                    <?php foreach ( $overview['highlights'] as $hl ) : ?>
                        <li style="margin-bottom:6px;"><?php echo esc_html( is_array( $hl ) ? ( $hl['title'] ?? '' ) : $hl ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Gallery via shortcode
     * Usage: [trek_gallery id="123"]
     */
    public static function render_trek_gallery_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_gallery' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) || empty( $data['gallery']['images'] ) ) return '';

        $images = $data['gallery']['images'];
        ob_start();
        ?>
        <div class="ya-trek-shortcode-gallery" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin:24px 0;">
            <?php foreach ( $images as $img_url ) : ?>
                <a href="<?php echo esc_url( $img_url ); ?>" target="_blank" style="display:block; aspect-ratio:4/3; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0;">
                    <img src="<?php echo esc_url( $img_url ); ?>" alt="" style="width:100%; height:100%; object-fit:cover; display:block;">
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Essential Information / FAQs via shortcode
     * Usage: [trek_essentials id="123"]
     */
    public static function render_trek_essentials_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_essentials' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) || empty( $data['faqs'] ) ) return '';

        $faqs = $data['faqs'];
        ob_start();
        ?>
        <div class="ya-trek-shortcode-faqs" style="margin:24px 0; display:flex; flex-direction:column; gap:10px;">
            <?php foreach ( $faqs as $faq ) : ?>
                <details style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px 18px; cursor:pointer;">
                    <summary style="font-weight:700; color:#022f2e;"><?php echo esc_html( $faq['question'] ?? '' ); ?></summary>
                    <div style="margin-top:10px; color:#475569; font-size:14px; line-height:1.6;">
                        <?php echo wp_kses_post( $faq['answer'] ?? '' ); ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Reviews / Testimonials via shortcode
     * Usage: [trek_reviews id="123"]
     */
    public static function render_trek_reviews_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_reviews' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) || empty( $data['reviews_section']['reviews'] ) ) return '';

        $revs = $data['reviews_section']['reviews'];
        ob_start();
        ?>
        <div class="ya-trek-shortcode-reviews" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin:24px 0;">
            <?php foreach ( $revs as $r ) : ?>
                <div style="background:#f8f9fa; border:1px solid #e2e8f0; border-radius:8px; padding:18px;">
                    <div style="color:#c6a45f; margin-bottom:8px;"><?php echo str_repeat( '★', intval( $r['rating'] ?? 5 ) ); ?></div>
                    <blockquote style="margin:0 0 12px 0; font-size:14px; color:#334155; font-style:italic;">
                        “<?php echo esc_html( $r['quote'] ?? '' ); ?>”
                    </blockquote>
                    <strong style="color:#022f2e; font-size:13px; display:block;"><?php echo esc_html( $r['name'] ?? 'Traveller' ); ?></strong>
                    <?php if ( ! empty( $r['location'] ) ) : ?>
                        <span style="font-size:12px; color:#64748b;"><?php echo esc_html( $r['location'] ); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Fixed Departures & Contact box via shortcode
     * Usage: [trek_departures id="123"]
     */
    public static function render_trek_departures_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_departures' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $contact = $data['contact'] ?? array();
        $wa_num  = preg_replace( '/[^0-9]/', '', $contact['whatsapp'] ?? '9779803197006' );
        $wa_link = 'https://wa.me/' . $wa_num . '?text=' . rawurlencode( 'Hello Yeti Adventure, I would like to inquire about ' . ( $data['title'] ?? 'this trek' ) );
        ob_start();
        ?>
        <div class="ya-trek-shortcode-departures" style="background:#022f2e; color:#fff; border-radius:12px; padding:28px; margin:24px 0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
            <div style="max-width:560px;">
                <span style="color:#c6a45f; font-size:12px; text-transform:uppercase; letter-spacing:1px;"><?php echo esc_html( $contact['box_script'] ?? 'Join a Departure' ); ?></span>
                <h3 style="color:#fff; margin:6px 0 10px 0; font-size:22px;"><?php echo esc_html( $contact['box_title'] ?? 'Inquire About Upcoming Dates' ); ?></h3>
                <p style="color:#cbd5e1; font-size:14px; margin:0;"><?php echo esc_html( $contact['box_desc'] ?? 'Connect directly with our expedition team on WhatsApp for departures and custom routes.' ); ?></p>
            </div>
            <div>
                <a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" style="display:inline-block; background:#25d366; color:#fff; font-weight:700; padding:12px 24px; border-radius:8px; text-decoration:none; font-size:14px;">
                    💬 Chat on WhatsApp
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Map section via shortcode
     * Usage: [trek_map id="123"]
     */
    public static function render_trek_map_section( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_map' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_Renderer' ) ? Trek_Renderer::get_full_trek_data( $post_id ) : array();
        if ( empty( $data ) ) return '';

        $overview = $data['overview'] ?? array();
        ob_start();
        ?>
        <div class="ya-trek-shortcode-map" style="margin:24px 0; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0;">
            <?php if ( ! empty( $overview['map_embed_code'] ) ) : ?>
                <div class="ya-map-embed" style="width:100%; min-height:400px;"><?php echo $overview['map_embed_code']; ?></div>
            <?php elseif ( ! empty( $overview['route_img'] ) ) : ?>
                <img src="<?php echo esc_url( $overview['route_img'] ); ?>" alt="Route Map" style="width:100%; height:auto; display:block;">
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Single Destination Card via shortcode
     * Usage: [trek_card id="123"]
     */
    public static function render_trek_card_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'trek_card' );
        $post_id = ! empty( $atts['id'] ) ? absint( $atts['id'] ) : get_the_ID();
        if ( ! $post_id ) return '';

        $data = class_exists( 'Trek_CPT' ) ? Trek_CPT::get_card_data( $post_id ) : null;
        if ( ! $data ) return '';

        ob_start();
        ?>
        <div class="ya-trek-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; max-width:380px; box-shadow:0 4px 12px rgba(0,0,0,0.06); margin:16px 0;">
            <?php if ( ! empty( $data['image'] ) ) : ?>
                <a href="<?php echo esc_url( $data['permalink'] ?? get_permalink( $post_id ) ); ?>" style="display:block; aspect-ratio:16/10; overflow:hidden;">
                    <img src="<?php echo esc_url( $data['image'] ); ?>" alt="" style="width:100%; height:100%; object-fit:cover; display:block;">
                </a>
            <?php endif; ?>
            <div style="padding:18px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11px; text-transform:uppercase; letter-spacing:0.5px; color:#c6a45f; font-weight:700;"><?php echo esc_html( $data['tag'] ?? '' ); ?></span>
                    <span style="font-size:12px; background:#e2f2e9; color:#045857; padding:2px 8px; border-radius:4px; font-weight:600;"><?php echo esc_html( $data['meta'] ?? '' ); ?></span>
                </div>
                <h4 style="margin:0 0 8px 0; font-size:18px;">
                    <a href="<?php echo esc_url( $data['permalink'] ?? get_permalink( $post_id ) ); ?>" style="color:#022f2e; text-decoration:none;"><?php echo esc_html( $data['title'] ); ?></a>
                </h4>
                <p style="color:#64748b; font-size:13px; line-height:1.5; margin:0 0 14px 0;"><?php echo esc_html( $data['excerpt'] ?? '' ); ?></p>
                <a href="<?php echo esc_url( $data['permalink'] ?? get_permalink( $post_id ) ); ?>" style="display:inline-block; color:#045857; font-weight:700; font-size:13px; text-decoration:underline;">View Journey →</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
