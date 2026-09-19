<?php
/**
 * Template Name: Single Trek Destination Detail Page
 * Description: 100% Dynamic luxury editorial single trek detail page for all destinations.
 */

// 1. Identify Post ID accurately before loading the theme chrome.
$is_shortcode = isset( $atts ) && is_array( $atts );
$post_id  = $is_shortcode && ! empty( $atts['id'] ) ? intval( $atts['id'] ) : ( get_queried_object_id() ?: get_the_ID() );
$post_obj = get_post( $post_id );

if ( ! $is_shortcode ) {
  get_header();
}

// 2. Hero Fields
$title               = get_the_title( $post_id );
$regions             = get_the_terms( $post_id, 'trek_region' );
$default_region      = ( ! empty( $regions ) && ! is_wp_error( $regions ) ) ? $regions[0]->name : 'Nepal';
$breadcrumb_region   = get_post_meta( $post_id, '_trek_breadcrumb_region', true ) ?: $default_region;
$script_title        = get_post_meta( $post_id, '_trek_script_title', true ) ?: $title;
$hero_title          = get_post_meta( $post_id, '_trek_hero_title', true ) ?: $title;
$hero_days           = get_post_meta( $post_id, '_trek_hero_days', true ) ?: '15';
$hero_badge_sub      = get_post_meta( $post_id, '_trek_hero_badge_sub', true ) ?: ( 'Days in ' . $breadcrumb_region );

$default_hero_sub    = has_excerpt( $post_id ) 
    ? get_the_excerpt( $post_id ) 
    : "A carefully paced {$hero_days}-day journey through high Himalayan valleys, authentic local culture and the legendary mountain landscapes of {$breadcrumb_region}.";
$hero_subtitle       = get_post_meta( $post_id, '_trek_hero_subtitle', true ) ?: $default_hero_sub;

$featured_img        = get_the_post_thumbnail_url( $post_id, 'full' );
$hero_bg_img         = get_post_meta( $post_id, '_trek_hero_bg_img', true ) ?: ( $featured_img ?: 'https://yetiadventure.com/wp-content/uploads/2022/02/15-Days_Everest-Base-Camp-trek-1024x682.jpg' );

// Keep the primary hero image first, then add the dedicated Hero Slider Gallery.
// Older destinations without that gallery continue to use their photo gallery as a fallback.
$hero_slider_images  = array( esc_url_raw( $hero_bg_img ) );
$hero_slider_gallery = get_post_meta( $post_id, '_trek_hero_slider_images', true );
if ( empty( $hero_slider_gallery ) || ! is_array( $hero_slider_gallery ) ) {
    $hero_slider_gallery = get_post_meta( $post_id, '_trek_gallery_list', true );
}
if ( empty( $hero_slider_gallery ) || ! is_array( $hero_slider_gallery ) ) {
    $hero_slider_gallery = get_post_meta( $post_id, '_trek_gallery_images', true );
}
if ( ! empty( $hero_slider_gallery ) && is_array( $hero_slider_gallery ) ) {
    foreach ( $hero_slider_gallery as $hero_slide ) {
        $hero_slide_url = is_array( $hero_slide ) ? ( $hero_slide['url'] ?? '' ) : $hero_slide;
        $hero_slide_url = esc_url_raw( $hero_slide_url );
        if ( ! empty( $hero_slide_url ) ) {
            $hero_slider_images[] = $hero_slide_url;
        }
    }
}
$hero_slider_images = array_values( array_unique( array_filter( $hero_slider_images ) ) );

// 3. Quick Facts & Metrics
$fact_high_point     = get_post_meta( $post_id, '_trek_fact_high_point', true ) ?: '5,364m / 17,598ft';
$fact_low_point      = get_post_meta( $post_id, '_trek_fact_low_point', true ) ?: '1,400m · Kathmandu';
$fact_region         = get_post_meta( $post_id, '_trek_fact_region', true ) ?: ( $breadcrumb_region );
$fact_trekking       = get_post_meta( $post_id, '_trek_fact_trekking', true ) ?: 'Usually 5–6 hours';
$fact_accom          = get_post_meta( $post_id, '_trek_fact_accom', true ) ?: 'Teahouses / Lodges';
$fact_style          = get_post_meta( $post_id, '_trek_fact_style', true ) ?: 'High-altitude trek';
$fact_distance       = get_post_meta( $post_id, '_trek_fact_distance', true ) ?: '130 km';
$fact_start          = get_post_meta( $post_id, '_trek_fact_start', true ) ?: 'Lukla Airport';
$fact_end            = get_post_meta( $post_id, '_trek_fact_end', true ) ?: 'Lukla Airport';
$fact_season         = get_post_meta( $post_id, '_trek_fact_season', true ) ?: 'Spring / Autumn';
$fact_group_size     = get_post_meta( $post_id, '_trek_fact_group_size', true ) ?: '2–12 Travellers';

$diff_terms          = get_the_terms( $post_id, 'trek_difficulty' );
$default_diff        = ( ! empty( $diff_terms ) && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->name : 'Challenging';
$fact_difficulty     = get_post_meta( $post_id, '_trek_difficulty', true ) ?: $default_diff;

// 4. Overview Narrative & Highlight Box
$overview_kicker     = get_post_meta( $post_id, '_trek_overview_kicker', true ) ?: ( $breadcrumb_region . ' · ' . $hero_days . ' Days' );
$overview_script     = get_post_meta( $post_id, '_trek_overview_script', true ) ?: 'The Ultimate Trek';
$overview_heading    = get_post_meta( $post_id, '_trek_overview_heading', true ) ?: ( 'To the Heart of ' . $title );
$overview_subheading = get_post_meta( $post_id, '_trek_overview_subheading', true ) ?: ( $title . ' through the Himalayan homelands' );

// Check stored paragraph keys (_trek_overview_p1, _trek_overview_p2) or rich content
$p1 = get_post_meta( $post_id, '_trek_overview_p1', true );
$p2 = get_post_meta( $post_id, '_trek_overview_p2', true );
$p3 = get_post_meta( $post_id, '_trek_overview_p3', true );
$stored_paragraphs  = array_filter( array( $p1, $p2, $p3 ) );

if ( ! empty( $stored_paragraphs ) ) {
    $overview_content = '<p>' . implode( "</p>\n<p>", $stored_paragraphs ) . '</p>';
} else {
    $overview_content = get_post_meta( $post_id, '_trek_overview_content', true );
    if ( empty( $overview_content ) ) {
        $post_body = ! empty( $post_obj->post_content ) ? $post_obj->post_content : '';
        if ( ! empty( $post_body ) ) {
            $overview_content = $post_body;
        } else {
            $overview_content = "<p> {$title} </p>";
        }
    }
}

$overview_highlight  = get_post_meta( $post_id, '_trek_overview_highlight', true ) ?: ( "{$title}" );

$inclusions          = get_post_meta( $post_id, '_trek_inclusions_list', true );
if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
    $inclusions = get_post_meta( $post_id, '_trek_inclusions_full', true );
}
if ( empty( $inclusions ) || ! is_array( $inclusions ) ) {
    $inclusions = array(
        'Airport welcome and hotel assistance in Kathmandu',
        'Carefully placed acclimatisation days',
        'Kathmandu hotel and local lodge accommodation',
        "{$title} scenic viewpoints and landmark highlights"
    );
}

$route_img           = get_post_meta( $post_id, '_trek_route_img', true ) ?: ( $featured_img ?: 'https://yetiadventure.com/wp-content/uploads/2022/02/15-Days_Everest-Base-Camp-trek-map-722x1024.jpg' );
$map_embed_code      = get_post_meta( $post_id, '_trek_map_embed_code', true ) ?: '';
$map_file_url        = get_post_meta( $post_id, '_trek_map_file_url', true ) ?: '';
$route_caption_sub   = get_post_meta( $post_id, '_trek_route_caption_sub', true ) ?: 'ROUTE & ELEVATION';
$route_caption_h3    = get_post_meta( $post_id, '_trek_route_caption_h3', true ) ?: ( 'Roadmap of the ' . $title . ' Trek' );

// 5. Itinerary
$itin_kicker         = get_post_meta( $post_id, '_trek_itin_kicker', true ) ?: 'Day by Day';
$itin_script         = get_post_meta( $post_id, '_trek_itin_script', true ) ?: 'YOUR JOURNEY';
$itin_heading        = get_post_meta( $post_id, '_trek_itin_heading', true ) ?: 'Day by Day Itinerary';
$itin_subtext        = get_post_meta( $post_id, '_trek_itin_subtext', true ) ?: 'A flexible private itinerary blending heritage, nature, mountain views and local encounters.';

$itinerary           = get_post_meta( $post_id, '_trek_itinerary_list', true );
if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
    $itinerary = get_post_meta( $post_id, '_trek_itinerary', true );
}
if ( empty( $itinerary ) || ! is_array( $itinerary ) ) {
    $itinerary = array(
        array( 'day' => '01', 'stage' => 'Kathmandu', 'title' => 'Arrival in Kathmandu', 'desc' => 'Welcome to Nepal. On arrival, a representative and driver will meet you at the airport, escort you to your hotel and brief you on the days ahead.', 'pills' => 'Meals: None, Hotel Accommodation' ),
        array( 'day' => '02', 'stage' => 'Trek Departure', 'title' => 'Journey from Kathmandu to Trailhead', 'desc' => "Meet your guide and team for the journey to the trek starting point. Begin the first walking stage through mountain villages and lush foothills.", 'pills' => 'Breakfast & Dinner, Mountain Lodge, Approx. 4 hrs' ),
        array( 'day' => '03', 'stage' => 'Mountain Valley', 'title' => "Trek through the {$breadcrumb_region} Valley", 'desc' => 'Ascend along river trails and cross suspension bridges as you enter the protected conservation area with magnificent mountain vistas.', 'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 5–6 hrs' ),
        array( 'day' => '04', 'stage' => 'Acclimatisation', 'title' => 'Acclimatisation & Day Exploration Walk', 'desc' => 'Take an active rest day to adapt to the altitude. Enjoy a panoramic day walk to higher viewpoints before returning to the lodge.', 'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 4 hrs' ),
        array( 'day' => '05', 'stage' => 'High Valley', 'title' => "Ascend toward Upper {$script_title}", 'desc' => 'Follow scenic ridge paths above the tree line with breathtaking 360-degree Himalayan views.', 'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 5–6 hrs' ),
        array( 'day' => '06', 'stage' => 'Summit Landmark', 'title' => "Reach the {$title} Landmark", 'desc' => 'A highlight day! Follow steady glacial and alpine trails to reach the main destination viewpoint.', 'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 7 hrs' ),
        array( 'day' => '07', 'stage' => 'Return Descent', 'title' => 'Descend through Historic Mountain Villages', 'desc' => 'Begin the descent back through tranquil villages, monasteries and scenic valleys.', 'pills' => 'Breakfast, Lunch & Dinner, Local Lodge, Approx. 6 hrs' ),
        array( 'day' => '08', 'stage' => 'Trailhead Return', 'title' => 'Complete Trek & Celebrate with Team', 'desc' => 'Walk the final stage back to the trailhead town. Enjoy a farewell dinner celebrating the completed journey with guides and porters.', 'pills' => 'Breakfast, Lunch & Dinner, Lodge, Approx. 5 hrs' ),
        array( 'day' => '09', 'stage' => 'Kathmandu', 'title' => 'Return to Kathmandu', 'desc' => 'Transfer back to Kathmandu. Check into your hotel with the afternoon free for relaxation or shopping in Thamel.', 'pills' => 'Breakfast, Hotel Accommodation' ),
        array( 'day' => '10', 'stage' => 'Departure', 'title' => 'Airport Transfer & Departure', 'desc' => 'Transfer to the international airport for your onward flight home with lifelong Himalayan memories.', 'pills' => 'Breakfast, Airport Transfer' )
    );
}

if ( ! function_exists( 'ya_get_itin_pill_icon' ) ) {
    function ya_get_itin_pill_icon( $text ) {
        $t = strtolower( $text );
        if ( preg_match( '/meal|breakfast|lunch|dinner|food|cuisine|bbq|tea|coffee/', $t ) ) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 2v20M18 2a3 3 0 0 0-3 3v5a3 3 0 0 0 3 3M6 2v6a3 3 0 0 0 3 3v11M3 2v6a3 3 0 0 0 3 3M9 2v6a3 3 0 0 1-3 3"/></svg>';
        }
        if ( preg_match( '/hotel|lodge|stay|resort|boutique|accommodation|camp|teahouse|room/', $t ) ) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/><circle cx="9" cy="9" r="1"/><circle cx="9" cy="13" r="1"/><circle cx="9" cy="17" r="1"/></svg>';
        }
        if ( preg_match( '/walk|trek|hike|trail|climb|hour|hr|approx|km|distance/', $t ) ) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="4" r="2"/><path d="m15 22-3-7-3 3v6M9 13l3-3 3 1 3 4M6 17l3-4"/></svg>';
        }
        if ( preg_match( '/guide|sherpa|leader|escort|briefing|team|porter/', $t ) ) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>';
        }
        if ( preg_match( '/heritage|durbar|temple|culture|city|monastery|stupa|craft|art/', $t ) ) {
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18M4 18h16M6 18V9M10 18V9M14 18V9M18 18V9M2 9l10-6 10 6"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>';
    }
}

if ( ! function_exists( 'ya_parse_day_stage_meta' ) ) {
    function ya_parse_day_stage_meta( $day, $fallback_region ) {
        $stage    = $day['stage'] ?? '';
        $altitude = $day['altitude'] ?? '';
        $loc      = '';
        $alt      = '';

        if ( preg_match( '/^(.*?)\s*\((.*?)\)$/', trim( $stage ), $matches ) ) {
            $loc = trim( $matches[1] );
            $alt = trim( $matches[2] );
        } elseif ( preg_match( '/(\d+[\d,]*\s*m)/i', $stage, $alt_match ) ) {
            $alt = trim( $alt_match[1] );
            $loc = trim( str_replace( $alt_match[0], '', $stage ) );
            $loc = trim( trim( $loc, ', -·' ) );
        } else {
            $loc = trim( $stage );
        }

        if ( ! empty( $altitude ) && empty( $alt ) ) {
            $alt = trim( $altitude );
        }
        if ( empty( $loc ) ) {
            $loc = $fallback_region ?: 'Trailhead';
        }
        if ( empty( $alt ) ) {
            $alt = '1,350m';
        }
        return array( 'location' => $loc, 'altitude' => $alt );
    }
}

// 6. Gallery
$gal_kicker          = get_post_meta( $post_id, '_trek_gallery_kicker', true ) ?: 'Visual Journey';
$gal_script          = get_post_meta( $post_id, '_trek_gallery_script', true ) ?: 'Above the Clouds';
$gal_heading         = get_post_meta( $post_id, '_trek_gallery_heading', true ) ?: ( 'Landscapes of ' . $title );
$gal_subtext         = get_post_meta( $post_id, '_trek_gallery_subtext', true ) ?: 'Select any photograph to view it at a larger size.';
$gallery             = get_post_meta( $post_id, '_trek_gallery_list', true ) ?: array();
if ( empty( $gallery ) ) {
    $gallery = array(
        array( 'url' => $hero_bg_img, 'label' => $title ),
        array( 'url' => 'https://yetiadventure.com/wp-content/uploads/2022/02/NAMCHE-BAZAAR-1024x576.jpg', 'label' => 'Mountain Village' ),
        array( 'url' => 'https://yetiadventure.com/wp-content/uploads/2022/02/AERIAL-VIEW-OF-THE-EVEREST-BASE-CAMP.jpg', 'label' => 'High Altitude Panorama' ),
        array( 'url' => 'https://yetiadventure.com/wp-content/uploads/2022/02/CHORTEIN-ON-THE-WAY-TO-TENGBOCHE-1024x476.jpg', 'label' => 'Sacred Chortens' ),
        array( 'url' => 'https://yetiadventure.com/wp-content/uploads/2022/02/KHUMBU-GLACIER-1024x683.jpg', 'label' => 'Glacial Landscapes' )
    );
}

// 7. Essential Info
$ess_kicker          = get_post_meta( $post_id, '_trek_ess_kicker', true ) ?: 'Before You Go';
$ess_script          = get_post_meta( $post_id, '_trek_ess_script', true ) ?: 'Prepare Well';
$ess_heading         = get_post_meta( $post_id, '_trek_ess_heading', true ) ?: ( 'Essential ' . $title . ' Information' );
$ess_intro           = get_post_meta( $post_id, '_trek_ess_intro', true );
if ( empty( $ess_intro ) ) {
    $ess_intro = '<p>Requirements and conditions can change. Confirm final visa, health, insurance and equipment guidance in your departure-specific trip notes.</p>';
}
$card_img            = get_post_meta( $post_id, '_trek_ess_card_img', true ) ?: 'https://yetiadventure.com/wp-content/uploads/2022/02/KHUMBU-GLACIER-1024x683.jpg';
$card_kicker         = get_post_meta( $post_id, '_trek_ess_card_kicker', true ) ?: 'Detailed Preparation';
$card_title          = get_post_meta( $post_id, '_trek_ess_card_title', true ) ?: 'Request the current trip notes';
$card_desc           = get_post_meta( $post_id, '_trek_ess_card_desc', true ) ?: 'Departure-specific notes contain the final kit list, inclusions, practical guidance and route updates.';
$card_btn            = get_post_meta( $post_id, '_trek_ess_card_btn', true ) ?: 'Email Yeti Adventure';
$card_email          = get_post_meta( $post_id, '_trek_ess_card_email', true ) ?: 'sales@yetiadventure.com';

$accordions          = get_post_meta( $post_id, '_trek_essential_list', true ) ?: array();
if ( empty( $accordions ) ) {
    $accordions = array(
        array( 'title' => 'Visa and travel documents', 'content' => '<p>Many visitors require a Nepal visa, available either online before travel or upon arrival at Tribhuvan International Airport depending on nationality. Carry a passport with at least 6 months validity.</p>' ),
        array( 'title' => 'Health, altitude and acclimatisation', 'content' => '<p>This itinerary reaches high elevations. The schedule includes dedicated acclimatisation days, but every traveller should understand altitude symptoms, hydrate well and follow the guide’s instructions.</p><p>Consult a qualified travel physician regarding recommended vaccinations and medication before departure.</p>' ),
        array( 'title' => 'Meals and drinking water', 'content' => '<p>Tea-house menus include fresh dal bhat, soups, pasta, noodles, rice dishes and warm breakfasts. Hydration is vital at altitude. Always use treated, filtered or boiled drinking water.</p>' ),
        array( 'title' => 'Weather and best trekking seasons', 'content' => '<p>Autumn (Sep–Nov) and Spring (Mar–May) provide the clearest skies and most stable weather. Mountain weather can change quickly, so multi-layer clothing is essential.</p>' ),
        array( 'title' => 'Equipment checklist', 'content' => '<ul><li>Sturdy, worn-in waterproof trekking boots</li><li>Thermal base layers, fleece and down insulated jacket</li><li>Waterproof/windproof outer shell jacket and trousers</li><li>Warm hat, UV sunglasses, sun hat, and gloves</li><li>Quality sleeping bag rated for sub-zero temperatures</li><li>Daypack, trekking poles, headlamp and personal first aid</li></ul>' ),
        array( 'title' => 'Lodge accommodation & facilities', 'content' => '<p>Nights in Kathmandu feature comfortable hotels. During the trek, stay in authentic mountain teahouses and lodges. Hot showers, Wi-Fi, and device charging are available along most routes.</p>' )
    );
}

// 9. Reviews
$rev_kicker          = get_post_meta( $post_id, '_trek_review_kicker', true ) ?: 'Traveller Stories';
$rev_script          = get_post_meta( $post_id, '_trek_review_script', true ) ?: 'From the Trail';
$rev_heading         = get_post_meta( $post_id, '_trek_review_heading', true ) ?: ( $title . ' Reviews' );
$rev_subtext         = get_post_meta( $post_id, '_trek_review_subtext', true ) ?: 'Read verified traveller reviews or share your feedback directly with our team.';

// Query approved database reviews for this specific trek
$db_reviews_query = new WP_Query( array(
    'post_type'      => 'trek_review',
    'posts_per_page' => 50,
    'post_status'    => 'publish',
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
    ),
    'orderby'        => 'date',
    'order'          => 'DESC'
) );

// Helper to generate elegant 2-letter initials from traveler name
if ( ! function_exists( 'ya_get_reviewer_initials' ) ) {
    function ya_get_reviewer_initials( $name ) {
        $clean = trim( preg_replace( '/[^\p{L}\s&]/u', '', (string) $name ) );
        if ( empty( $clean ) ) return 'TR';
        $parts = preg_split( '/[\s&]+/', $clean );
        $parts = array_values( array_filter( $parts ) );
        if ( count( $parts ) >= 2 ) {
            return strtoupper( mb_substr( $parts[0], 0, 1 ) . mb_substr( $parts[1], 0, 1 ) );
        } elseif ( ! empty( $parts[0] ) ) {
            return strtoupper( mb_substr( $parts[0], 0, min( 2, mb_strlen( $parts[0] ) ) ) );
        }
        return 'TR';
    }
}

// Destination options for review form dropdown
$dest_options = array();
$all_trek_destinations = get_posts( array(
    'post_type'      => 'trek_destination',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );
if ( ! empty( $all_trek_destinations ) ) {
    foreach ( $all_trek_destinations as $td_post ) {
        $dest_options[] = $td_post->post_title;
    }
}
$default_dest_options = array(
    'Everest Base Camp',
    'Annapurna Circuit',
    'Manaslu Circuit',
    'Upper Mustang',
    'Kathmandu Valley',
    'Chitwan & Pokhara',
    'Custom Bespoke Journey'
);
$dest_options = array_values( array_unique( array_merge( $dest_options, $default_dest_options ) ) );

$display_reviews = array();

// Strictly query approved/published reviews for THIS specific trek destination only
if ( isset( $db_reviews_query ) && $db_reviews_query->have_posts() ) {
    while ( $db_reviews_query->have_posts() ) {
        $db_reviews_query->the_post();
        $rid = get_the_ID();
        $city = get_post_meta( $rid, '_trek_city', true );
        $country = get_post_meta( $rid, '_trek_country', true );
        $loc = array_filter( array( $city, $country ) );
        $rev_type  = get_post_meta( $rid, '_trek_traveler_type', true ) ?: 'Solo Traveler';
        $rev_title = get_post_meta( $rid, '_trek_review_title', true ) ?: 'An Unforgettable Experience!';
        $rev_name  = get_post_meta( $rid, '_trek_reviewer_name', true ) ?: get_the_title();
        $rev_trip  = get_post_meta( $rid, '_trek_trip_name', true ) ?: $title;
        $rev_date  = get_post_meta( $rid, '_trek_tour_date', true ) ?: get_the_date( 'M j, Y', $rid );
        $rating_val = floatval( get_post_meta( $rid, '_trek_rating', true ) ?: 5.0 );
        $display_reviews[] = array(
            'title'       => $rev_title,
            'name'        => $rev_name,
            'location'    => ! empty( $loc ) ? implode( ', ', $loc ) : ( get_post_meta( $rid, '_trek_city', true ) ?: 'Verified Traveller' ),
            'rating'      => $rating_val,
            'quote'       => get_the_content(),
            'type'        => $rev_type,
            'trip'        => $rev_trip,
            'date'        => $rev_date,
        );
    }
    wp_reset_postdata();
}
// STRICT DATA INTEGRITY: ZERO fallback reviews. If no approved reviews exist, $display_reviews remains empty.

// 10. Fixed Departure & Contact
$dep_kicker          = get_post_meta( $post_id, '_trek_dep_kicker', true ) ?: 'Travel Together';
$dep_script          = get_post_meta( $post_id, '_trek_dep_script', true ) ?: 'Choose Your Month';
$dep_heading         = get_post_meta( $post_id, '_trek_dep_heading', true ) ?: 'Join a Fixed Departure';
$dep_subtext         = get_post_meta( $post_id, '_trek_dep_subtext', true ) ?: 'No fixed dates have been hard-coded into this page. Submit your preferred month and the team will confirm the current departure schedule and available places.';

$box_script          = get_post_meta( $post_id, '_trek_dep_box_script', true ) ?: ( $script_title . ' Awaits' );
$box_title           = get_post_meta( $post_id, '_trek_dep_box_title', true ) ?: 'Ask about upcoming group dates';
$box_desc            = get_post_meta( $post_id, '_trek_dep_box_desc', true ) ?: 'Choose a preferred month, share your group size and send the enquiry directly to Yeti Adventure through WhatsApp.';
$box_photo           = get_post_meta( $post_id, '_trek_dep_box_photo', true ) ?: 'https://yetiadventure.com/wp-content/uploads/2022/02/NAMCHE-BAZAAR-1024x576.jpg';
$box_note            = get_post_meta( $post_id, '_trek_dep_box_note', true ) ?: 'Departure availability, itinerary details and prices should be confirmed directly with Yeti Adventure before booking flights or making non-refundable arrangements.';

$whatsapp_number     = get_post_meta( $post_id, '_trek_whatsapp_number', true ) ?: '9779803197006';
$sales_email         = get_post_meta( $post_id, '_trek_sales_email', true ) ?: 'sales@yetiadventure.com';

// Fixed departure dates repeater
$departure_dates_raw = get_post_meta( $post_id, '_trek_departure_dates', true );
$departure_dates     = ( ! empty( $departure_dates_raw ) && is_array( $departure_dates_raw ) ) ? $departure_dates_raw : array();

// 11. Related Treks Query
$related_query = new WP_Query( array(
    'post_type'      => 'trek_destination',
    'posts_per_page' => 4,
    'post__not_in'   => array( $post_id ),
    'post_status'    => 'publish'
) );
?>

<section class="ya-ebc-page" id="ya-ebc-page">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;0,6..72,700;1,6..72,400;1,6..72,600&family=Open+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap');

    html:has(.ya-ebc-page),
    body:has(.ya-ebc-page){
      margin:0!important;
      padding:0!important;
      overflow-x:clip!important;
      overflow-y:visible!important;
    }

    body:has(.ya-ebc-page) #page,
    body:has(.ya-ebc-page) .site,
    body:has(.ya-ebc-page) .site-content,
    body:has(.ya-ebc-page) #content,
    body:has(.ya-ebc-page) #primary,
    body:has(.ya-ebc-page) #main,
    body:has(.ya-ebc-page) .site-main,
    body:has(.ya-ebc-page) article,
    body:has(.ya-ebc-page) .inside-article,
    body:has(.ya-ebc-page) .entry-content{
      margin-top:0!important;
      margin-bottom:0!important;
      padding-top:0!important;
      padding-bottom:0!important;
      border-top:0!important;
      overflow:visible!important;
    }

    body:has(.ya-ebc-page) h1.entry-title,
    body:has(.ya-ebc-page) .entry-header,
    body:has(.ya-ebc-page) .page-header,
    body:has(.ya-ebc-page) .elementor-page-title,
    body:has(.ya-ebc-page) .page-title{
      display:none!important;
      height:0!important;
      margin:0!important;
      padding:0!important;
      overflow:hidden!important;
    }

    body:has(.ya-ebc-page) .elementor-location-header,
    body:has(.ya-ebc-page) #masthead,
    body:has(.ya-ebc-page) header.site-header{
      position:absolute!important;
      top:0!important;
      right:0!important;
      left:0!important;
      width:100%!important;
      margin:0!important;
      border:0!important;
      z-index:9999!important;
      background:transparent!important;
    }

    body:has(.ya-ebc-page) .elementor-section:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-container:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-column:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-wrap:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-html:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-html:has(.ya-ebc-page)>.elementor-widget-container,
    body:has(.ya-ebc-page) .e-con:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .e-con-inner:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-element:has(.ya-ebc-page){
      width:100%!important;
      max-width:none!important;
      margin:0!important;
      padding-top:0!important;
      padding-bottom:0!important;
      overflow:visible!important;
    }

    .ya-ebc-page,
    .ya-ebc-page *{box-sizing:border-box}

    .ya-ebc-page{
      --green:#1a231b;
      --green-2:#617555;
      --green-3:#4d6044;
      --gold:#cfab55;
      --gold-hover:#bc9944;
      --ivory:#f7f5ee;
      --cream:#fbfaf7;
      --ink:#1e261e;
      --muted:#667265;
      --line:#dfe4dc;
      --shadow:0 8px 30px rgba(26,35,27,.06);
      width:100vw;
      max-width:100vw;
      margin:-1px 0 0 calc(50% - 50vw)!important;
      padding:0!important;
      overflow:visible;
      background:var(--cream);
      color:var(--ink);
      font-family:"Open Sans","Inter",Arial,sans-serif;
      line-height:1.65;
      position:relative;
      z-index:1;
    }

    .ya-ebc-page button,
    .ya-ebc-page input,
    .ya-ebc-page select,
    .ya-ebc-page textarea{font:inherit}

    .ya-ebc-page a{color:inherit;text-decoration:none}
    .ya-ebc-page img{display:block;max-width:100%}

    .ya-ebc-page .ya-inner{
      width:min(1340px,calc(100% - 48px));
      margin:0 auto;
      position:relative;
    }

    /* Headings font cap: max font size 42px only */
    .ya-ebc-page h1,
    .ya-ebc-page h2{
      font-family:"Newsreader",Georgia,serif;
      font-size:clamp(28px,3.8vw,42px)!important;
      line-height:1.15;
      font-weight:600;
      color:var(--ink);
    }

    .ya-ebc-page h3{
      font-family:"Newsreader",Georgia,serif;
      font-size:clamp(20px,2.5vw,28px);
      line-height:1.25;
      font-weight:600;
      color:var(--ink);
    }

    .ya-ebc-page .ya-script{
      font-family:"Newsreader",Georgia,serif;
      font-style:italic;
      font-weight:500;
      letter-spacing:0.02em;
    }

    .ya-ebc-page .ya-kicker{
      display:inline-flex;
      align-items:center;
      gap:8px;
      color:var(--gold);
      font-size:11px;
      font-weight:800;
      letter-spacing:2px;
      text-transform:uppercase;
      font-family:"Inter","Open Sans",sans-serif;
    }

    .ya-ebc-page .ya-kicker:before{
      content:"";
      width:24px;
      height:1.5px;
      background:currentColor;
    }

    /* Buttons */
    .ya-ebc-page .ya-btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      min-height:48px;
      padding:0 24px;
      border:1px solid transparent;
      border-radius:6px;
      text-decoration:none;
      font-size:11.5px;
      font-weight:700;
      letter-spacing:1px;
      text-transform:uppercase;
      cursor:pointer;
      font-family:"Inter","Open Sans",sans-serif;
      transition:all .22s ease;
    }

    .ya-ebc-page .ya-btn svg{width:16px;height:16px;flex:0 0 auto}
    .ya-ebc-page .ya-btn:hover{transform:translateY(-2px)}
    .ya-ebc-page .ya-btn-primary{background:var(--green-2);border-color:var(--green-2);color:#fff}
    .ya-ebc-page .ya-btn-primary:hover{background:var(--gold);border-color:var(--gold);color:var(--green);box-shadow:0 8px 20px rgba(207,171,85,.25)}
    .ya-ebc-page .ya-btn-light{background:#fff;border-color:#fff;color:var(--green-2)}
    .ya-ebc-page .ya-btn-light:hover{background:var(--gold);border-color:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-btn-outline{border-color:rgba(255,255,255,.85);background:transparent;color:#fff}
    .ya-ebc-page .ya-btn-outline:hover{background:#fff;border-color:#fff;color:var(--green)}
    .ya-ebc-page .ya-btn-secondary{border-color:var(--green-2);background:#fff;color:var(--green-2)}
    .ya-ebc-page .ya-btn-secondary:hover{background:var(--green-2);color:#fff}
    .ya-ebc-page .ya-btn-gold{background:var(--gold);border-color:var(--gold);color:var(--green);font-weight:700}
    .ya-ebc-page .ya-btn-gold:hover{background:var(--gold-hover);border-color:var(--gold-hover)}
    .ya-ebc-page .ya-btn-sm{padding:6px 14px;font-size:11px;font-weight:600;border:1px solid var(--line);background:#fff;border-radius:4px;cursor:pointer;color:var(--ink);transition:all .2s ease}
    .ya-ebc-page .ya-btn-sm:hover{background:var(--cream);border-color:#b5b5aa}

    .ya-ebc-page .ya-reveal{
      opacity:0;
      transform:translateY(20px);
      transition:opacity .65s ease,transform .65s ease;
    }
    .ya-ebc-page .ya-reveal.is-visible{opacity:1;transform:none}

    /* 1. HERO SECTION */
    .ya-ebc-page .ya-hero{
      min-height:92vh;
      min-height:92svh;
      display:flex;
      align-items:center;
      justify-content:center;
      position:relative;
      isolation:isolate;
      padding:120px 0 60px;
      overflow:hidden;
      background:#202821;
    }

    .ya-ebc-page .ya-hero-background,
    .ya-ebc-page .ya-hero-background::after,
    .ya-ebc-page .ya-hero-background-slide{
      position:absolute;
      inset:0;
    }
    .ya-ebc-page .ya-hero-background{z-index:-2;background:#1e261e}
    .ya-ebc-page .ya-hero-background::after{
      z-index:1;
      content:"";
      background:linear-gradient(180deg,rgba(15,22,17,.6) 0%,rgba(15,22,17,.4) 50%,rgba(15,22,17,.72) 100%);
    }
    .ya-ebc-page .ya-hero-background-slide{
      z-index:0;
      opacity:0;
      background:center/cover no-repeat;
      transform:scale(1.04);
      transition:opacity 950ms ease,transform 1200ms ease;
    }
    .ya-ebc-page .ya-hero-background-slide.is-active{opacity:1;transform:scale(1.01)}
    .ya-ebc-page .ya-hero-background-slide.is-exiting{opacity:0;transform:scale(1.01)}

    .ya-ebc-page .ya-hero-slider-dots{
      position:absolute;
      z-index:3;
      bottom:24px;
      left:50%;
      display:flex;
      align-items:center;
      gap:8px;
      transform:translateX(-50%);
    }
    .ya-ebc-page .ya-hero-slider-dot{
      width:8px;
      height:8px;
      padding:0;
      border:1px solid rgba(255,255,255,.8);
      border-radius:999px;
      background:rgba(255,255,255,.25);
      cursor:pointer;
      transition:all .3s ease;
    }
    .ya-ebc-page .ya-hero-slider-dot.is-active{width:26px;background:var(--gold);border-color:var(--gold)}

    .ya-ebc-page .ya-hero-content{
      width:min(880px,100%);
      color:#fff;
      text-align:center;
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:0;
      margin:0 auto;
    }

    /* Hero text: smaller script title, reduced gap, text closer to buttons */
    .ya-ebc-page .ya-hero .ya-script{
      font-size:clamp(18px,2.2vw,24px);
      line-height:1.2;
      color:var(--gold);
      margin-bottom:4px;
      text-shadow:0 2px 8px rgba(0,0,0,.45);
    }

    .ya-ebc-page .ya-hero h1{
      font-size:clamp(30px,4.2vw,42px)!important;
      line-height:1.1;
      font-weight:600;
      color:#fff;
      margin:0 0 8px;
      text-shadow:0 3px 14px rgba(0,0,0,.45);
      max-width:920px;
    }

    .ya-ebc-page .ya-hero-subtitle{
      max-width:660px;
      margin:4px auto 14px;
      font-size:15px;
      line-height:1.65;
      color:rgba(255,255,255,.92);
    }

    .ya-ebc-page .ya-hero-actions{
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:12px;
      margin-top:4px;
    }

    /* ─── 2. QUICK FACTS STRIP — premium editorial luxury travel ─── */

    /* Import Cormorant Garamond for values */
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&display=swap');

    .yt-qf{
      position:relative;
      z-index:10;
      width:100%;
      background:#F5F2EA;
      border-top:1px solid rgba(140,115,75,0.18);
      border-bottom:1px solid rgba(140,115,75,0.18);
    }
    .yt-qf__inner{
      display:flex;
      align-items:stretch;
      width:min(calc(100% - 48px),1320px);
      margin:0 auto;
    }
    .yt-qf__item{
      flex:1 1 0;
      display:flex;
      flex-direction:column;
      align-items:center;
      text-align:center;
      padding:32px 20px 28px;
      min-width:0;
      position:relative;
      transition:transform .2s ease;
    }
    .yt-qf__item:not(:last-child)::after{
      content:'';
      position:absolute;
      right:0;
      top:20%;
      height:60%;
      width:1px;
      background:rgba(140,115,75,0.22);
    }
    .yt-qf__item:hover .yt-qf__icon{
      transform:translateY(-2px);
      color:#173F38;
      opacity:.9;
    }
    .yt-qf__icon{
      width:28px;
      height:28px;
      color:#3d6b5a;
      margin-bottom:10px;
      flex-shrink:0;
      transition:transform .2s ease, color .2s ease, opacity .2s ease;
    }
    .yt-qf__icon svg{
      width:100%;
      height:100%;
      display:block;
    }
    .yt-qf__label{
      display:block;
      font-family:Inter,'DM Sans',Arial,sans-serif;
      font-size:9.5px;
      font-weight:600;
      letter-spacing:.12em;
      text-transform:uppercase;
      color:#7a8a78;
      margin-bottom:7px;
      line-height:1.2;
      white-space:nowrap;
    }
    .yt-qf__value{
      display:block;
      font-family:'Cormorant Garamond','Libre Baskerville','Newsreader',Georgia,serif;
      font-size:clamp(16px,1.45vw,20px);
      font-weight:600;
      color:#173F38;
      line-height:1.25;
      letter-spacing:.01em;
    }
    .yt-qf__sub{
      display:block;
      font-family:Inter,'DM Sans',Arial,sans-serif;
      font-size:11px;
      color:#8a9487;
      margin-top:3px;
      line-height:1.3;
    }

    /* Tablet: 3-column × 2-row */
    @media(max-width:900px){
      .yt-qf__inner{flex-wrap:wrap;}
      .yt-qf__item{
        flex:1 1 33.333%;
        padding:26px 16px 22px;
        border-bottom:1px solid rgba(140,115,75,0.15);
      }
      .yt-qf__item:not(:last-child)::after{
        top:15%; height:70%;
      }
      /* remove right divider on every 3rd item */
      .yt-qf__item:nth-child(3n)::after{display:none;}
      /* bottom row: no bottom border */
      .yt-qf__item:nth-last-child(-n+3){border-bottom:none;}
    }

    /* Mobile: 2-column × 3-row */
    @media(max-width:560px){
      .yt-qf__inner{width:100%;}
      .yt-qf__item{
        flex:1 1 50%;
        padding:22px 14px 18px;
        border-bottom:1px solid rgba(140,115,75,0.15);
      }
      .yt-qf__item:nth-child(3n)::after{display:block;}
      .yt-qf__item:nth-child(2n)::after{display:none;}
      .yt-qf__item:nth-last-child(-n+3){border-bottom:1px solid rgba(140,115,75,0.15);}
      .yt-qf__item:nth-last-child(-n+2){border-bottom:none;}
      .yt-qf__value{font-size:15px;}
    }

    /* Legacy .ya-facts/.ya-fact aliases — kept so nothing breaks */
    .ya-ebc-page .ya-facts{display:none!important;}

    /* 3. STICKY NAVIGATION & MAIN LAYOUT */
    .ya-tab-mman-section{
      width:100%;
      margin:0 auto;
    }

    @media(min-width:1101px){
      .ya-ebc-page .ya-tab-mman-section{
        display:grid;
        margin-top:10px;
        grid-template-columns:250px minmax(0,1fr);
        column-gap:20px;
        align-items:flex-start;
        width:min(calc(100% - 48px),1340px);
        margin:0 auto;
      }
      .ya-ebc-page .ya-tabs-wrap{
        position:sticky;
        top:140px;
        z-index:70;
        grid-column:1;
        width:250px;
        background:#ffffff;
        padding:18px 14px;
        margin:12px 0 ;
        box-sizing:border-box;
      }
      .ya-ebc-page .ya-nav-title{
        font-size:11px;
        font-weight:800;
        letter-spacing:0.12em;
        text-transform:uppercase;
        color:#8a9488;
        padding:4px 10px 12px;
        border-bottom:1px solid var(--line);
        margin-bottom:10px;
      }
      .ya-ebc-page .ya-tabs{
        width:100%;
        display:flex;
        flex-direction:column;
        gap:4px;
      }
      .ya-ebc-page .ya-tab{
        width:100%;
        display:flex;
        align-items:center;
        justify-content:flex-start;
        padding:10px 14px;
        border:0;
        border-radius:8px;
        background:transparent;
        color:#525f51;
        font-size:14px;
        font-weight:600;
        text-align:left;
        cursor:pointer;
        transition:all .2s ease;
      }
      .ya-ebc-page .ya-tab:hover{
        background:#f5f8f3;
        color:var(--green-2);
      }
      .ya-ebc-page .ya-tab.is-active{
        background:#f2f6ef;
        color:var(--green-2);
        font-weight:700;
        border-left:3px solid var(--green-2);
      }
      .ya-ebc-page .ya-heli-card{
        margin-top:16px;
        padding:16px 14px;
        background:#1a231b;
        border-radius:10px;
        color:#ffffff;
        box-shadow:0 6px 20px rgba(0,0,0,.1);
      }
      .ya-ebc-page .ya-heli-tag{
        display:flex;
        align-items:center;
        gap:6px;
        font-size:10px;
        font-weight:800;
        letter-spacing:0.08em;
        color:var(--gold);
        margin-bottom:8px;
        text-transform:uppercase;
      }
      .ya-ebc-page .ya-heli-tag svg{width:14px;height:14px;fill:currentColor}
      .ya-ebc-page .ya-heli-card p{
        margin:0 0 12px;
        font-size:12px;
        line-height:1.55;
        color:#d1ded0;
      }
      .ya-ebc-page .ya-heli-btn{
        width:100%;
        background:var(--gold);
        color:var(--green);
        font-size:12px;
        font-weight:700;
        border-radius:6px;
        border:0;
        padding:9px 12px;
        text-align:center;
        cursor:pointer;
        transition:all .2s ease;
      }
      .ya-ebc-page .ya-heli-btn:hover{background:var(--gold-hover)}

      /* Right Side Stage: Proper padding, spacing and max font size 42px */
      .ya-ebc-page .ya-tab-stage{
        grid-column:2;
        min-width:0;
      }
      .ya-ebc-page .ya-tab-stage .ya-panel{
        padding:44px 0 60px;
      }
    }

    .ya-ebc-page .ya-section-heading{
      margin-bottom:34px;
    }
    .ya-ebc-page .ya-section-heading h2{
      margin:8px 0 0;
      font-size:clamp(28px,3.8vw,42px)!important;
    }
    .ya-ebc-page .ya-section-heading p{
      margin:12px 0 0;
      color:var(--muted);
      font-size:15px;
      line-height:1.75;
      max-width:720px;
    }

    /* 4. OVERVIEW SECTION: 2 Columns on Desktop */
    .ya-ebc-page .ya-overview-grid{
      display:grid;
      grid-template-columns:1fr 360px;
      gap:44px;
      align-items:start;
    }
    .ya-ebc-page .ya-overview-copy h3{
      margin:0 0 16px;
      font-size:clamp(22px,2.6vw,28px);
      line-height:1.2;
      color:var(--ink);
    }
    .ya-ebc-page .ya-overview-text{
      font-size:15px;
      line-height:1.85;
      color:#485247;
    }
    .ya-ebc-page .ya-overview-text p{margin:0 0 16px}

    /* Vertical highlights list with green checkmarks */
    .ya-ebc-page .ya-inclusions-checklist{
      margin:28px 0 0;
      display:flex;
      flex-direction:column;
      gap:14px;
    }
    .ya-ebc-page .ya-check-item{
      display:flex;
      align-items:flex-start;
      gap:12px;
    }
    .ya-ebc-page .ya-check-icon{
      flex-shrink:0;
      width:22px;
      height:22px;
      border-radius:50%;
      background:rgba(97,117,85,.14);
      color:var(--green-2);
      display:grid;
      place-items:center;
      margin-top:2px;
    }
    .ya-ebc-page .ya-check-icon svg{width:13px;height:13px;stroke:var(--green-2)}
    .ya-ebc-page .ya-check-content strong{
      display:block;
      font-size:14.5px;
      font-weight:700;
      color:var(--ink);
      line-height:1.35;
    }
    .ya-ebc-page .ya-check-desc{
      font-size:13px;
      color:var(--muted);
      margin-top:2px;
    }

    /* Right Column: Landscape Photo + Trip Facts Card */
    .ya-ebc-page .ya-overview-side{
      display:flex;
      flex-direction:column;
      gap:20px;
    }
    .ya-ebc-page .ya-side-scenic-photo{
      border-radius:10px;
      overflow:hidden;
      box-shadow:0 6px 20px rgba(27,35,27,.08);
      height:210px;
      background:#eee;
    }
    .ya-ebc-page .ya-side-scenic-photo img{
      width:100%;
      height:100%;
      object-fit:cover;
    }

    .ya-ebc-page .ya-trip-facts-card{
      background:#ffffff;
      border:1px solid var(--line);
      border-radius:10px;
      padding:22px;
      box-shadow:0 4px 18px rgba(27,35,27,.03);
    }
    .ya-ebc-page .ya-trip-facts-card h4{
      margin:0 0 14px;
      font-size:11.5px;
      font-weight:800;
      letter-spacing:1.5px;
      color:var(--ink);
      border-bottom:1px solid var(--line);
      padding-bottom:8px;
      font-family:"Inter","Open Sans",sans-serif;
    }
    .ya-ebc-page .ya-trip-facts-list{
      list-style:none;
      margin:0;
      padding:0;
      display:flex;
      flex-direction:column;
      gap:11px;
    }
    .ya-ebc-page .ya-trip-facts-list li{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      font-size:13px;
      border-bottom:1px dashed #e9ede7;
      padding-bottom:9px;
    }
    .ya-ebc-page .ya-trip-facts-list li:last-child{border-bottom:0;padding-bottom:0}
    .ya-ebc-page .ya-tf-label{display:flex;align-items:center;gap:7px;color:#707c6f;font-size:12px}
    .ya-ebc-page .ya-tf-label svg{width:15px;height:15px;color:var(--gold)}
    .ya-ebc-page .ya-tf-val{font-size:13px;color:var(--ink);font-weight:600;text-align:right}

    /* Highlight Quote Banner */
    .ya-ebc-page .ya-quote-banner{
      margin:40px 0 34px;
      padding:24px 30px;
      background:#fdfaf3;
      border-left:4px solid var(--gold);
      border-radius:6px;
    }
    .ya-ebc-page .ya-quote-banner blockquote{
      margin:0;
      font-family:"Newsreader",Georgia,serif;
      font-size:clamp(17px,2vw,21px);
      line-height:1.55;
      color:#252f24;
      font-style:italic;
    }

    /* Roadmap Section */
    .ya-ebc-page .ya-roadmap-section{
      margin-top:36px;
      background:#fff;
      border:1px solid var(--line);
      border-radius:10px;
      padding:24px;
      box-shadow:0 6px 20px rgba(27,35,27,.03);
    }
    .ya-ebc-page .ya-roadmap-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px}
    .ya-ebc-page .ya-roadmap-header h3{margin:4px 0 0;font:600 22px/1.2 "Newsreader",Georgia,serif;color:var(--ink)}
    .ya-ebc-page .ya-roadmap-frame{position:relative;border-radius:8px;overflow:hidden;background:#eee;max-height:500px}
    .ya-ebc-page .ya-roadmap-frame img{width:100%;height:auto;display:block;object-fit:cover}
    .ya-ebc-page .ya-roadmap-caption{display:flex;align-items:center;gap:8px;margin-top:12px;font-size:12.5px;color:var(--muted)}
    .ya-ebc-page .ya-roadmap-caption svg{width:16px;height:16px;color:var(--gold);flex-shrink:0}

    /* 5. ITINERARY SECTION: Luxury Editorial 3-Column Cards */
    .ya-ebc-page .yt-itin-header {
      margin-bottom: 44px;
      padding-bottom: 28px;
      border-bottom: 1px solid var(--line);
    }
    .ya-ebc-page .yt-itin-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      color: var(--gold);
      font-family: Inter, Arial, sans-serif;
    }
    .ya-ebc-page .yt-itin-dash {
      width: 24px;
      height: 2px;
      background: var(--gold);
      border-radius: 1px;
    }
    .ya-ebc-page .yt-itin-h2 {
      margin: 0 0 10px;
      font: 600 clamp(28px, 3.4vw, 40px) / 1.15 "Newsreader", Georgia, serif;
      color: var(--green);
    }
    .ya-ebc-page .yt-itin-desc {
      margin: 0;
      font-size: 15.5px;
      line-height: 1.75;
      color: var(--muted);
      max-width: 700px;
    }
    .ya-ebc-page .yt-days-list {
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    /* Itinerary Day Editorial Card */
    .ya-ebc-page .yt-day-card {
     
      padding: 28px 28px 28px 24px;
      display: grid;
      grid-template-columns: 140px minmax(0, 1fr) 340px;
      gap: 0;
      align-items: stretch;
      overflow: hidden;
      transition: box-shadow .3s ease, border-color .3s ease;
      position: relative;
    }
    .ya-ebc-page .yt-day-card:hover {
      box-shadow: 0 10px 40px rgba(23, 63, 56, 0.10);
      border-color: #d4cbbc;
    }
    /* Subtle left accent line per card */
    .ya-ebc-page .yt-day-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 20px;
      bottom: 20px;
      width: 3px;
      border-radius: 0 2px 2px 0;
      opacity: 0.6;
    }

    /* Left Column: Day Index & Location/Altitude */
    .ya-ebc-page .yt-day-left {
      border-right: 1px solid #ece8df;
      padding: 4px 24px 4px 4px;
      display: flex;
      flex-direction: column;
      gap: 0;
    }
    .ya-ebc-page .yt-day-num-tag {
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--gold);
      font-family: Inter, Arial, sans-serif;
      margin-bottom: 8px;
    }
    .ya-ebc-page .yt-day-gold-dash {
      width: 22px;
      height: 2px;
      background: var(--gold);
      border-radius: 1px;
      margin-bottom: 18px;
    }
    .ya-ebc-page .yt-day-stat {
      display: flex;
      align-items: flex-start;
      gap: 7px;
      margin-bottom: 10px;
    }
    .ya-ebc-page .yt-day-stat-icon {
      width: 15px;
      height: 15px;
      color: var(--green);
      flex-shrink: 0;
      margin-top: 1px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .ya-ebc-page .yt-day-stat-icon svg {
      width: 14px;
      height: 14px;
    }
    .ya-ebc-page .yt-day-stat-val {
      font-size: 13px;
      font-weight: 600;
      color: #24352e;
      line-height: 1.35;
    }

    /* Center Column: Title, Narrative, Pills */
    .ya-ebc-page .yt-day-center {
      display: flex;
      flex-direction: column;
      min-width: 0;
      padding: 4px 28px 4px 28px;
    }
    .ya-ebc-page .yt-day-title {
      margin: 0 0 11px;
      font: 600 21px / 1.28 "Newsreader", Georgia, serif;
      color: var(--green);
    }
    .ya-ebc-page .yt-day-body-text {
      font-size: 14px;
      line-height: 1.78;
      color: #505c52;
      flex: 1;
      margin-bottom: 16px;
    }
    .ya-ebc-page .yt-day-body-text p {
      margin: 0 0 9px;
    }
    .ya-ebc-page .yt-day-body-text p:last-child {
      margin-bottom: 0;
    }
    .ya-ebc-page .yt-day-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 7px;
      margin-top: auto;
      padding-top: 14px;
      border-top: 1px solid #f0ece3;
    }
    .ya-ebc-page .yt-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #f2f6ef;
      border-radius: 999px;
      padding: 5px 12px 5px 9px;
      font-size: 11.5px;
      font-weight: 600;
      color: var(--green);
      border: 1px solid #dde8de;
      letter-spacing: 0.01em;
    }
    .ya-ebc-page .yt-pill:hover {
      background: #e8f0e8;
    }
    .ya-ebc-page .yt-pill-icon {
      width: 13px;
      height: 13px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      opacity: 0.8;
    }
    .ya-ebc-page .yt-pill-icon svg {
      width: 12px;
      height: 12px;
    }

    /* Right Column: Image Gallery Carousel — fixed 16:10 aspect */
    .ya-ebc-page .yt-day-gallery {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      aspect-ratio: 16 / 10;
      background: #eceae3;
      align-self: center;
      flex-shrink: 0;
    }
    /* No-image placeholder */
    .ya-ebc-page .yt-day-no-image {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      background: #f5f2ea;
      color: #a0a898;
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.05em;
      text-align: center;
      padding: 20px;
      min-height: 190px;
    }
    .ya-ebc-page .yt-day-no-image svg {
      width: 36px;
      height: 36px;
      opacity: 0.35;
    }
    /* Carousel internals */
    .ya-ebc-page .yt-day-carousel-viewport {
      width: 100%;
      height: 100%;
      position: relative;
      overflow: hidden;
    }
    .ya-ebc-page .yt-day-carousel-track {
      width: 100%;
      height: 100%;
      position: relative;
    }
    .ya-ebc-page .yt-day-carousel-slide {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity .4s ease;
      pointer-events: none;
    }
    .ya-ebc-page .yt-day-carousel-slide.is-active {
      opacity: 1;
      pointer-events: auto;
    }
    .ya-ebc-page .yt-day-carousel-slide img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    /* Bottom gradient scrim on gallery for high contrast of text and dots */
    .ya-ebc-page .yt-day-gallery::after {
      content: "";
      position: absolute;
      inset: 0;
      pointer-events: none;
      background: linear-gradient(to top, rgba(10, 25, 22, 0.58) 0%, rgba(10, 25, 22, 0.18) 26%, rgba(0, 0, 0, 0) 52%);
      z-index: 3;
    }

    /* Counter badge (top-right pill like reference e.g. "1 / 4") */
    .ya-ebc-page .yt-gallery-counter-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      z-index: 5;
      background: rgba(14, 40, 34, 0.85);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      border-radius: 999px;
      padding: 4px 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.22);
      pointer-events: none;
      display: inline-flex;
      align-items: center;
    }
    .ya-ebc-page .yt-gallery-counter {
      color: #ffffff;
      font-size: 11px;
      font-weight: 700;
      font-family: Inter, Arial, sans-serif;
      letter-spacing: 0.5px;
      line-height: 1;
    }

    /* Side navigation arrow buttons (left & right side, centered vertically) */
    .ya-ebc-page .yt-gallery-side-btn,
    .ya-ebc-page .yt-gallery-nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      z-index: 6;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid rgba(255, 255, 255, 0.65);
      background: rgba(255, 255, 255, 0.90);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      color: #17362e;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      padding: 0;
      transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ya-ebc-page .yt-gallery-prev {
      left: 12px;
    }
    .ya-ebc-page .yt-gallery-next {
      right: 12px;
    }
    .ya-ebc-page .yt-gallery-side-btn:hover,
    .ya-ebc-page .yt-gallery-nav-btn:hover {
      background: #ffffff;
      color: #0b1f1a;
      transform: translateY(-50%) scale(1.08);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.26);
      border-color: #ffffff;
    }
    .ya-ebc-page .yt-gallery-side-btn:active,
    .ya-ebc-page .yt-gallery-nav-btn:active {
      transform: translateY(-50%) scale(0.94);
    }
    .ya-ebc-page .yt-gallery-side-btn svg,
    .ya-ebc-page .yt-gallery-nav-btn svg {
      display: block;
      width: 16px;
      height: 16px;
      stroke: currentColor;
    }

    /* Bottom left location tag (e.g. 📍 Kathmandu) */
    .ya-ebc-page .yt-gallery-location-tag {
      position: absolute;
      bottom: 12px;
      left: 14px;
      z-index: 5;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: #ffffff;
      font-size: 11.5px;
      font-weight: 600;
      font-family: Inter, Arial, sans-serif;
      text-shadow: 0 1px 4px rgba(0,0,0,0.85);
      pointer-events: none;
    }
    .ya-ebc-page .yt-gallery-location-tag svg {
      width: 13px;
      height: 13px;
      filter: drop-shadow(0 1px 3px rgba(0,0,0,0.8));
    }

    /* Bottom center pagination dots */
    .ya-ebc-page .yt-gallery-dots {
      position: absolute;
      bottom: 14px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 5;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .ya-ebc-page .yt-gallery-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.65);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4);
      transition: all 0.28s ease;
      display: inline-block;
      cursor: pointer;
    }
    .ya-ebc-page .yt-gallery-dot.is-active {
      width: 22px;
      border-radius: 999px;
      background: #cba148; /* warm gold matching reference */
      box-shadow: 0 1px 4px rgba(0,0,0,0.5);
    }

    /* Roadmap embed styling */
    .ya-ebc-page .ya-map-embed-wrapper iframe {
      width: 100%!important;
      min-height: 420px;
      border: 0;
      display: block;
    }

    /* 6. ESSENTIAL INFORMATION: Clean Collapsible Panels with Icons */
    .ya-ebc-page .ya-essential-layout{
      display:grid;
      grid-template-columns:1fr 340px;
      gap:40px;
      align-items:start;
    }
    .ya-ebc-page .ya-info-list{
      display:flex;
      flex-direction:column;
      gap:12px;
    }
    .ya-ebc-page .ya-info-item{
      border:1px solid var(--line);
      border-radius:8px;
      background:#ffffff;
      overflow:hidden;
      transition:border-color .2s ease;
    }
    .ya-ebc-page .ya-info-item:hover{border-color:var(--green-2)}
    .ya-ebc-page .ya-info-head{
      padding:16px 20px;
      background:#ffffff;
      border:0;
      width:100%;
      display:flex;
      justify-content:space-between;
      align-items:center;
      cursor:pointer;
      text-align:left;
    }
    .ya-ebc-page .ya-info-head span{
      font-size:16px;
      font-weight:600;
      color:var(--ink);
      display:flex;
      align-items:center;
      gap:12px;
      font-family:"Newsreader",Georgia,serif;
    }
    .ya-ebc-page .ya-info-icon{
      width:26px;
      height:26px;
      display:grid;
      place-items:center;
      color:var(--green-2);
      flex-shrink:0;
    }
    .ya-ebc-page .ya-info-icon svg{width:20px;height:20px}
    .ya-ebc-page .ya-info-arrow{
      width:18px;
      height:18px;
      color:var(--muted);
      transition:transform .3s ease;
      flex-shrink:0;
    }
    .ya-ebc-page .ya-info-item.is-open .ya-info-arrow{transform:rotate(180deg);color:var(--green-2)}
    .ya-ebc-page .ya-info-body{
      display:grid;
      grid-template-rows:0fr;
      transition:grid-template-rows .35s ease;
    }
    .ya-ebc-page .ya-info-item.is-open .ya-info-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-info-body>div{overflow:hidden}
    .ya-ebc-page .ya-info-content{
      padding:0 20px 18px 58px;
      color:#505c4f;
      font-size:14px;
      line-height:1.8;
    }
    .ya-ebc-page .ya-info-content p{margin:0 0 10px}
    .ya-ebc-page .ya-info-content ul{margin:0;padding-left:18px}
    .ya-ebc-page .ya-info-content li{margin:5px 0}

    .ya-ebc-page .ya-trip-note-card{
      border-radius:10px;
      background:var(--green);
      color:#fff;
      overflow:hidden;
      box-shadow:0 8px 24px rgba(0,0,0,.1);
    }
    .ya-ebc-page .ya-trip-note-image{height:200px;background:center/cover no-repeat}
    .ya-ebc-page .ya-trip-note-copy{padding:24px}
    .ya-ebc-page .ya-trip-note-copy small{color:var(--gold);font-size:10px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}
    .ya-ebc-page .ya-trip-note-copy h3{margin:8px 0 10px;font-size:24px;color:#fff}
    .ya-ebc-page .ya-trip-note-copy p{margin:0 0 18px;color:#cdd8cb;font-size:13px;line-height:1.7}

    /* 7. TRAVELER REVIEWS: Bespoke Editorial Stories & Hierarchy */
    .ya-ebc-page .ya-rev-head-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 36px;
    }
    .ya-ebc-page .ya-rev-head-row h2 {
      margin: 6px 0 10px;
      font: 600 clamp(28px, 3.2vw, 42px) / 1.15 "Newsreader", Georgia, serif;
      color: var(--green);
    }
    .ya-ebc-page .ya-rev-head-row p {
      margin: 0;
      color: var(--muted);
      font-size: 15px;
      max-width: 540px;
      line-height: 1.6;
    }
    .ya-ebc-page .ya-write-rev-btn {
      padding: 10px 22px;
      font-size: 13.5px;
      font-weight: 600;
      letter-spacing: 0.02em;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border-radius: 8px;
      background: var(--green);
      color: #ffffff;
      border: 1px solid var(--green);
      box-shadow: 0 4px 14px rgba(22, 51, 42, 0.12);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .ya-ebc-page .ya-write-rev-btn:hover {
      background: #0d231d;
      border-color: #0d231d;
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(22, 51, 42, 0.2);
    }

    /* ── Traveler Reviews Redesign ────────────────────────────────────── */
    #ya-panel-review {
      position: relative;
      background: #ffffff;
      padding: 64px 0 80px;
    }
    .ya-ebc-page .ya-rev-head-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 24px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    .ya-ebc-page .ya-rev-head-row .ya-kicker {
      display: block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #b38838;
      margin-bottom: 6px;
    }
    .ya-ebc-page .ya-rev-head-row h2 {
      margin: 0 0 8px;
      font: 600 clamp(28px, 3.2vw, 42px) / 1.15 "Newsreader", Georgia, serif;
      color: #1a2e26;
    }
    .ya-ebc-page .ya-rev-head-row p {
      margin: 0;
      color: #637069;
      font-size: 15px;
      line-height: 1.6;
      max-width: 580px;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-cta-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border-radius: 999px;
      background: #045857;
      color: #ffffff;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 0.02em;
      border: 1px solid #045857;
      box-shadow: 0 4px 14px rgba(4, 88, 87, 0.15);
      cursor: pointer;
      transition: all 0.22s ease;
      white-space: nowrap;
      text-decoration: none;
    }
    .ya-ebc-page .ya-rev-cta-btn:hover {
      background: #022f2e;
      border-color: #022f2e;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(4, 88, 87, 0.25);
    }
    .ya-ebc-page .ya-rev-cta-btn svg {
      width: 16px;
      height: 16px;
    }

    /* Review Cards Desktop/Tablet Grid */
    .ya-ebc-page .ya-review-cards-wrap {
      position: relative;
      width: 100%;
    }
    .ya-ebc-page .ya-review-cards {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 24px;
      max-width: 1360px;
      margin: 0 auto;
    }
    .ya-ebc-page .ya-review-card {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 30px;
      border: 1px solid #eae6de;
      border-radius: 18px;
      background: #ffffff;
      box-shadow: 0 2px 14px rgba(18, 38, 32, 0.035);
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
      box-sizing: border-box;
    }
    .ya-ebc-page .ya-review-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 30px rgba(18, 38, 32, 0.08);
      border-color: #dfd9ce;
    }
    .ya-ebc-page .ya-rev-card-top {
      margin-bottom: 16px;
    }
    .ya-ebc-page .ya-rev-card-top-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 8px;
    }
    .ya-ebc-page .ya-rev-card-title {
      margin: 0;
      font: 600 18px / 1.35 "Newsreader", Georgia, serif;
      color: #1a2e26;
      flex-grow: 1;
    }
    .ya-ebc-page .ya-rev-stars {
      display: inline-flex;
      align-items: center;
      gap: 2px;
      flex-shrink: 0;
      margin-top: 2px;
    }
    .ya-ebc-page .ya-star-svg {
      width: 14px;
      height: 14px;
      display: block;
    }
    .ya-ebc-page .ya-star-svg.is-full {
      fill: #c6a45f;
    }
    .ya-ebc-page .ya-star-svg.is-empty {
      fill: #e5e7eb;
    }
    .ya-ebc-page .ya-rev-type-badge {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #617555;
      background: #f4f7f2;
      border: 1px solid #e1e8dd;
      border-radius: 999px;
      padding: 2px 9px;
      line-height: 1.4;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-card-text {
      flex-grow: 1;
      margin: 0 0 20px;
      padding: 0;
      border: none;
    }
    .ya-ebc-page .ya-rev-card-text p {
      margin: 0;
      font-size: 14.5px;
      line-height: 1.68;
      color: #4a5651;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-card-bottom {
      border-top: 1px solid #f0ede6;
      padding-top: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .ya-ebc-page .ya-rev-author {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }
    .ya-ebc-page .ya-rev-avatar {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: #eef3ec;
      color: #2d4237;
      border: 1px solid #dce5da;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
      flex-shrink: 0;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-author-meta {
      min-width: 0;
    }
    .ya-ebc-page .ya-rev-author-name {
      display: block;
      font-size: 14px;
      font-weight: 700;
      color: #1a2e26;
      line-height: 1.25;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-author-loc {
      display: block;
      font-size: 12px;
      color: #728079;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-date {
      font-size: 12px;
      color: #8c9891;
      font-weight: 500;
      white-space: nowrap;
      flex-shrink: 0;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Empty State */
    .ya-ebc-page .ya-review-empty-state {
      background: #ffffff;
      border: 1px dashed #d5cebf;
      border-radius: 20px;
      padding: 56px 28px;
      text-align: center;
      max-width: 580px;
      margin: 10px auto 0;
    }
    .ya-ebc-page .ya-rev-empty-icon {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: #f4f6f2;
      color: #617555;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
    }
    .ya-ebc-page .ya-rev-empty-icon svg {
      width: 24px;
      height: 24px;
    }
    .ya-ebc-page .ya-rev-empty-title {
      margin: 0 0 8px;
      font: 600 24px / 1.25 "Newsreader", Georgia, serif;
      color: #1a2e26;
    }
    .ya-ebc-page .ya-rev-empty-sub {
      margin: 0 0 24px;
      color: #637069;
      font-size: 14.5px;
      line-height: 1.6;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-empty-cta {
      margin: 0 auto;
    }

    /* Load More Button */
    .ya-ebc-page .ya-rev-load-more-wrap {
      margin-top: 40px;
      text-align: center;
    }
    .ya-ebc-page .ya-review-card.ya-review-hidden {
      display: none !important;
    }

    /* Tablet (768px - 1024px) */
    @media (max-width: 1024px) and (min-width: 769px) {
      .ya-ebc-page .ya-review-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
      }
    }

    /* Mobile Carousel (under 768px) */
    @media (max-width: 768px) {
      .ya-ebc-page .ya-rev-head-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
      }
      .ya-ebc-page .ya-rev-cta-btn {
        width: 100%;
        justify-content: center;
      }
      .ya-ebc-page .ya-review-cards-wrap {
        overflow: hidden;
      }
      .ya-ebc-page .ya-review-cards {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        gap: 16px;
        padding: 4px 16px 20px;
        margin: 0 -16px;
        scrollbar-width: none;
      }
      .ya-ebc-page .ya-review-cards::-webkit-scrollbar {
        display: none;
      }
      .ya-ebc-page .ya-review-card {
        flex: 0 0 85vw;
        max-width: 86vw;
        scroll-snap-align: start;
        min-width: 0;
        padding: 24px 22px;
      }
      .ya-ebc-page .ya-review-card.ya-review-hidden {
        display: flex !important;
      }
      .ya-ebc-page .ya-rev-load-more-wrap {
        display: none;
      }
    }
    .ya-ebc-page .ya-modal-close:hover{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-modal-header{margin-bottom:20px}
    .ya-ebc-page .ya-modal-header h3{margin:4px 0 6px;font-size:26px}
    .ya-ebc-page .ya-modal-header p{margin:0;color:var(--muted);font-size:13.5px}

    .ya-ebc-page .ya-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
    .ya-ebc-page .ya-field{display:grid;gap:6px}
    .ya-ebc-page .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-field label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:#495648}
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select,
    .ya-ebc-page .ya-field textarea{
      width:100%;
      border:1px solid var(--line);
      border-radius:8px;
      background:#fafbf9;
      color:var(--ink);
      outline:none;
      font-size:14px;
      transition:border-color .2s ease;
    }
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select{height:46px;padding:0 14px}
    .ya-ebc-page .ya-field textarea{min-height:100px;padding:12px 14px;resize:vertical}
    .ya-ebc-page .ya-field input:focus,
    .ya-ebc-page .ya-field select:focus,
    .ya-ebc-page .ya-field textarea:focus{border-color:var(--green-2);background:#fff;box-shadow:0 0 0 3px rgba(97,117,85,.12)}

    .ya-ebc-page .ya-rating-row{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px}
    .ya-ebc-page .ya-rating-row input{position:absolute;opacity:0;pointer-events:none}
    .ya-ebc-page .ya-rating-row label{cursor:pointer;color:#cbd5cb}
    .ya-ebc-page .ya-rating-row label svg{width:26px;height:26px;fill:currentColor}
    .ya-ebc-page .ya-rating-row label:hover,
    .ya-ebc-page .ya-rating-row label:hover~label,
    .ya-ebc-page .ya-rating-row input:checked~label{color:var(--gold)}

    .ya-review-alert{padding:12px 16px;border-radius:8px;margin-bottom:14px;font-size:13px}
    .ya-review-alert--success{background:#edf6f0;color:#1e6b3a;border:1px solid #b7e2c7}
    .ya-review-alert--error{background:#fde8e8;color:#9b1c1c;border:1px solid #f8b4b4}

    /* Long-form editorial reading & progress */
    .ya-ebc-page .ya-tab-stage .ya-panel{
      display:block;
      scroll-margin-top:112px;
    }
    .ya-ebc-page .ya-reading-progress{--ya-reading-progress:0%;position:absolute;top:0;right:0;bottom:0;width:3px;background:var(--line);border-radius:2px;overflow:hidden}
    .ya-ebc-page .ya-reading-progress-bar{position:absolute;top:0;right:0;width:100%;height:var(--ya-reading-progress);background:var(--gold);transition:height .18s linear;border-radius:2px}

    /* Responsive */
    @media(max-width:1300px){
      /* Slightly tighten gallery column at large tablet */
      .ya-ebc-page .yt-day-card{grid-template-columns:130px minmax(0,1fr) 300px}
      .ya-ebc-page .yt-day-center{padding:4px 20px}
    }
    @media(max-width:1100px){
      .ya-ebc-page .ya-tab-mman-section{display:block;width:100%}
      .ya-ebc-page .ya-tabs-wrap{position:static;width:100%;margin:0 0 24px;box-shadow:none;border-radius:0;border:none;padding:0;background:transparent}
      .ya-ebc-page .ya-nav-title{display:none;}
      .ya-ebc-page .ya-tabs{display:flex;flex-direction:column;gap:10px}
      .ya-ebc-page .ya-tab{
        display:flex;
        align-items:center;
        justify-content:space-between;
        width:100%;
        padding:15px 20px;
        background:#ffffff;
        border:1px solid var(--line);
        border-radius:12px;
        font-size:15.5px;
        font-weight:700;
        color:var(--green);
        text-align:left;
        box-shadow:0 2px 10px rgba(0,0,0,.03);
      }
      .ya-ebc-page .ya-tab::after{
        content:"";
        width:8px;
        height:8px;
        border-right:2px solid var(--gold);
        border-bottom:2px solid var(--gold);
        transform:rotate(45deg);
        transition:transform .25s ease;
        margin-left:auto;
      }
      .ya-ebc-page .ya-tab.is-active{
        background:#f5f8f3;
        border-color:var(--gold);
        border-left:4px solid var(--gold);
      }
      .ya-ebc-page .ya-tab.is-active::after{
        transform:rotate(-135deg);
      }
      /* Panels moved inside .ya-tabs (mobile accordion) */
      .ya-ebc-page .ya-tabs > .ya-panel{
        display:none;
        padding:20px 16px 32px;
        background:var(--cream);
        border:1px solid var(--line);
        border-top:0;
        border-radius:0 0 12px 12px;
        margin-top:-10px;
        margin-bottom:10px;
      }
      .ya-ebc-page .ya-tabs > .ya-panel.is-active{display:block}
      /* Fallback panels in ya-tab-stage */
      .ya-ebc-page .ya-tab-stage .ya-panel{display:none;padding:18px 0 28px}
      .ya-ebc-page .ya-tab-stage .ya-panel.is-active{display:block}
      .ya-ebc-page .ya-overview-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-review-cards{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr}
      .ya-ebc-page .ya-heli-card{margin:24px 0}
      /* Itinerary card: 2-col (left + center stacked, gallery right) on tablet */
      .ya-ebc-page .yt-day-card{
        grid-template-columns:110px minmax(0,1fr) 240px;
        padding:20px 20px 20px 18px;
      }
      .ya-ebc-page .yt-day-center{padding:4px 16px}
    }

    @media(max-width:860px){
      /* Full single-column stack on mobile */
      .ya-ebc-page .yt-day-card{
        grid-template-columns:1fr;
        padding:22px 18px;
        border-radius:16px;
        gap:0;
      }
      .ya-ebc-page .yt-day-card::before{display:none}
      /* Gallery moves to top */
      .ya-ebc-page .yt-day-gallery{
        order:-1;
        border-radius:10px;
        aspect-ratio:16/9;
        margin-bottom:16px;
      }
      .ya-ebc-page .yt-day-no-image{min-height:160px}
      /* Left strip becomes horizontal row */
      .ya-ebc-page .yt-day-left{
        border-right:0;
        border-bottom:1px solid #ece8df;
        padding:0 0 14px;
        flex-direction:row;
        flex-wrap:wrap;
        align-items:center;
        gap:14px;
        margin-bottom:14px;
      }
      .ya-ebc-page .yt-day-gold-dash{display:none}
      .ya-ebc-page .yt-day-num-tag{font-size:10px}
      /* Center gets no side padding */
      .ya-ebc-page .yt-day-center{padding:0}
      .ya-ebc-page .ya-review-cards{grid-template-columns:1fr}
      .ya-ebc-page .ya-modal-dialog{padding:26px 20px}
    }

    @media(max-width:560px){
      .ya-ebc-page .ya-hero-actions{width:100%}
      .ya-ebc-page .ya-btn{width:100%}
      .ya-ebc-page .ya-form-grid{grid-template-columns:1fr}
      .ya-ebc-page .yt-day-gallery{aspect-ratio:16/10}
    }
  </style>

  <!-- 1. Hero Section -->
  <div class="ya-hero">
    <div class="ya-hero-background" aria-hidden="true">
      <div class="ya-hero-background-slide is-active" style="background-image:url('<?php echo esc_url( $hero_slider_images[0] ?? $hero_bg_img ); ?>');"></div>
      <div class="ya-hero-background-slide"></div>
    </div>
    <?php if ( count( $hero_slider_images ) > 1 ) : ?>
      <div class="ya-hero-slider-dots" role="group" aria-label="<?php esc_attr_e( 'Hero image slideshow', 'trek-content-manager' ); ?>">
        <?php foreach ( $hero_slider_images as $slide_index => $hero_slider_image ) : ?>
          <button class="ya-hero-slider-dot<?php echo 0 === $slide_index ? ' is-active' : ''; ?>" type="button" data-hero-slide="<?php echo esc_attr( $slide_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show hero image %d', 'trek-content-manager' ), $slide_index + 1 ) ); ?>" aria-current="<?php echo 0 === $slide_index ? 'true' : 'false'; ?>"></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="ya-inner">
      <div class="ya-hero-content ya-reveal">
        <div class="ya-script"><?php echo esc_html( $script_title ); ?></div>
        <h1><?php echo esc_html( $hero_title ); ?></h1>
        <p class="ya-hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <div class="ya-hero-actions">
          <button class="ya-btn ya-btn-light ya-jump-tab" type="button" data-target="itinerary">View Itinerary</button>
          <button class="ya-btn ya-btn-outline" type="button" id="ya-open-enquiry-modal">Start Planning Your Trip</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══ 2. Quick Facts Strip — premium editorial ═══ -->
  <?php
    /* Build facts array — only include non-empty fields */
    $yt_facts_raw = array(
      array(
        'key'   => 'duration',
        'label' => 'Duration',
        'value' => ! empty( $hero_days ) ? $hero_days . ' Days' : '',
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><circle cx="12" cy="16" r="1.5"/></svg>',
      ),
      array(
        'key'   => 'destination',
        'label' => 'Destination',
        'value' => ! empty( $fact_region ) ? $fact_region : ( ! empty( $title ) ? $title : '' ),
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>',
      ),
      array(
        'key'   => 'difficulty',
        'label' => 'Difficulty',
        'value' => ! empty( $difficulty ) ? $difficulty : '',
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20l4.5-9 4 5.5 3-4.5L20 20H3z"/><path d="M7 14l1.5-3 1.5 2"/></svg>',
      ),
      array(
        'key'   => 'highest_point',
        'label' => 'Highest Point',
        'value' => ! empty( $fact_high_point ) ? $fact_high_point : '',
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 21l7-9 4 5 3-4 6 8H2z"/><path d="M15.5 10l-3-5-3 5"/></svg>',
      ),
      array(
        'key'   => 'best_season',
        'label' => 'Best Season',
        'value' => ! empty( $fact_season ) ? $fact_season : '',
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>',
      ),
      array(
        'key'   => 'travel_style',
        'label' => 'Travel Style',
        'value' => ! empty( $fact_style ) ? $fact_style : '',
        'sub'   => '',
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="2.5"/><circle cx="17" cy="7" r="2"/><path d="M2 21c0-3.87 3.13-7 7-7h4c3.87 0 7 3.13 7 7"/><path d="M17 14c1.66 0 3 1.34 3 3"/></svg>',
      ),
    );
    /* Filter to only non-empty values */
    $yt_facts = array_values( array_filter( $yt_facts_raw, function( $f ) {
      return ! empty( $f['value'] ) && trim( $f['value'] ) !== '';
    } ) );
  ?>
  <?php if ( ! empty( $yt_facts ) ) : ?>
  <section class="yt-qf" aria-label="Quick Facts" id="yt-quick-facts">
    <div class="yt-qf__inner ya-reveal">
      <?php foreach ( $yt_facts as $yt_fact ) : ?>
        <div class="yt-qf__item">
          <span class="yt-qf__icon" aria-hidden="true"><?php echo $yt_fact['icon']; ?></span>
          <span class="yt-qf__label"><?php echo esc_html( $yt_fact['label'] ); ?></span>
          <span class="yt-qf__value"><?php echo esc_html( $yt_fact['value'] ); ?></span>
          <?php if ( ! empty( $yt_fact['sub'] ) ) : ?>
            <span class="yt-qf__sub"><?php echo esc_html( $yt_fact['sub'] ); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- 3. Sticky Navigation Tabs -->
  <section class="ya-tab-mman-section">
    <nav class="ya-tabs-wrap" aria-label="Trip information tabs">
      <div class="ya-nav-title">NAVIGATION</div>
      <div class="ya-inner ya-tabs" role="tablist">
        <button class="ya-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="ya-panel-overview" data-tab="overview">
          Overview
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" data-tab="overview" data-scroll-target=".ya-inclusions-checklist">
          Highlights
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-itinerary" data-tab="itinerary">
          Itinerary
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-essential-info" data-tab="essential-info">
          Essential Info
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-review" data-tab="review">
          Reviews
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-departures" data-tab="departures">
          Fixed Departures
        </button>
      </div>

      <!-- Private Heli Charters Promo Card in Left Sidebar -->
      <div class="ya-heli-card ya-reveal">
        <div class="ya-heli-tag">
          <span>THE SELECTION OF TREKS</span>
        </div>
        <p>Explore our curated selection of treks, tailored to offer unforgettable experiences in the mountains.</p>
        <button type="button" class="ya-btn ya-heli-btn" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">Explore Treks</button>
      </div>

      <div class="ya-reading-progress" aria-hidden="true"><span class="ya-reading-progress-bar"></span></div>
    </nav>

    <div class="ya-tab-stage">
      <!-- Panel 1: Overview (2 Columns Desktop: Left=Intro/Overview+Text+Checkmarks; Right=Landscape Photo+Trip Facts; Below=Highlight+Roadmap) -->
      <section class="ya-panel is-active" id="ya-panel-overview" role="tabpanel" data-panel="overview">
        <div class="ya-inner">
          <div class="ya-overview-grid">
            <div class="ya-overview-copy ya-reveal">
              <h2>Overview</h2>
              <h3><?php echo esc_html( $overview_subheading ); ?></h3>
              <div class="ya-overview-text">
                <?php echo wp_kses_post( wpautop( $overview_content ) ); ?>
              </div>

              <!-- Vertical list of trip highlights with green checkmarks -->
              <div class="ya-inclusions-checklist">
                <?php foreach ( $inclusions as $inc_point ) : 
                  $inc_title = is_array( $inc_point ) ? ( $inc_point['title'] ?? '' ) : $inc_point;
                  $inc_desc  = is_array( $inc_point ) ? ( $inc_point['desc'] ?? '' ) : '';
                  if ( empty( $inc_title ) && empty( $inc_desc ) ) continue;
                ?>
                  <div class="ya-check-item">
                    <span class="ya-check-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg></span>
                    <div class="ya-check-content">
                      <?php if ( ! empty( $inc_title ) ) : ?><strong><?php echo esc_html( $inc_title ); ?></strong><?php endif; ?>
                      <?php if ( ! empty( $inc_desc ) ) : ?><div class="ya-check-desc"><?php echo wp_kses_post( wpautop( $inc_desc ) ); ?></div><?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Right Column: Top Stunning Landscape Photograph + Bottom Trip Facts Card -->
            <div class="ya-overview-side ya-reveal">
              <div class="ya-side-scenic-photo">
                <img src="<?php echo esc_url( $hero_slider_images[1] ?? ( $gallery[0]['url'] ?? $hero_bg_img ) ); ?>" alt="<?php echo esc_attr( $title ); ?> Mountain Vista" loading="lazy">
              </div>

              <div class="ya-trip-facts-card">
                <h4>TRIP NOTES</h4>
                <ul class="ya-trip-facts-list">
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Duration</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $hero_days ); ?> Days</strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 20L8.5 7l4 7.5L16 9l6 11H2z"/><path d="M6.5 13l2-3 2 3"/></svg> Highest Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_high_point ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg> Starting Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_start ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg> Ending Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_end ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12h16M16 6l6 6-6 6"/></svg> Total Distance</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_distance ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2"/></svg> Best Season</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_season ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10L12 3l9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10z"/><path d="M9 21V12h6v9"/></svg> Accommodation</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_accom ); ?></strong>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Highlight Quote Banner -->
          <div class="ya-quote-banner ya-reveal">
            <blockquote><?php echo wp_kses_post( wp_strip_all_tags( $overview_highlight ) ); ?></blockquote>
          </div>

          <!-- Roadmap Section -->
          <?php if ( ! empty( $map_embed_code ) || ! empty( $map_file_url ) || ! empty( $route_img ) ) : ?>
          <div class="ya-roadmap-section ya-reveal">
            <div class="ya-roadmap-header">
              <div>
                <span class="ya-kicker"><?php echo esc_html( $route_caption_sub ); ?></span>
                <h3><?php echo esc_html( $route_caption_h3 ); ?></h3>
              </div>
            </div>
            <div class="ya-roadmap-frame">
              <?php if ( ! empty( $map_embed_code ) ) : ?>
                <div class="ya-map-embed-wrapper">
                  <?php echo wp_kses( $map_embed_code, array(
                    'iframe' => array(
                      'src'             => true,
                      'width'           => true,
                      'height'          => true,
                      'frameborder'     => true,
                      'allowfullscreen' => true,
                      'style'           => true,
                      'loading'         => true,
                    ),
                    'div' => array( 'id' => true, 'class' => true, 'style' => true ),
                    'script' => array( 'src' => true, 'type' => true ),
                  ) ); ?>
                </div>
              <?php elseif ( ! empty( $map_file_url ) ) : ?>
                <div class="ya-map-file-wrapper" style="position:relative;">
                  <?php if ( ! empty( $route_img ) ) : ?>
                    <img src="<?php echo esc_url( $route_img ); ?>" alt="<?php echo esc_attr( $title ); ?> route map" loading="lazy">
                  <?php endif; ?>
                  <div class="ya-map-file-cta" style="margin-top:14px; text-align:center;">
                    <a href="<?php echo esc_url( $map_file_url ); ?>" target="_blank" rel="noopener noreferrer" class="ya-btn ya-btn-sm ya-btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                      View / Download Interactive Route File (<?php echo esc_html( strtoupper( pathinfo( parse_url( $map_file_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ?: 'MAP' ) ); ?>)
                    </a>
                  </div>
                </div>
              <?php elseif ( ! empty( $route_img ) ) : ?>
                <img src="<?php echo esc_url( $route_img ); ?>" alt="<?php echo esc_attr( $title ); ?> route map" loading="lazy">
              <?php endif; ?>
            </div>
            <div class="ya-roadmap-caption">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
              <span>Route: <?php echo esc_html( $fact_start ); ?> → <?php echo esc_html( $fact_high_point ); ?> → <?php echo esc_html( $fact_end ); ?> · Total distance ~<?php echo esc_html( $fact_distance ); ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Panel 2: Itinerary — day-specific images only; no external fallbacks -->
      <?php
      // NOTE: No gallery pool / external fallback images.
      // Each day shows ONLY its own uploaded photos.
      // If a day has no image, a styled placeholder is shown instead.
      ?>
      <section class="ya-panel" id="ya-panel-itinerary" role="tabpanel" data-panel="itinerary">
        <div class="ya-inner">
          <div class="yt-itin-header ya-reveal">
            <div class="yt-itin-eyebrow">
              <span class="yt-itin-dash"></span>
              <span><?php echo esc_html( $itin_script ?: 'YOUR JOURNEY' ); ?></span>
            </div>
            <h2 class="yt-itin-h2"><?php echo esc_html( $itin_heading ?: 'Day by Day Itinerary' ); ?></h2>
            <p class="yt-itin-desc"><?php echo esc_html( $itin_subtext ?: 'A flexible private itinerary blending heritage, nature, mountain views and local encounters.' ); ?></p>
          </div>

          <div class="yt-days-list">
            <?php foreach ( $itinerary as $i_idx => $day ) :
              $day_num  = $day['day'] ?? sprintf( '%02d', $i_idx + 1 );
              $meta_info = ya_parse_day_stage_meta( $day, $breadcrumb_region );
              $loc_name  = $meta_info['location'];
              $alt_val   = $meta_info['altitude'];

              // Collect ONLY this day's own uploaded images — no external fallbacks.
              $day_images = array();
              if ( ! empty( $day['gallery'] ) && is_array( $day['gallery'] ) ) {
                foreach ( $day['gallery'] as $gi ) {
                  $gi_url = is_array( $gi ) ? ( $gi['url'] ?? '' ) : $gi;
                  if ( ! empty( $gi_url ) ) $day_images[] = $gi_url;
                }
              }
              if ( empty( $day_images ) && ! empty( $day['images'] ) && is_array( $day['images'] ) ) {
                foreach ( $day['images'] as $gi ) {
                  $gi_url = is_array( $gi ) ? ( $gi['url'] ?? '' ) : $gi;
                  if ( ! empty( $gi_url ) ) $day_images[] = $gi_url;
                }
              }
              if ( empty( $day_images ) && ! empty( $day['photo'] ) ) {
                $day_images[] = $day['photo'];
              }
              // Deduplicate & keep only real URLs
              $day_images = array_values( array_unique( array_filter( $day_images ) ) );
              $has_images = ! empty( $day_images );

              // Pills
              $pills_raw   = $day['pills'] ?? ( $day['meals'] ?? '' );
              $pills_items = array();
              if ( ! empty( $pills_raw ) ) {
                $pills_items = array_values( array_filter( array_map( 'trim', preg_split( '/[,;]+/', $pills_raw ) ) ) );
              }
            ?>
              <article class="yt-day-card ya-reveal" id="day-<?php echo esc_attr( $day_num ); ?>">

                <!-- Left: Day number, location, altitude -->
                <div class="yt-day-left">
                  <div class="yt-day-num-tag">DAY <?php echo esc_html( $day_num ); ?></div>
                  <div class="yt-day-gold-dash"></div>
                  <div class="yt-day-stat">
                    <span class="yt-day-stat-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                    </span>
                    <span class="yt-day-stat-val"><?php echo esc_html( $loc_name ); ?></span>
                  </div>
                  <?php if ( ! empty( $alt_val ) ) : ?>
                  <div class="yt-day-stat">
                    <span class="yt-day-stat-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                    </span>
                    <span class="yt-day-stat-val"><?php echo esc_html( $alt_val ); ?></span>
                  </div>
                  <?php endif; ?>
                </div>

                <!-- Center: Title, description, pills -->
                <div class="yt-day-center">
                  <h3 class="yt-day-title"><?php echo esc_html( $day['title'] ?? ( 'Day ' . $day_num ) ); ?></h3>
                  <div class="yt-day-body-text">
                    <?php echo wp_kses_post( wpautop( $day['desc'] ?? ( $day['description'] ?? '' ) ) ); ?>
                  </div>
                  <?php if ( ! empty( $pills_items ) ) : ?>
                  <div class="yt-day-pills">
                    <?php foreach ( $pills_items as $pill ) :
                      $pill_icon = ya_get_itin_pill_icon( $pill );
                    ?>
                      <span class="yt-pill">
                        <span class="yt-pill-icon" aria-hidden="true"><?php echo $pill_icon; ?></span>
                        <span class="yt-pill-text"><?php echo esc_html( $pill ); ?></span>
                      </span>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
                </div>

                <!-- Right: Images or no-image placeholder -->
                <?php if ( $has_images ) : ?>
                <div class="yt-day-gallery" data-day-gallery>
                  <div class="yt-day-carousel-viewport">
                    <div class="yt-day-carousel-track">
                      <?php foreach ( $day_images as $d_idx => $d_img ) : ?>
                        <div class="yt-day-carousel-slide<?php echo 0 === $d_idx ? ' is-active' : ''; ?>">
                          <img src="<?php echo esc_url( $d_img ); ?>"
                               alt="<?php echo esc_attr( ( $day['title'] ?? ( 'Day ' . $day_num ) ) . ' — photo ' . ( $d_idx + 1 ) ); ?>"
                               loading="lazy">
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <?php if ( count( $day_images ) > 1 ) : ?>
                    <!-- Top-right counter pill (e.g. 1 / 4) -->
                    <div class="yt-gallery-counter-badge">
                      <span class="yt-gallery-counter">1 / <?php echo count( $day_images ); ?></span>
                    </div>

                    <!-- Left & right side navigation buttons -->
                    <button type="button" class="yt-gallery-side-btn yt-gallery-nav-btn yt-gallery-prev" aria-label="Previous image">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <button type="button" class="yt-gallery-side-btn yt-gallery-nav-btn yt-gallery-next" aria-label="Next image">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>

                    <!-- Bottom-center dots -->
                    <div class="yt-gallery-dots" aria-hidden="true">
                      <?php for ( $dot_i = 0; $dot_i < count( $day_images ); $dot_i++ ) : ?>
                        <span class="yt-gallery-dot<?php echo 0 === $dot_i ? ' is-active' : ''; ?>"></span>
                      <?php endfor; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ( ! empty( $loc_name ) ) : ?>
                    <!-- Bottom-left location tag -->
                    <div class="yt-gallery-location-tag">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                      <span><?php echo esc_html( $loc_name ); ?></span>
                    </div>
                  <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="yt-day-gallery">
                  <div class="yt-day-no-image" role="img" aria-label="No image uploaded for this day">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
                      <rect x="3" y="3" width="18" height="18" rx="3"/>
                      <path d="M3 16l5-5 4 4 3-3 6 5"/>
                      <circle cx="8.5" cy="8.5" r="1.5"/>
                    </svg>
                    <span>No image uploaded<br>for this day</span>
                  </div>
                </div>
                <?php endif; ?>

              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Panel 3: Essential Info (Clean collapsible panel format with icons & smooth transitions) -->
      <section class="ya-panel" id="ya-panel-essential-info" role="tabpanel" data-panel="essential-info">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <span class="ya-kicker"><?php echo esc_html( $ess_kicker ); ?></span>
            <h2>Essential Information</h2>
            <div class="ya-ess-intro-p">
              <?php echo wp_kses_post( wpautop( $ess_intro ) ); ?>
            </div>
          </div>

          <div class="ya-essential-layout">
            <div class="ya-info-list ya-reveal">
              <?php 
              $ess_icons = array(
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 18v-6a9 9 0 0 1 18 0v6M3 14h18M3 18h18"/></svg>'
              );
              foreach ( $accordions as $a_idx => $acc ) : 
                $icon_svg = $ess_icons[ $a_idx % count($ess_icons) ];
              ?>
                <article class="ya-info-item <?php echo 0 === $a_idx ? 'is-open' : ''; ?>">
                  <button class="ya-info-head" type="button" aria-expanded="<?php echo 0 === $a_idx ? 'true' : 'false'; ?>">
                    <span>
                      <span class="ya-info-icon"><?php echo $icon_svg; ?></span>
                      <?php echo esc_html( $acc['title'] ?? '' ); ?>
                    </span>
                    <svg class="ya-info-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                  </button>
                  <div class="ya-info-body"><div><div class="ya-info-content">
                    <?php echo wp_kses_post( $acc['content'] ?? '' ); ?>
                  </div></div></div>
                </article>
              <?php endforeach; ?>
            </div>

            <aside class="ya-trip-note-card ya-reveal">
              <div class="ya-trip-note-image" style="background-image:url('<?php echo esc_url( $card_img ); ?>');" role="img" aria-label="<?php echo esc_attr( $card_title ); ?>"></div>
              <div class="ya-trip-note-copy">
                <small><?php echo esc_html( $card_kicker ); ?></small>
                <h3><?php echo esc_html( $card_title ); ?></h3>
                <p><?php echo esc_html( $card_desc ); ?></p>
                <a class="ya-btn ya-btn-light" href="mailto:<?php echo esc_attr( $card_email ); ?>?subject=<?php echo rawurlencode( 'Request for Current ' . $title . ' Trip Notes' ); ?>"><?php echo esc_html( $card_btn ); ?></a>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <!-- Panel 4: Traveler Reviews (Database-driven, strictly current trek, no fallbacks) -->
      <section class="ya-panel" id="ya-panel-review" role="tabpanel" data-panel="review">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <div class="ya-rev-head-row">
              <div>
                <span class="ya-kicker"><?php echo esc_html( $rev_kicker ); ?></span>
                <h2>Traveler Reviews</h2>
                <p><?php echo esc_html( $rev_subtext ); ?></p>
              </div>
              <div>
                <button type="button" class="ya-btn ya-btn-primary ya-rev-cta-btn" id="ya-open-review-modal" aria-haspopup="dialog" aria-controls="ya-write-review-modal">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Write Your Review
                </button>
              </div>
            </div>
          </div>

          <?php if ( empty( $display_reviews ) ) : ?>
            <!-- 100% Database-Driven: Empty State when no approved reviews exist yet -->
            <div class="ya-review-empty-state ya-reveal">
              <div class="ya-rev-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
              <h3 class="ya-rev-empty-title"><?php esc_html_e( 'No reviews yet', 'trek-content-manager' ); ?></h3>
              <p class="ya-rev-empty-sub"><?php esc_html_e( 'Be the first traveler to share your experience.', 'trek-content-manager' ); ?></p>
              <button type="button" class="ya-btn ya-btn-primary ya-rev-cta-btn ya-rev-empty-cta" onclick="document.querySelector('#ya-open-review-modal')?.click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <?php esc_html_e( 'Write Your Review', 'trek-content-manager' ); ?>
              </button>
            </div>
          <?php else : ?>
            <!-- Review Cards Grid (3 columns on desktop, horizontal scroll-snap on mobile) -->
            <div class="ya-review-cards-wrap ya-reveal">
              <div class="ya-review-cards" id="ya-review-cards-list">
                <?php foreach ( $display_reviews as $rev_idx => $rev ) : 
                  $raw_rating  = floatval( $rev['rating'] ?? 5.0 );
                  $rating_val  = max( 1.0, min( 5.0, $raw_rating ) );
                  $full_stars  = floor( $rating_val );
                  $has_half    = ( ( $rating_val - $full_stars ) >= 0.4 );
                  $empty_stars = max( 0, 5 - $full_stars - ( $has_half ? 1 : 0 ) );
                  $rev_name    = $rev['name'] ?? 'Traveller';
                  $initials    = ya_get_reviewer_initials( $rev_name );
                  $rev_type    = $rev['type'] ?? 'Solo Traveler';
                  $rev_title   = $rev['title'] ?? 'An Unforgettable Experience!';
                  $rev_loc     = $rev['location'] ?? 'Verified Traveller';
                  $rev_date    = $rev['date'] ?? '';
                  $is_hidden   = $rev_idx >= 6 ? ' ya-review-hidden' : '';
                ?>
                  <article class="ya-review-card<?php echo esc_attr( $is_hidden ); ?>" data-index="<?php echo esc_attr( $rev_idx ); ?>">
                    <div class="ya-rev-card-top">
                      <div class="ya-rev-card-top-row">
                        <h4 class="ya-rev-card-title"><?php echo esc_html( $rev_title ); ?></h4>
                        <div class="ya-rev-stars" aria-label="<?php echo esc_attr( $rating_val ); ?> out of 5 stars">
                          <?php for ( $s = 0; $s < $full_stars; $s++ ) : ?>
                            <svg class="ya-star-svg is-full" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>
                          <?php endfor; ?>
                          <?php if ( $has_half ) : ?>
                            <svg class="ya-star-svg is-half" viewBox="0 0 24 24" aria-hidden="true"><defs><linearGradient id="ya-grad-star-<?php echo esc_attr( $rev_idx ); ?>"><stop offset="50%" stop-color="#c6a45f"/><stop offset="50%" stop-color="#e5e7eb"/></linearGradient></defs><path fill="url(#ya-grad-star-<?php echo esc_attr( $rev_idx ); ?>)" d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>
                          <?php endif; ?>
                          <?php for ( $e = 0; $e < $empty_stars; $e++ ) : ?>
                            <svg class="ya-star-svg is-empty" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>
                          <?php endfor; ?>
                        </div>
                      </div>
                      <?php if ( ! empty( $rev_type ) ) : ?>
                        <span class="ya-rev-type-badge"><?php echo esc_html( $rev_type ); ?></span>
                      <?php endif; ?>
                    </div>

                    <blockquote class="ya-rev-card-text">
                      <p><?php echo nl2br( esc_html( $rev['quote'] ?? '' ) ); ?></p>
                    </blockquote>

                    <div class="ya-rev-card-bottom">
                      <div class="ya-rev-author">
                        <div class="ya-rev-avatar" aria-hidden="true">
                          <span><?php echo esc_html( $initials ); ?></span>
                        </div>
                        <div class="ya-rev-author-meta">
                          <strong class="ya-rev-author-name"><?php echo esc_html( $rev_name ); ?></strong>
                          <span class="ya-rev-author-loc"><?php echo esc_html( $rev_loc ); ?></span>
                        </div>
                      </div>
                      <?php if ( ! empty( $rev_date ) ) : ?>
                        <span class="ya-rev-date"><?php echo esc_html( $rev_date ); ?></span>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </div>

            <?php if ( count( $display_reviews ) > 6 ) : ?>
              <div class="ya-rev-load-more-wrap ya-reveal">
                <button type="button" class="ya-btn ya-btn-secondary" id="ya-load-more-reviews">
                  <?php esc_html_e( 'Load More Reviews', 'trek-content-manager' ); ?>
                </button>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </section>

      <!-- Panel 5: Fixed Departure Dates -->
      <section class="ya-panel ya-departures ya-reveal" id="ya-departures" role="tabpanel" data-panel="departures">
        <style>
          /* ── Departure Dates ──────────────────────────────── */
          .ya-ebc-page .ya-departures {
            padding: 44px 0 60px;
            border-top: 1px solid var(--line);
          }
      .ya-ebc-page .ya-dep-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-end;
        gap: 24px;
        margin-bottom: 48px;
      }
      .ya-ebc-page .ya-dep-head .ya-kicker { display: block; margin-bottom: 10px; }
      .ya-ebc-page .ya-dep-head h2 {
        margin: 0 0 10px;
        font: 600 clamp(28px, 3vw, 40px) / 1.1 "Newsreader", Georgia, serif;
        color: var(--green);
      }
      .ya-ebc-page .ya-dep-head p {
        margin: 0;
        color: var(--muted);
        font-size: 15px;
        max-width: 520px;
        line-height: 1.7;
      }
      /* Table styles */
      .ya-ebc-page .ya-dep-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 28px rgba(14, 49, 47, 0.06);
        background: #fff;
      }
      .ya-ebc-page .ya-dep-table thead tr {
        background: var(--green);
        color: #fff;
      }
      .ya-ebc-page .ya-dep-table th {
        padding: 14px 20px;
        font: 600 11px / 1 "Open Sans", Arial, sans-serif;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        text-align: left;
      }
      .ya-ebc-page .ya-dep-table tbody tr {
        background: #fff;
        border-bottom: 1px solid var(--line);
        transition: background .18s ease;
      }
      .ya-ebc-page .ya-dep-table tbody tr:last-child {
        border-bottom: none;
      }
      .ya-ebc-page .ya-dep-table tbody tr:hover {
        background: #f5faf7;
      }
      .ya-ebc-page .ya-dep-table td {
        padding: 16px 20px;
        font-size: 14px;
        color: var(--ink);
        vertical-align: middle;
      }
      /* Table cells */
      .ya-ebc-page .ya-dep-date-cell  { font: 600 15px / 1.3 "Newsreader", Georgia, serif; }
      .ya-ebc-page .ya-dep-day        { display: block; font-size: 22px; color: var(--green); }
      .ya-ebc-page .ya-dep-month-year {
        display: block;
        font: 500 12px / 1 Inter, Arial, sans-serif;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
      }
      .ya-ebc-page .ya-dep-price     { font: 600 16px / 1 "Newsreader", Georgia, serif; color: var(--green); }
      .ya-ebc-page .ya-dep-price-sub { display: block; font: 400 11px / 1 Inter, Arial, sans-serif; color: var(--muted); margin-top: 4px; }
      .ya-ebc-page .ya-dep-seats     { color: var(--muted); font-size: 13px; }
      .ya-ebc-page .ya-dep-note      { color: var(--muted); font-size: 12px; font-style: italic; margin-top: 3px; }
      .ya-ebc-page .ya-dep-book-btn  { white-space: nowrap; }
      /* Status badges */
      .ya-ebc-page .ya-dep-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        border-radius: 20px;
        font: 600 11px / 1 Inter, Arial, sans-serif;
        text-transform: uppercase;
        letter-spacing: .7px;
      }
      .ya-ebc-page .ya-dep-status--open      { background: #edf7f0; color: #1e6b3a; border: 1px solid #b7e2c7; }
      .ya-ebc-page .ya-dep-status--limited   { background: #fff8e6; color: #8a5c00; border: 1px solid #fcd97a; }
      .ya-ebc-page .ya-dep-status--soldout   { background: #f5f5f5; color: #6b7280; border: 1px solid #d1d5db; }
      .ya-ebc-page .ya-dep-status--guaranteed{ background: #f0f4ff; color: #2b4aaf; border: 1px solid #c7d4fa; }
      /* Empty state */
      .ya-ebc-page .ya-dep-no-dates {
        text-align: center;
        padding: 64px 24px 56px;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.04);
      }
      .ya-ebc-page .ya-dep-no-dates-icon {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: #f5f0e8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
      }
      .ya-ebc-page .ya-dep-no-dates-icon svg { width: 26px; height: 26px; color: var(--gold); }
      .ya-ebc-page .ya-dep-no-dates h3 {
        margin: 0 0 10px;
        font: 600 24px / 1.2 "Newsreader", Georgia, serif;
        color: var(--green);
      }
      .ya-ebc-page .ya-dep-no-dates p {
        margin: 0 auto 28px;
        max-width: 420px;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.7;
      }
      .ya-ebc-page .ya-dep-no-dates-perks {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px 28px;
        margin-bottom: 28px;
        font-size: 13px;
        color: var(--muted);
      }
      .ya-ebc-page .ya-dep-no-dates-perks span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
      }
      .ya-ebc-page .ya-dep-no-dates-perks svg { width: 15px; height: 15px; color: var(--gold); }
      @media(max-width:820px){
        .ya-ebc-page .ya-dep-table thead { display: none; }
        .ya-ebc-page .ya-dep-table tbody tr { display: block; padding: 16px; margin-bottom: 12px; border: 1px solid var(--line); border-radius: 10px; }
        .ya-ebc-page .ya-dep-table td { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--line); }
        .ya-ebc-page .ya-dep-table td:last-child { border-bottom: none; margin-top: 6px; }
        .ya-ebc-page .ya-dep-table td::before { content: attr(data-label); font: 600 11px/1 Inter, Arial, sans-serif; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); flex-shrink: 0; margin-right: 12px; }
      }
    </style>
    <div class="ya-inner">
      <div class="ya-dep-head">
        <div class="ya-dep-head-copy">
          <span class="ya-kicker"><?php echo esc_html( $dep_kicker ); ?></span>
          <h2><?php echo esc_html( $dep_heading ); ?></h2>
          <p><?php echo esc_html( $dep_subtext ); ?></p>
        </div>
        <button type="button" class="ya-btn ya-btn-primary" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">
          Enquire About Dates
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px;margin-left:6px"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </div>

      <?php if ( ! empty( $departure_dates ) ) : ?>
      <table class="ya-dep-table" aria-label="Upcoming departure dates for <?php echo esc_attr( $title ); ?>">
        <thead>
          <tr>
            <th>Departure Date</th>
            <th>Duration</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $departure_dates as $dep ) :
            $dep_date_str = $dep['date'] ?? '';
            $dep_ts       = strtotime( $dep_date_str );
            $dep_day      = $dep_ts ? date( 'd', $dep_ts ) : '';
            $dep_mon_yr   = $dep_ts ? date( 'M Y', $dep_ts ) : $dep_date_str;
            $dep_price    = $dep['price'] ?? '';
            $dep_seats    = $dep['seats'] ?? '';
            $dep_status   = $dep['status'] ?? 'open';
            $dep_note     = $dep['note'] ?? '';

            $status_label = array(
              'open'        => 'Open',
              'limited'     => 'Limited',
              'soldout'     => 'Sold Out',
              'guaranteed'  => 'Guaranteed',
            )[ $dep_status ] ?? 'Open';
          ?>
          <tr>
            <td data-label="Date" class="ya-dep-date-cell">
              <?php if ( $dep_day ) : ?>
                <span class="ya-dep-day"><?php echo esc_html( $dep_day ); ?></span>
                <span class="ya-dep-month-year"><?php echo esc_html( $dep_mon_yr ); ?></span>
              <?php else : ?>
                <?php echo esc_html( $dep_date_str ); ?>
              <?php endif; ?>
            </td>
            <td data-label="Duration"><?php echo esc_html( $hero_days ); ?> Days</td>
            <td data-label="Price">
              <span class="ya-dep-price"><?php echo esc_html( $dep_price ?: 'Contact us' ); ?></span>
              <?php if ( $dep_price ) : ?><span class="ya-dep-price-sub">per person</span><?php endif; ?>
            </td>
            <td data-label="Availability">
              <span class="ya-dep-seats"><?php echo esc_html( $dep_seats ?: '—' ); ?></span>
              <?php if ( $dep_note ) : ?><div class="ya-dep-note"><?php echo esc_html( $dep_note ); ?></div><?php endif; ?>
            </td>
            <td data-label="Status">
              <span class="ya-dep-status ya-dep-status--<?php echo esc_attr( $dep_status ); ?>"><?php echo esc_html( $status_label ); ?></span>
            </td>
            <td data-label="Book">
              <?php if ( 'soldout' !== $dep_status ) : ?>
              <button type="button" class="ya-btn ya-btn-primary ya-dep-book-btn" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()" style="padding:9px 18px;font-size:13px;">
                Book Now
              </button>
              <?php else : ?>
              <button type="button" class="ya-btn ya-dep-book-btn" disabled style="padding:9px 18px;font-size:13px;opacity:.5;cursor:not-allowed;background:#e5e7eb;color:#9ca3af;border:1px solid #d1d5db;border-radius:8px;">
                Sold Out
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else : ?>
      <div class="ya-dep-no-dates">
        <div class="ya-dep-no-dates-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4M8 2v4M3 10h18"/>
          </svg>
        </div>
        <h3>Departure Dates on Request</h3>
        <p>We offer private and group departures throughout the year. Contact our team to check the current schedule and available dates that suit your plans.</p>
        <div class="ya-dep-no-dates-perks">
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> Fixed Group Departures</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> Private Custom Dates</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> Year-Round Availability</span>
        </div>
        <button type="button" class="ya-btn ya-btn-primary" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">
          Ask About Available Dates
        </button>
      </div>
      <?php endif; ?>
    </div>
  </section>
    </div>
  </section>

 

  <!-- Dark CTA Banner ("Ready for the Himalaya?") -->
  <style>
    /* ── Dark CTA Banner ──────────────────────────────── */
    .ya-ebc-page .ya-dark-cta-section {
      position: relative;
      overflow: hidden;
      color: #fff;
      padding: 100px 0;
      text-align: center;
      background: #0c1610 center / cover no-repeat;
      background-image: url('<?php echo esc_url( plugins_url( "assets/images/dark-cta-bg.jpg", dirname(__FILE__) . "/../trek-content-manager.php" ) ); ?>');
    }
    .ya-ebc-page .ya-dark-cta-section::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(5, 14, 9, 0.62) 0%,
        rgba(5, 14, 9, 0.72) 50%,
        rgba(5, 14, 9, 0.80) 100%
      );
    }
    .ya-ebc-page .ya-dark-cta-inner {
      position: relative;
      z-index: 1;
      max-width: 780px;
      margin: 0 auto;
    }
    .ya-ebc-page .ya-dark-cta-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 18px;
      font: 600 11px / 1 Inter, Arial, sans-serif;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: #e8be61;
      opacity: .9;
    }
    .ya-ebc-page .ya-dark-cta-eyebrow::before,
    .ya-ebc-page .ya-dark-cta-eyebrow::after {
      content: "";
      display: block;
      width: 28px;
      height: 1px;
      background: #e8be61;
      opacity: .5;
    }
    .ya-ebc-page .ya-dark-cta-heading {
      margin: 0;
      font: italic 600 clamp(38px, 5vw, 62px) / 1.1 "Newsreader", Georgia, serif;
      color: #fff;
      letter-spacing: -.5px;
    }
    .ya-ebc-page .ya-dark-cta-heading em { color: #e8be61; font-style: inherit; }
    .ya-ebc-page .ya-dark-cta-sub {
      margin: 18px auto 36px;
      max-width: 580px;
      color: rgba(255,255,255,.8);
      font-size: 15px;
      line-height: 1.75;
    }
    .ya-ebc-page .ya-dark-cta-form {
      display: flex;
      justify-content: center;
      max-width: 500px;
      margin: 0 auto 32px;
      gap: 0;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 50px;
      padding: 5px 5px 5px 22px;
      backdrop-filter: blur(6px);
    }
    .ya-ebc-page .ya-dark-cta-form input {
      flex-grow: 1;
      height: 44px;
      border: none;
      background: transparent;
      color: #fff;
      font-size: 14px;
      min-width: 0;
    }
    .ya-ebc-page .ya-dark-cta-form input:focus { outline: none; }
    .ya-ebc-page .ya-dark-cta-form input::placeholder { color: rgba(255,255,255,.45); }
    .ya-ebc-page .ya-dark-cta-form .ya-btn-gold {
      height: 44px;
      padding: 0 26px;
      border-radius: 50px;
      font-size: 13px;
      font-weight: 700;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .ya-ebc-page .ya-dark-cta-divider {
      width: 40px;
      height: 1px;
      background: rgba(255,255,255,.2);
      margin: 0 auto 28px;
    }
    .ya-ebc-page .ya-dark-cta-badges {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 6px 28px;
      flex-wrap: wrap;
      font-size: 13px;
      color: rgba(255,255,255,.65);
    }
    .ya-ebc-page .ya-dark-cta-badges span {
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }
    .ya-ebc-page .ya-dark-cta-badges svg { width: 15px; height: 15px; color: #e8be61; }
    .ya-ebc-page .ya-dark-cta-badges span + span::before {
      content: "";
      display: inline-block;
      width: 1px;
      height: 12px;
      background: rgba(255,255,255,.2);
      margin-right: 0;
    }
    @media(max-width:560px){
      .ya-ebc-page .ya-dark-cta-section { padding: 72px 0; }
      .ya-ebc-page .ya-dark-cta-form {
        flex-direction: column;
        align-items: stretch;
        border-radius: 14px;
        padding: 14px;
        gap: 10px;
      }
      .ya-ebc-page .ya-dark-cta-form input { height: 48px; text-align: center; }
      .ya-ebc-page .ya-dark-cta-form .ya-btn-gold { height: 48px; border-radius: 10px; }
      .ya-ebc-page .ya-dark-cta-badges span + span::before { display: none; }
    }

    /* ── Related Journeys ────────────────────────────── */
    .ya-ebc-page .ya-related {
      padding: 100px 0;
      background: #f5f4f0;
      border-top: 1px solid var(--line);
    }
    .ya-ebc-page .ya-related-head {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: flex-end;
      gap: 20px;
      margin-bottom: 52px;
    }
    .ya-ebc-page .ya-related-head .ya-kicker { display: block; margin-bottom: 8px; }
    .ya-ebc-page .ya-related-head .ya-script { font-size: clamp(20px, 2.5vw, 28px); margin-bottom: 4px; }
    .ya-ebc-page .ya-related-head h2 {
      margin: 0;
      font: 600 clamp(26px, 3vw, 38px) / 1.15 "Newsreader", Georgia, serif;
      color: var(--ink);
    }
    .ya-ebc-page .ya-related-head > p {
      margin: 0;
      max-width: 380px;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.7;
    }
    .ya-ebc-page .ya-related-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 28px;
    }
    /* Premium card */
    .ya-ebc-page .ya-related-card {
      display: flex;
      flex-direction: column;
      border-radius: 2px;
      overflow: hidden;
      background: #fff;
      border: 1px solid #dce8e3;
      box-shadow: 0 4px 20px rgba(7,94,84,.05);
      transition: transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease;
      text-decoration: none;
      color: inherit;
    }
    .ya-ebc-page .ya-related-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 20px 52px rgba(7,94,84,.12);
    }
    /* Image area */
    .ya-ebc-page .ya-related-image {
      position: relative;
      height: 226px;
      overflow: hidden;
      flex-shrink: 0;
    }
    .ya-ebc-page .ya-related-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform .55s cubic-bezier(.22,1,.36,1);
    }
    .ya-ebc-page .ya-related-card:hover .ya-related-image img { transform: scale(1.06); }
    /* Category badge on image */
    .ya-ebc-page .ya-related-img-badge {
      position: absolute;
      top: 14px;
      left: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      background: rgba(7,94,84,.88);
      border-radius: 50px;
      font: 600 11px / 1 Inter, Arial, sans-serif;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: #fff;
      backdrop-filter: blur(4px);
    }
    .ya-ebc-page .ya-related-img-badge svg { width: 12px; height: 12px; }
    /* Location pill overlapping image/content border */
    .ya-ebc-page .ya-related-img-loc {
      position: absolute;
      bottom: 14px;
      left: 14px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      background: rgba(10,30,20,.75);
      border-radius: 50px;
      font: 600 10px / 1 Inter, Arial, sans-serif;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: #fff;
      backdrop-filter: blur(4px);
    }
    .ya-ebc-page .ya-related-img-loc svg { width: 11px; height: 11px; color: #e8be61; }
    /* Content area */
    .ya-ebc-page .ya-related-copy {
      padding: 22px 22px 20px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      position: relative;
    }
    .ya-ebc-page .ya-related-eyebrow {
      font: 700 10px / 1 Inter, Arial, sans-serif;
      letter-spacing: 1.8px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 8px;
      display: block;
    }
    .ya-ebc-page .ya-related-copy h3 {
      margin: 0 0 16px;
      font: 600 18px / 1.25 "Newsreader", Georgia, serif;
      color: #102f35;
    }
    /* Info strip */
    .ya-ebc-page .ya-related-info-row {
      display: flex;
      gap: 0;
      margin-bottom: 18px;
    }
    .ya-ebc-page .ya-related-info-item {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 3px;
      padding-right: 14px;
    }
    .ya-ebc-page .ya-related-info-item:not(:last-child) {
      border-right: 1px solid #dce8e3;
      margin-right: 14px;
    }
    .ya-ebc-page .ya-related-info-label {
      font: 500 10px / 1 Inter, Arial, sans-serif;
      letter-spacing: .8px;
      text-transform: uppercase;
      color: #617579;
    }
    .ya-ebc-page .ya-related-info-value {
      font: 600 13px / 1.2 Inter, Arial, sans-serif;
      color: #102f35;
    }
    /* CTA row */
    .ya-ebc-page .ya-related-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 16px;
      border-top: 1px solid #eaf5f0;
      margin-top: auto;
    }
    .ya-ebc-page .ya-related-link {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font: 700 11px / 1 Inter, Arial, sans-serif;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: #075e54;
      transition: gap .2s ease;
    }
    .ya-ebc-page .ya-related-card:hover .ya-related-link { gap: 11px; }
    .ya-ebc-page .ya-related-link svg { width: 15px; height: 15px; transition: transform .2s ease; }
    .ya-ebc-page .ya-related-card:hover .ya-related-link svg { transform: translateX(3px); }
    .ya-ebc-page .ya-related-duration-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      background: #eaf5f0;
      border-radius: 50px;
      font: 600 11px / 1 Inter, Arial, sans-serif;
      color: #075e54;
    }
    .ya-ebc-page .ya-related-duration-pill svg { width: 12px; height: 12px; }
    /* Contour art behind content */
    .ya-ebc-page .ya-related-copy::after {
      content: "";
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 60px;
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 60' preserveAspectRatio='none'%3E%3Cpath d='M0 40 Q40 20 80 35 Q120 50 160 30 Q200 10 240 28 Q280 46 320 32 Q360 18 400 38 L400 60 L0 60Z' fill='%23eaf5f0' opacity='0.55'/%3E%3C/svg%3E") bottom / cover no-repeat;
      pointer-events: none;
      border-radius: 0 0 20px 20px;
    }
    @media(max-width:900px){ .ya-ebc-page .ya-related-grid{ grid-template-columns:repeat(2,1fr); } }
    @media(max-width:560px){ .ya-ebc-page .ya-related-grid{ grid-template-columns:1fr; } }
  </style>

  <section class="ya-dark-cta-section ya-reveal">
    <div class="ya-inner ya-dark-cta-inner">
      <div class="ya-dark-cta-eyebrow">Plan Your Journey</div>
      <h2 class="ya-dark-cta-heading">Ready for the <em>Himalaya?</em></h2>
      <p class="ya-dark-cta-sub">Whether you want a customized itinerary, private departure, or have questions about acclimatization, our local expert team in Kathmandu is here to help.</p>

      <form class="ya-dark-cta-form" id="ya-dark-cta-form" onsubmit="event.preventDefault(); const email = this.querySelector('input[type=email]')?.value || ''; window.yaOpenEnquiry ? window.yaOpenEnquiry({email: email}) : document.querySelector('#ya-open-enquiry-modal')?.click();">
        <input type="email" id="ya-dark-cta-email" placeholder="Enter your email address" required aria-label="Email address">
        <button type="submit" class="ya-btn ya-btn-gold">Start Planning</button>
      </form>

      <div class="ya-dark-cta-divider"></div>
      <div class="ya-dark-cta-badges">
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Best Price Guarantee</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Flexible Cancellation</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> 24/7 Local Support</span>
      </div>
    </div>
  </section>

  <!-- Related Journeys -->
  <section class="ya-related">
    <div class="ya-inner">
      <div class="ya-related-head ya-reveal">
        <div>
          <span class="ya-kicker">Continue Exploring</span>
          <div class="ya-script">More of <?php echo esc_html( $breadcrumb_region ); ?></div>
          <h2>Trips You Might Also Like</h2>
        </div>
        <p>Alternative journeys combining spectacular mountain scenery, culture, wildlife and remote trekking routes.</p>
      </div>

      <div class="ya-related-grid">
        <?php
        if ( $related_query->have_posts() ) :
          while ( $related_query->have_posts() ) :
            $related_query->the_post();
            $r_id     = get_the_ID();
            $r_days   = get_post_meta( $r_id, '_trek_hero_days', true ) ?: '12';
            $r_region = get_post_meta( $r_id, '_trek_breadcrumb_region', true ) ?: 'Nepal';
            $r_diff   = get_post_meta( $r_id, '_trek_difficulty', true ) ?: 'Moderate';
            $r_season = get_post_meta( $r_id, '_trek_best_season', true ) ?: 'Mar – Nov';
            $r_thumb  = get_the_post_thumbnail_url( $r_id, 'large' ) ?: get_post_meta( $r_id, '_trek_hero_bg_img', true ) ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85';
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( get_permalink() ); ?>">
            <div class="ya-related-image">
              <img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
              <span class="ya-related-img-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l4-8 4 5 3-3 4 6"/><circle cx="7" cy="4" r="1" fill="currentColor"/></svg>
                Trekking
              </span>
              <span class="ya-related-img-loc">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <?php echo esc_html( $r_region ); ?>
              </span>
            </div>
            <div class="ya-related-copy">
              <span class="ya-related-eyebrow"><?php echo esc_html( $r_diff ); ?> Trek</span>
              <h3><?php the_title(); ?></h3>
              <div class="ya-related-info-row">
                <div class="ya-related-info-item">
                  <span class="ya-related-info-label">Duration</span>
                  <span class="ya-related-info-value"><?php echo esc_html( $r_days ); ?> Days</span>
                </div>
                <div class="ya-related-info-item">
                  <span class="ya-related-info-label">Best Season</span>
                  <span class="ya-related-info-value"><?php echo esc_html( $r_season ); ?></span>
                </div>
              </div>
              <div class="ya-related-footer">
                <div class="ya-related-link">
                  <span>View Journey</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </div>
                <span class="ya-related-duration-pill">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                  <?php echo esc_html( $r_days ); ?>D
                </span>
              </div>
            </div>
          </a>
        <?php
          endwhile;
          wp_reset_postdata();
        else :
          // Fallback cards
          $fallback_cards = array(
            array(
              'url'    => home_url( '/all-trips/' ),
              'img'    => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85',
              'alt'    => 'Himalayan landscape Nepal',
              'diff'   => 'Moderate',
              'title'  => 'Where Culture Meets the Wild: Nepal&#8217;s Treasures',
              'days'   => '12',
              'region' => 'Nepal',
              'season' => 'Mar – Nov',
            ),
            array(
              'url'    => home_url( '/all-trips/' ),
              'img'    => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=85',
              'alt'    => 'Mountain river trek Nepal',
              'diff'   => 'Challenging',
              'title'  => 'Nepal&#8217;s Wild Trio: Mountains, Rivers &amp; Jungle',
              'days'   => '14',
              'region' => 'Nepal',
              'season' => 'Oct – May',
            ),
            array(
              'url'    => home_url( '/all-trips/' ),
              'img'    => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=900&q=85',
              'alt'    => 'Everest Base Camp trekking',
              'diff'   => 'Strenuous',
              'title'  => 'Everest Base Camp: The Classic Himalayan Pilgrimage',
              'days'   => '16',
              'region' => 'Nepal',
              'season' => 'Apr – Jun',
            ),
          );
          foreach ( $fallback_cards as $fc ) :
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( $fc['url'] ); ?>">
            <div class="ya-related-image">
              <img src="<?php echo esc_url( $fc['img'] ); ?>" alt="<?php echo esc_attr( $fc['alt'] ); ?>" loading="lazy">
              <span class="ya-related-img-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l4-8 4 5 3-3 4 6"/><circle cx="7" cy="4" r="1" fill="currentColor"/></svg>
                Trekking
              </span>
              <span class="ya-related-img-loc">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <?php echo esc_html( $fc['region'] ); ?>
              </span>
            </div>
            <div class="ya-related-copy">
              <span class="ya-related-eyebrow"><?php echo esc_html( $fc['diff'] ); ?> Trek</span>
              <h3><?php echo $fc['title']; ?></h3>
              <div class="ya-related-info-row">
                <div class="ya-related-info-item">
                  <span class="ya-related-info-label">Duration</span>
                  <span class="ya-related-info-value"><?php echo esc_html( $fc['days'] ); ?> Days</span>
                </div>
                <div class="ya-related-info-item">
                  <span class="ya-related-info-label">Best Season</span>
                  <span class="ya-related-info-value"><?php echo esc_html( $fc['season'] ); ?></span>
                </div>
              </div>
              <div class="ya-related-footer">
                <div class="ya-related-link">
                  <span>View Journey</span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </div>
                <span class="ya-related-duration-pill">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                  <?php echo esc_html( $fc['days'] ); ?>D
                </span>
              </div>
            </div>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </section>

  <style>
    .ya-ebc-page .ya-enquiry-modal{position:fixed;inset:0;z-index:1000001;display:none;align-items:center;justify-content:center;padding:28px;background:rgba(15,25,18,.72);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);overflow-y:auto}
    .ya-ebc-page .ya-enquiry-modal.is-open{display:flex}
    .ya-ebc-page .ya-enquiry-dialog{position:relative;display:grid;grid-template-columns:minmax(330px,.9fr) minmax(460px,1.1fr);width:min(1040px,100%);max-height:calc(100svh - 56px);overflow:auto;background:#fff;border-radius:20px;box-shadow:0 32px 90px rgba(0,0,0,.35)}
    .ya-ebc-page .ya-enquiry-image{position:relative;min-height:620px;background:center / cover no-repeat;color:#fff}
    .ya-ebc-page .ya-enquiry-image:after{position:absolute;inset:0;content:"";background:linear-gradient(180deg,rgba(12,22,16,.05) 25%,rgba(12,22,16,.8) 100%)}
    .ya-ebc-page .ya-enquiry-image-copy{position:absolute;z-index:1;right:32px;bottom:30px;left:32px}
    .ya-ebc-page .ya-enquiry-image-copy small{display:block;margin-bottom:8px;font-size:13px;font-weight:600;letter-spacing:0;text-transform:none;color:#f5dd9c}
    .ya-ebc-page .ya-enquiry-image-copy strong{font:600 16px/1.35 "Newsreader",Georgia,serif}
    .ya-ebc-page .ya-enquiry-content{padding:44px 46px 40px;color:#202821}
    .ya-ebc-page .ya-enquiry-close{position:absolute;z-index:2;top:16px;right:16px;width:38px;height:38px;display:grid;place-items:center;border:1px solid #d7dfd4;border-radius:50%;background:#fff;color:#617654;cursor:pointer;transition:background .2s ease,color .2s ease,transform .2s ease}
    .ya-ebc-page .ya-enquiry-close:hover{background:#d1ad54;color:#202821;transform:rotate(90deg)}
    .ya-ebc-page .ya-enquiry-close:active{background:#617654;color:#fff;transform:rotate(90deg) scale(.92)}
    .ya-ebc-page .ya-enquiry-close svg{width:19px;height:19px}
    .ya-ebc-page .ya-enquiry-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:14px;color:#617654;font-size:13px;font-weight:600;letter-spacing:0;text-transform:none}
    .ya-ebc-page .ya-enquiry-meta span{display:inline-flex;align-items:center;gap:6px}
    .ya-ebc-page .ya-enquiry-meta svg{width:16px;height:16px;color:#d1ad54}
    .ya-ebc-page .ya-enquiry-content h2{margin:0;font:600 16px/1.4 "Newsreader",Georgia,serif;color:#202821;text-transform:none}
    .ya-ebc-page .ya-enquiry-intro{margin:10px 0 25px;color:#667068;font-size:14px;line-height:1.65}
    .ya-ebc-page .ya-enquiry-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
    .ya-ebc-page .ya-enquiry-form .ya-field{margin:0}
    .ya-ebc-page .ya-enquiry-form .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-enquiry-form label{display:block;margin:0 0 7px;color:#435045;font-size:14px;font-weight:600;letter-spacing:0;text-transform:none}
    .ya-ebc-page .ya-enquiry-form label em{font-weight:400}
    .ya-ebc-page .ya-enquiry-form input,.ya-ebc-page .ya-enquiry-form select,.ya-ebc-page .ya-enquiry-form textarea{width:100%;border:1px solid #d7dfd4;border-radius:10px;background:#fbfcfa;color:#202821;font-size:14px;transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}
    .ya-ebc-page .ya-enquiry-form input,.ya-ebc-page .ya-enquiry-form select{height:48px;padding:0 14px}
    .ya-ebc-page .ya-enquiry-form textarea{min-height:108px;padding:13px 14px;resize:vertical}
    .ya-ebc-page .ya-enquiry-form input:focus,.ya-ebc-page .ya-enquiry-form select:focus,.ya-ebc-page .ya-enquiry-form textarea:focus{border-color:#617654;outline:0;background:#fff;box-shadow:0 0 0 3px rgba(97,118,84,.14)}
    .ya-ebc-page .ya-enquiry-form [aria-invalid="true"]{border-color:#a34231;background:#fff7f5}
    .ya-ebc-page .ya-enquiry-actions{display:block;margin-top:2px}
    .ya-ebc-page .ya-enquiry-actions .ya-btn{width:100%;min-height:50px;justify-content:center;border-radius:10px;text-transform:none}
    .ya-ebc-page .ya-enquiry-note{margin:10px 0 0;color:#667068;font-size:12px;line-height:1.5;text-align:center}
    .ya-ebc-page .ya-enquiry-alert{display:none;grid-column:1/-1;padding:12px 14px;border-radius:10px;font-size:13px;line-height:1.45}
    .ya-ebc-page .ya-enquiry-alert.is-success{display:block;background:#edf4ea;color:#315336}
    .ya-ebc-page .ya-enquiry-alert.is-error{display:block;background:#fff1ee;color:#a34231}
    .ya-ebc-page .ya-enquiry-honeypot{position:absolute!important;left:-9999px!important}
    @media(max-width:760px){.ya-ebc-page .ya-enquiry-modal{padding:14px}.ya-ebc-page .ya-enquiry-dialog{grid-template-columns:1fr;max-height:calc(100svh - 28px)}.ya-ebc-page .ya-enquiry-image{min-height:210px}.ya-ebc-page .ya-enquiry-image-copy{right:24px;bottom:22px;left:24px}.ya-ebc-page .ya-enquiry-content{padding:30px 24px}.ya-ebc-page .ya-enquiry-form{grid-template-columns:1fr}.ya-ebc-page .ya-enquiry-form .ya-field-full{grid-column:auto}}

    /* ── Write Your Review Modal (z-index: 1000000) ─────────────────── */
    .ya-ebc-page .ya-review-modal {
      position: fixed;
      inset: 0;
      z-index: 1000000;
      display: none;
      place-items: center;
      padding: 20px;
      background: rgba(12, 28, 22, 0.78);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }
    .ya-ebc-page .ya-review-modal.is-open {
      display: grid;
    }
    .ya-ebc-page .ya-review-modal-dialog {
      position: relative;
      width: min(880px, 96vw);
      max-height: calc(100svh - 40px);
      background: #ffffff;
      border-radius: 22px;
      box-shadow: 0 32px 90px rgba(0, 0, 0, 0.4);
      border: 1px solid #eae5db;
      display: grid;
      grid-template-columns: 320px 1fr;
      overflow: hidden;
      animation: yaRevModalIn 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes yaRevModalIn {
      from { opacity: 0; transform: scale(0.97) translateY(12px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .ya-ebc-page .ya-review-modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      width: 36px;
      height: 36px;
      display: grid;
      place-items: center;
      border: 1px solid #dcd7cc;
      border-radius: 50%;
      background: #ffffff;
      color: #617555;
      cursor: pointer;
      transition: all 0.2s ease;
      z-index: 10;
      padding: 0;
    }
    .ya-ebc-page .ya-review-modal-close:hover {
      background: #045857;
      color: #ffffff;
      border-color: #045857;
      transform: rotate(90deg);
    }
    .ya-ebc-page .ya-review-modal-close svg {
      width: 16px;
      height: 16px;
    }

    /* Modal Left: Destination Info */
    .ya-ebc-page .ya-rev-modal-left {
      background: #fbfaf7;
      border-right: 1px solid #ede8df;
      padding: 32px 26px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      overflow-y: auto;
    }
    .ya-ebc-page .ya-rev-modal-img {
      width: 100%;
      height: 160px;
      border-radius: 14px;
      object-fit: cover;
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
      display: block;
      background: #ede8df;
    }
    .ya-ebc-page .ya-rev-modal-kicker {
      display: block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #b38838;
      margin-bottom: 4px;
    }
    .ya-ebc-page .ya-rev-modal-dest-title {
      margin: 0 0 6px;
      font: 600 20px / 1.25 "Newsreader", Georgia, serif;
      color: #1a2e26;
    }
    .ya-ebc-page .ya-rev-modal-dest-desc {
      margin: 0;
      font-size: 13px;
      line-height: 1.6;
      color: #5c6863;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Modal Right: Review Form */
    .ya-ebc-page .ya-rev-modal-right {
      padding: 34px 36px;
      overflow-y: auto;
      max-height: calc(100svh - 40px);
    }
    .ya-ebc-page .ya-rev-modal-header {
      margin-bottom: 22px;
    }
    .ya-ebc-page .ya-rev-modal-header h2 {
      margin: 0 0 6px;
      font: 600 24px / 1.2 "Newsreader", Georgia, serif;
      color: #1a2e26;
    }
    .ya-ebc-page .ya-rev-modal-header p {
      margin: 0;
      color: #637069;
      font-size: 14px;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* Rating Selector */
    .ya-ebc-page .ya-star-rating-wrap {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 20px;
      padding: 14px 18px;
      background: #fbfaf7;
      border-radius: 12px;
      border: 1px solid #eae5db;
    }
    .ya-ebc-page .ya-star-rating-label {
      font-size: 13px;
      font-weight: 700;
      color: #1a2e26;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-star-select {
      display: inline-flex;
      flex-direction: row-reverse;
      gap: 4px;
    }
    .ya-ebc-page .ya-star-select input {
      display: none;
    }
    .ya-ebc-page .ya-star-select label {
      cursor: pointer;
      color: #dcd7cb;
      transition: color 0.15s ease, transform 0.15s ease;
      margin: 0;
      padding: 0 2px;
    }
    .ya-ebc-page .ya-star-select label svg {
      width: 26px;
      height: 26px;
      fill: currentColor;
      display: block;
    }
    .ya-ebc-page .ya-star-select label:hover,
    .ya-ebc-page .ya-star-select label:hover ~ label,
    .ya-ebc-page .ya-star-select input:checked ~ label {
      color: #c6a45f;
    }
    .ya-ebc-page .ya-star-select label:hover {
      transform: scale(1.15);
    }

    /* Form Fields */
    .ya-ebc-page .ya-review-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    .ya-ebc-page .ya-review-form-grid .ya-field-full {
      grid-column: 1 / -1;
    }
    .ya-ebc-page .ya-review-modal .ya-field label {
      display: block;
      margin: 0 0 6px;
      color: #1a2e26;
      font-size: 13px;
      font-weight: 600;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-review-modal input,
    .ya-ebc-page .ya-review-modal select,
    .ya-ebc-page .ya-review-modal textarea {
      width: 100%;
      border: 1px solid #dcd7cc;
      border-radius: 9px;
      background: #fbfaf7;
      color: #1a2e26;
      font-size: 14px;
      padding: 11px 13px;
      box-sizing: border-box;
      transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-review-modal input:focus,
    .ya-ebc-page .ya-review-modal select:focus,
    .ya-ebc-page .ya-review-modal textarea:focus {
      border-color: #045857;
      outline: 0;
      background: #ffffff;
      box-shadow: 0 0 0 3px rgba(4, 88, 87, 0.12);
    }
    .ya-ebc-page .ya-review-modal textarea {
      min-height: 95px;
      resize: vertical;
    }

    /* Submit Button */
    .ya-ebc-page .ya-rev-submit-btn {
      width: 100%;
      justify-content: center;
      height: 48px;
      font-size: 15px;
      font-weight: 600;
      border-radius: 9px;
      background: #045857;
      color: #ffffff;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-submit-btn:hover {
      background: #022f2e;
    }
    .ya-ebc-page .ya-rev-submit-btn:disabled {
      opacity: 0.65;
      cursor: not-allowed;
    }

    /* Alert / Error Message */
    .ya-ebc-page .ya-rev-modal-alert {
      display: none;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 13.5px;
      line-height: 1.5;
      margin-bottom: 16px;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-modal-alert.is-error {
      display: block;
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    /* Success State View */
    .ya-ebc-page .ya-rev-modal-success {
      display: none;
      text-align: center;
      padding: 30px 10px;
    }
    .ya-ebc-page .ya-rev-modal-success.is-visible {
      display: block;
    }
    .ya-ebc-page .ya-rev-success-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: #eef7ee;
      color: #15803d;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }
    .ya-ebc-page .ya-rev-success-icon svg {
      width: 28px;
      height: 28px;
    }
    .ya-ebc-page .ya-rev-modal-success h3 {
      margin: 0 0 10px;
      font: 600 24px / 1.2 "Newsreader", Georgia, serif;
      color: #1a2e26;
    }
    .ya-ebc-page .ya-rev-modal-success p {
      margin: 0 0 14px;
      color: #55625c;
      font-size: 14.5px;
      line-height: 1.6;
      font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .ya-ebc-page .ya-rev-success-note {
      font-size: 13.5px;
      color: #718079;
      background: #f8faf8;
      border: 1px solid #e2ebe2;
      border-radius: 10px;
      padding: 12px 16px;
      margin: 16px 0 24px;
      text-align: left;
    }
    .ya-ebc-page .ya-rev-success-close-btn {
      padding: 11px 32px;
      border-radius: 999px;
      background: #045857;
      color: #ffffff;
      font-size: 14px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .ya-ebc-page .ya-rev-success-close-btn:hover {
      background: #022f2e;
    }

    /* Modal Responsive (Mobile < 768px) */
    @media (max-width: 768px) {
      .ya-ebc-page .ya-review-modal-dialog {
        grid-template-columns: 1fr;
        max-height: calc(100svh - 24px);
        overflow-y: auto;
      }
      .ya-ebc-page .ya-rev-modal-left {
        padding: 24px 20px;
        border-right: none;
        border-bottom: 1px solid #ede8df;
      }
      .ya-ebc-page .ya-rev-modal-img {
        height: 130px;
      }
      .ya-ebc-page .ya-rev-modal-right {
        padding: 24px 20px;
        max-height: none;
      }
      .ya-ebc-page .ya-review-form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="ya-enquiry-modal" id="ya-enquiry-modal" role="dialog" aria-modal="true" aria-labelledby="ya-enquiry-heading" aria-hidden="true">
    <div class="ya-enquiry-dialog" role="document">
      <button class="ya-enquiry-close" type="button" aria-label="Close enquiry form"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
      <div class="ya-enquiry-image" style="background-image:url('<?php echo esc_url( $hero_slider_images[0] ?? $hero_bg_img ); ?>');">
        <div class="ya-enquiry-image-copy"><small><?php echo esc_html( $breadcrumb_region ); ?></small><strong><?php echo esc_html( $title ); ?></strong></div>
      </div>
      <div class="ya-enquiry-content">
        <div class="ya-enquiry-meta"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg><?php echo esc_html( $breadcrumb_region ); ?></span><span><?php echo esc_html( $hero_days ); ?> Days</span></div>
        <h2 id="ya-enquiry-heading">Plan your <?php echo esc_html( $title ); ?> trip</h2>
        <p class="ya-enquiry-intro">Tell us a little about your travel plans and our local team will tailor this journey for you.</p>
        <form class="ya-enquiry-form" id="ya-enquiry-form" novalidate>
          <input type="hidden" id="ya-enquiry-trek-id" value="<?php echo esc_attr( $post_id ); ?>">
          <div class="ya-enquiry-honeypot" aria-hidden="true"><label>Website<input id="ya-enquiry-website" type="text" tabindex="-1" autocomplete="off"></label></div>
          <div class="ya-field"><label for="ya-enquiry-name">Full name *</label><input id="ya-enquiry-name" type="text" required minlength="2" autocomplete="name" placeholder="Your full name"></div>
          <div class="ya-field"><label for="ya-enquiry-people">Number of travellers *</label><select id="ya-enquiry-people" required><option value="" selected disabled>Select travellers</option><option>1 traveller</option><option>2 travellers</option><option>3–5 travellers</option><option>6–10 travellers</option><option>10+ travellers</option></select></div>
          <div class="ya-field"><label for="ya-enquiry-email">Email *</label><input id="ya-enquiry-email" type="email" required autocomplete="email" placeholder="you@email.com"></div>
          <div class="ya-field"><label for="ya-enquiry-phone">Phone / WhatsApp <em>(optional)</em></label><input id="ya-enquiry-phone" type="tel" autocomplete="tel" placeholder="Your phone number"></div>
          <div class="ya-field ya-field-full"><label for="ya-enquiry-message">Message</label><textarea id="ya-enquiry-message" placeholder="Tell us about your travel plans, fitness, dates or any special requests."></textarea></div>
          <div class="ya-enquiry-alert" id="ya-enquiry-alert" role="status" aria-live="polite"></div>
          <div class="ya-field ya-field-full ya-enquiry-actions">
            <button class="ya-btn ya-btn-primary" type="submit" data-enquiry-action="whatsapp">Send enquiry and continue to WhatsApp</button>
            <p class="ya-enquiry-note">Your enquiry is saved before WhatsApp opens.</p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Write Your Review Modal (z-index: 1000000) -->
  <div class="ya-review-modal" id="ya-write-review-modal" role="dialog" aria-modal="true" aria-labelledby="ya-write-review-title" aria-hidden="true">
    <div class="ya-review-modal-dialog" role="document">
      <button class="ya-review-modal-close" type="button" aria-label="Close review form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>

      <div class="ya-rev-modal-left">
        <?php
        $modal_cover_img = ! empty( $hero_slider_images[0] ) ? $hero_slider_images[0] : ( ! empty( $hero_bg_img ) ? $hero_bg_img : '' );
        if ( $modal_cover_img ) :
        ?>
          <img src="<?php echo esc_url( $modal_cover_img ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="ya-rev-modal-img" loading="lazy">
        <?php endif; ?>
        <div>
          <span class="ya-rev-modal-kicker"><?php echo esc_html( $breadcrumb_region ?: 'HIMALAYAN EXPEDITION' ); ?></span>
          <h3 class="ya-rev-modal-dest-title"><?php echo esc_html( $title ); ?></h3>
          <p class="ya-rev-modal-dest-desc">
            Your authentic experiences and trail insights help fellow adventurers prepare for their journey. All reviews are checked for authenticity.
          </p>
        </div>
      </div>

      <div class="ya-rev-modal-right">
        <div class="ya-rev-modal-header">
          <span class="ya-kicker">COMMUNITY STORIES</span>
          <h2 id="ya-write-review-title">Write Your Review</h2>
          <p>Share your authentic experiences from the trail.</p>
        </div>

        <div class="ya-rev-modal-alert" id="ya-rev-modal-alert" role="status" aria-live="polite"></div>

        <form id="ya-modal-review-form" novalidate>
          <input type="hidden" id="ya-modal-rev-trek-id" value="<?php echo esc_attr( $post_id ); ?>">
          <input type="hidden" id="ya-modal-rev-nonce" value="<?php echo esc_attr( wp_create_nonce( 'trek_review_nonce' ) ); ?>">

          <div class="ya-star-rating-wrap">
            <span class="ya-star-rating-label">Overall Rating *</span>
            <div class="ya-star-select" role="radiogroup" aria-label="Star Rating">
              <input type="radio" id="ya-star-5" name="ya-modal-rating" value="5" checked>
              <label for="ya-star-5" title="5 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
              <input type="radio" id="ya-star-4" name="ya-modal-rating" value="4">
              <label for="ya-star-4" title="4 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
              <input type="radio" id="ya-star-3" name="ya-modal-rating" value="3">
              <label for="ya-star-3" title="3 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
              <input type="radio" id="ya-star-2" name="ya-modal-rating" value="2">
              <label for="ya-star-2" title="2 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
              <input type="radio" id="ya-star-1" name="ya-modal-rating" value="1">
              <label for="ya-star-1" title="1 star"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
            </div>
          </div>

          <div class="ya-review-form-grid">
            <div class="ya-field ya-field-full">
              <label for="ya-modal-rev-title">Review Title *</label>
              <input type="text" id="ya-modal-rev-title" required placeholder="e.g. Unforgettable journey through the Himalayas">
            </div>

            <div class="ya-field">
              <label for="ya-modal-rev-type">Traveler Type</label>
              <select id="ya-modal-rev-type">
                <option value="Solo Traveler">Solo Traveler</option>
                <option value="Couples Trek">Couples Trek</option>
                <option value="Small Group">Small Group</option>
                <option value="Family Trek">Family Trek</option>
                <option value="Guided Expedition">Guided Expedition</option>
              </select>
            </div>

            <div class="ya-field">
              <label for="ya-modal-rev-date">Tour Date / Season</label>
              <input type="text" id="ya-modal-rev-date" placeholder="e.g. October 2025">
            </div>

            <div class="ya-field">
              <label for="ya-modal-rev-name">Your Full Name *</label>
              <input type="text" id="ya-modal-rev-name" required placeholder="e.g. Marcus Vance">
            </div>

            <div class="ya-field">
              <label for="ya-modal-rev-email">Email Address *</label>
              <input type="email" id="ya-modal-rev-email" required placeholder="name@domain.com">
            </div>

            <div class="ya-field ya-field-full">
              <label for="ya-modal-rev-location">Location / Country</label>
              <input type="text" id="ya-modal-rev-location" placeholder="e.g. Melbourne, Australia">
            </div>

            <div class="ya-field ya-field-full">
              <label for="ya-modal-rev-comment">Your Story / Review *</label>
              <textarea id="ya-modal-rev-comment" required placeholder="Share your experience regarding the route, accommodation, acclimatization, and overall expedition..."></textarea>
            </div>

            <div class="ya-field ya-field-full" style="margin-top:6px;">
              <button type="submit" class="ya-rev-submit-btn" id="ya-modal-rev-submit">
                <span>Submit Review for Moderation</span>
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
              </button>
            </div>
          </div>
        </form>

        <div class="ya-rev-modal-success" id="ya-rev-modal-success" role="status" aria-live="polite">
          <div class="ya-rev-success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <h3>Review Submitted!</h3>
          <p>Thank you for taking the time to share your trail story with our community.</p>
          <div class="ya-rev-success-note">
            <strong>Pending Moderation:</strong> To ensure honest and verified traveler experiences, your review will be published once reviewed by our expedition team.
          </div>
          <button type="button" class="ya-rev-success-close-btn" id="ya-rev-success-close">Close Window</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    (()=>{
      const root = document.querySelector('.ya-ebc-page');
      if (!root) return;

      const restUrl = '<?php echo esc_url_raw( rest_url( 'trek-manager/v1/' ) ); ?>';
      const whatsappTarget = '<?php echo esc_js( preg_replace( '/[^0-9]/', '', $whatsapp_number ) ); ?>';
      const salesEmailTarget = '<?php echo esc_js( $sales_email ); ?>';
      const tripTitle = '<?php echo esc_js( $title ); ?>';

      const getValue = selector => root.querySelector(selector)?.value?.trim() || '';

      // Planning enquiry modal: save every enquiry to WordPress, with WhatsApp as an optional follow-up.
      const enquiryModal = document.querySelector('#ya-enquiry-modal');
      const enquiryForm = root.querySelector('#ya-enquiry-form');
      const enquiryAlert = root.querySelector('#ya-enquiry-alert');

      const openEnquiryModal = (options = {}) => {
        if (!enquiryModal) return;
        enquiryModal.classList.add('is-open');
        enquiryModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (options.email && root.querySelector('#ya-enquiry-email')) {
          root.querySelector('#ya-enquiry-email').value = options.email;
        }
        if (options.message && root.querySelector('#ya-enquiry-message')) {
          root.querySelector('#ya-enquiry-message').value = options.message;
        }

        window.setTimeout(() => root.querySelector('#ya-enquiry-name')?.focus(), 50);
      };

      const closeEnquiryModal = () => {
        if (!enquiryModal) return;
        enquiryModal.classList.remove('is-open');
        enquiryModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      };

      window.yaOpenEnquiry = openEnquiryModal;
      window.yaCloseEnquiry = closeEnquiryModal;

      // Delegated click handler: opens modal reliably from any trigger button across the entire page
      document.addEventListener('click', event => {
        const trigger = event.target.closest('#ya-open-enquiry-modal, .ya-open-enquiry-modal, [data-open-enquiry], .ya-dep-book-btn, .ya-heli-btn');
        if (trigger) {
          event.preventDefault();
          const depDate = trigger.dataset.depDate || trigger.getAttribute('data-dep-date');
          const email = trigger.dataset.email || '';
          let message = '';
          if (depDate) {
            message = `Interested in departure date: ${depDate}`;
          }
          openEnquiryModal({ email, message });
        }
      });

      enquiryModal?.querySelector('.ya-enquiry-close')?.addEventListener('click', closeEnquiryModal);
      enquiryModal?.addEventListener('click', event => { if (event.target === enquiryModal) closeEnquiryModal(); });
      document.addEventListener('keydown', event => { if (event.key === 'Escape' && enquiryModal?.classList.contains('is-open')) closeEnquiryModal(); });

      const validateEnquiryForm = () => {
        if (!enquiryForm) return false;
        const fields = [...enquiryForm.querySelectorAll('[required]')];
        let firstInvalidField;

        fields.forEach(field => {
          const valid = field.checkValidity();
          field.setAttribute('aria-invalid', String(!valid));
          if (!valid && !firstInvalidField) firstInvalidField = field;
        });

        if (!firstInvalidField) return true;
        const label = firstInvalidField.closest('.ya-field')?.querySelector('label')?.textContent.replace('*', '').trim() || 'required field';
        if (enquiryAlert) {
          enquiryAlert.textContent = `Please enter a valid ${label}.`;
          enquiryAlert.className = 'ya-enquiry-alert is-error';
        }
        firstInvalidField.focus();
        return false;
      };

      enquiryForm?.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', () => {
          if (field.checkValidity()) field.setAttribute('aria-invalid', 'false');
        });
        field.addEventListener('change', () => {
          if (field.checkValidity()) field.setAttribute('aria-invalid', 'false');
        });
      });

      enquiryForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!validateEnquiryForm()) return;
        const submitter = event.submitter;
        const action = submitter?.dataset.enquiryAction || 'save';
        const submitButtons = [...enquiryForm.querySelectorAll('button[type="submit"]')];
        const payload = {
          trek_id: root.querySelector('#ya-enquiry-trek-id')?.value || '',
          name: getValue('#ya-enquiry-name'),
          email: getValue('#ya-enquiry-email'),
          phone: getValue('#ya-enquiry-phone'),
          travellers: getValue('#ya-enquiry-people'),
          message: getValue('#ya-enquiry-message') || 'No additional message provided.',
          website_hp: root.querySelector('#ya-enquiry-website')?.value || '',
          source: 'single-destination-planning-popup'
        };
        if (enquiryAlert) enquiryAlert.className = 'ya-enquiry-alert';
        submitButtons.forEach(button => button.disabled = true);
        const originalText = submitter?.textContent;
        if (submitter) submitter.textContent = 'Saving your enquiry…';
        try {
          const response = await fetch(`${restUrl}enquiries`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
          const data = await response.json();
          if (!response.ok || !data.success) throw new Error(data.message || 'We could not save your enquiry. Please try again.');
          if (enquiryAlert) {
            enquiryAlert.textContent = 'Thank you — your enquiry has been saved. Our team will be in touch shortly.';
            enquiryAlert.className = 'ya-enquiry-alert is-success';
          }
          enquiryForm.reset();
          if (action === 'whatsapp' && whatsappTarget) {
            const whatsappMessage = [
              `Hello Yeti Adventure, I would like to plan ${tripTitle}.`,
              `Name: ${payload.name}`,
              `Travellers: ${payload.travellers}`,
              `Email: ${payload.email}`,
              payload.phone ? `Phone / WhatsApp: ${payload.phone}` : '',
              `Message: ${payload.message}`
            ].filter(Boolean).join('\n');
            window.location.assign(`https://wa.me/${whatsappTarget}?text=${encodeURIComponent(whatsappMessage)}`);
          }
        } catch (error) {
          if (enquiryAlert) {
            enquiryAlert.textContent = error.message || 'We could not save your enquiry. Please try again.';
            enquiryAlert.className = 'ya-enquiry-alert is-error';
          }
        } finally {
          submitButtons.forEach(button => button.disabled = false);
          if (submitter && originalText) submitter.textContent = originalText;
        }
      });

      // 1. Hero background slideshow — image order comes from the destination Hero Slider Gallery.
      const heroSlides = <?php echo wp_json_encode( $hero_slider_images ); ?>;
      const heroBackgrounds = [...root.querySelectorAll('.ya-hero-background-slide')];
      const heroDots = [...root.querySelectorAll('.ya-hero-slider-dot')];
      const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
      if (heroSlides.length > 1 && heroBackgrounds.length === 2) {
        let heroSlideIndex = 0;
        let activeBackground = 0;
        let heroTimer;
        const updateHeroDots = () => {
          heroDots.forEach((dot, index) => {
            const active = index === heroSlideIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-current', String(active));
          });
        };
        const showHeroSlide = nextIndex => {
          const targetIndex = typeof nextIndex === 'number'
            ? nextIndex
            : (heroSlideIndex + 1) % heroSlides.length;
          if (targetIndex === heroSlideIndex) return;
          const nextBackground = activeBackground === 0 ? 1 : 0;
          const outgoingBackground = heroBackgrounds[activeBackground];
          const incomingBackground = heroBackgrounds[nextBackground];
          heroSlideIndex = targetIndex;
          incomingBackground.classList.remove('is-active', 'is-exiting');
          incomingBackground.style.backgroundImage = `url(${JSON.stringify(heroSlides[heroSlideIndex])})`;
          void incomingBackground.offsetWidth;
          incomingBackground.classList.add('is-active');
          outgoingBackground.classList.remove('is-active');
          outgoingBackground.classList.add('is-exiting');
          window.setTimeout(() => outgoingBackground.classList.remove('is-exiting'), 1250);
          activeBackground = nextBackground;
          updateHeroDots();
        };
        const startHeroTimer = () => {
          window.clearInterval(heroTimer);
          if (!reduceMotion) heroTimer = window.setInterval(showHeroSlide, 4000);
        };
        heroDots.forEach(dot => dot.addEventListener('click', () => {
          showHeroSlide(Number(dot.dataset.heroSlide));
          startHeroTimer();
        }));
        startHeroTimer();
        document.addEventListener('visibilitychange', () => {
          window.clearInterval(heroTimer);
          if (!document.hidden) startHeroTimer();
        });
      }

      // 2. Reveal on scroll animations
      const revealItems = root.querySelectorAll('.ya-reveal');
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });
        revealItems.forEach((item, index) => {
          item.style.transitionDelay = `${Math.min(index * 35, 210)}ms`;
          observer.observe(item);
        });
      } else {
        revealItems.forEach(item => item.classList.add('is-visible'));
      }

      // 2. Tab navigation
      const tabs = [...root.querySelectorAll('.ya-tab')];
      const panels = [...root.querySelectorAll('.ya-panel')];
      const tabsList = root.querySelector('.ya-tabs');
      const tabStage = root.querySelector('.ya-tab-stage');
      const mobileTabs = window.matchMedia('(max-width: 1100px)');

      // On mobile, place each panel directly below its matching navigation row.
      // Desktop restores every panel to the main reading column.
      const syncMobileTabLayout = () => {
        if (!tabsList || !tabStage) return;

        if (mobileTabs.matches) {
          tabsList.removeAttribute('role');
          panels.forEach(panel => {
            const tab = tabs.find(item => item.dataset.tab === panel.dataset.panel);
            if (tab && panel.previousElementSibling !== tab) tab.insertAdjacentElement('afterend', panel);
          });
          // On mobile: Overview open by default, all others closed
          panels.forEach(panel => {
            const isOverview = panel.dataset.panel === 'overview';
            panel.classList.toggle('is-active', isOverview);
          });
          tabs.forEach(tab => {
            const isOverview = tab.dataset.tab === 'overview' && !tab.dataset.scrollTarget;
            tab.classList.toggle('is-active', isOverview);
            tab.setAttribute('aria-selected', String(isOverview));
          });
        } else {
          tabsList.setAttribute('role', 'tablist');
          panels.forEach(panel => tabStage.appendChild(panel));
        }
      };

      syncMobileTabLayout();
      if (mobileTabs.addEventListener) {
        mobileTabs.addEventListener('change', syncMobileTabLayout);
      } else {
        mobileTabs.addListener(syncMobileTabLayout);
      }

      const setActiveTab = name => {
        const targetTab = tabs.find(tab => tab.dataset.tab === name);
        const targetPanel = panels.find(panel => panel.dataset.panel === name);
        if (!targetTab) return;

        tabs.forEach(tab => {
          const active = tab === targetTab;
          tab.classList.toggle('is-active', active);
          tab.setAttribute('aria-selected', String(active));
        });
        if (targetPanel) {
          panels.forEach(panel => panel.classList.toggle('is-active', panel === targetPanel));
        }
        return { targetTab, targetPanel };
      };

      const activateTab = (name, scroll = true, clickedTab = null) => {
        const targetTab = clickedTab || tabs.find(tab => tab.dataset.tab === name);
        if (!targetTab) return;

        const active = setActiveTab(name);
        if (!active) return;
        const { targetPanel } = active;
        if (!mobileTabs.matches) {
          targetTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
        if (scroll && !mobileTabs.matches && targetPanel) {
          const scrollTarget = targetTab.dataset.scrollTarget ? document.querySelector(targetTab.dataset.scrollTarget) : null;
          if (scrollTarget) {
            scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
          } else {
            targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      };

      tabs.forEach(tab => tab.addEventListener('click', () => {
        if (mobileTabs.matches) {
          // Accordion behaviour on mobile: toggle the panel, don't close others
          const targetPanel = panels.find(p => p.dataset.panel === tab.dataset.tab);
          if (!targetPanel) return;
          const isNowOpen = !targetPanel.classList.contains('is-active');
          targetPanel.classList.toggle('is-active', isNowOpen);
          tab.classList.toggle('is-active', isNowOpen);
          tab.setAttribute('aria-selected', String(isNowOpen));
          if (isNowOpen) {
            // Smooth scroll to the tab header
            setTimeout(() => tab.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
          }
        } else {
          activateTab(tab.dataset.tab, true, tab);
        }
      }));
      root.querySelectorAll('.ya-jump-tab').forEach(button => button.addEventListener('click', () => activateTab(button.dataset.target, true)));

      // Keep the visual navigation state aligned with the visible long-form section.
      if ('IntersectionObserver' in window) {
        const sectionObserver = new IntersectionObserver(entries => {
          const visible = entries
            .filter(entry => entry.isIntersecting)
            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
          if (visible?.target?.dataset?.panel) {
            setActiveTab(visible.target.dataset.panel);
          }
        }, { rootMargin: '-18% 0px -65% 0px', threshold: 0.01 });
        panels.forEach(panel => sectionObserver.observe(panel));
      }

      // Reading progress for the long-form content, shown directly below the fixed left navigation.
      const readingProgress = root.querySelector('.ya-reading-progress-bar');
      if (readingProgress && panels.length) {
        let progressFrame;
        const updateReadingProgress = () => {
          progressFrame = undefined;
          const firstPanelTop = panels[0].getBoundingClientRect().top + window.scrollY;
          const lastPanelBottom = panels[panels.length - 1].getBoundingClientRect().bottom + window.scrollY;
          const viewportMarker = window.scrollY + window.innerHeight * .45;
          const distance = Math.max(1, lastPanelBottom - firstPanelTop);
          const progress = Math.min(100, Math.max(0, ((viewportMarker - firstPanelTop) / distance) * 100));
          readingProgress.style.setProperty('--ya-reading-progress', `${progress}%`);
        };
        const requestProgressUpdate = () => {
          if (!progressFrame) progressFrame = window.requestAnimationFrame(updateReadingProgress);
        };
        window.addEventListener('scroll', requestProgressUpdate, { passive: true });
        window.addEventListener('resize', requestProgressUpdate);
        requestProgressUpdate();
      }

      // 3. Day-by-Day Interactive Image Carousels (.yt-day-gallery)
      root.querySelectorAll('[data-day-gallery]').forEach(gallery => {
        const slides  = [...gallery.querySelectorAll('.yt-day-carousel-slide')];
        const dots    = [...gallery.querySelectorAll('.yt-gallery-dot')];
        const prevBtn = gallery.querySelector('.yt-gallery-prev');
        const nextBtn = gallery.querySelector('.yt-gallery-next');
        const counter = gallery.querySelector('.yt-gallery-counter');
        const total   = slides.length;
        if (total <= 1) return;

        let cur = 0;

        const goTo = idx => {
          slides[cur].classList.remove('is-active');
          dots[cur]?.classList.remove('is-active');
          cur = (idx + total) % total;
          slides[cur].classList.add('is-active');
          dots[cur]?.classList.add('is-active');
          if (counter) counter.textContent = `${cur + 1} / ${total}`;
        };

        prevBtn?.addEventListener('click', e => { e.stopPropagation(); goTo(cur - 1); });
        nextBtn?.addEventListener('click', e => { e.stopPropagation(); goTo(cur + 1); });

        // Dot click support
        dots.forEach((dot, dIdx) => {
          dot.addEventListener('click', e => {
            e.stopPropagation();
            goTo(dIdx);
          });
        });

        // Keyboard navigation when gallery is focused
        gallery.setAttribute('tabindex', '0');
        gallery.addEventListener('keydown', e => {
          if (e.key === 'ArrowLeft')  { e.preventDefault(); goTo(cur - 1); }
          if (e.key === 'ArrowRight') { e.preventDefault(); goTo(cur + 1); }
        });

        // Touch / swipe support
        let touchStartX = 0;
        gallery.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
        gallery.addEventListener('touchend', e => {
          const dx = e.changedTouches[0].clientX - touchStartX;
          if (Math.abs(dx) > 40) goTo(dx < 0 ? cur + 1 : cur - 1);
        }, { passive: true });
      });

      // 4. Essential Info Collapsible Accordion
      root.querySelectorAll('.ya-info-head').forEach(button => {
        button.addEventListener('click', () => {
          const item = button.closest('.ya-info-item');
          if (!item) return;
          const open = item.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(open));
        });
      });



      // 6. Travel Reviews: Modal, Validation & AJAX Submission
      const reviewModal = document.querySelector('#ya-write-review-modal');
      const openReviewBtns = root.querySelectorAll('#ya-open-review-modal, [data-open-review-modal], .ya-write-review-btn');
      const closeReviewBtn = reviewModal?.querySelector('.ya-review-modal-close');
      const successCloseBtn = reviewModal?.querySelector('#ya-rev-success-close');
      const reviewFormModal = reviewModal?.querySelector('#ya-modal-review-form');
      const reviewAlert = reviewModal?.querySelector('#ya-rev-modal-alert');
      const reviewSuccess = reviewModal?.querySelector('#ya-rev-modal-success');
      const ajaxUrl = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';

      const openRevModal = () => {
        if (!reviewModal) return;
        // Reset form & success state on open
        if (reviewSuccess) reviewSuccess.classList.remove('is-visible');
        if (reviewFormModal) reviewFormModal.style.display = '';
        if (reviewAlert) {
          reviewAlert.style.display = 'none';
          reviewAlert.className = 'ya-rev-modal-alert';
          reviewAlert.textContent = '';
        }
        reviewModal.classList.add('is-open');
        reviewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(() => reviewModal.querySelector('#ya-modal-rev-title')?.focus(), 80);
      };

      const closeRevModal = () => {
        if (!reviewModal) return;
        reviewModal.classList.remove('is-open');
        reviewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      };

      openReviewBtns.forEach(btn => {
        btn.addEventListener('click', e => {
          e.preventDefault();
          openRevModal();
        });
      });

      closeReviewBtn?.addEventListener('click', closeRevModal);
      successCloseBtn?.addEventListener('click', closeRevModal);

      reviewModal?.addEventListener('click', e => {
        if (e.target === reviewModal) closeRevModal();
      });

      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && reviewModal?.classList.contains('is-open')) closeRevModal();
      });

      // Submit Review Form via WordPress AJAX
      reviewFormModal?.addEventListener('submit', async event => {
        event.preventDefault();
        const submitBtn = reviewModal.querySelector('#ya-modal-rev-submit');
        const rating = reviewModal.querySelector('input[name="ya-modal-rating"]:checked')?.value || '5';
        const titleVal = reviewModal.querySelector('#ya-modal-rev-title')?.value?.trim() || '';
        const nameVal = reviewModal.querySelector('#ya-modal-rev-name')?.value?.trim() || '';
        const emailVal = reviewModal.querySelector('#ya-modal-rev-email')?.value?.trim() || '';
        const locVal = reviewModal.querySelector('#ya-modal-rev-location')?.value?.trim() || '';
        const typeVal = reviewModal.querySelector('#ya-modal-rev-type')?.value?.trim() || '';
        const commentVal = reviewModal.querySelector('#ya-modal-rev-comment')?.value?.trim() || '';
        const dateVal = reviewModal.querySelector('#ya-modal-rev-date')?.value?.trim() || '';
        const trekId = reviewModal.querySelector('#ya-modal-rev-trek-id')?.value || '';
        const nonce = reviewModal.querySelector('#ya-modal-rev-nonce')?.value || '';

        // Validation: Required fields
        if (!titleVal || !nameVal || !emailVal || !commentVal) {
          if (reviewAlert) {
            reviewAlert.className = 'ya-rev-modal-alert is-error';
            reviewAlert.textContent = 'Please complete all required fields (Review Title, Your Name, Email Address, and Review).';
            reviewAlert.style.display = 'block';
          }
          if (!titleVal) reviewModal.querySelector('#ya-modal-rev-title')?.focus();
          else if (!nameVal) reviewModal.querySelector('#ya-modal-rev-name')?.focus();
          else if (!emailVal) reviewModal.querySelector('#ya-modal-rev-email')?.focus();
          else reviewModal.querySelector('#ya-modal-rev-comment')?.focus();
          return;
        }

        // Validation: Email format
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailVal)) {
          if (reviewAlert) {
            reviewAlert.className = 'ya-rev-modal-alert is-error';
            reviewAlert.textContent = 'Please enter a valid email address (e.g. name@example.com).';
            reviewAlert.style.display = 'block';
          }
          reviewModal.querySelector('#ya-modal-rev-email')?.focus();
          return;
        }

        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span>Submitting review…</span>';
        }
        if (reviewAlert) reviewAlert.style.display = 'none';

        const formData = new FormData();
        formData.append('action', 'ya_submit_trek_review');
        formData.append('security', nonce);
        formData.append('trek_id', trekId);
        formData.append('title', titleVal);
        formData.append('name', nameVal);
        formData.append('email', emailVal);
        formData.append('rating', rating);
        formData.append('traveler_type', typeVal);
        formData.append('city', locVal);
        formData.append('tour_date', dateVal);
        formData.append('comment', commentVal);

        try {
          const response = await fetch(ajaxUrl, {
            method: 'POST',
            body: formData
          });
          const result = await response.json();

          if (result.success) {
            // Switch to success view (do NOT auto-add card since review is pending moderation)
            reviewFormModal.style.display = 'none';
            reviewFormModal.reset();
            if (reviewSuccess) {
              reviewSuccess.classList.add('is-visible');
            }
          } else {
            throw new Error(result.data?.message || result.message || 'We could not submit your review. Please try again.');
          }
        } catch (err) {
          if (reviewAlert) {
            reviewAlert.className = 'ya-rev-modal-alert is-error';
            reviewAlert.textContent = err.message || 'An error occurred while submitting your review. Please try again.';
            reviewAlert.style.display = 'block';
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Submit Review for Moderation</span><svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
          }
        }
      });

      // 7. Load More Reviews Button
      root.querySelector('#ya-load-more-reviews')?.addEventListener('click', function() {
        const hiddenCards = root.querySelectorAll('.ya-review-hidden');
        hiddenCards.forEach(card => {
          card.classList.remove('ya-review-hidden');
          card.classList.add('is-visible');
        });
        const parentWrap = this.closest('.ya-rev-load-more-wrap');
        if (parentWrap) parentWrap.style.display = 'none';
      });
    })();
  </script>
</section>

<?php if ( ! $is_shortcode ) : ?>
  <?php get_footer(); ?>
<?php endif; ?>
