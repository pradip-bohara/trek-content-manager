<?php
/**
 * Trek Content Manager - Journey Matcher
 *
 * Dedicated, deterministic, database-driven matching engine.
 * Calculates weighted keyword scores against published trek_destination records
 * and resolves ties deterministically.
 *
 * @package TrekContentManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Journey_Matcher {

    /**
     * Category Definitions: Explore, Feel, Pace
     *
     * @return array
     */
    public static function get_categories() {
        return array(
            'explore' => array(
                'label'       => __( 'I Want to Explore', 'trek-content-manager' ),
                'description' => __( 'Choose what draws you in.', 'trek-content-manager' ),
                'weight'      => 3,
                'options'     => array(
                    'culture_heritage'  => __( 'Culture & Heritage', 'trek-content-manager' ),
                    'nature_landscapes' => __( 'Nature & Landscapes', 'trek-content-manager' ),
                    'mountains'         => __( 'Mountains', 'trek-content-manager' ),
                    'wildlife'          => __( 'Wildlife', 'trek-content-manager' ),
                    'adventure'         => __( 'Adventure', 'trek-content-manager' ),
                    'local_life'        => __( 'Local Life', 'trek-content-manager' ),
                    'spirituality'      => __( 'Spirituality', 'trek-content-manager' ),
                    'history'           => __( 'History', 'trek-content-manager' ),
                    'remote_places'     => __( 'Remote Places', 'trek-content-manager' ),
                    'food_traditions'   => __( 'Food & Traditions', 'trek-content-manager' ),
                ),
            ),
            'feel' => array(
                'label'       => __( 'I Want to Feel', 'trek-content-manager' ),
                'description' => __( 'Choose the feeling you want from your journey.', 'trek-content-manager' ),
                'weight'      => 2,
                'options'     => array(
                    'challenged'        => __( 'Challenged', 'trek-content-manager' ),
                    'free'              => __( 'Free', 'trek-content-manager' ),
                    'restored'          => __( 'Restored', 'trek-content-manager' ),
                    'inspired'          => __( 'Inspired', 'trek-content-manager' ),
                    'connected'         => __( 'Connected', 'trek-content-manager' ),
                    'curious'           => __( 'Curious', 'trek-content-manager' ),
                    'adventurous'       => __( 'Adventurous', 'trek-content-manager' ),
                    'peaceful'          => __( 'Peaceful', 'trek-content-manager' ),
                    'immersed'          => __( 'Immersed', 'trek-content-manager' ),
                    'far_from_ordinary' => __( 'Far From Ordinary', 'trek-content-manager' ),
                    'accomplished'      => __( 'Accomplished', 'trek-content-manager' ),
                    'alive'             => __( 'Alive', 'trek-content-manager' ),
                ),
            ),
            'pace' => array(
                'label'       => __( 'My Ideal Pace', 'trek-content-manager' ),
                'description' => __( 'How would you like to experience the journey?', 'trek-content-manager' ),
                'weight'      => 2,
                'options'     => array(
                    'slow_scenic'       => __( 'Slow & Scenic', 'trek-content-manager' ),
                    'relaxed'           => __( 'Relaxed', 'trek-content-manager' ),
                    'balanced'          => __( 'Balanced', 'trek-content-manager' ),
                    'active'            => __( 'Active', 'trek-content-manager' ),
                    'trekking'          => __( 'Trekking', 'trek-content-manager' ),
                    'adventure'         => __( 'Adventure', 'trek-content-manager' ),
                    'challenging'       => __( 'Challenging', 'trek-content-manager' ),
                    'expedition'        => __( 'Expedition', 'trek-content-manager' ),
                    'wild_remote'       => __( 'Wild & Remote', 'trek-content-manager' ),
                    'culture_discovery' => __( 'Culture & Discovery', 'trek-content-manager' ),
                ),
            ),
        );
    }

    /**
     * Get human label for a specific key
     *
     * @param string $category Category slug (explore, feel, pace).
     * @param string $key Option key.
     * @return string
     */
    public static function get_label( $category, $key ) {
        $categories = self::get_categories();
        if ( isset( $categories[ $category ]['options'][ $key ] ) ) {
            return $categories[ $category ]['options'][ $key ];
        }
        return ucwords( str_replace( '_', ' ', $key ) );
    }

    /**
     * Normalize and sanitize user selections
     *
     * @param array $input Raw input array containing 'explore', 'feel', 'pace'.
     * @return array Normalized array of valid keys.
     */
    public static function normalize_selections( array $input ) {
        $categories = self::get_categories();
        $normalized = array(
            'explore' => array(),
            'feel'    => array(),
            'pace'    => array(),
        );

        foreach ( array( 'explore', 'feel', 'pace' ) as $cat ) {
            $raw_values = $input[ $cat ] ?? array();
            if ( is_string( $raw_values ) ) {
                $raw_values = explode( ',', $raw_values );
            }
            if ( ! is_array( $raw_values ) ) {
                $raw_values = array();
            }

            $valid_options = array_keys( $categories[ $cat ]['options'] );
            $clean_keys    = array();

            foreach ( $raw_values as $val ) {
                $clean = sanitize_key( trim( (string) $val ) );
                if ( in_array( $clean, $valid_options, true ) && ! in_array( $clean, $clean_keys, true ) ) {
                    $clean_keys[] = $clean;
                }
            }

            $normalized[ $cat ] = $clean_keys;
        }

        return $normalized;
    }

    /**
     * Retrieve matching metadata for a destination post
     *
     * @param int $post_id Post ID.
     * @return array
     */
    public static function get_destination_keywords( $post_id ) {
        $explore = get_post_meta( $post_id, '_trek_match_explore', true );
        $feel    = get_post_meta( $post_id, '_trek_match_feel', true );
        $pace    = get_post_meta( $post_id, '_trek_match_pace', true );
        $default = get_post_meta( $post_id, '_trek_match_default', true );

        return array(
            'explore'    => is_array( $explore ) ? array_map( 'sanitize_key', $explore ) : array(),
            'feel'       => is_array( $feel ) ? array_map( 'sanitize_key', $feel ) : array(),
            'pace'       => is_array( $pace ) ? array_map( 'sanitize_key', $pace ) : array(),
            'is_default' => ( '1' === (string) $default || 1 === $default || true === $default ),
        );
    }

    /**
     * Calculate score between user selections and destination keywords
     *
     * Weighted formula:
     * (explore_matches * 3) + (feel_matches * 2) + (pace_matches * 2)
     *
     * @param array $user_selections Normalized user selections.
     * @param array $dest_keywords Destination keywords.
     * @return array
     */
    public static function calculate_score( array $user_selections, array $dest_keywords ) {
        $explore_matches = array_values( array_intersect( $user_selections['explore'] ?? array(), $dest_keywords['explore'] ?? array() ) );
        $feel_matches    = array_values( array_intersect( $user_selections['feel'] ?? array(), $dest_keywords['feel'] ?? array() ) );
        $pace_matches    = array_values( array_intersect( $user_selections['pace'] ?? array(), $dest_keywords['pace'] ?? array() ) );

        $explore_count = count( $explore_matches );
        $feel_count    = count( $feel_matches );
        $pace_count    = count( $pace_matches );

        $score = ( $explore_count * 3 ) + ( $feel_count * 2 ) + ( $pace_count * 2 );

        return array(
            'score'           => $score,
            'explore_matches' => $explore_matches,
            'feel_matches'    => $feel_matches,
            'pace_matches'    => $pace_matches,
            'explore_count'   => $explore_count,
            'feel_count'      => $feel_count,
            'pace_count'      => $pace_count,
        );
    }

    /**
     * Main Match Engine: Evaluates user preferences against all published destinations
     *
     * @param array $user_selections Raw or normalized user selections.
     * @return array Matched destination result.
     */
    public static function match( array $user_selections ) {
        $selections = self::normalize_selections( $user_selections );

        // Fetch all published trek_destination records
        $destinations = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
        ) );

        if ( empty( $destinations ) ) {
            return array(
                'destination_id'  => 0,
                'score'           => 0,
                'explore_matches' => array(),
                'feel_matches'    => array(),
                'pace_matches'    => array(),
                'is_fallback'     => true,
            );
        }

        $candidates         = array();
        $fallback_post_id   = 0;
        $default_fallback_id = 0;

        foreach ( $destinations as $dest ) {
            $post_id  = $dest->ID;
            $keywords = self::get_destination_keywords( $post_id );

            // Track default fallback
            if ( $keywords['is_default'] && ! $default_fallback_id ) {
                $default_fallback_id = $post_id;
            }
            if ( ! $fallback_post_id ) {
                $fallback_post_id = $post_id;
            }

            $calc = self::calculate_score( $selections, $keywords );

            $card_order = get_post_meta( $post_id, '_trek_card_order', true );
            $order_val  = ( '' !== $card_order && is_numeric( $card_order ) ) ? intval( $card_order ) : 999;

            $candidates[] = array(
                'destination_id'  => $post_id,
                'score'           => $calc['score'],
                'explore_matches' => $calc['explore_matches'],
                'feel_matches'    => $calc['feel_matches'],
                'pace_matches'    => $calc['pace_matches'],
                'explore_count'   => $calc['explore_count'],
                'feel_count'      => $calc['feel_count'],
                'pace_count'      => $calc['pace_count'],
                'card_order'      => $order_val,
            );
        }

        // Deterministic Sorting:
        // 1. Higher total score
        // 2. More Explore matches
        // 3. More Feel matches
        // 4. More Pace matches
        // 5. Card priority order (ASC)
        // 6. Post ID (ASC)
        usort( $candidates, function( $a, $b ) {
            if ( $a['score'] !== $b['score'] ) {
                return $b['score'] <=> $a['score'];
            }
            if ( $a['explore_count'] !== $b['explore_count'] ) {
                return $b['explore_count'] <=> $a['explore_count'];
            }
            if ( $a['feel_count'] !== $b['feel_count'] ) {
                return $b['feel_count'] <=> $a['feel_count'];
            }
            if ( $a['pace_count'] !== $b['pace_count'] ) {
                return $b['pace_count'] <=> $a['pace_count'];
            }
            if ( $a['card_order'] !== $b['card_order'] ) {
                return $a['card_order'] <=> $b['card_order'];
            }
            return $a['destination_id'] <=> $b['destination_id'];
        } );

        $winner = $candidates[0] ?? null;

        // Fallback rule: If highest score is 0, use controlled fallback
        if ( ! $winner || $winner['score'] <= 0 ) {
            $selected_fallback = $default_fallback_id ?: $fallback_post_id;
            return array(
                'destination_id'  => $selected_fallback,
                'score'           => 0,
                'explore_matches' => array(),
                'feel_matches'    => array(),
                'pace_matches'    => array(),
                'is_fallback'     => true,
            );
        }

        return array(
            'destination_id'  => $winner['destination_id'],
            'score'           => $winner['score'],
            'explore_matches' => $winner['explore_matches'],
            'feel_matches'    => $winner['feel_matches'],
            'pace_matches'    => $winner['pace_matches'],
            'is_fallback'     => false,
        );
    }

    /**
     * Retrieve complete presentation payload for a destination ID
     *
     * @param int   $post_id Destination post ID.
     * @param array $match_result Optional match result array.
     * @return array
     */
    public static function get_destination_display_data( $post_id, array $match_result = array() ) {
        $post = get_post( $post_id );
        if ( ! $post || 'trek_destination' !== $post->post_type ) {
            return array();
        }

        // Image Resolution: Featured Image > Card Image URL > Hero Background URL
        $image_id  = get_post_thumbnail_id( $post_id );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';

        if ( empty( $image_url ) ) {
            $card_img = get_post_meta( $post_id, '_trek_card_image_url', true );
            $hero_bg  = get_post_meta( $post_id, '_trek_hero_bg_img', true );
            $image_url = $card_img ?: $hero_bg ?: '';
        }

        // Region Resolution
        $region = get_post_meta( $post_id, '_trek_fact_region', true );
        if ( empty( $region ) ) {
            $region = get_post_meta( $post_id, '_trek_breadcrumb_region', true );
        }
        if ( empty( $region ) ) {
            $region = get_post_meta( $post_id, '_trek_map_region', true );
        }
        if ( empty( $region ) ) {
            $regions = wp_get_post_terms( $post_id, 'trek_region', array( 'fields' => 'names' ) );
            if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) {
                $region = $regions[0];
            }
        }
        if ( empty( $region ) ) {
            $region = __( 'Himalayas, Nepal', 'trek-content-manager' );
        }

        // Duration Resolution
        $duration = get_post_meta( $post_id, '_trek_hero_days', true );
        if ( empty( $duration ) ) {
            $facts = get_post_meta( $post_id, '_trek_quick_facts', true );
            if ( is_array( $facts ) && ! empty( $facts['duration'] ) ) {
                $duration = $facts['duration'];
            }
        }
        if ( empty( $duration ) ) {
            $duration = __( 'Custom Itinerary', 'trek-content-manager' );
        }

        // Description / Excerpt
        $excerpt = get_post_meta( $post_id, '_trek_card_excerpt', true );
        if ( empty( $excerpt ) ) {
            $excerpt = get_post_meta( $post_id, '_trek_hero_subtitle', true );
        }
        if ( empty( $excerpt ) && ! empty( $post->post_excerpt ) ) {
            $excerpt = $post->post_excerpt;
        }
        if ( empty( $excerpt ) && ! empty( $post->post_content ) ) {
            $excerpt = wp_trim_words( wp_strip_all_tags( $post->post_content ), 32 );
        }
        if ( empty( $excerpt ) ) {
            $excerpt = __( 'An extraordinary Himalayan journey curated to immerse you in breathtaking mountain scenery, rich cultural heritage, and pristine trails.', 'trek-content-manager' );
        }

        // Format matched human labels
        $categories = self::get_categories();
        $explore_labels = array();
        foreach ( ( $match_result['explore_matches'] ?? array() ) as $k ) {
            $explore_labels[] = $categories['explore']['options'][ $k ] ?? $k;
        }
        $feel_labels = array();
        foreach ( ( $match_result['feel_matches'] ?? array() ) as $k ) {
            $feel_labels[] = $categories['feel']['options'][ $k ] ?? $k;
        }
        $pace_labels = array();
        foreach ( ( $match_result['pace_matches'] ?? array() ) as $k ) {
            $pace_labels[] = $categories['pace']['options'][ $k ] ?? $k;
        }

        return array(
            'destination_id'  => $post_id,
            'title'           => get_the_title( $post_id ),
            'permalink'       => get_permalink( $post_id ),
            'image_url'       => esc_url_raw( $image_url ),
            'region'          => sanitize_text_field( $region ),
            'duration'        => sanitize_text_field( $duration ),
            'excerpt'         => wp_kses_post( $excerpt ),
            'score'           => intval( $match_result['score'] ?? 0 ),
            'explore_matches' => $explore_labels,
            'feel_matches'    => $feel_labels,
            'pace_matches'    => $pace_labels,
            'is_fallback'     => ! empty( $match_result['is_fallback'] ),
        );
    }
}
