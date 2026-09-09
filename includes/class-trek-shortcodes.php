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
        add_shortcode( 'trek_nepal_map',            array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'ya_tourism_map',            array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'trek_tourism_map',          array( __CLASS__, 'render_nepal_map' ) );
        add_shortcode( 'trek_journal_posts',         array( __CLASS__, 'render_journal_posts' ) );
        add_shortcode( 'trek_home_notes',            array( __CLASS__, 'render_home_notes' ) );
        add_shortcode( 'trek_trip_detail',           array( __CLASS__, 'render_trip_detail' ) );
        add_action( 'rest_api_init', array( __CLASS__, 'register_journal_comment_routes' ) );
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
     * Render dynamic Field Notes cards for the homepage.
     * Usage: [ya_home_notes count="6"]
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public static function render_home_notes( $atts = array() ) {
        self::$instance_counter++;
        $uid = 'ya_home_notes_' . self::$instance_counter;
        $atts = shortcode_atts( array( 'count' => 6 ), $atts, 'ya_home_notes' );
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
            return '';
        }

        ob_start();
        ?>
        <div id="<?php echo esc_attr( $uid ); ?>" class="ya-note-carousel <?php echo count( $query->posts ) > 3 ? 'is-scrollable' : 'is-static'; ?>">
            <div class="ya-note-track">
                <?php foreach ( $query->posts as $index => $post ) :
                    $categories = get_the_category( $post->ID );
                    $category   = ! empty( $categories ) ? $categories[0]->name : 'Journal';
                    $content    = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : $post->post_content;
                    $words      = str_word_count( wp_strip_all_tags( $post->post_content ) );
                    $minutes    = max( 1, (int) ceil( $words / 200 ) );
                    $image      = get_the_post_thumbnail_url( $post->ID, 'large' );
                    ?>
                    <a class="ya-note ya-reveal" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
                        <div class="ya-note-img">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post->ID ) ); ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="ya-note-body">
                            <div class="ya-note-meta"><span><?php echo esc_html( $category ); ?></span><span><?php echo esc_html( $minutes . ' min read' ); ?></span></div>
                            <h3><?php echo esc_html( get_the_title( $post->ID ) ); ?></h3>
                            <div class="ya-note-link">Read story <span aria-hidden="true">-></span></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ( count( $query->posts ) > 3 ) : ?>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                var root = document.getElementById(<?php echo wp_json_encode( $uid ); ?>);
                if (!root) return;
                var track = root.querySelector('.ya-note-track');
                var direction = 1;
                var paused = false;
                var frame;
                function move() {
                    if (!paused) {
                        var end = track.scrollWidth - track.clientWidth;
                        if (track.scrollLeft >= end - 1) direction = -1;
                        if (track.scrollLeft <= 1) direction = 1;
                        track.scrollLeft += direction * 0.45;
                    }
                    frame = requestAnimationFrame(move);
                }
                root.addEventListener('mouseenter', function () { paused = true; });
                root.addEventListener('mouseleave', function () { paused = false; });
                root.addEventListener('touchstart', function () { paused = true; }, { passive: true });
                root.addEventListener('touchend', function () { paused = false; });
                frame = requestAnimationFrame(move);
                window.addEventListener('pagehide', function () { cancelAnimationFrame(frame); }, { once: true });
            });
            </script>
        <?php endif; ?>
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
        $uid = 'ya_finder_' . self::$instance_counter;

        $atts = shortcode_atts( array(
            'title'    => 'Find Your Ideal Journey',
            'subtitle' => 'Select your preferred region, feeling, and pace to discover the journey that fits you best.',
        ), $atts, 'trek_journey_finder' );

        // Fetch published regions from database
        $regions = get_terms( array(
            'taxonomy'   => 'trek_region',
            'hide_empty' => true,
        ) );

        ob_start();
        ?>
        <div class="ya-selector-wrap" id="<?php echo esc_attr( $uid ); ?>" style="max-width:1100px; margin:40px auto; font-family:'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">
            <style>
                #<?php echo esc_attr( $uid ); ?> * { box-sizing: border-box; }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-box { padding: 34px; border-radius: 24px; background: #fff; box-shadow: 0 24px 70px rgba(2,47,46,.10); border: 1px solid rgba(17,17,17,.08); }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-row { display: grid; grid-template-columns: 200px 1fr; align-items: center; gap: 25px; padding: 18px 0; border-bottom: 1px solid rgba(17,17,17,.10); }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-row:first-child { padding-top: 0; }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-row label { font-size: 14px; color: #4a5568; font-weight: 600; }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-row select { width: 100%; appearance: none; border: 0; outline: 0; background: linear-gradient(45deg,transparent 50%,#022f2e 50%) calc(100% - 16px) 53%/6px 6px no-repeat,linear-gradient(135deg,#022f2e 50%,transparent 50%) calc(100% - 11px) 53%/6px 6px no-repeat; color: #022f2e; font-family: 'Cormorant Garamond', Georgia, serif; font-size: 22px; font-weight: 600; padding: 8px 35px 8px 0; cursor: pointer; border-bottom: 2px solid transparent; transition: border-color 0.2s; }
                #<?php echo esc_attr( $uid ); ?> .ya-choice-row select:focus { border-bottom-color: #045857; }
                #<?php echo esc_attr( $uid ); ?> .ya-match { display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: center; margin-top: 24px; padding: 24px 28px; border-radius: 18px; background: #022f2e; color: #fff; }
                #<?php echo esc_attr( $uid ); ?> .ya-match small { display: block; margin-bottom: 5px; color: #c7a767; font: 700 10px/1 'Plus Jakarta Sans', sans-serif; letter-spacing: 2px; text-transform: uppercase; }
                #<?php echo esc_attr( $uid ); ?> .ya-match strong { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 26px; line-height: 1.15; font-weight: 600; color: #fff; }
                #<?php echo esc_attr( $uid ); ?> .ya-match a { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 50%; background: #fff; color: #022f2e; text-decoration: none; transition: transform .3s ease, background-color .3s ease; }
                #<?php echo esc_attr( $uid ); ?> .ya-match a:hover { background: #c7a767; color: #fff; transform: rotate(-35deg) scale(1.05); }
                @media(max-width: 768px) {
                    #<?php echo esc_attr( $uid ); ?> .ya-choice-row { grid-template-columns: 1fr; gap: 8px; }
                    #<?php echo esc_attr( $uid ); ?> .ya-match { grid-template-columns: 1fr; }
                    #<?php echo esc_attr( $uid ); ?> .ya-match a { width: 100%; border-radius: 999px; height: 46px; }
                }
            </style>
            <div class="ya-choice-box">
                <div class="ya-choice-row">
                    <label for="<?php echo esc_attr( $uid ); ?>_region"><?php esc_html_e( 'I want to explore', 'trek-content-manager' ); ?></label>
                    <select id="<?php echo esc_attr( $uid ); ?>_region">
                        <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                            <?php foreach ( $regions as $r ) : ?>
                                <option value="<?php echo esc_attr( $r->slug ); ?>"><?php echo esc_html( $r->name ); ?></option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="everest"><?php esc_html_e( 'Everest Region', 'trek-content-manager' ); ?></option>
                            <option value="annapurna"><?php esc_html_e( 'Annapurna Region', 'trek-content-manager' ); ?></option>
                            <option value="mustang"><?php esc_html_e( 'Upper Mustang', 'trek-content-manager' ); ?></option>
                            <option value="chitwan"><?php esc_html_e( 'Chitwan & the Lowlands', 'trek-content-manager' ); ?></option>
                            <option value="culture"><?php esc_html_e( 'Kathmandu Valley', 'trek-content-manager' ); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="ya-choice-row">
                    <label for="<?php echo esc_attr( $uid ); ?>_feeling"><?php esc_html_e( 'I want to feel', 'trek-content-manager' ); ?></label>
                    <select id="<?php echo esc_attr( $uid ); ?>_feeling">
                        <option value="challenged"><?php esc_html_e( 'Challenged & Inspired', 'trek-content-manager' ); ?></option>
                        <option value="restored"><?php esc_html_e( 'Restored & Peaceful', 'trek-content-manager' ); ?></option>
                        <option value="curious"><?php esc_html_e( 'Culturally Immersed', 'trek-content-manager' ); ?></option>
                        <option value="remote"><?php esc_html_e( 'Far From Ordinary', 'trek-content-manager' ); ?></option>
                    </select>
                </div>
                <div class="ya-choice-row">
                    <label for="<?php echo esc_attr( $uid ); ?>_pace"><?php esc_html_e( 'My ideal pace', 'trek-content-manager' ); ?></label>
                    <select id="<?php echo esc_attr( $uid ); ?>_pace">
                        <option value="active"><?php esc_html_e( 'Active (5-6 hrs / day)', 'trek-content-manager' ); ?></option>
                        <option value="balanced"><?php esc_html_e( 'Balanced (4-5 hrs / day)', 'trek-content-manager' ); ?></option>
                        <option value="slow"><?php esc_html_e( 'Unhurried & Gentle', 'trek-content-manager' ); ?></option>
                        <option value="intense"><?php esc_html_e( 'Expedition-Style (6-8 hrs / day)', 'trek-content-manager' ); ?></option>
                    </select>
                </div>
                <div class="ya-match">
                    <div>
                        <small><?php esc_html_e( 'Your Curated Starting Point', 'trek-content-manager' ); ?></small>
                        <strong id="<?php echo esc_attr( $uid ); ?>_match_title"><?php esc_html_e( 'The Ultimate Everest Base Camp Trek', 'trek-content-manager' ); ?></strong>
                    </div>
                    <a id="<?php echo esc_attr( $uid ); ?>_match_link" href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>" aria-label="<?php esc_attr_e( 'View suggested journey', 'trek-content-manager' ); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M9 7h8v8"/></svg>
                    </a>
                </div>
            </div>
            <script>
                (function(){
                    const box = document.getElementById('<?php echo esc_js( $uid ); ?>');
                    if(!box) return;
                    const rEl = box.querySelector('#<?php echo esc_js( $uid ); ?>_region');
                    const fEl = box.querySelector('#<?php echo esc_js( $uid ); ?>_feeling');
                    const pEl = box.querySelector('#<?php echo esc_js( $uid ); ?>_pace');
                    const titleEl = box.querySelector('#<?php echo esc_js( $uid ); ?>_match_title');
                    const linkEl = box.querySelector('#<?php echo esc_js( $uid ); ?>_match_link');

                    const recs = {
                        everest:{active:'The Ultimate Everest Base Camp Trek',balanced:'Everest in Comfort',slow:'Everest Monasteries & Mountain Lodges',intense:'Everest Three Passes Expedition'},
                        annapurna:{active:'A Himalayan Sanctuary',balanced:'Annapurna Villages & Mountain Views',slow:'A Gentle Annapurna Escape',intense:'Annapurna Circuit Adventure'},
                        mustang:{active:'Sky Caves & Ancient Trails',balanced:'The Kingdom of Lo',slow:'Mustang Culture in Comfort',intense:'Remote Mustang Traverse'},
                        chitwan:{active:'Jungle Trails & River Life',balanced:'Where Culture Meets the Wild',slow:'A Restorative Lowland Escape',intense:'Nepal Multi-Adventure'},
                        culture:{active:'Kathmandu Valley on Foot',balanced:'Living Heritage of Nepal',slow:'Courtyards, Craft & Cuisine',intense:'Culture and Mountain Adventure'}
                    };

                    const updateFinder = () => {
                        const r = rEl?.value || 'everest';
                        const p = pEl?.value || 'active';
                        const f = fEl?.value || 'challenged';
                        let t = recs[r]?.[p] || recs['everest']?.[p] || 'The Ultimate Everest Base Camp Trek';
                        if (f === 'restored' && p !== 'slow') t = recs[r]?.slow || t;
                        if (f === 'remote' && r !== 'mustang') t = r === 'everest' ? 'Remote Khumbu Lodges & Trails' : 'The Road Less Travelled';
                        if (titleEl) titleEl.textContent = t;
                        if (linkEl) linkEl.href = '<?php echo esc_url( home_url( "/contact-us/?journey=" ) ); ?>' + encodeURIComponent(t);
                    };

                    [rEl, fEl, pEl].forEach(el => el?.addEventListener('change', updateFinder));
                    updateFinder();
                })();
            </script>
        </div>
        <?php
        return ob_get_clean();
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
}
