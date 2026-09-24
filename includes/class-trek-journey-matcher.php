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
                    'mountains'         => __( 'Mountains & High Passes', 'trek-content-manager' ),
                    'culture_heritage'  => __( 'Culture & Heritage', 'trek-content-manager' ),
                    'nature_landscapes' => __( 'Nature & Landscapes', 'trek-content-manager' ),
                    'wildlife'          => __( 'Wildlife & Safari', 'trek-content-manager' ),
                    'adventure'         => __( 'Adventure Expeditions', 'trek-content-manager' ),
                    'local_life'        => __( 'Local Life & Villages', 'trek-content-manager' ),
                    'spirituality'      => __( 'Spirituality & Monasteries', 'trek-content-manager' ),
                    'history'           => __( 'Ancient Kingdoms', 'trek-content-manager' ),
                    'remote_places'     => __( 'Remote Wilderness', 'trek-content-manager' ),
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
     * Automatically detect matching keywords from database destination content.
     * Analyzes post title, taxonomies (category, region, difficulty), overview narrative,
     * duration, style, highlights, and excerpt.
     *
     * @param int $post_id Post ID.
     * @return array Array with 'explore', 'feel', and 'pace' arrays.
     */
    public static function auto_detect_keywords( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return array( 'explore' => array(), 'feel' => array(), 'pace' => array() );
        }

        // 1. Taxonomies
        $cats = wp_get_post_terms( $post_id, 'trek_category', array( 'fields' => 'names' ) );
        $regs = wp_get_post_terms( $post_id, 'trek_region', array( 'fields' => 'names' ) );
        $diff = wp_get_post_terms( $post_id, 'trek_difficulty', array( 'fields' => 'names' ) );

        $cats_str = implode( ' ', is_array( $cats ) ? $cats : array() );
        $regs_str = implode( ' ', is_array( $regs ) ? $regs : array() );
        $diff_str = implode( ' ', is_array( $diff ) ? $diff : array() );

        // 2. Content & Meta fields
        $title       = $post->post_title;
        $content     = wp_strip_all_tags( $post->post_content );
        $excerpt     = wp_strip_all_tags( $post->post_excerpt );
        $overview    = wp_strip_all_tags( get_post_meta( $post_id, '_trek_overview_content', true ) );
        $highlight   = wp_strip_all_tags( get_post_meta( $post_id, '_trek_overview_highlight', true ) );
        $card_kicker = get_post_meta( $post_id, '_trek_card_kicker', true );
        $card_tag    = get_post_meta( $post_id, '_trek_card_tag', true );
        $style       = get_post_meta( $post_id, '_trek_fact_style', true );
        $days_raw    = get_post_meta( $post_id, '_trek_hero_days', true );
        $days        = intval( preg_replace( '/[^0-9]/', '', (string) $days_raw ) );

        // Build combined lowercase text corpus for search
        $text = strtolower(
            $title . ' ' .
            $cats_str . ' ' .
            $regs_str . ' ' .
            $diff_str . ' ' .
            $content . ' ' .
            $excerpt . ' ' .
            $overview . ' ' .
            $highlight . ' ' .
            $card_kicker . ' ' .
            $card_tag . ' ' .
            $style
        );
        $cats_lower = strtolower( $cats_str );
        $diff_lower = strtolower( $diff_str );

        $explore = array();
        $feel    = array();
        $pace    = array();

        // 1. Explore Keywords Patterns
        $explore_patterns = array(
            'culture_heritage'  => array( 'cultur', 'heritage', 'temple', 'monaster', 'stupa', 'traditional', 'ancient', 'pagoda', 'festival', 'newar', 'sherpa', 'tibetan', 'historical', 'museum', 'monument', 'palace', 'bazaar', 'unesco', 'tsum' ),
            'nature_landscapes' => array( 'nature', 'landscape', 'scenic', 'scenery', 'valley', 'lake', 'glacier', 'forest', 'rhododendron', 'flora', 'panorama', 'river', 'waterfall', 'greenery', 'jungle' ),
            'mountains'         => array( 'mountain', 'himalaya', 'peak', 'everest', 'annapurna', 'kanchenjunga', 'summit', 'pass', 'base camp', 'basecamp', 'range', 'altitude', 'alpine', 'col', 'makalu', 'lhotse', 'manaslu', 'poon hill', 'ghorepani' ),
            'wildlife'          => array( 'wildlife', 'animal', 'bird', 'national park', 'chitwan', 'bardia', 'safari', 'tiger', 'rhino', 'snow leopard', 'fauna', 'jungle' ),
            'adventure'         => array( 'adventure', 'thrill', 'trek', 'climb', 'expedition', 'rugged', 'extreme', 'crossing', 'circuit', 'wild', 'rafting' ),
            'local_life'        => array( 'local', 'village', 'homestay', 'community', 'people', 'tea house', 'teahouse', 'hospitality', 'sherpa', 'gurung', 'tamang', 'settlement', 'authentic' ),
            'spirituality'      => array( 'spiritual', 'sacred', 'holy', 'pilgrimage', 'meditat', 'monastery', 'monasteries', 'temple', 'monk', 'prayer', 'buddhis', 'hindu', 'lumbini', 'muktinath', 'gosainkunda', 'pashupati', 'peace' ),
            'history'           => array( 'history', 'historic', 'ancient', 'kingdom', 'medieval', 'palace', 'ruins', 'dynasty', 'century', 'old', 'heritage', 'mustang' ),
            'remote_places'     => array( 'remote', 'off-the-beaten', 'isolated', 'hidden', 'wilderness', 'untouched', 'mustang', 'dolpo', 'manaslu', 'kanchenjunga', 'makalu', 'nar phu', 'wild', 'pristine', 'tsum' ),
            'food_traditions'   => array( 'food', 'culinary', 'dining', 'cuisine', 'tea', 'taste', 'feast', 'tradition', 'cooking', 'delicac', 'dal bhat' ),
        );

        foreach ( $explore_patterns as $key => $patterns ) {
            foreach ( $patterns as $p ) {
                if ( false !== strpos( $text, $p ) ) {
                    $explore[] = $key;
                    break;
                }
            }
        }

        // 2. Feel Keywords Patterns
        $feel_patterns = array(
            'challenged'        => array( 'challeng', 'strenuous', 'difficult', 'steep', 'high altitude', 'endurance', 'tough', 'demanding', 'rigorous', 'expedition' ),
            'free'              => array( 'free', 'freedom', 'vast', 'open', 'horizon', 'escape', 'boundless', 'wild' ),
            'restored'          => array( 'restore', 'peaceful', 'rejuvenat', 'calm', 'serenity', 'fresh', 'wellness', 'retreat', 'tranquil', 'silence', 'relax' ),
            'inspired'          => array( 'inspire', 'inspiring', 'breathtaking', 'majestic', 'awe', 'stunning', 'wonder', 'sublime', 'magnificent', 'spectacular', 'unforgettable', 'epic' ),
            'connected'         => array( 'connect', 'bond', 'local', 'warmth', 'friendly', 'hospitality', 'community', 'shared', 'together', 'people' ),
            'curious'           => array( 'curious', 'discover', 'explore', 'secret', 'learn', 'mystery', 'hidden', 'fascinat', 'unique' ),
            'adventurous'       => array( 'adventure', 'adventurous', 'daring', 'thrill', 'bold', 'expedition', 'wilderness', 'trek' ),
            'peaceful'          => array( 'peace', 'peaceful', 'serene', 'serenity', 'tranquil', 'quiet', 'calm', 'gentle', 'harmony', 'stillness' ),
            'immersed'          => array( 'immerse', 'deep', 'living', 'culture', 'authentic', 'intimate', 'surround', 'homestay', 'village' ),
            'far_from_ordinary' => array( 'unique', 'exotic', 'extraordinary', 'otherworldly', 'distinct', 'rare', 'spectacular', 'unusual', 'mustang', 'dolpo' ),
            'accomplished'      => array( 'accomplish', 'summit', 'achieve', 'triumph', 'conquer', 'base camp', 'goal', 'milestone', 'reward' ),
            'alive'             => array( 'alive', 'energy', 'vibrant', 'invigorat', 'rush', 'exhilarat', 'excite', 'fresh' ),
        );

        foreach ( $feel_patterns as $key => $patterns ) {
            foreach ( $patterns as $p ) {
                if ( false !== strpos( $text, $p ) ) {
                    $feel[] = $key;
                    break;
                }
            }
        }

        // 3. Pace Keywords Patterns
        $pace_patterns = array(
            'slow_scenic'       => array( 'slow', 'scenic', 'leisure', 'gentle', 'photograph', 'view', 'sight', 'relax', 'easy' ),
            'relaxed'           => array( 'relax', 'comfortable', 'mild', 'gentle', 'short walk', 'resort', 'lodge', 'easy' ),
            'balanced'          => array( 'balance', 'moderate', 'steady', 'standard', 'regular' ),
            'active'            => array( 'active', 'moderate', 'trek', 'walk', 'hiking', 'hike' ),
            'trekking'          => array( 'trek', 'trekking', 'trail', 'hiking', 'pass', 'teahouse', 'backpack' ),
            'adventure'         => array( 'adventure', 'dynamic', 'diverse', 'explorer', 'rugged' ),
            'challenging'       => array( 'challeng', 'strenuous', 'steep', 'rigorous', 'demanding' ),
            'expedition'        => array( 'expedition', 'climbing', 'summit', 'mountaineering', 'technical' ),
            'wild_remote'       => array( 'wild', 'remote', 'wilderness', 'untouched', 'camping', 'isolated', 'off-grid' ),
            'culture_discovery' => array( 'culture', 'discovery', 'city', 'tour', 'excursion', 'heritage', 'sightseeing' ),
        );

        foreach ( $pace_patterns as $key => $patterns ) {
            foreach ( $patterns as $p ) {
                if ( false !== strpos( $text, $p ) ) {
                    $pace[] = $key;
                    break;
                }
            }
        }

        // Taxonomy & Duration contextual adjustments
        if ( false !== strpos( $diff_lower, 'easy' ) ) {
            $pace[] = 'relaxed';
            $pace[] = 'slow_scenic';
            $feel[] = 'restored';
        } elseif ( false !== strpos( $diff_lower, 'demanding' ) || false !== strpos( $diff_lower, 'expedition' ) || false !== strpos( $diff_lower, 'challeng' ) ) {
            $pace[] = 'challenging';
            $feel[] = 'challenged';
            $feel[] = 'accomplished';
        } elseif ( false !== strpos( $diff_lower, 'moderate' ) ) {
            $pace[] = 'balanced';
            $pace[] = 'active';
        }

        if ( $days > 0 && $days <= 7 ) {
            $pace[] = 'slow_scenic';
            $pace[] = 'relaxed';
        } elseif ( $days > 14 ) {
            $pace[] = 'trekking';
            $explore[] = 'remote_places';
            $feel[] = 'immersed';
        }

        return array(
            'explore' => array_values( array_unique( $explore ) ),
            'feel'    => array_values( array_unique( $feel ) ),
            'pace'    => array_values( array_unique( $pace ) ),
        );
    }

    /**
     * Retrieve matching metadata for a destination post.
     * Automatically detects keywords from the destination's database content,
     * seamlessly merged with any saved custom keywords.
     *
     * @param int $post_id Post ID.
     * @return array
     */
    public static function get_destination_keywords( $post_id ) {
        $saved_explore = get_post_meta( $post_id, '_trek_match_explore', true );
        $saved_feel    = get_post_meta( $post_id, '_trek_match_feel', true );
        $saved_pace    = get_post_meta( $post_id, '_trek_match_pace', true );
        $default       = get_post_meta( $post_id, '_trek_match_default', true );

        $explore = is_array( $saved_explore ) ? array_map( 'sanitize_key', $saved_explore ) : array();
        $feel    = is_array( $saved_feel ) ? array_map( 'sanitize_key', $saved_feel ) : array();
        $pace    = is_array( $saved_pace ) ? array_map( 'sanitize_key', $saved_pace ) : array();

        // Automatically detect keywords from database destination content
        $auto = self::auto_detect_keywords( $post_id );

        // Merge: auto-detected keywords provide rich coverage, manual overrides take priority
        $explore = array_values( array_unique( array_merge( $auto['explore'], $explore ) ) );
        $feel    = array_values( array_unique( array_merge( $auto['feel'], $feel ) ) );
        $pace    = array_values( array_unique( array_merge( $auto['pace'], $pace ) ) );

        return array(
            'explore'    => $explore,
            'feel'       => $feel,
            'pace'       => $pace,
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
     * Multi-Match Engine: Evaluates user preferences against all published destinations
     * and returns top N matched destination results deterministically.
     *
     * @param array $user_selections Raw or normalized user selections.
     * @param int   $limit           Maximum number of matches to return (default 3).
     * @return array Array of matched destination results.
     */
    public static function match_multiple( array $user_selections, $limit = 3 ) {
        $selections = self::normalize_selections( $user_selections );
        $limit      = max( 1, intval( $limit ) );

        // Fetch all published trek_destination records
        $destinations = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
        ) );

        if ( empty( $destinations ) ) {
            return array();
        }

        $candidates          = array();
        $fallback_post_ids   = array();
        $default_fallback_id = 0;

        foreach ( $destinations as $dest ) {
            $post_id  = $dest->ID;
            $keywords = self::get_destination_keywords( $post_id );

            // Track default fallback
            if ( $keywords['is_default'] && ! $default_fallback_id ) {
                $default_fallback_id = $post_id;
            }
            $fallback_post_ids[] = $post_id;

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

        // Filter for candidates with score > 0
        $positive_candidates = array_filter( $candidates, function( $c ) {
            return $c['score'] > 0;
        } );

        $results = array();

        if ( ! empty( $positive_candidates ) ) {
            $slice = array_slice( $positive_candidates, 0, $limit );
            foreach ( $slice as $cand ) {
                $results[] = array(
                    'destination_id'  => $cand['destination_id'],
                    'score'           => $cand['score'],
                    'explore_matches' => $cand['explore_matches'],
                    'feel_matches'    => $cand['feel_matches'],
                    'pace_matches'    => $cand['pace_matches'],
                    'is_fallback'     => false,
                );
            }
        }

        // If fewer than $limit results, pad with fallback destinations (avoiding duplicates)
        if ( count( $results ) < $limit ) {
            $existing_ids = array_column( $results, 'destination_id' );

            // Try default fallback first if not already present
            if ( $default_fallback_id && ! in_array( $default_fallback_id, $existing_ids, true ) ) {
                $results[] = array(
                    'destination_id'  => $default_fallback_id,
                    'score'           => 0,
                    'explore_matches' => array(),
                    'feel_matches'    => array(),
                    'pace_matches'    => array(),
                    'is_fallback'     => true,
                );
                $existing_ids[] = $default_fallback_id;
            }

            // Fill remaining with other destinations in order
            foreach ( $fallback_post_ids as $fb_id ) {
                if ( count( $results ) >= $limit ) {
                    break;
                }
                if ( ! in_array( $fb_id, $existing_ids, true ) ) {
                    $results[] = array(
                        'destination_id'  => $fb_id,
                        'score'           => 0,
                        'explore_matches' => array(),
                        'feel_matches'    => array(),
                        'pace_matches'    => array(),
                        'is_fallback'     => true,
                    );
                    $existing_ids[] = $fb_id;
                }
            }
        }

        return $results;
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
