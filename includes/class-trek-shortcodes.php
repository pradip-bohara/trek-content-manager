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
        add_action( 'wp_ajax_ya_submit_journey_lead',        array( __CLASS__, 'ajax_submit_journey_lead' ) );
        add_action( 'wp_ajax_nopriv_ya_submit_journey_lead', array( __CLASS__, 'ajax_submit_journey_lead' ) );
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
            $card_image = 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1400&q=85';
        }

        // 4. Duration & Days Meta
        $hero_days = get_post_meta( $post_id, '_trek_hero_days', true );
        $card_meta = get_post_meta( $post_id, '_trek_card_meta', true );
        if ( empty( $card_meta ) ) {
            $card_meta = ! empty( $hero_days ) ? trim( $hero_days ) . ' Days' : '12 Days';
        }

        $duration_val = ! empty( $hero_days ) ? preg_replace( '/[^0-9]/', '', $hero_days ) : '12';
        if ( empty( $duration_val ) ) {
            $duration_val = '12';
        }

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
            $country_meta = 'Nepal';
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

        $primary_tag   = ! empty( $primary_cat_name ) ? $primary_cat_name : ( ! empty( $cat_names[0] ) ? $cat_names[0] : 'Trek' );
        $secondary_tag = ! empty( $region_names[0] ) ? $region_names[0] : ( ! empty( $cat_names[1] ) ? $cat_names[1] : '' );

        $trip_city = get_post_meta( $post_id, '_trek_city', true );
        $trip_country = get_post_meta( $post_id, '_trek_country', true );
        $trip_address = implode( ', ', array_filter( array( $trip_city, $trip_country ) ) );
        if ( empty( $trip_address ) ) {
            $trip_address = $country_meta . ', Nepal';
        }

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
     * Fallback demo trips when database is empty
     */
    private static function get_fallback_country_trips( $country = 'nepal' ) {
        $country_lower = strtolower( trim( $country ) );

        if ( strpos( $country_lower, 'india' ) !== false ) {
            return array(
                array(
                    'id'             => 101,
                    'number'         => '01',
                    'title'          => 'Mughals, Maharajas & Mystics',
                    'excerpt'        => 'Explore the royal forts of Rajasthan, the spiritual ghats of Varanasi and timeless cultural wonders.',
                    'image'          => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/mughals-maharajas-mystics/' ),
                    'duration_val'   => '14',
                    'duration_meta'  => '14 Days',
                    'duration_label' => '14-Day Trip',
                    'country'        => 'India',
                    'country_slug'   => 'india',
                    'tag'            => 'India · Cultural Heritage',
                    'tag_primary'    => 'Culture',
                    'tag_secondary'  => 'Rajasthan',
                    'category_slugs' => 'culture luxury',
                    'all_slugs'      => 'india culture luxury'
                ),
                array(
                    'id'             => 102,
                    'number'         => '02',
                    'title'          => 'Taj, Tiger & Tea',
                    'excerpt'        => 'Journey from Delhi and the Taj Mahal through Ranthambhore National Park to the scenic tea hills of Darjeeling.',
                    'image'          => 'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/taj-tiger-tea/' ),
                    'duration_val'   => '12',
                    'duration_meta'  => '12 Days',
                    'duration_label' => '12-Day Trip',
                    'country'        => 'India',
                    'country_slug'   => 'india',
                    'tag'            => 'India · Wildlife & Culture',
                    'tag_primary'    => 'Wildlife',
                    'tag_secondary'  => 'Darjeeling',
                    'category_slugs' => 'wildlife culture',
                    'all_slugs'      => 'india wildlife culture'
                ),
                array(
                    'id'             => 103,
                    'number'         => '03',
                    'title'          => 'The Buddhist Circuit',
                    'excerpt'        => 'Follow the sacred footsteps of Buddha from Lumbini to Bodhgaya, Varanasi, Rajgir and Sarnath.',
                    'image'          => 'https://images.unsplash.com/photo-1561731216-c3a4d99437d5?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/the-buddhist-circuit/' ),
                    'duration_val'   => '14',
                    'duration_meta'  => '14 Days',
                    'duration_label' => '14-Day Trip',
                    'country'        => 'India',
                    'country_slug'   => 'india',
                    'tag'            => 'India & Nepal · Spiritual',
                    'tag_primary'    => 'Spiritual',
                    'tag_secondary'  => 'Varanasi',
                    'category_slugs' => 'spiritual culture',
                    'all_slugs'      => 'india spiritual culture'
                )
            );
        } elseif ( strpos( $country_lower, 'bhutan' ) !== false ) {
            return array(
                array(
                    'id'             => 201,
                    'number'         => '01',
                    'title'          => 'Jhomolhari Base Camp Trek',
                    'excerpt'        => 'A classic Bhutanese trek into high alpine valleys under the dramatic peak of Mount Jhomolhari.',
                    'image'          => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/jhomolhari-trek/' ),
                    'duration_val'   => '11',
                    'duration_meta'  => '11 Days',
                    'duration_label' => '11-Day Trip',
                    'country'        => 'Bhutan',
                    'country_slug'   => 'bhutan',
                    'tag'            => 'Bhutan · Himalayan Trek',
                    'tag_primary'    => 'Trekking',
                    'tag_secondary'  => 'Jhomolhari',
                    'category_slugs' => 'trekking himalayan',
                    'all_slugs'      => 'bhutan trekking himalayan'
                ),
                array(
                    'id'             => 202,
                    'number'         => '02',
                    'title'          => 'Cultural Kingdom of Bhutan',
                    'excerpt'        => 'Discover the iconic Tiger’s Nest monastery, sacred dzongs and pristine valleys of Paro and Punakha.',
                    'image'          => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/cultural-bhutan/' ),
                    'duration_val'   => '8',
                    'duration_meta'  => '8 Days',
                    'duration_label' => '8-Day Trip',
                    'country'        => 'Bhutan',
                    'country_slug'   => 'bhutan',
                    'tag'            => 'Bhutan · Cultural Immersion',
                    'tag_primary'    => 'Culture',
                    'tag_secondary'  => 'Paro',
                    'category_slugs' => 'culture luxury',
                    'all_slugs'      => 'bhutan culture luxury'
                )
            );
        } elseif ( strpos( $country_lower, 'tibet' ) !== false || strpos( $country_lower, 'tibit' ) !== false ) {
            return array(
                array(
                    'id'             => 301,
                    'number'         => '01',
                    'title'          => 'Mount Kailash & Lake Manasarovar',
                    'excerpt'        => 'The sacred pilgrimage circuit around Mount Kailash crossing high trans-Himalayan passes.',
                    'image'          => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/kailash-kora/' ),
                    'duration_val'   => '16',
                    'duration_meta'  => '16 Days',
                    'duration_label' => '16-Day Trip',
                    'country'        => 'Tibet',
                    'country_slug'   => 'tibet',
                    'tag'            => 'Tibet · Sacred Kora',
                    'tag_primary'    => 'Spiritual',
                    'tag_secondary'  => 'Kailash',
                    'category_slugs' => 'spiritual trekking',
                    'all_slugs'      => 'tibet spiritual trekking'
                ),
                array(
                    'id'             => 302,
                    'number'         => '02',
                    'title'          => 'Lhasa to Kathmandu Overland',
                    'excerpt'        => 'Traverse the Tibetan Plateau from the Potala Palace to Everest North Face and into the Kathmandu Valley.',
                    'image'          => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1400&q=86',
                    'permalink'      => home_url( '/all-trips/lhasa-kathmandu-overland/' ),
                    'duration_val'   => '13',
                    'duration_meta'  => '13 Days',
                    'duration_label' => '13-Day Trip',
                    'country'        => 'Tibet',
                    'country_slug'   => 'tibet',
                    'tag'            => 'Tibet · Overland Expedition',
                    'tag_primary'    => 'Expedition',
                    'tag_secondary'  => 'Lhasa',
                    'category_slugs' => 'expedition culture',
                    'all_slugs'      => 'tibet expedition culture'
                )
            );
        }

        // Default Nepal Fallbacks
        return array(
            array(
                'id'             => 1,
                'number'         => '01',
                'title'          => 'The Ultimate Everest Base Camp Trek',
                'excerpt'        => 'Walk through the legendary Khumbu, visit Sherpa villages and stand beneath the world’s highest mountain.',
                'image'          => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1400&q=84',
                'permalink'      => home_url( '/all-trips/everest-basecamp-trek/' ),
                'duration_val'   => '15',
                'duration_meta'  => '15 Days',
                'duration_label' => '15-Day Trip',
                'country'        => 'Nepal',
                'country_slug'   => 'nepal',
                'tag'            => 'Nepal · Signature Trek',
                'tag_primary'    => 'Trekking',
                'tag_secondary'  => 'Everest',
                'category_slugs' => 'trekking himalayan',
                'all_slugs'      => 'nepal trekking himalayan'
            ),
            array(
                'id'             => 2,
                'number'         => '02',
                'title'          => 'A Himalayan Sanctuary: Annapurna’s Heart',
                'excerpt'        => 'Walk through terraced settlements and rhododendron forests into a natural amphitheatre of Himalayan peaks.',
                'image'          => 'https://images.unsplash.com/photo-1531719555052-632b0348c404?auto=format&fit=crop&w=1400&q=84',
                'permalink'      => home_url( '/all-trips/annapurna-sanctuary/' ),
                'duration_val'   => '13',
                'duration_meta'  => '13 Days',
                'duration_label' => '13-Day Trip',
                'country'        => 'Nepal',
                'country_slug'   => 'nepal',
                'tag'            => 'Nepal · Moderate Trek',
                'tag_primary'    => 'Trekking',
                'tag_secondary'  => 'Annapurna',
                'category_slugs' => 'trekking himalayan',
                'all_slugs'      => 'nepal trekking himalayan'
            ),
            array(
                'id'             => 3,
                'number'         => '03',
                'title'          => 'Sky Caves & Ancient Trails: Upper Mustang',
                'excerpt'        => 'Cross an arid trans-Himalayan landscape where cliff villages, monasteries and old trade routes still shape daily life.',
                'image'          => 'https://images.unsplash.com/photo-1540876508220-988a11575ed6?auto=format&fit=crop&w=1400&q=84',
                'permalink'      => home_url( '/all-trips/upper-mustang/' ),
                'duration_val'   => '12',
                'duration_meta'  => '12 Days',
                'duration_label' => '12-Day Trip',
                'country'        => 'Nepal',
                'country_slug'   => 'nepal',
                'tag'            => 'Nepal · Cultural Trek',
                'tag_primary'    => 'Culture',
                'tag_secondary'  => 'Mustang',
                'category_slugs' => 'culture trekking',
                'all_slugs'      => 'nepal culture trekking'
            ),
            array(
                'id'             => 4,
                'number'         => '04',
                'title'          => 'Where Culture Meets the Wild: Nepal’s Treasures',
                'excerpt'        => 'Combine Kathmandu’s living heritage, Himalayan sunrise views and wildlife safaris in Chitwan National Park.',
                'image'          => 'https://images.unsplash.com/photo-1583079903715-10d17697cd92?auto=format&fit=crop&w=1400&q=84',
                'permalink'      => home_url( '/all-trips/nepal-wild-trio/' ),
                'duration_val'   => '12',
                'duration_meta'  => '12 Days',
                'duration_label' => '12-Day Trip',
                'country'        => 'Nepal',
                'country_slug'   => 'nepal',
                'tag'            => 'Nepal · Wildlife & Culture',
                'tag_primary'    => 'Wildlife',
                'tag_secondary'  => 'Culture',
                'category_slugs' => 'wildlife culture soft',
                'all_slugs'      => 'nepal wildlife culture soft'
            )
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
            return '<p class="ya-journal-empty">No journal stories are available yet.</p>';
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
                'count'          => 6,
                'view_all_url'   => get_permalink( get_option( 'page_for_posts' ) ),
                'view_all_label' => 'View All',
            ),
            $atts,
            'ya_home_notes'
        );
        $count = max( 1, min( 30, absint( $atts['count'] ) ) );

        if ( empty( $atts['view_all_url'] ) ) {
            $atts['view_all_url'] = home_url( '/' );
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
            return '';
        }

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $uid ); ?>" class="ya-inspiration-cards">
            <style>
                #<?php echo esc_attr( $uid ); ?>,
                #<?php echo esc_attr( $uid ); ?> * { box-sizing: border-box; }
                #<?php echo esc_attr( $uid ); ?> { color: #333; font-family: "Open Sans", Arial, sans-serif; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card { display: flex; min-width: 0; flex-direction: column; padding: 0 14px 20px; border-left: 1px solid #e8e8e8; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card:first-child { border-left: 0; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-image { display: block; aspect-ratio: 1.48 / 1; overflow: hidden; background: #e9e5de; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-image img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card:hover .ya-inspiration-image img { transform: scale(1.045); }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-body { display: flex; flex: 1; flex-direction: column; padding: 16px 0 0; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-meta { display: flex; flex-wrap: wrap; gap: 0; margin: 0 0 16px; color: #999; font-size: 11px; line-height: 1.35; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-meta span + span::before { padding: 0 4px; color: #b7b7b7; content: "|"; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-title { margin: 0; color: #3d3d3d; font-family: "Newsreader", Georgia, serif; font-size: clamp(20px, 1.7vw, 25px); font-weight: 400; line-height: 1.18; letter-spacing: -.025em; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-excerpt { display: -webkit-box; margin: 14px 0 0; overflow: hidden; color: #5f5f5f; font-size: 13px; line-height: 1.45; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link { margin-top: auto; padding-top: 28px; color: #444; font-size: 14px; line-height: 1.2; text-decoration: none; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link:hover { color: #022f2e; text-decoration: underline; text-underline-offset: 4px; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-link span { margin-left: 4px; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-all { display: table; margin: 44px auto 0; padding: 11px 19px; border: 1px solid #858585; color: #022f2e; font-size: 14px; line-height: 1.2; text-decoration: none; transition: background-color .25s ease, color .25s ease; }
                #<?php echo esc_attr( $uid ); ?> .ya-inspiration-all:hover { background: #022f2e; color: #fff; }
                @media (max-width: 760px) { #<?php echo esc_attr( $uid ); ?> .ya-inspiration-grid { grid-template-columns: 1fr; gap: 30px; } #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card { padding: 0 0 30px; border-top: 1px solid #e8e8e8; border-left: 0; } #<?php echo esc_attr( $uid ); ?> .ya-inspiration-card:first-child { border-top: 0; } }
            </style>
            <div class="ya-inspiration-grid">
                <?php foreach ( $query->posts as $post ) :
                    $categories = get_the_category( $post->ID );
                    $category   = ! empty( $categories ) ? $categories[0]->name : 'Journal';
                    $content    = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : $post->post_content;
                    $excerpt    = wp_trim_words( wp_strip_all_tags( $content ), 28, '...' );
                    $image      = get_the_post_thumbnail_url( $post->ID, 'large' );
                    ?>
                    <article class="ya-inspiration-card ya-reveal">
                        <a class="ya-inspiration-image" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read %s', 'trek-content-manager' ), get_the_title( $post->ID ) ) ); ?>">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>" loading="lazy">
                            <?php endif; ?>
                        </a>
                        <div class="ya-inspiration-body">
                            <div class="ya-inspiration-meta"><span><?php echo esc_html( get_the_date( 'M j, Y', $post->ID ) ); ?></span><span><?php echo esc_html( $category ); ?></span></div>
                            <h3 class="ya-inspiration-title"><?php echo esc_html( get_the_title( $post->ID ) ); ?></h3>
                            <p class="ya-inspiration-excerpt"><?php echo esc_html( $excerpt ); ?></p>
                            <a class="ya-inspiration-link" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php esc_html_e( 'Read More', 'trek-content-manager' ); ?><span aria-hidden="true">↗</span></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <a class="ya-inspiration-all" href="<?php echo esc_url( $atts['view_all_url'] ); ?>"><?php echo esc_html( $atts['view_all_label'] ); ?></a>
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
     * Generates dynamic home signature journeys slider shell with all region cards
     * sequentially numbered 01, 02, 03... matching the home.html layout.
     */
    public static function render_signature_journeys( $atts ) {

    self::$instance_counter++;
    $uid = 'ya_sig_' . self::$instance_counter;

    $atts = shortcode_atts(
        array(
            'category'       => '',
            'region'         => 'nepal',   // Default region: Nepal
            'default_region' => 'nepal',   // Fallback default region
            'featured'       => '1',
            'count'          => 12,        // Default to 12 posts
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ),
        $atts,
        'ya_signature_journeys'
    );

    // Resolve effective region: prefer explicit att, else fall back to default_region
    $effective_region = ! empty( $atts['region'] )
        ? sanitize_title( $atts['region'] )
        : sanitize_title( $atts['default_region'] );

    $posts_count = intval( $atts['count'] );

    if ( $posts_count === 0 ) {
        $posts_count = -1;
    }

    /*
     * ---------------------------------------------------------
     * BASE QUERY
     * ---------------------------------------------------------
     */
    $query_args = array(
        'post_type'      => 'trek_destination',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_count,
        'orderby'        => sanitize_sql_orderby( $atts['orderby'] ) ?: 'menu_order title',
        'order'          => strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC',
        'no_found_rows'  => true,
    );

    /*
     * ---------------------------------------------------------
     * FEATURED FILTER
     * ---------------------------------------------------------
     */
    $featured_enabled =
        '1' === $atts['featured'] ||
        'yes' === strtolower( $atts['featured'] ) ||
        'true' === strtolower( $atts['featured'] );

    if ( $featured_enabled ) {
        $query_args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key'     => '_trek_featured_home',
                'value'   => '1',
                'compare' => '=',
            ),
            array(
                'key'     => '_trek_is_featured',
                'value'   => '1',
                'compare' => '=',
            ),
        );
    }

    /*
     * ---------------------------------------------------------
     * TAXONOMY FILTERS
     * ---------------------------------------------------------
     */
    $tax_query = array();

    /*
     * CATEGORY
     */
    if ( ! empty( $atts['category'] ) && 'all' !== strtolower( $atts['category'] ) ) {

        $cat_terms = array_filter(
            array_map(
                'sanitize_title',
                explode( ',', $atts['category'] )
            )
        );

        if ( ! empty( $cat_terms ) ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_category',
                'field'    => 'slug',
                'terms'    => $cat_terms,
                'operator' => 'IN',
            );
        }
    }

    /*
     * REGION – always applied (defaults to 'nepal')
     */
    if ( 'all' !== $effective_region ) {

        // Support comma-separated multi-region: region="nepal,bhutan"
        $reg_terms = array_filter(
            array_map(
                'sanitize_title',
                explode( ',', $effective_region )
            )
        );

        if ( ! empty( $reg_terms ) ) {
            $tax_query[] = array(
                'taxonomy' => 'trek_region',
                'field'    => 'slug',
                'terms'    => $reg_terms,
                'operator' => 'IN',
            );
        }
    }

    /*
     * Only add relation when both category and region
     * filters exist.
     */
    if ( ! empty( $tax_query ) ) {

        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        $query_args['tax_query'] = $tax_query;
    }

    /*
     * ---------------------------------------------------------
     * DATABASE QUERY
     * ---------------------------------------------------------
     */
    $query = new WP_Query( $query_args );

    $cards = array();
    $idx   = 1;

    /*
     * ---------------------------------------------------------
     * FEATURED FALLBACK
     *
     * If no featured posts exist, remove ONLY the featured
     * filter and keep category/region filters.
     * ---------------------------------------------------------
     */
    if ( ! $query->have_posts() && isset( $query_args['meta_query'] ) ) {

        unset( $query_args['meta_query'] );

        $query = new WP_Query( $query_args );
    }

    /*
     * ---------------------------------------------------------
     * BUILD CARDS FROM DATABASE
     * ---------------------------------------------------------
     */
    if ( $query->have_posts() ) {

        while ( $query->have_posts() ) {

            $query->the_post();

            $trip = self::get_formatted_trip_data(
                get_the_ID(),
                $idx
            );

            if ( ! empty( $trip ) ) {

                $cards[] = $trip;
                $idx++;
            }
        }

        wp_reset_postdata();
    }

    /*
     * ---------------------------------------------------------
     * OUTPUT
     * ---------------------------------------------------------
     */
    ob_start();
    ?>

    <div class="ya-journey-shell ya-reveal" id="<?php echo esc_attr( $uid ); ?>">

        <button
            class="ya-slider-btn ya-slider-prev"
            type="button"
            aria-label="<?php esc_attr_e( 'Previous journey', 'trek-content-manager' ); ?>"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M15 18l-6-6 6-6"/>
            </svg>
        </button>

        <div
            class="ya-journey-track"
            id="<?php echo esc_attr( $uid ); ?>_track"
        >

            <?php foreach ( $cards as $c ) : ?>

                <a
                    class="ya-journey-card"
                    href="<?php echo esc_url( $c['permalink'] ); ?>"
                    style="--img:url('<?php echo esc_url( $c['image'] ); ?>')"
                >

                    <span class="ya-card-meta">
                        <?php echo esc_html( $c['duration_meta'] ); ?>
                    </span>

                    <span class="ya-card-number">
                        <?php echo esc_html( $c['number'] ); ?>
                    </span>

                    <div class="ya-card-body">

                        <small>
                            <?php echo esc_html( $c['tag'] ); ?>
                        </small>

                        <h3>
                            <?php echo esc_html( $c['title'] ); ?>
                        </h3>

                        <p>
                            <?php echo esc_html( $c['excerpt'] ); ?>
                        </p>

                        <div class="ya-card-link">

                            <span>
                                <?php esc_html_e( 'View journey', 'trek-content-manager' ); ?>
                            </span>

                            <b>
                                <svg
                                    class="ya-icon-arrow"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                    focusable="false"
                                >
                                    <path d="M7 17L17 7M9 7h8v8"/>
                                </svg>
                            </b>

                        </div>

                    </div>

                </a>

            <?php endforeach; ?>

        </div>

        <button
            class="ya-slider-btn ya-slider-next"
            type="button"
            aria-label="<?php esc_attr_e( 'Next journey', 'trek-content-manager' ); ?>"
        >
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M9 18l6-6-6-6"/>
            </svg>
        </button>

    </div>

    <script>
        (function() {
            const shell = document.getElementById('<?php echo esc_js( $uid ); ?>');
            if (!shell) return;
            const track = shell.querySelector('#<?php echo esc_js( $uid ); ?>_track');
            const prev = shell.querySelector('.ya-slider-prev');
            const next = shell.querySelector('.ya-slider-next');
            const scrollAmount = () => (track ? Math.max(track.clientWidth * 0.8, 300) : 320);

            if (prev && track) {
                prev.addEventListener('click', function() {
                    track.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
                });
            }
            if (next && track) {
                next.addEventListener('click', function() {
                    track.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                });
            }
        })();
    </script>

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

        // Map published destinations if available to provide direct links
        $published_treks = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'fields'         => 'ids',
        ) );
        $trek_links_map = array();
        if ( ! empty( $published_treks ) ) {
            foreach ( $published_treks as $tid ) {
                $t_title = get_the_title( $tid );
                if ( ! empty( $t_title ) ) {
                    $trek_links_map[ trim( mb_strtolower( $t_title ) ) ] = get_permalink( $tid );
                }
            }
        }

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'trek_journey_lead_nonce' );

        ob_start();
        ?>
        <section class="ya-selector <?php echo esc_attr( $atts['class'] ); ?>" id="<?php echo esc_attr( $section_id ); ?>" data-uid="<?php echo esc_attr( $uid ); ?>">
            <style>
                #<?php echo esc_attr( $section_id ); ?>,
                #<?php echo esc_attr( $section_id ); ?> * {
                    box-sizing: border-box;
                }

                #<?php echo esc_attr( $section_id ); ?> {
                    --forest: #022f2e;
                    --gold: #c7a767;
                    --paper: #fbf9f4;
                    --text: #111111;
                    --muted: #666666;
                    --line: rgba(17, 17, 17, .14);
                    --font-display: "Newsreader", "Cormorant Garamond", Georgia, serif;
                    --font-sans: "Open Sans", "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

                    width: 100%;
                    padding: 100px 0;
                    background: var(--paper);
                    color: var(--text);
                    font-family: var(--font-sans);
                    overflow: hidden;
                    position: relative;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-inner {
                    position: relative;
                    width: min(1240px, calc(100% - 56px));
                    margin: 0 auto;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-selector-grid {
                    display: grid;
                    grid-template-columns: .85fr 1.15fr;
                    gap: 70px;
                    align-items: center;
                }

                #<?php echo esc_attr( $section_id ); ?> h2 {
                    margin: 0;
                    color: var(--text);
                    font-family: var(--font-display);
                    font-size: clamp(38px, 4.8vw, 68px);
                    font-weight: 400;
                    line-height: 1.05;
                    letter-spacing: -.035em;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-selector-copy p {
                    max-width: 540px;
                    margin: 22px 0 0;
                    color: var(--muted);
                    font-size: 15.5px;
                    line-height: 1.8;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-box {
                    padding: 34px;
                    border-radius: 28px;
                    background: #ffffff;
                    box-shadow: 0 24px 70px rgba(2, 47, 46, .10);
                    border: 1px solid rgba(17, 17, 17, .08);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-row {
                    display: grid;
                    grid-template-columns: 180px 1fr;
                    gap: 25px;
                    align-items: center;
                    padding: 18px 0;
                    border-bottom: 1px solid var(--line);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-row:first-child {
                    padding-top: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-row label {
                    color: var(--muted);
                    font-size: 13px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-row select {
                    width: 100%;
                    padding: 6px 35px 6px 0;
                    border: 0;
                    outline: 0;
                    appearance: none;
                    background:
                        linear-gradient(45deg, transparent 50%, var(--forest) 50%) calc(100% - 16px) 53% / 6px 6px no-repeat,
                        linear-gradient(135deg, var(--forest) 50%, transparent 50%) calc(100% - 11px) 53% / 6px 6px no-repeat;
                    color: var(--text);
                    cursor: pointer;
                    font-family: var(--font-display);
                    font-size: 24px;
                    font-weight: 500;
                    border-bottom: 2px solid transparent;
                    transition: border-color .2s;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-choice-row select:focus {
                    border-bottom-color: var(--gold);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match {
                    display: grid;
                    grid-template-columns: 1fr auto;
                    gap: 24px;
                    align-items: center;
                    margin-top: 26px;
                    padding: 24px;
                    border-radius: 20px;
                    background: var(--forest);
                    color: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match small {
                    display: block;
                    margin-bottom: 6px;
                    color: rgba(255, 255, 255, .72);
                    font: 700 9.5px/1 var(--font-sans);
                    letter-spacing: 2px;
                    text-transform: uppercase;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match strong {
                    display: block;
                    font-family: var(--font-display);
                    font-size: clamp(22px, 2.3vw, 29px);
                    font-weight: 500;
                    line-height: 1.15;
                    color: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match a {
                    display: grid;
                    width: 50px;
                    height: 50px;
                    place-items: center;
                    border-radius: 50%;
                    background: #ffffff;
                    color: var(--forest);
                    text-decoration: none;
                    transition: background .3s ease, transform .3s ease, color .3s ease;
                    flex-shrink: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-match a:hover {
                    background: var(--gold);
                    color: #ffffff;
                    transform: rotate(-35deg) scale(1.05);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-icon-arrow {
                    width: 20px;
                    height: 20px;
                    fill: none;
                    stroke: currentColor;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                    stroke-width: 2;
                }

                /* Email Collection Box */
                #<?php echo esc_attr( $section_id ); ?> .ya-email-collect {
                    margin-top: 22px;
                    padding-top: 22px;
                    border-top: 1px solid var(--line);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-header {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 12px;
                    color: var(--forest);
                    font-size: 13px;
                    font-weight: 600;
                    letter-spacing: 0.2px;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-icon {
                    width: 16px;
                    height: 16px;
                    color: var(--gold);
                    flex-shrink: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-group {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-input {
                    flex: 1;
                    height: 48px;
                    padding: 0 18px;
                    border: 1px solid rgba(2, 47, 46, .20);
                    border-radius: 999px;
                    background: #fdfdfd;
                    color: var(--text);
                    font-family: var(--font-sans);
                    font-size: 14px;
                    outline: none;
                    transition: border-color .25s ease, box-shadow .25s ease, background-color .25s ease;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-input:focus {
                    border-color: var(--forest);
                    box-shadow: 0 0 0 3px rgba(2, 47, 46, .08);
                    background: #ffffff;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-btn {
                    height: 48px;
                    padding: 0 24px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    border: 0;
                    border-radius: 999px;
                    background: var(--forest);
                    color: #ffffff;
                    font-family: var(--font-sans);
                    font-size: 13.5px;
                    font-weight: 600;
                    cursor: pointer;
                    white-space: nowrap;
                    transition: background-color .3s ease, transform .2s ease, opacity .2s ease;
                    flex-shrink: 0;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-btn:hover {
                    background: var(--gold);
                    transform: translateY(-1px);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-btn:disabled {
                    opacity: 0.65;
                    cursor: not-allowed;
                    transform: none;
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-btn-loader {
                    width: 14px;
                    height: 14px;
                    border: 2px solid rgba(255, 255, 255, .3);
                    border-top-color: #ffffff;
                    border-radius: 50%;
                    animation: yaFinderSpin 0.7s infinite linear;
                }

                @keyframes yaFinderSpin {
                    to { transform: rotate(360deg); }
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-email-feedback {
                    margin-top: 12px;
                    padding: 12px 16px;
                    border-radius: 12px;
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
                    transform: translateY(30px);
                    transition: opacity .85s cubic-bezier(.2, .7, .2, 1), transform .85s cubic-bezier(.2, .7, .2, 1);
                }

                #<?php echo esc_attr( $section_id ); ?> .ya-reveal.is-visible {
                    opacity: 1;
                    transform: none;
                }

                @media (max-width: 1100px) {
                    #<?php echo esc_attr( $section_id ); ?> .ya-selector-grid {
                        grid-template-columns: 1fr;
                        gap: 50px;
                    }
                }

                @media (max-width: 820px) {
                    #<?php echo esc_attr( $section_id ); ?> {
                        padding: 70px 0;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-inner {
                        width: min(100% - 30px, 1240px);
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-choice-row {
                        grid-template-columns: 1fr;
                        gap: 8px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-choice-row select {
                        font-size: 22px;
                    }
                }

                @media (max-width: 560px) {
                    #<?php echo esc_attr( $section_id ); ?> .ya-choice-box {
                        padding: 24px 18px;
                        border-radius: 20px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-match {
                        grid-template-columns: 1fr;
                        padding: 20px 18px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-match a {
                        width: 100%;
                        border-radius: 999px;
                        height: 44px;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-email-group {
                        flex-direction: column;
                    }
                    #<?php echo esc_attr( $section_id ); ?> .ya-email-btn {
                        width: 100%;
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
                    <h2><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p>
                        <?php echo esc_html( $atts['subtitle'] ); ?>
                    </p>
                </div>

                <div class="ya-choice-box ya-reveal">
                    <div class="ya-choice-row">
                        <label for="<?php echo esc_attr( $uid ); ?>_region"><?php esc_html_e( 'I want to explore', 'trek-content-manager' ); ?></label>
                        <select id="<?php echo esc_attr( $uid ); ?>_region" class="ya-select-region">
                            <option value="everest"><?php esc_html_e( 'Everest Region', 'trek-content-manager' ); ?></option>
                            <option value="annapurna"><?php esc_html_e( 'Annapurna Region', 'trek-content-manager' ); ?></option>
                            <option value="mustang"><?php esc_html_e( 'Upper Mustang', 'trek-content-manager' ); ?></option>
                            <option value="chitwan"><?php esc_html_e( 'Chitwan & the Lowlands', 'trek-content-manager' ); ?></option>
                            <option value="culture"><?php esc_html_e( 'Kathmandu Valley', 'trek-content-manager' ); ?></option>
                        </select>
                    </div>

                    <div class="ya-choice-row">
                        <label for="<?php echo esc_attr( $uid ); ?>_feeling"><?php esc_html_e( 'I want to feel', 'trek-content-manager' ); ?></label>
                        <select id="<?php echo esc_attr( $uid ); ?>_feeling" class="ya-select-feeling">
                            <option value="challenged"><?php esc_html_e( 'Challenged', 'trek-content-manager' ); ?></option>
                            <option value="restored"><?php esc_html_e( 'Restored', 'trek-content-manager' ); ?></option>
                            <option value="curious"><?php esc_html_e( 'Culturally immersed', 'trek-content-manager' ); ?></option>
                            <option value="remote"><?php esc_html_e( 'Far from ordinary', 'trek-content-manager' ); ?></option>
                        </select>
                    </div>

                    <div class="ya-choice-row">
                        <label for="<?php echo esc_attr( $uid ); ?>_pace"><?php esc_html_e( 'My ideal pace', 'trek-content-manager' ); ?></label>
                        <select id="<?php echo esc_attr( $uid ); ?>_pace" class="ya-select-pace">
                            <option value="active"><?php esc_html_e( 'Active', 'trek-content-manager' ); ?></option>
                            <option value="balanced"><?php esc_html_e( 'Balanced', 'trek-content-manager' ); ?></option>
                            <option value="slow"><?php esc_html_e( 'Unhurried', 'trek-content-manager' ); ?></option>
                            <option value="intense"><?php esc_html_e( 'Expedition-style', 'trek-content-manager' ); ?></option>
                        </select>
                    </div>

                    <div class="ya-match">
                        <div>
                            <small><?php esc_html_e( 'Your starting point', 'trek-content-manager' ); ?></small>
                            <strong id="<?php echo esc_attr( $uid ); ?>_match_title" class="ya-match-title" aria-live="polite"><?php esc_html_e( 'The Ultimate Everest Base Camp Trek', 'trek-content-manager' ); ?></strong>
                        </div>
                        <a id="<?php echo esc_attr( $uid ); ?>_match_link" class="ya-match-link" href="<?php echo esc_url( home_url( '/contact-us/?journey=' . rawurlencode( 'The Ultimate Everest Base Camp Trek' ) ) ); ?>" aria-label="<?php esc_attr_e( 'View suggested journey', 'trek-content-manager' ); ?>">
                            <svg class="ya-icon-arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M7 17L17 7M9 7h8v8" />
                            </svg>
                        </a>
                    </div>

                    <!-- EMAIL COLLECTION / SEND ITINERARY -->
                    <div class="ya-email-collect">
                        <div class="ya-email-header">
                            <svg class="ya-email-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <span><?php esc_html_e( 'Email me this customized route itinerary & trip guide', 'trek-content-manager' ); ?></span>
                        </div>
                        <form class="ya-journey-email-form" id="<?php echo esc_attr( $uid ); ?>_email_form" data-ajax-url="<?php echo esc_url( $ajax_url ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" onsubmit="return false;">
                            <div class="ya-email-group">
                                <input type="email" id="<?php echo esc_attr( $uid ); ?>_email_input" name="email" class="ya-email-input" placeholder="<?php esc_attr_e( 'Enter your email address...', 'trek-content-manager' ); ?>" required autocomplete="email" />
                                <button type="submit" id="<?php echo esc_attr( $uid ); ?>_submit_btn" class="ya-email-btn" aria-label="<?php esc_attr_e( 'Send Itinerary to Email', 'trek-content-manager' ); ?>">
                                    <span class="ya-btn-text"><?php esc_html_e( 'Send Itinerary', 'trek-content-manager' ); ?></span>
                                    <span class="ya-btn-loader" style="display:none;" aria-hidden="true"></span>
                                    <svg class="ya-btn-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                    </svg>
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

                    const rEl = section.querySelector('.ya-select-region');
                    const fEl = section.querySelector('.ya-select-feeling');
                    const pEl = section.querySelector('.ya-select-pace');
                    const titleEl = section.querySelector('.ya-match-title');
                    const linkEl = section.querySelector('.ya-match-link');

                    const formEl = section.querySelector('.ya-journey-email-form');
                    const emailInput = section.querySelector('.ya-email-input');
                    const submitBtn = section.querySelector('.ya-email-btn');
                    const btnText = submitBtn ? submitBtn.querySelector('.ya-btn-text') : null;
                    const btnLoader = submitBtn ? submitBtn.querySelector('.ya-btn-loader') : null;
                    const btnIcon = submitBtn ? submitBtn.querySelector('.ya-btn-icon') : null;
                    const feedbackEl = section.querySelector('.ya-email-feedback');

                    // Known trip links published in WordPress database
                    const tripLinks = <?php echo wp_json_encode( $trek_links_map ); ?> || {};
                    const contactBase = '<?php echo esc_url( home_url( '/contact-us/?journey=' ) ); ?>';

                    const recommendations = {
                        everest: {
                            active: 'The Ultimate Everest Base Camp Trek',
                            balanced: 'Everest in Comfort',
                            slow: 'Everest Monasteries & Mountain Lodges',
                            intense: 'Everest Three Passes Expedition'
                        },
                        annapurna: {
                            active: 'A Himalayan Sanctuary',
                            balanced: 'Annapurna Villages & Mountain Views',
                            slow: 'A Gentle Annapurna Escape',
                            intense: 'Annapurna Circuit Adventure'
                        },
                        mustang: {
                            active: 'Sky Caves & Ancient Trails',
                            balanced: 'The Kingdom of Lo',
                            slow: 'Mustang Culture in Comfort',
                            intense: 'Remote Mustang Traverse'
                        },
                        chitwan: {
                            active: 'Jungle Trails & River Life',
                            balanced: 'Where Culture Meets the Wild',
                            slow: 'A Restorative Lowland Escape',
                            intense: 'Nepal Multi-Adventure'
                        },
                        culture: {
                            active: 'Kathmandu Valley on Foot',
                            balanced: 'Living Heritage of Nepal',
                            slow: 'Courtyards, Craft & Cuisine',
                            intense: 'Culture and Mountain Adventure'
                        }
                    };

                    const updateMatch = () => {
                        const selectedRegion = rEl ? rEl.value : 'everest';
                        const selectedPace = pEl ? pEl.value : 'active';
                        const selectedFeeling = fEl ? fEl.value : 'challenged';

                        let title = recommendations[selectedRegion]?.[selectedPace] ||
                            recommendations[selectedRegion]?.active ||
                            'The Ultimate Everest Base Camp Trek';

                        if (selectedFeeling === 'restored' && selectedPace !== 'slow') {
                            title = recommendations[selectedRegion]?.slow || title;
                        }

                        if (selectedFeeling === 'remote' && selectedRegion !== 'mustang') {
                            title = (selectedRegion === 'everest')
                                ? 'Remote Khumbu Lodges & Trails'
                                : 'The Road Less Travelled';
                        }

                        if (titleEl) {
                            titleEl.textContent = title;
                        }

                        if (linkEl) {
                            const key = title.trim().toLowerCase();
                            if (tripLinks[key]) {
                                linkEl.href = tripLinks[key];
                            } else {
                                linkEl.href = contactBase + encodeURIComponent(title);
                            }
                        }
                    };

                    [rEl, fEl, pEl].forEach(field => {
                        if (field) {
                            field.addEventListener('change', updateMatch);
                        }
                    });
                    updateMatch();

                    // Scroll reveal animation
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

                    // Email Form Submission
                    if (formEl && submitBtn && emailInput) {
                        formEl.addEventListener('submit', function(e) {
                            e.preventDefault();

                            const email = emailInput.value.trim();
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!email || !emailRegex.test(email)) {
                                showFeedback('<?php echo esc_js( __( 'Please enter a valid email address.', 'trek-content-manager' ) ); ?>', 'error');
                                emailInput.focus();
                                return;
                            }

                            const region = rEl ? rEl.value : 'everest';
                            const feeling = fEl ? fEl.value : 'challenged';
                            const pace = pEl ? pEl.value : 'active';
                            const trekTitle = titleEl ? titleEl.textContent.trim() : 'The Ultimate Everest Base Camp Trek';

                            const ajaxUrl = formEl.getAttribute('data-ajax-url') || '<?php echo esc_url( $ajax_url ); ?>';
                            const nonce = formEl.getAttribute('data-nonce') || '<?php echo esc_js( $nonce ); ?>';

                            setLoading(true);

                            const formData = new FormData();
                            formData.append('action', 'ya_submit_journey_lead');
                            formData.append('nonce', nonce);
                            formData.append('email', email);
                            formData.append('region', region);
                            formData.append('feeling', feeling);
                            formData.append('pace', pace);
                            formData.append('trek_title', trekTitle);

                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                setLoading(false);
                                if (data && data.success) {
                                    showFeedback(data.data && data.data.message ? data.data.message : '<?php echo esc_js( __( '✓ Thank you! Your personalized itinerary has been sent to your email.', 'trek-content-manager' ) ); ?>', 'success');
                                    emailInput.value = '';
                                } else {
                                    const errMsg = (data && data.data && data.data.message) ? data.data.message : '<?php echo esc_js( __( 'Could not send itinerary. Please try again.', 'trek-content-manager' ) ); ?>';
                                    showFeedback(errMsg, 'error');
                                }
                            })
                            .catch(err => {
                                setLoading(false);
                                showFeedback('<?php echo esc_js( __( 'Network error. Please try again later.', 'trek-content-manager' ) ); ?>', 'error');
                            });
                        });
                    }

                    function setLoading(isLoading) {
                        if (!submitBtn) return;
                        submitBtn.disabled = isLoading;
                        if (btnText) btnText.textContent = isLoading ? '<?php echo esc_js( __( 'Sending...', 'trek-content-manager' ) ); ?>' : '<?php echo esc_js( __( 'Send Itinerary', 'trek-content-manager' ) ); ?>';
                        if (btnLoader) btnLoader.style.display = isLoading ? 'inline-block' : 'none';
                        if (btnIcon) btnIcon.style.display = isLoading ? 'none' : 'inline-block';
                    }

                    function showFeedback(msg, type) {
                        if (!feedbackEl) return;
                        feedbackEl.textContent = msg;
                        feedbackEl.className = 'ya-email-feedback ' + (type === 'success' ? 'is-success' : 'is-error');
                        feedbackEl.style.display = 'block';
                    }
                })();
            </script>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handler for Journey Selector Lead Submissions
     * Stores lead in `trek_enquiry` post type and sends custom HTML itinerary email.
     */
    public static function ajax_submit_journey_lead() {
        check_ajax_referer( 'trek_journey_lead_nonce', 'nonce' );

        $email      = sanitize_email( $_POST['email'] ?? '' );
        $region     = sanitize_text_field( $_POST['region'] ?? 'everest' );
        $feeling    = sanitize_text_field( $_POST['feeling'] ?? 'challenged' );
        $pace       = sanitize_text_field( $_POST['pace'] ?? 'active' );
        $trek_title = sanitize_text_field( $_POST['trek_title'] ?? 'The Ultimate Everest Base Camp Trek' );

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'trek-content-manager' ) ) );
        }

        // Region label map
        $region_labels = array(
            'everest'   => __( 'Everest Region', 'trek-content-manager' ),
            'annapurna' => __( 'Annapurna Region', 'trek-content-manager' ),
            'mustang'   => __( 'Upper Mustang', 'trek-content-manager' ),
            'chitwan'   => __( 'Chitwan & the Lowlands', 'trek-content-manager' ),
            'culture'   => __( 'Kathmandu Valley', 'trek-content-manager' ),
        );
        $region_name = $region_labels[ $region ] ?? ucwords( str_replace( array( '-', '_' ), ' ', $region ) );

        // Feeling label map
        $feeling_labels = array(
            'challenged' => __( 'Challenged & Inspired', 'trek-content-manager' ),
            'restored'   => __( 'Restored & Peaceful', 'trek-content-manager' ),
            'curious'    => __( 'Culturally Immersed', 'trek-content-manager' ),
            'remote'     => __( 'Far From Ordinary', 'trek-content-manager' ),
        );
        $feeling_name = $feeling_labels[ $feeling ] ?? ucwords( $feeling );

        // Pace label map
        $pace_labels = array(
            'active'   => __( 'Active', 'trek-content-manager' ),
            'balanced' => __( 'Balanced', 'trek-content-manager' ),
            'slow'     => __( 'Unhurried', 'trek-content-manager' ),
            'intense'  => __( 'Expedition-Style', 'trek-content-manager' ),
        );
        $pace_name = $pace_labels[ $pace ] ?? ucwords( $pace );

        // Check if matching destination exists in WordPress database
        $matched_post_id   = 0;
        $destination_url   = home_url( '/contact-us/?journey=' . rawurlencode( $trek_title ) );
        $destination_intro = '';

        $lookup = get_page_by_title( $trek_title, OBJECT, 'trek_destination' );
        if ( $lookup && 'publish' === $lookup->post_status ) {
            $matched_post_id   = $lookup->ID;
            $destination_url   = get_permalink( $matched_post_id );
            $destination_intro = wp_strip_all_tags( $lookup->post_excerpt ?: wp_trim_words( $lookup->post_content, 35 ) );
        }

        if ( empty( $destination_intro ) ) {
            // Default rich curated blurbs for selected journey
            $blurbs = array(
                'The Ultimate Everest Base Camp Trek'     => 'A high-altitude classic leading into the heart of the Khumbu, crossing legendary suspension bridges, Sherpa villages, and ancient monasteries beneath the world’s greatest peaks.',
                'Everest in Comfort'                      => 'Experience the majestic Khumbu valley staying at comfortable luxury lodges with heated blankets, local gourmet dining, and panoramic views of Ama Dablam.',
                'Everest Monasteries & Mountain Lodges'   => 'An unhurried spiritual and cultural journey through Tengboche and Thame monasteries, taking gentle acclimatization days in alpine forests.',
                'Everest Three Passes Expedition'         => 'A demanding expedition crossing Kongma La, Cho La, and Renjo La passes for seasoned trekkers seeking raw Himalayan wilderness.',
                'A Himalayan Sanctuary'                   => 'Trek into the glacial amphitheatre of Annapurna Sanctuary, surrounded 360 degrees by 7,000 and 8,000-metre snow peaks.',
                'Annapurna Villages & Mountain Views'     => 'Gentle terraced hillsides, vibrant Gurung hospitality, and sunrise panoramas over Dhaulagiri and Machapuchare.',
                'A Gentle Annapurna Escape'               => 'Unhurried walks between charming heritage lodges with peaceful forest walks and riverside relaxation.',
                'Annapurna Circuit Adventure'             => 'An epic circumnavigation crossing the 5,416m Thorong La pass from lush subtropical valleys into Tibetan-influenced high desert.',
                'Sky Caves & Ancient Trails'              => 'Venture into Upper Mustang’s wind-sculpted sandstone canyons, 800-year-old cliff caves, and fortified medieval villages.',
                'The Kingdom of Lo'                       => 'Journey to the walled capital of Lo Manthang, experiencing pure Tibetan Buddhist traditions, royal palaces, and ancient chortens.',
                'Mustang Culture in Comfort'              => 'Discover Upper Mustang staying in heritage boutique guest houses and luxury lodges surrounded by dramatic trans-Himalayan landscapes.',
                'Remote Mustang Traverse'                 => 'A rugged, seldom-trodden route across high passes connecting hidden gompas and deep desert river gorges.',
                'Jungle Trails & River Life'              => 'Canoeing down mist-shrouded rivers, walking safaris in Chitwan National Park, and tracking one-horned rhinos and Bengal tigers.',
                'Where Culture Meets the Wild'            => 'A harmonious balance of Tharu indigenous traditions, organic culinary experiences, and peaceful wildlife encounters.',
                'A Restorative Lowland Escape'            => 'Lush luxury jungle lodges, sunset river decks, and gentle nature walks designed for rest and regeneration.',
                'Nepal Multi-Adventure'                   => 'From white-water river rafting to elephant conservation sanctuaries and deep jungle expeditions.',
                'Kathmandu Valley on Foot'                => 'Ridge walks connecting medieval hill stations, pine forests, and timeless Newari brick towns.',
                'Living Heritage of Nepal'                => 'Centuries of artistry, bronze casting, wood carving, and pagoda architecture across Kathmandu, Patan, and Bhaktapur.',
                'Courtyards, Craft & Cuisine'             => 'An intimate slow-travel exploration of traditional culinary secrets, monastic chanting, and artisan pottery studios.',
                'Culture and Mountain Adventure'          => 'Combine ancient UNESCO World Heritage sanctuaries with scenic Himalayan foothills and sunrise viewpoints.',
                'Remote Khumbu Lodges & Trails'           => 'Trek away from the main Everest highway into pristine side valleys, tranquil glacial lakes, and secluded Sherpa settlements.',
                'The Road Less Travelled'                 => 'Off-the-beaten-path paths revealing untouched wilderness, raw mountain hospitality, and deep wilderness tranquility.',
            );
            $destination_intro = $blurbs[ $trek_title ] ?? 'An extraordinary Himalayan journey curated to immerse you in breathtaking mountain scenery, rich cultural heritage, and pristine trails.';
        }

        // 1. Store Lead in WordPress Database as `trek_enquiry`
        $post_title = sprintf( 'Journey Lead: %s (%s)', $trek_title, $email );
        $post_content = sprintf(
            "Traveller Email: %s\nSuggested Journey: %s\nRegion: %s\nDesired Feeling: %s\nIdeal Pace: %s\nSource: Journey Selector\nTimestamp: %s\nIP: %s",
            $email,
            $trek_title,
            $region_name,
            $feeling_name,
            $pace_name,
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
            update_post_meta( $enquiry_id, '_trek_enquiry_trek_title', $trek_title );
            update_post_meta( $enquiry_id, '_trek_enquiry_trek_id', $matched_post_id );
            update_post_meta( $enquiry_id, '_trek_enquiry_source', 'Journey Selector' );
            update_post_meta( $enquiry_id, '_trek_enquiry_region', $region );
            update_post_meta( $enquiry_id, '_trek_enquiry_feeling', $feeling );
            update_post_meta( $enquiry_id, '_trek_enquiry_pace', $pace );
            update_post_meta( $enquiry_id, '_trek_enquiry_date', current_time( 'mysql' ) );
        }

        // 2. Prepare and Send Customer Itinerary Email via wp_mail()
        $site_name   = get_bloginfo( 'name' ) ?: 'Yeti Adventure';
        $admin_email = get_option( 'admin_email' );

        $email_subject = sprintf( __( 'Your Curated Nepal Journey: %s | %s', 'trek-content-manager' ), $trek_title, $site_name );

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
                            <!-- Intro Copy -->
                            <tr>
                                <td style="padding:35px 35px 25px;">
                                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#333333;">Dear Explorer,</p>
                                    <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#555555;">
                                        Thank you for using our interactive Journey Selector. Based on the landscape, feeling, and pace that draw you in, our mountain planners have curated this starting point for your next Himalayan adventure:
                                    </p>

                                    <!-- Recommended Trek Card -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f5f8f7;border-left:4px solid #c7a767;border-radius:8px;margin-bottom:28px;">
                                        <tr>
                                            <td style="padding:22px 24px;">
                                                <span style="display:block;color:#c7a767;font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;">CURATED STARTING POINT</span>
                                                <h2 style="margin:0 0 12px;font-family:Georgia,serif;font-size:24px;color:#022f2e;line-height:1.25;"><?php echo esc_html( $trek_title ); ?></h2>
                                                <p style="margin:0 0 16px;font-size:14.5px;line-height:1.7;color:#444444;"><?php echo esc_html( $destination_intro ); ?></p>
                                                
                                                <!-- Meta Pills -->
                                                <table border="0" cellpadding="0" cellspacing="0" style="margin-top:10px;">
                                                    <tr>
                                                        <td style="padding-right:12px;padding-bottom:6px;">
                                                            <span style="display:inline-block;background:#ffffff;padding:5px 12px;border-radius:999px;font-size:12px;color:#022f2e;border:1px solid #d8e5e2;"><strong>Region:</strong> <?php echo esc_html( $region_name ); ?></span>
                                                        </td>
                                                        <td style="padding-right:12px;padding-bottom:6px;">
                                                            <span style="display:inline-block;background:#ffffff;padding:5px 12px;border-radius:999px;font-size:12px;color:#022f2e;border:1px solid #d8e5e2;"><strong>Pace:</strong> <?php echo esc_html( $pace_name ); ?></span>
                                                        </td>
                                                        <td style="padding-bottom:6px;">
                                                            <span style="display:inline-block;background:#ffffff;padding:5px 12px;border-radius:999px;font-size:12px;color:#022f2e;border:1px solid #d8e5e2;"><strong>Feeling:</strong> <?php echo esc_html( $feeling_name ); ?></span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- CTA Button -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom:30px;">
                                        <tr>
                                            <td align="center">
                                                <a href="<?php echo esc_url( $destination_url ); ?>" style="display:inline-block;background-color:#022f2e;color:#ffffff;text-decoration:none;padding:15px 32px;border-radius:999px;font-size:14px;font-weight:700;letter-spacing:0.5px;">
                                                    Explore This Journey &amp; Route Details &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#666666;">
                                        Every Yeti Adventure itinerary is completely customizable. We can adjust the route, pacing, lodge standards, and acclimatization days to shape the perfect experience around you.
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
                                        You received this email because you requested route information from the Journey Selector on <?php echo esc_html( home_url() ); ?>.
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
        $admin_subject = sprintf( '[New Journey Lead] %s - %s', $trek_title, $email );
        $admin_body = sprintf(
            "A new traveller has requested an itinerary from the Journey Selector:\n\n" .
            "Email: %s\n" .
            "Suggested Journey: %s\n" .
            "Region: %s\n" .
            "Feeling: %s\n" .
            "Pace: %s\n" .
            "Date: %s\n\n" .
            "View in WordPress Dashboard:\n%s\n",
            $email,
            $trek_title,
            $region_name,
            $feeling_name,
            $pace_name,
            current_time( 'mysql' ),
            admin_url( 'post.php?post=' . $enquiry_id . '&action=edit' )
        );
        wp_mail( $admin_email, $admin_subject, $admin_body );

        wp_send_json_success( array(
            'message' => sprintf( __( '✓ Thank you! We have sent your personalized itinerary for %s to %s. Please check your inbox.', 'trek-content-manager' ), $trek_title, $email ),
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
