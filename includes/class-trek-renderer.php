<?php
/**
 * Trek Content Manager - Comprehensive Server-Side SEO, Social Meta & Schema Renderer
 * 
 * 100% Complete CPT Field Coverage:
 * - Full data extraction from all 10 metaboxes of `trek_destination`
 * - Schema.org JSON-LD (`TouristTrip`, `Product`, `ItemList` Itinerary, `FAQPage`, `BreadcrumbList`, `AggregateRating`)
 * - Open Graph & Twitter Card Rich Meta Tags
 * - Canonical & Semantic SEO Meta
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Renderer {

    public static function init() {
        // Automatically inject SEO schema & social meta in <head> for single trek destinations
        add_action( 'wp_head', array( __CLASS__, 'auto_inject_head_meta' ), 1 );
    }

    /**
     * Automatically output meta tags & JSON-LD on single trek pages
     */
    public static function auto_inject_head_meta() {
        if ( is_singular( 'trek_destination' ) ) {
            $post_id = get_the_ID();
            $trek_data = self::get_full_trek_data( $post_id );
            self::render_social_meta( $trek_data );
            self::render_schema_json_ld( $trek_data );
        }
    }

    /**
     * Extract every single field from `trek_destination` CPT into a structured array
     *
     * @param int|WP_Post|array $post_item Post ID, WP_Post object, or existing array
     * @return array Complete normalized trek data array
     */
    public static function get_full_trek_data( $post_item ) {
        if ( is_array( $post_item ) && isset( $post_item['id'] ) && isset( $post_item['hero'] ) ) {
            return $post_item;
        }

        $post = is_object( $post_item ) ? $post_item : get_post( $post_item );
        if ( ! ( $post instanceof WP_Post ) ) {
            return array();
        }

        $post_id = $post->ID;

        // 1. Core Post Fields
        $title          = get_the_title( $post_id );
        $permalink      = get_permalink( $post_id );
        $post_content   = $post->post_content;
        $post_excerpt   = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( $post_content, 25, '...' );
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );

        // 2. Taxonomies (Categories, Regions, Difficulties)
        $cat_terms = get_the_terms( $post_id, 'trek_category' );
        $categories = array();
        $category_slugs = array();
        if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
            foreach ( $cat_terms as $ct ) {
                $categories[]     = $ct->name;
                $category_slugs[] = $ct->slug;
            }
        }

        $reg_terms = get_the_terms( $post_id, 'trek_region' );
        $regions = array();
        $region_slugs = array();
        if ( ! empty( $reg_terms ) && ! is_wp_error( $reg_terms ) ) {
            foreach ( $reg_terms as $rt ) {
                $regions[]      = $rt->name;
                $region_slugs[] = $rt->slug;
            }
        }

        $diff_terms = get_the_terms( $post_id, 'trek_difficulty' );
        $diff_meta  = get_post_meta( $post_id, '_trek_difficulty', true );
        $difficulty = ( ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->name : ( ! empty( $diff_meta ) ? $diff_meta : 'Moderate to Demanding' );
        $diff_slug  = ( ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->slug : sanitize_title( $difficulty );

        $primary_cat_slug = get_post_meta( $post_id, '_trek_primary_category', true );
        $primary_cat_name = '';
        if ( ! empty( $primary_cat_slug ) ) {
            $term_obj = get_term_by( 'slug', $primary_cat_slug, 'trek_category' );
            if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                $primary_cat_name = $term_obj->name;
            }
        }
        if ( empty( $primary_cat_name ) && ! empty( $categories ) ) {
            $primary_cat_name = $categories[0];
        }

        // Map region and district select fields
        $map_region   = get_post_meta( $post_id, '_trek_map_region', true ) ?: '';
        $map_district = get_post_meta( $post_id, '_trek_map_district', true ) ?: '';

        // 3. Hero & Card Meta Fields
        $script_title     = get_post_meta( $post_id, '_trek_script_title', true ) ?: $title;
        $hero_title       = get_post_meta( $post_id, '_trek_hero_title', true ) ?: $title;
        $card_title       = get_post_meta( $post_id, '_trek_card_title', true ) ?: $title;
        $hero_kicker      = get_post_meta( $post_id, '_trek_hero_kicker', true ) ?: ( ! empty( $regions ) ? $regions[0] : 'Nepal' );
        $hero_days        = get_post_meta( $post_id, '_trek_hero_days', true ) ?: '15';
        $breadcrumb_reg   = get_post_meta( $post_id, '_trek_breadcrumb_region', true ) ?: ( ! empty( $regions ) ? $regions[0] : 'Nepal' );
        $hero_badge_sub   = get_post_meta( $post_id, '_trek_hero_badge_sub', true ) ?: ( 'Days in ' . $breadcrumb_reg );
        $hero_subtitle    = get_post_meta( $post_id, '_trek_hero_subtitle', true ) ?: $post_excerpt;
        $hero_bg_img      = get_post_meta( $post_id, '_trek_hero_bg_img', true ) ?: ( $featured_image ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1600&q=85' );

        $card_meta        = get_post_meta( $post_id, '_trek_card_meta', true ) ?: ( $hero_days . ' Days' );
        $card_tag         = get_post_meta( $post_id, '_trek_card_tag', true ) ?: ( $breadcrumb_reg . ' · ' . ( $primary_cat_name ?: 'Signature Trek' ) );
        $card_kicker      = get_post_meta( $post_id, '_trek_card_kicker', true ) ?: $primary_cat_name;
        $card_excerpt     = get_post_meta( $post_id, '_trek_card_excerpt', true ) ?: $post_excerpt;
        $card_image_url   = get_post_meta( $post_id, '_trek_card_image_url', true ) ?: ( $featured_image ?: $hero_bg_img );
        $card_order       = get_post_meta( $post_id, '_trek_card_order', true ) ?: '0';
        $is_featured      = get_post_meta( $post_id, '_trek_featured_home', true ) ?: get_post_meta( $post_id, '_trek_is_featured', true );

        // 4. Quick Facts Grid
        $fact_high_point  = get_post_meta( $post_id, '_trek_fact_high_point', true ) ?: '5,550m · Kala Patthar';
        $fact_low_point   = get_post_meta( $post_id, '_trek_fact_low_point', true ) ?: '1,400m · Kathmandu';
        $trip_status      = get_post_meta( $post_id, '_trek_trip_status', true ) ?: 'open';
        $fact_region      = get_post_meta( $post_id, '_trek_fact_region', true ) ?: $breadcrumb_reg;
        $fact_trekking    = get_post_meta( $post_id, '_trek_fact_trekking', true ) ?: 'Usually 5–6 hours daily';
        $fact_accom       = get_post_meta( $post_id, '_trek_fact_accom', true ) ?: 'Hotel & local mountain lodges';
        $fact_style       = get_post_meta( $post_id, '_trek_fact_style', true ) ?: 'Privately Guided High-Altitude Trek';
        $fact_season      = 'Autumn (Sep–Nov) & Spring (Mar–May)';
        $fact_group_size  = '2–12 Travellers';

        // Stored structured quick facts if present
        $stored_facts     = get_post_meta( $post_id, '_trek_quick_facts', true );
        if ( is_array( $stored_facts ) ) {
            $fact_high_point = ! empty( $stored_facts['max_altitude'] ) ? $stored_facts['max_altitude'] : ( ! empty( $stored_facts['high_point'] ) ? $stored_facts['high_point'] : $fact_high_point );
            $fact_low_point  = ! empty( $stored_facts['low_point'] ) ? $stored_facts['low_point'] : $fact_low_point;
            $trip_status     = ! empty( $stored_facts['status'] ) ? $stored_facts['status'] : $trip_status;
            $fact_region     = ! empty( $stored_facts['destination'] ) ? $stored_facts['destination'] : ( ! empty( $stored_facts['region'] ) ? $stored_facts['region'] : $fact_region );
            $fact_trekking   = ! empty( $stored_facts['daily_walking'] ) ? $stored_facts['daily_walking'] : ( ! empty( $stored_facts['trekking'] ) ? $stored_facts['trekking'] : $fact_trekking );
            $fact_accom      = ! empty( $stored_facts['accommodation'] ) ? $stored_facts['accommodation'] : ( ! empty( $stored_facts['accom'] ) ? $stored_facts['accom'] : $fact_accom );
            $fact_style      = ! empty( $stored_facts['style'] ) ? $stored_facts['style'] : $fact_style;
            $fact_season     = ! empty( $stored_facts['season'] ) ? $stored_facts['season'] : $fact_season;
            $fact_group_size = ! empty( $stored_facts['group_size'] ) ? $stored_facts['group_size'] : $fact_group_size;
        }

        // 5. Overview Narrative, Highlights & Route Map
        $overview_kicker     = get_post_meta( $post_id, '_trek_overview_kicker', true ) ?: ( $breadcrumb_reg . ' · ' . $hero_days . ' Days' );
        $overview_script     = get_post_meta( $post_id, '_trek_overview_script', true ) ?: 'The Ultimate Trek';
        $overview_heading    = get_post_meta( $post_id, '_trek_overview_heading', true ) ?: ( 'To the Foot of ' . $title );
        $overview_subheading = get_post_meta( $post_id, '_trek_overview_subheading', true ) ?: ( $title . ' through the Himalayan homelands' );
        
        $p1 = get_post_meta( $post_id, '_trek_overview_p1', true );
        $p2 = get_post_meta( $post_id, '_trek_overview_p2', true );
        $p3 = get_post_meta( $post_id, '_trek_overview_p3', true );
        $paragraphs = array_filter( array( $p1, $p2, $p3 ) );

        $overview_content = get_post_meta( $post_id, '_trek_overview_content', true );
        if ( empty( $overview_content ) && ! empty( $paragraphs ) ) {
            $overview_content = '<p>' . implode( "</p>\n<p>", $paragraphs ) . '</p>';
        } elseif ( empty( $overview_content ) ) {
            $overview_content = ! empty( $post_content ) ? $post_content : "<p>A memorable journey to {$title} through alpine valleys and iconic Himalayan landscapes.</p>";
        }

        $overview_highlight  = get_post_meta( $post_id, '_trek_overview_highlight', true ) ?: ( "A demanding Himalayan journey defined by altitude, changing landscapes and the approach to {$title}." );
        
        $highlights = get_post_meta( $post_id, '_trek_highlights', true );
        if ( empty( $highlights ) || ! is_array( $highlights ) ) {
            $highlights = get_post_meta( $post_id, '_trek_inclusions_list', true );
        }
        if ( empty( $highlights ) || ! is_array( $highlights ) ) {
            $highlights = array(
                "Experience the high trails and alpine vistas of {$title}",
                "Immerse yourself in authentic mountain heritage and welcoming local culture",
                "Carefully paced itinerary structured with safety and acclimatisation days",
                "Full support from experienced Sherpa guides and private logistics"
            );
        }

        $overview_img        = get_post_meta( $post_id, '_trek_overview_img', true ) ?: ( $featured_image ?: '' );
        $route_img           = get_post_meta( $post_id, '_trek_route_img', true ) ?: ( $featured_image ?: '' );
        $map_embed_code      = get_post_meta( $post_id, '_trek_map_embed_code', true ) ?: '';
        $map_file_url        = get_post_meta( $post_id, '_trek_map_file_url', true ) ?: '';
        $route_caption_sub   = get_post_meta( $post_id, '_trek_route_caption_sub', true ) ?: 'Route Map';
        $route_caption_h3    = get_post_meta( $post_id, '_trek_route_caption_h3', true ) ?: ( 'Kathmandu to ' . $title );

        // 6. Day-by-Day Itinerary
        $itin_kicker         = get_post_meta( $post_id, '_trek_itin_kicker', true ) ?: 'Day by Day';
        $itin_script         = get_post_meta( $post_id, '_trek_itin_script', true ) ?: 'Your Journey';
        $itin_heading        = get_post_meta( $post_id, '_trek_itin_heading', true ) ?: ( $hero_days . ' Days Through ' . $breadcrumb_reg );
        $itin_subtext        = get_post_meta( $post_id, '_trek_itin_subtext', true ) ?: 'Open each day to view the walking plan, meals, accommodation and approximate duration.';

        $itinerary = get_post_meta( $post_id, '_trek_itinerary_list', true );
        if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
            $itinerary = get_post_meta( $post_id, '_trek_itinerary', true );
        }
        if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
            $itinerary = array(
                array(
                    'day'   => '01',
                    'stage' => 'Kathmandu (1,400m)',
                    'title' => 'Arrive in Kathmandu & Hotel Transfer',
                    'desc'  => 'Welcome to Nepal. A Yeti Adventure representative and driver will meet you at the airport, escort you to your hotel and brief you on the journey ahead.',
                    'pills' => 'Meals: Welcome Dinner, Boutique Hotel'
                ),
                array(
                    'day'   => '02',
                    'stage' => 'Mountain Flight & Trek Start',
                    'title' => 'Begin the Journey into the Mountains',
                    'desc'  => 'Scenic flight or private overland transfer to the trek trailhead. Meet your Sherpa crew and begin hiking along scenic river valleys and forested slopes.',
                    'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 4 hrs'
                )
            );
        }

        // Normalize itinerary items with day multi-image gallery
        $normalized_itinerary = array();
        foreach ( $itinerary as $idx => $day_item ) {
            if ( ! is_array( $day_item ) ) continue;
            $day_gal = $day_item['gallery'] ?? ( $day_item['images'] ?? array() );
            if ( is_string( $day_gal ) ) {
                $day_gal = array_values( array_filter( array_map( 'trim', explode( ',', $day_gal ) ) ) );
            } elseif ( ! is_array( $day_gal ) ) {
                $day_gal = array();
            }
            $normalized_itinerary[] = array(
                'day'           => $day_item['day'] ?? sprintf( '%02d', $idx + 1 ),
                'stage'         => $day_item['stage'] ?? ( $day_item['altitude'] ?? '' ),
                'title'         => $day_item['title'] ?? ( 'Day ' . ( $idx + 1 ) ),
                'description'   => $day_item['desc'] ?? ( $day_item['description'] ?? '' ),
                'pills'         => $day_item['pills'] ?? ( $day_item['meals'] ?? '' ),
                'gallery'       => $day_gal,
                'images'        => $day_gal,
                'altitude'      => $day_item['altitude'] ?? ( $day_item['stage'] ?? '' ),
                'stay'          => $day_item['stay'] ?? ( $day_item['accommodation'] ?? '' ),
                'walking_hours' => $day_item['walking_hours'] ?? ''
            );
        }

        // 7. Inclusions & Exclusions
        $inex_kicker         = get_post_meta( $post_id, '_trek_inex_kicker', true ) ?: 'What is Covered';
        $inex_script         = get_post_meta( $post_id, '_trek_inex_script', true ) ?: 'Clear & Transparent';
        $inex_heading        = get_post_meta( $post_id, '_trek_inex_heading', true ) ?: 'Inclusions & Exclusions';
        $inex_subtext        = get_post_meta( $post_id, '_trek_inex_subtext', true ) ?: 'Everything included in your bespoke Himalayan journey, with no hidden surprises.';

        $inclusions_full = get_post_meta( $post_id, '_trek_inclusions_full', true );
        if ( empty( $inclusions_full ) || ! is_array( $inclusions_full ) ) {
            $inclusions_full = get_post_meta( $post_id, '_trek_inclusions', true );
        }
        if ( empty( $inclusions_full ) || ! is_array( $inclusions_full ) ) {
            $inclusions_full = array(
                'All private airport and domestic transfers throughout the trip',
                'All necessary trekking permits, national park entry fees and TIMS card',
                'Experienced licensed English-speaking mountain guide and porter team',
                'All boutique hotel stays in Kathmandu and tea house accommodation on trek',
                'Three meals daily (breakfast, lunch, dinner) during the mountain journey',
                'Comprehensive first aid medical kit and pulse oximeter monitoring'
            );
        }
        // Normalize to {title, description} shape for JS engine compatibility
        $inclusions_full = array_map( function( $item ) {
            if ( is_array( $item ) ) {
                return array(
                    'title'       => $item['title'] ?? ( $item['heading'] ?? '' ),
                    'description' => wp_strip_all_tags( $item['desc'] ?? ( $item['description'] ?? ( $item['content'] ?? '' ) ) ),
                );
            }
            return array( 'title' => (string) $item, 'description' => '' );
        }, $inclusions_full );

        $exclusions_full = get_post_meta( $post_id, '_trek_exclusions_full', true );
        if ( empty( $exclusions_full ) || ! is_array( $exclusions_full ) ) {
            $exclusions_full = get_post_meta( $post_id, '_trek_exclusions', true );
        }
        if ( empty( $exclusions_full ) || ! is_array( $exclusions_full ) ) {
            $exclusions_full = array(
                'International flights to and from Kathmandu',
                'Nepal tourist entry visa fees',
                'Personal travel, medical, and emergency helicopter evacuation insurance',
                'Personal trekking equipment, gear and alcoholic/bottled beverages',
                'Gratuities and tips for guides, porters and drivers'
            );
        }
        // Normalize to {title, description} shape for JS engine compatibility
        $exclusions_full = array_map( function( $item ) {
            if ( is_array( $item ) ) {
                return array(
                    'title'       => $item['title'] ?? ( $item['heading'] ?? '' ),
                    'description' => wp_strip_all_tags( $item['desc'] ?? ( $item['description'] ?? ( $item['content'] ?? '' ) ) ),
                );
            }
            return array( 'title' => (string) $item, 'description' => '' );
        }, $exclusions_full );

        // 8. Photo Gallery
        $gallery_kicker      = get_post_meta( $post_id, '_trek_gallery_kicker', true ) ?: 'Visual Moments';
        $gallery_script      = get_post_meta( $post_id, '_trek_gallery_script', true ) ?: 'In Pictures';
        $gallery_heading     = get_post_meta( $post_id, '_trek_gallery_heading', true ) ?: ( 'Glimpses of ' . $title );
        $gallery_subtext     = get_post_meta( $post_id, '_trek_gallery_subtext', true ) ?: 'A visual collection of landscapes, trails and mountain encounters.';

        $gallery_images = get_post_meta( $post_id, '_trek_gallery_list', true );
        if ( empty( $gallery_images ) || ! is_array( $gallery_images ) ) {
            $gallery_images = get_post_meta( $post_id, '_trek_gallery_images', true );
        }
        $clean_gallery = array();
        if ( ! empty( $gallery_images ) && is_array( $gallery_images ) ) {
            foreach ( $gallery_images as $g_item ) {
                $img_url = is_array( $g_item ) ? ( $g_item['url'] ?? '' ) : $g_item;
                if ( ! empty( $img_url ) ) {
                    $clean_gallery[] = esc_url_raw( $img_url );
                }
            }
        }
        if ( empty( $clean_gallery ) ) {
            $clean_gallery = array_filter( array( $hero_bg_img, $card_image_url, $route_img ) );
        }

        // 9. Essential Info & FAQs (Accordions)
        $ess_kicker          = get_post_meta( $post_id, '_trek_ess_kicker', true ) ?: 'Travel Advice';
        $ess_script          = get_post_meta( $post_id, '_trek_ess_script', true ) ?: 'Good to Know';
        $ess_heading         = get_post_meta( $post_id, '_trek_ess_heading', true ) ?: 'Essential Information';
        $ess_intro           = get_post_meta( $post_id, '_trek_ess_intro', true );
        $ess_card_img        = get_post_meta( $post_id, '_trek_ess_card_img', true ) ?: $card_image_url;
        $ess_card_kicker     = get_post_meta( $post_id, '_trek_ess_card_kicker', true ) ?: 'Trip Dossier';
        $ess_card_title      = get_post_meta( $post_id, '_trek_ess_card_title', true ) ?: 'Download Comprehensive Route Notes';
        $ess_card_desc       = get_post_meta( $post_id, '_trek_ess_card_desc', true ) ?: 'Detailed packing checklist, altitude profile, visa guidelines and weather notes.';
        $ess_card_btn        = get_post_meta( $post_id, '_trek_ess_card_btn', true ) ?: 'Request Trip Notes';
        $ess_card_email      = get_post_meta( $post_id, '_trek_ess_card_email', true ) ?: 'sales@yetiadventure.com';

        $faqs_list = get_post_meta( $post_id, '_trek_essential_list', true );
        if ( empty( $faqs_list ) || ! is_array( $faqs_list ) ) {
            $faqs_list = get_post_meta( $post_id, '_trek_faqs', true );
        }
        $normalized_faqs = array();
        if ( ! empty( $faqs_list ) && is_array( $faqs_list ) ) {
            foreach ( $faqs_list as $f_item ) {
                if ( ! is_array( $f_item ) ) continue;
                $normalized_faqs[] = array(
                    'question' => $f_item['title'] ?? ( $f_item['question'] ?? '' ),
                    'answer'   => $f_item['content'] ?? ( $f_item['answer'] ?? '' ),
                    'tag'      => $f_item['tag'] ?? ''
                );
            }
        }
        if ( empty( $normalized_faqs ) ) {
            $normalized_faqs = array(
                array(
                    'question' => 'What is the best season to undertake this trek?',
                    'answer'   => 'The optimal seasons are spring (March through May) for clear skies and blooming rhododendrons, and autumn (September through December) for crisp mountain visibility and stable weather.'
                ),
                array(
                    'question' => 'How fit do I need to be and how are altitude symptoms managed?',
                    'answer'   => 'A good level of cardiovascular fitness is recommended. Our itineraries are structured with gradual altitude gain, scheduled acclimatisation days, and daily health monitoring by certified guides.'
                ),
                array(
                    'question' => 'What type of accommodation and food can I expect?',
                    'answer'   => 'In Kathmandu, you will stay in high-quality boutique hotels. During the trek, accommodation is in comfortable teahouses and lodges offering fresh, hearty multi-course meals.'
                )
            );
        }

        // 10. Traveller Reviews & Rating
        $review_kicker       = get_post_meta( $post_id, '_trek_review_kicker', true ) ?: 'Guest Stories';
        $review_script       = get_post_meta( $post_id, '_trek_review_script', true ) ?: 'Traveller Feedback';
        $review_heading      = get_post_meta( $post_id, '_trek_review_heading', true ) ?: 'Words from Fellow Adventurers';
        $review_subtext      = get_post_meta( $post_id, '_trek_review_subtext', true ) ?: 'Real feedback from travelers who have explored the Himalayas with Yeti Adventure.';

        // Support both _trek_curated_reviews (CPT save) and _trek_reviews_list (importer/legacy)
        $curated_reviews = get_post_meta( $post_id, '_trek_curated_reviews', true );
        if ( empty( $curated_reviews ) || ! is_array( $curated_reviews ) ) {
            $curated_reviews = get_post_meta( $post_id, '_trek_reviews_list', true );
        }
        $all_reviews = array();

        // 10b. Also query approved reviews from database (`trek_review` post type)
        $db_reviews_query = new WP_Query( array(
            'post_type'      => 'trek_review',
            'post_status'    => array( 'publish', 'private' ),
            'posts_per_page' => 20,
            'meta_query'     => array(
                array(
                    'key'     => '_trek_id',
                    'value'   => $post_id,
                    'compare' => '='
                ),
                array(
                    'key'     => '_trek_approved',
                    'value'   => '1',
                    'compare' => '='
                )
            )
        ) );

        if ( $db_reviews_query->have_posts() ) {
            while ( $db_reviews_query->have_posts() ) {
                $db_reviews_query->the_post();
                $rev_id = get_the_ID();
                $all_reviews[] = array(
                    'name'     => get_the_title( $rev_id ),
                    'location' => implode( ', ', array_filter( array( get_post_meta( $rev_id, '_trek_city', true ), get_post_meta( $rev_id, '_trek_country', true ) ) ) ),
                    'rating'   => intval( get_post_meta( $rev_id, '_trek_rating', true ) ?: 5 ),
                    'quote'    => get_the_content( null, false, $rev_id ) ?: get_post_meta( $rev_id, '_trek_moment', true ),
                    'date'     => get_post_meta( $rev_id, '_trek_tour_date', true ) ?: get_the_date( 'Y-m-d', $rev_id )
                );
            }
            wp_reset_postdata();
        }

        if ( ! empty( $curated_reviews ) && is_array( $curated_reviews ) ) {
            foreach ( $curated_reviews as $cr ) {
                if ( ! is_array( $cr ) ) continue;
                $all_reviews[] = array(
                    'name'     => $cr['name'] ?? 'Traveller',
                    'location' => $cr['location'] ?? '',
                    'rating'   => intval( $cr['rating'] ?? 5 ),
                    'quote'    => $cr['quote'] ?? '',
                    'date'     => date( 'Y-m-d' )
                );
            }
        }

        if ( empty( $all_reviews ) ) {
            $all_reviews = array(
                array(
                    'name'     => 'Jo & Laurie Prowse',
                    'location' => 'United Kingdom',
                    'rating'   => 5,
                    'quote'    => 'Our trek manager’s professionalism and meticulous care made the mountain journey deeply memorable from beginning to end.',
                    'date'     => date( 'Y-m-d' )
                ),
                array(
                    'name'     => 'Marcus & Elena Vance',
                    'location' => 'Australia',
                    'rating'   => 5,
                    'quote'    => 'The pacing was thoughtful, the lodges comfortable, and standing at base camp was a lifetime highlight.',
                    'date'     => date( 'Y-m-d' )
                )
            );
        }

        // Calculate Average Rating
        $total_ratings = 0;
        foreach ( $all_reviews as $r ) {
            $total_ratings += intval( $r['rating'] );
        }
        $avg_rating = ! empty( $all_reviews ) ? round( $total_ratings / count( $all_reviews ), 1 ) : 5.0;

        // 11. Fixed Departure, WhatsApp & Contact Settings
        $dep_kicker          = get_post_meta( $post_id, '_trek_dep_kicker', true ) ?: 'Travel Together';
        $dep_script          = get_post_meta( $post_id, '_trek_dep_script', true ) ?: 'Choose Your Month';
        $dep_heading         = get_post_meta( $post_id, '_trek_dep_heading', true ) ?: 'Join a Fixed Departure';
        $dep_subtext         = get_post_meta( $post_id, '_trek_dep_subtext', true ) ?: 'Submit your preferred travel month and our team will confirm the current schedule, dates and available spots.';
        $dep_box_script      = get_post_meta( $post_id, '_trek_dep_box_script', true ) ?: ( $script_title . ' Awaits' );
        $dep_box_title       = get_post_meta( $post_id, '_trek_dep_box_title', true ) ?: 'Ask about upcoming group departures';
        $dep_box_desc        = get_post_meta( $post_id, '_trek_dep_box_desc', true ) ?: 'Share your group size, preferred dates and questions directly with Yeti Adventure through WhatsApp.';
        $dep_box_photo       = get_post_meta( $post_id, '_trek_dep_box_photo', true ) ?: $hero_bg_img;
        $dep_box_note        = get_post_meta( $post_id, '_trek_dep_box_note', true ) ?: 'Departure availability and custom itineraries are confirmed directly before booking.';

        $whatsapp_number     = get_post_meta( $post_id, '_trek_whatsapp_number', true ) ?: '9779803197006';
        $sales_email         = get_post_meta( $post_id, '_trek_sales_email', true ) ?: 'sales@yetiadventure.com';

        // 12. Pricing / Offers
        $price               = get_post_meta( $post_id, '_trek_price', true ) ?: get_post_meta( $post_id, '_trek_cost', true );
        $currency            = get_post_meta( $post_id, '_trek_currency', true ) ?: 'USD';
        $numeric_price       = ! empty( $price ) ? floatval( preg_replace( '/[^0-9.]/', '', $price ) ) : 0;
        $formatted_price     = $numeric_price > 0 ? ( $currency . ' ' . number_format( $numeric_price ) ) : '';

        // Parsed duration days integer
        $days_num = intval( preg_replace( '/[^0-9]/', '', $hero_days ) ) ?: 15;

        return array(
            'id'               => $post_id,
            'title'            => $title,
            'permalink'        => $permalink,
            'slug'             => $post->post_name,
            'categories'       => $categories,
            'category_slugs'   => $category_slugs,
            'primary_category' => $primary_cat_name,
            'primary_category_slug' => $primary_cat_slug,
            'regions'          => $regions,
            'region_slugs'     => $region_slugs,
            'breadcrumb_region'=> $breadcrumb_reg,
            'difficulty'       => $difficulty,
            'difficulty_slug'  => $diff_slug,
            'map_region'       => $map_region,
            'map_district'     => $map_district,
            'featured'         => $is_featured,
            'status'           => $trip_status,
            
            // Hero section
            'hero' => array(
                'script_title' => $script_title,
                'title'        => $hero_title,
                'kicker'       => $hero_kicker,
                'subtitle'     => $hero_subtitle,
                'days'         => $hero_days,
                'badge_sub'    => $hero_badge_sub,
                'image'        => $hero_bg_img,
            ),

            // Card display
            'card' => array(
                'title'        => $card_title,
                'meta'         => $card_meta,
                'tag'          => $card_tag,
                'kicker'       => $card_kicker,
                'excerpt'      => $card_excerpt,
                'image'        => $card_image_url,
                'order'        => $card_order,
            ),

            // Quick Facts
            'quick_facts' => array(
                'high_point'   => $fact_high_point,
                'low_point'    => $fact_low_point,
                'status'       => $trip_status,
                'region'       => $fact_region,
                'trekking'     => $fact_trekking,
                'accom'        => $fact_accom,
                'style'        => $fact_style,
                'season'       => $fact_season,
                'group_size'   => $fact_group_size,
            ),

            // Overview & Route
            'overview' => array(
                'kicker'        => $overview_kicker,
                'script'        => $overview_script,
                'heading'       => $overview_heading,
                'subheading'    => $overview_subheading,
                'content'       => $overview_content,
                'highlight'     => $overview_highlight,
                'highlights'    => $highlights,
                'image'         => $overview_img,
                'route_img'     => $route_img,
                'map_embed_code'=> $map_embed_code,
                'map_file_url'  => $map_file_url,
                'route_sub'     => $route_caption_sub,
                'route_heading' => $route_caption_h3,
            ),

            // Itinerary
            'itinerary_section' => array(
                'kicker'       => $itin_kicker,
                'script'       => $itin_script,
                'heading'      => $itin_heading,
                'subtext'      => $itin_subtext,
            ),
            'itinerary'        => $normalized_itinerary,

            // Inclusions & Exclusions
            'inex' => array(
                'kicker'       => $inex_kicker,
                'script'       => $inex_script,
                'heading'      => $inex_heading,
                'subtext'      => $inex_subtext,
                'inclusions'   => $inclusions_full,
                'exclusions'   => $exclusions_full,
            ),

            // Gallery
            'gallery' => array(
                'kicker'       => $gallery_kicker,
                'script'       => $gallery_script,
                'heading'      => $gallery_heading,
                'subtext'      => $gallery_subtext,
                'images'       => $clean_gallery,
            ),

            // Essential Info & FAQs
            'essential' => array(
                'kicker'       => $ess_kicker,
                'script'       => $ess_script,
                'heading'      => $ess_heading,
                'intro'        => $ess_intro,
                'card_img'     => $ess_card_img,
                'card_kicker'  => $ess_card_kicker,
                'card_title'   => $ess_card_title,
                'card_desc'    => $ess_card_desc,
                'card_btn'     => $ess_card_btn,
                'card_email'   => $ess_card_email,
            ),
            'faqs'             => $normalized_faqs,

            // Reviews & Ratings
            'reviews_section' => array(
                'kicker'       => $review_kicker,
                'script'       => $review_script,
                'heading'      => $review_heading,
                'subtext'      => $review_subtext,
                'average_rating'=> $avg_rating,
                'review_count' => count( $all_reviews ),
                'reviews'      => $all_reviews,
            ),

            // Fixed Departure & Contact
            'contact' => array(
                'dep_kicker'   => $dep_kicker,
                'dep_script'   => $dep_script,
                'dep_heading'  => $dep_heading,
                'dep_subtext'  => $dep_subtext,
                'box_script'   => $dep_box_script,
                'box_title'    => $dep_box_title,
                'box_desc'     => $dep_box_desc,
                'box_photo'    => $dep_box_photo,
                'box_note'     => $dep_box_note,
                'whatsapp'     => $whatsapp_number,
                'email'        => $sales_email,
            ),

            // Pricing
            'pricing' => array(
                'price'        => $price,
                'numeric_price'=> $numeric_price,
                'formatted'    => $formatted_price,
                'currency'     => $currency,
            ),

            // SEO & Structured Metadata Helper
            'seo' => array(
                'meta_title'       => $hero_title . ' | ' . $hero_days . ' Days Himalayan Journey | Yeti Adventure',
                'meta_description' => wp_strip_all_tags( $hero_subtitle ),
                'canonical_url'    => $permalink,
                'og_type'          => 'article',
                'og_image'         => $hero_bg_img,
                'twitter_card'     => 'summary_large_image',
                'duration_days'    => $days_num,
                'duration_iso'     => 'P' . $days_num . 'D',
            ),

            // Client Engine / JS Canonical Aliases
            'keyFacts' => array(
                'duration'     => $days_num,
                'highestPoint' => $fact_high_point,
                'lowestPoint'  => $fact_low_point,
                'status'       => $trip_status,
                'region'       => $fact_region,
                'destination'  => $fact_region,
                'difficulty'   => $difficulty,
                'accommodation'=> $fact_accom,
                'dailyWalking' => $fact_trekking,
                'tripStyle'    => $fact_style,
                'bestSeason'   => $fact_season,
                'groupSize'    => $fact_group_size,
            ),
            'geography' => array(
                'country'      => $breadcrumb_reg,
                'region'       => ! empty( $regions ) ? $regions[0] : $breadcrumb_reg,
                'mapRegion'    => $map_region,
                'mapDistrict'  => $map_district,
            ),
            'included' => $inclusions_full,
            'excluded' => $exclusions_full,
            'reviews'  => $all_reviews,
        );
    }

    /**
     * Render Comprehensive Schema.org JSON-LD Structured Data
     *
     * Outputs:
     * 1. TouristTrip (with Itinerary ItemList, Inclusions, Duration, High Point)
     * 2. Product (with AggregateRating, Reviews, Offers)
     * 3. FAQPage (with Question/Answer pairs from Essential Info)
     * 4. BreadcrumbList (Home > All Trips > Region > Destination)
     */
    public static function render_schema_json_ld( $trek_data ) {
        if ( ! is_array( $trek_data ) || empty( $trek_data['title'] ) ) {
            $trek_data = self::get_full_trek_data( $trek_data );
        }

        if ( empty( $trek_data ) || empty( $trek_data['title'] ) ) {
            return;
        }

        $title       = $trek_data['title'];
        $permalink   = $trek_data['permalink'] ?? home_url();
        $description = wp_strip_all_tags( $trek_data['hero']['subtitle'] ?? ( $trek_data['card']['excerpt'] ?? '' ) );
        $image       = $trek_data['hero']['image'] ?? ( $trek_data['card']['image'] ?? '' );
        $days_count  = intval( preg_replace( '/[^0-9]/', '', $trek_data['hero']['days'] ?? '15' ) ) ?: 15;
        $region_name = $trek_data['breadcrumb_region'] ?? 'Nepal';

        // 1. Schema: TouristTrip
        $tourist_trip = array(
            '@context'          => 'https://schema.org',
            '@type'             => 'TouristTrip',
            'name'              => esc_html( $title ),
            'description'       => esc_html( $description ),
            'url'               => esc_url( $permalink ),
            'touristType'       => esc_html( implode( ', ', $trek_data['categories'] ?? array( 'Trekking', 'Himalayan Adventure' ) ) ),
            'subTrip'           => esc_html( $trek_data['quick_facts']['style'] ?? 'High-Altitude Guided Trek' ),
            'duration'          => 'P' . $days_count . 'D',
            'subjectOf'         => array(
                '@type'         => 'GovernmentPermit',
                'name'          => 'National Park & Trekking Permits'
            ),
            'provider'          => array(
                '@type'         => 'TravelAgency',
                'name'          => 'Yeti Adventure',
                'url'           => home_url( '/' ),
                'telephone'     => esc_html( $trek_data['contact']['whatsapp'] ?? '+9779803197006' ),
                'email'         => esc_html( $trek_data['contact']['email'] ?? 'sales@yetiadventure.com' ),
            ),
            'itinerary'         => array(
                '@type'           => 'ItemList',
                'numberOfItems'   => count( $trek_data['itinerary'] ?? array() ),
                'itemListElement' => array()
            )
        );

        if ( ! empty( $image ) ) {
            $tourist_trip['image'] = esc_url( $image );
        }

        // Itinerary items
        if ( ! empty( $trek_data['itinerary'] ) && is_array( $trek_data['itinerary'] ) ) {
            foreach ( $trek_data['itinerary'] as $idx => $day ) {
                $tourist_trip['itinerary']['itemListElement'][] = array(
                    '@type'       => 'ListItem',
                    'position'    => $idx + 1,
                    'name'        => esc_html( 'Day ' . ( $day['day'] ?? ( $idx + 1 ) ) . ': ' . ( $day['title'] ?? '' ) ),
                    'description' => esc_html( wp_strip_all_tags( $day['description'] ?? '' ) ),
                    'item'        => array(
                        '@type'       => 'TouristAttraction',
                        'name'        => esc_html( $day['stage'] ?? ( $day['title'] ?? '' ) ),
                        'description' => esc_html( $day['pills'] ?? '' )
                    )
                );
            }
        }

        // Offers / Pricing
        if ( ! empty( $trek_data['pricing']['price'] ) ) {
            $tourist_trip['offers'] = array(
                '@type'         => 'Offer',
                'price'         => floatval( $trek_data['pricing']['price'] ),
                'priceCurrency' => esc_html( $trek_data['pricing']['currency'] ?? 'USD' ),
                'availability'  => 'https://schema.org/InStock',
                'url'           => esc_url( $permalink ),
                'validFrom'     => date( 'c' )
            );
        }

        // 2. Schema: Product with AggregateRating & Reviews
        $product_schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => esc_html( $title ),
            'description' => esc_html( $description ),
            'image'       => esc_url( $image ),
            'url'         => esc_url( $permalink ),
            'brand'       => array(
                '@type'   => 'Brand',
                'name'    => 'Yeti Adventure'
            ),
        );

        $rev_count = $trek_data['reviews_section']['review_count'] ?? 0;
        $avg_rat   = $trek_data['reviews_section']['average_rating'] ?? 5.0;

        if ( $rev_count > 0 ) {
            $product_schema['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => esc_html( (string) $avg_rat ),
                'reviewCount' => intval( $rev_count ),
                'bestRating'  => '5',
                'worstRating' => '1'
            );

            $schema_reviews = array();
            foreach ( $trek_data['reviews_section']['reviews'] as $rev ) {
                $schema_reviews[] = array(
                    '@type'         => 'Review',
                    'author'        => array(
                        '@type'     => 'Person',
                        'name'      => esc_html( $rev['name'] ?? 'Traveller' )
                    ),
                    'reviewRating'  => array(
                        '@type'       => 'Rating',
                        'ratingValue' => esc_html( (string) ( $rev['rating'] ?? 5 ) ),
                        'bestRating'  => '5',
                        'worstRating' => '1'
                    ),
                    'reviewBody'    => esc_html( wp_strip_all_tags( $rev['quote'] ?? '' ) ),
                    'datePublished' => esc_html( $rev['date'] ?? date( 'Y-m-d' ) )
                );
            }
            $product_schema['review'] = $schema_reviews;
        }

        // 3. Schema: FAQPage (if FAQs present)
        $faq_schema = array();
        if ( ! empty( $trek_data['faqs'] ) && is_array( $trek_data['faqs'] ) ) {
            $faq_schema = array(
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => array()
            );

            foreach ( $trek_data['faqs'] as $faq ) {
                if ( empty( $faq['question'] ) ) continue;
                $faq_schema['mainEntity'][] = array(
                    '@type'          => 'Question',
                    'name'           => esc_html( $faq['question'] ),
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => esc_html( wp_strip_all_tags( $faq['answer'] ?? '' ) )
                    )
                );
            }
        }

        // 4. Schema: BreadcrumbList
        $breadcrumb_schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array(
                array(
                    '@type'    => 'ListItem',
                    'position' => 1,
                    'name'     => 'Home',
                    'item'     => home_url( '/' )
                ),
                array(
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => 'All Trips',
                    'item'     => home_url( '/all-trips/' )
                ),
                array(
                    '@type'    => 'ListItem',
                    'position' => 3,
                    'name'     => esc_html( $region_name ),
                    'item'     => home_url( '/destinations/' . sanitize_title( $region_name ) . '/' )
                ),
                array(
                    '@type'    => 'ListItem',
                    'position' => 4,
                    'name'     => esc_html( $title ),
                    'item'     => esc_url( $permalink )
                )
            )
        );

        // Output all schemas cleanly in application/ld+json blocks
        echo "\n<!-- Schema.org JSON-LD / Trek Manager -->\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $tourist_trip, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $product_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
        if ( ! empty( $faq_schema['mainEntity'] ) ) {
            echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
        }
        echo '<script type="application/ld+json">' . wp_json_encode( $breadcrumb_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
    }

    /**
     * Render Open Graph & Social Meta Tags in <head>
     */
    public static function render_social_meta( $trek_data ) {
        if ( ! is_array( $trek_data ) || empty( $trek_data['title'] ) ) {
            $trek_data = self::get_full_trek_data( $trek_data );
        }

        if ( empty( $trek_data ) || empty( $trek_data['title'] ) ) {
            return;
        }

        $title       = $trek_data['title'];
        $permalink   = $trek_data['permalink'] ?? get_permalink();
        $description = wp_strip_all_tags( $trek_data['hero']['subtitle'] ?? ( $trek_data['card']['excerpt'] ?? '' ) );
        $image       = $trek_data['hero']['image'] ?? ( $trek_data['card']['image'] ?? '' );
        $region      = $trek_data['breadcrumb_region'] ?? 'Nepal';
        $category    = ! empty( $trek_data['categories'] ) ? implode( ', ', $trek_data['categories'] ) : 'Trekking';
        $days        = $trek_data['hero']['days'] ?? '15';

        ?>
        <!-- Open Graph / Trek Content Manager -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ?: 'Yeti Adventure' ); ?>">
        <meta property="og:title" content="<?php echo esc_attr( $title . ' (' . $days . ' Days) | ' . $region ); ?>">
        <meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
        <meta property="og:url" content="<?php echo esc_url( $permalink ); ?>">
        <?php if ( ! empty( $image ) ) : ?>
            <meta property="og:image" content="<?php echo esc_url( $image ); ?>">
            <meta property="og:image:alt" content="<?php echo esc_attr( $title ); ?>">
        <?php endif; ?>
        <meta property="og:locale" content="<?php echo esc_attr( get_locale() ?: 'en_US' ); ?>">

        <!-- Twitter Cards -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr( $title . ' (' . $days . ' Days) | ' . $region ); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
        <?php if ( ! empty( $image ) ) : ?>
            <meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
            <meta name="twitter:image:alt" content="<?php echo esc_attr( $title ); ?>">
        <?php endif; ?>

        <!-- SEO Meta Keywords & Location -->
        <link rel="canonical" href="<?php echo esc_url( $permalink ); ?>">
        <meta name="keywords" content="<?php echo esc_attr( $title . ', ' . $category . ', ' . $region . ' trek, Himalayan adventure, Yeti Adventure' ); ?>">
        <meta name="geo.region" content="<?php echo esc_attr( $region ); ?>">
        <?php
    }
}
