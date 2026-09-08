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

// 3. Quick Facts
$fact_high_point     = get_post_meta( $post_id, '_trek_fact_high_point', true ) ?: '3,870m · Kyanjin Gompa';
$fact_region         = get_post_meta( $post_id, '_trek_fact_region', true ) ?: ( $breadcrumb_region );
$fact_trekking       = get_post_meta( $post_id, '_trek_fact_trekking', true ) ?: 'Usually 5–6 hours';
$fact_accom          = get_post_meta( $post_id, '_trek_fact_accom', true ) ?: 'Hotel & local lodges';
$fact_style          = get_post_meta( $post_id, '_trek_fact_style', true ) ?: 'High-altitude trek';

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
            $overview_content = "<p>The journey through the mountain homelands to the heart of {$title} deserves its reputation as one of the great Himalayan treks. It is a serious undertaking, rewarded with remarkable scenery, deeply rooted local culture and the grandeur of the high peaks.</p>\n<p>Most trekking days are around five to six hours, with some longer days of seven to eight hours. The itinerary gains altitude steadily and includes acclimatisation days so the journey can be approached at a measured pace.</p>";
        }
    }
}

$overview_highlight  = get_post_meta( $post_id, '_trek_overview_highlight', true ) ?: ( "A demanding Himalayan journey defined by altitude, changing landscapes and the unforgettable approach to {$title}." );

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
$route_caption_sub   = get_post_meta( $post_id, '_trek_route_caption_sub', true ) ?: 'Route Map';
$route_caption_h3    = get_post_meta( $post_id, '_trek_route_caption_h3', true ) ?: ( 'Kathmandu to ' . $title );

// 5. Itinerary
$itin_kicker         = get_post_meta( $post_id, '_trek_itin_kicker', true ) ?: 'Day by Day';
$itin_script         = get_post_meta( $post_id, '_trek_itin_script', true ) ?: 'Your Journey';
$itin_heading        = get_post_meta( $post_id, '_trek_itin_heading', true ) ?: ( $hero_days . ' Days of Discovery' );
$itin_subtext        = get_post_meta( $post_id, '_trek_itin_subtext', true ) ?: 'Open each day to view the walking plan, meals, accommodation and approximate duration.';

$side_script         = get_post_meta( $post_id, '_trek_itin_side_script', true ) ?: ( $hero_days . ' Days' );
$side_title          = get_post_meta( $post_id, '_trek_itin_side_title', true ) ?: ( 'Kathmandu to ' . $script_title . ' and Back' );
$side_desc           = get_post_meta( $post_id, '_trek_itin_side_desc', true ) ?: 'The route includes acclimatisation days and a gradual ascent before reaching key high-altitude landmarks and viewpoints.';
$side_points         = get_post_meta( $post_id, '_trek_itin_side_points', true );
if ( empty( $side_points ) || ! is_array( $side_points ) ) {
    $side_points = array(
        '2 nights in Kathmandu before the trek',
        'Acclimatisation and rest days scheduled for safety',
        "{$title} landmark and scenic excursion",
        'Sunrise ascent to high viewpoints'
    );
}

$itinerary           = get_post_meta( $post_id, '_trek_itinerary_list', true ) ?: array();
if ( empty( $itinerary ) ) {
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

// 7. Extensions
$ext_photo           = get_post_meta( $post_id, '_trek_ext_photo', true ) ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1400&q=88';
$ext_kicker          = get_post_meta( $post_id, '_trek_ext_kicker', true ) ?: 'Make It Yours';
$ext_script          = get_post_meta( $post_id, '_trek_ext_script', true ) ?: 'Stay a Little Longer';
$ext_heading         = get_post_meta( $post_id, '_trek_ext_heading', true ) ?: ( 'Add another side of ' . $breadcrumb_region . ' to your trek' );
$ext_intro           = get_post_meta( $post_id, '_trek_ext_intro', true );
if ( empty( $ext_intro ) ) {
    $ext_intro = "<p>The {$hero_days}-day itinerary can be adjusted around your available time, interests and preferred pace. Extension ideas below are examples only and can be customized with our travel specialists.</p>\n<p>Extra recovery time after the trek is often valuable, while cultural or wildlife extensions can add a completely different perspective to the journey.</p>";
}
$ext_btn_text        = get_post_meta( $post_id, '_trek_ext_btn_text', true ) ?: 'Plan a Bespoke Extension';
$ext_btn_url         = get_post_meta( $post_id, '_trek_ext_btn_url', true ) ?: ( home_url( '/bespoke-trips/' ) );
$extensions          = get_post_meta( $post_id, '_trek_extensions_list', true ) ?: array();
if ( empty( $extensions ) ) {
    $extensions = array(
        array( 'title' => 'Kathmandu Heritage', 'desc' => 'Add guided visits to the Kathmandu Valley’s UNESCO historic squares, sacred temples, stupas and artisan neighbourhoods.', 'suggested' => 'Suggested: 1–2 extra days' ),
        array( 'title' => 'Pokhara Lake Relaxation', 'desc' => 'Slow the pace beside serene Phewa Lake, enjoy mountain views and recover comfortably after your trek.', 'suggested' => 'Suggested: 2–3 extra days' ),
        array( 'title' => 'Chitwan Jungle Safari', 'desc' => 'Exchange high mountains for Nepal’s subtropical lowlands, dense sal forests, rhinos, and wildlife activities.', 'suggested' => 'Suggested: 2–3 extra days' )
    );
}

// 8. Essential Info
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
    'posts_per_page' => 10,
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

$display_reviews = array();
if ( $db_reviews_query->have_posts() ) {
    while ( $db_reviews_query->have_posts() ) {
        $db_reviews_query->the_post();
        $rid = get_the_ID();
        $city = get_post_meta( $rid, '_trek_city', true );
        $country = get_post_meta( $rid, '_trek_country', true );
        $loc = array_filter( array( $city, $country ) );
        $display_reviews[] = array(
            'name'     => get_the_title(),
            'location' => ! empty( $loc ) ? implode( ', ', $loc ) : 'Verified Traveller',
            'rating'   => intval( get_post_meta( $rid, '_trek_rating', true ) ?: 5 ),
            'quote'    => get_the_content(),
        );
    }
    wp_reset_postdata();
}

// Fallback to curated reviews if no database reviews exist yet
if ( empty( $display_reviews ) ) {
    $curated = get_post_meta( $post_id, '_trek_curated_reviews', true );
    if ( ! empty( $curated ) && is_array( $curated ) ) {
        $display_reviews = $curated;
    } else {
        $display_reviews = array(
            array(
                'name'     => 'Jo & Laurie Prowse',
                'location' => 'London, United Kingdom',
                'rating'   => '5',
                'quote'    => 'They praised their trek manager’s professionalism and said the team made the mountain journey enjoyable from beginning to end.'
            ),
            array(
                'name'     => 'Leslie & Cheyenne',
                'location' => 'New York, United States',
                'rating'   => '5',
                'quote'    => 'They described a strong connection with their guide and remembered the scenery, calm atmosphere and physical challenge of the trek.'
            )
        );
    }
}

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
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

    html:has(.ya-ebc-page),
    body:has(.ya-ebc-page){
      margin:0!important;
      padding:0!important;
      overflow-x:hidden!important;
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
    }

    .ya-ebc-page,
    .ya-ebc-page *{box-sizing:border-box}

    .ya-ebc-page{
      --green:#022f2e;
      --green-2:#045857;
      --green-3:#0d6d69;
      --gold:#c6a45f;
      --ivory:#f7f3ea;
      --cream:#fcfaf5;
      --ink:#173331;
      --muted:#667775;
      --line:#dfe7e4;
      --shadow:0 24px 70px rgba(14,49,47,.14);
      width:100vw;
      max-width:100vw;
      margin:-1px 0 0 calc(50% - 50vw)!important;
      padding:0!important;
      overflow:hidden;
      background:#fff;
      color:var(--ink);
      font-family:Inter,Arial,sans-serif;
      line-height:1.6;
      position:relative;
      z-index:1;
    }

    .ya-ebc-page button,
    .ya-ebc-page input,
    .ya-ebc-page select,
    .ya-ebc-page textarea{font:inherit}

    .ya-ebc-page a{color:inherit}
    .ya-ebc-page img{display:block;max-width:100%}

    .ya-ebc-page .ya-inner{
      width:min(1220px,calc(100% - 48px));
      margin:0 auto;
      position:relative;
    }

    .ya-ebc-page .ya-script{
      font-family:"Cormorant Garamond",serif;
      font-weight:600;
      letter-spacing:-.02em;
    }

    .ya-ebc-page .ya-serif{font-family:"Cormorant Garamond",serif}

    .ya-ebc-page .ya-kicker{
      display:inline-flex;
      align-items:center;
      gap:10px;
      color:var(--gold);
      font-size:11px;
      font-weight:800;
      letter-spacing:2.6px;
      text-transform:uppercase;
    }

    .ya-ebc-page .ya-kicker:before{
      content:"";
      width:34px;
      height:1px;
      background:currentColor;
    }

    .ya-ebc-page .ya-btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      min-height:52px;
      padding:0 27px;
      border:1px solid transparent;
      border-radius:999px;
      text-decoration:none;
      font-size:11px;
      font-weight:800;
      letter-spacing:1.5px;
      text-transform:uppercase;
      cursor:pointer;
      transition:transform .3s ease,background .3s ease,color .3s ease,border-color .3s ease;
    }

    .ya-ebc-page .ya-btn svg{width:17px;height:17px;flex:0 0 auto}
    .ya-ebc-page .ya-btn:hover{transform:translateY(-3px)}
    .ya-ebc-page .ya-btn-primary{background:var(--green-2);color:#fff}
    .ya-ebc-page .ya-btn-primary:hover{background:var(--green);color:#fff}
    .ya-ebc-page .ya-btn-light{background:#fff;color:var(--green)}
    .ya-ebc-page .ya-btn-light:hover{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-btn-outline{border-color:rgba(255,255,255,.72);background:transparent;color:#fff}
    .ya-ebc-page .ya-btn-outline:hover{background:#fff;color:var(--green)}

    .ya-ebc-page .ya-reveal{
      opacity:0;
      transform:translateY(28px);
      transition:opacity .75s ease,transform .75s ease;
    }

    .ya-ebc-page .ya-reveal.is-visible{opacity:1;transform:none}

    /* HERO */
    .ya-ebc-page .ya-hero{
      min-height:100vh;
      min-height:100svh;
      display:flex;
      align-items:flex-end;
      position:relative;
      isolation:isolate;
      padding:160px 0 96px;
      background-image:url("<?php echo esc_url( $hero_bg_img ); ?>");
      background-position:center;
      background-size:cover;
      background-repeat:no-repeat;
    }

    .ya-ebc-page .ya-hero:before{
      content:"";
      position:absolute;
      inset:0;
      z-index:-1;
      background:rgba(2,35,34,.46);
    }

    .ya-ebc-page .ya-hero-content{
      width:min(920px,100%);
      color:#fff;
    }

    .ya-ebc-page .ya-breadcrumbs{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      gap:8px;
      margin-bottom:25px;
      font-size:11px;
      font-weight:700;
      letter-spacing:1.1px;
      text-transform:uppercase;
      color:rgba(255,255,255,.78);
    }

    .ya-ebc-page .ya-breadcrumbs a{text-decoration:none}
    .ya-ebc-page .ya-breadcrumbs a:hover{color:#fff}
    .ya-ebc-page .ya-breadcrumbs svg{width:13px;height:13px}

    .ya-ebc-page .ya-hero .ya-script{
      margin-bottom:10px;
      font-size:clamp(42px,5vw,72px);
      line-height:.9;
      color:#fff;
      text-shadow:0 14px 40px rgba(0,0,0,.25);
    }

    .ya-ebc-page .ya-hero h1{
      max-width:930px;
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(44px,6.5vw,92px);
      line-height:.9;
      font-weight:600;
      color:#fff;
    }

    .ya-ebc-page .ya-hero-subtitle{
      max-width:760px;
      margin:25px 0 30px;
      font-size:16px;
      line-height:1.8;
      color:rgba(255,255,255,.9);
    }

    .ya-ebc-page .ya-hero-actions{display:flex;flex-wrap:wrap;gap:12px}

    .ya-ebc-page .ya-hero-badge{
      position:absolute;
      right:0;
      bottom:0;
      width:190px;
      height:190px;
      display:grid;
      place-items:center;
      border-radius:50% 50% 0 50%;
      background:var(--green);
      color:#fff;
      text-align:center;
      box-shadow:0 24px 60px rgba(0,0,0,.2);
    }

    .ya-ebc-page .ya-hero-badge strong{
      display:block;
      font-family:"Cormorant Garamond",serif;
      font-size:54px;
      line-height:.8;
      color:var(--gold);
    }

    .ya-ebc-page .ya-hero-badge span{
      display:block;
      margin-top:8px;
      font-size:10px;
      font-weight:800;
      letter-spacing:1.6px;
      text-transform:uppercase;
    }

    /* QUICK FACTS */
    .ya-ebc-page .ya-facts{
      position:relative;
      z-index:5;
      margin-top:-42px;
    }

    .ya-ebc-page .ya-facts-grid{
      display:grid;
      grid-template-columns:repeat(5,1fr);
      background:#fff;
      border-radius:24px;
      box-shadow:var(--shadow);
      overflow:hidden;
    }

    .ya-ebc-page .ya-fact{
      min-height:116px;
      display:flex;
      align-items:center;
      gap:14px;
      padding:24px;
      border-right:1px solid var(--line);
    }

    .ya-ebc-page .ya-fact:last-child{border-right:0}
    .ya-ebc-page .ya-fact-icon{
      width:45px;
      height:45px;
      display:grid;
      place-items:center;
      flex:0 0 45px;
      border-radius:50%;
      background:var(--ivory);
      color:var(--green-2);
    }

    .ya-ebc-page .ya-fact-icon svg{width:20px;height:20px}
    .ya-ebc-page .ya-fact small{
      display:block;
      margin-bottom:3px;
      color:#879391;
      font-size:9px;
      font-weight:800;
      letter-spacing:1.4px;
      text-transform:uppercase;
    }
    .ya-ebc-page .ya-fact strong{font-size:13px;color:var(--green)}

    /* TABS */
    .ya-ebc-page .ya-tabs-wrap{
      position:sticky;
      top:78px;
      z-index:80;
      margin-top:46px;
      background:#fff;
      border-top:1px solid var(--line);
      border-bottom:1px solid var(--line);
      box-shadow:0 10px 30px rgba(15,48,46,.06);
    }

    .ya-ebc-page .ya-tabs{
      display:flex;
      align-items:stretch;
      overflow-x:auto;
      scrollbar-width:none;
    }

    .ya-ebc-page .ya-tabs::-webkit-scrollbar{display:none}

    .ya-ebc-page .ya-tab{
      position:relative;
      min-width:max-content;
      min-height:70px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      padding:0 25px;
      border:0;
      background:#fff;
      color:#586a68;
      font-size:10px;
      font-weight:800;
      letter-spacing:1.35px;
      text-transform:uppercase;
      cursor:pointer;
      transition:background .25s ease,color .25s ease;
    }

    .ya-ebc-page .ya-tab svg{width:17px;height:17px}
    .ya-ebc-page .ya-tab:hover{background:var(--cream);color:var(--green)}
    .ya-ebc-page .ya-tab.is-active{background:var(--green);color:#fff}

    /* TAB CONTENT */
    .ya-ebc-page .ya-tab-stage{background:#fff}
    .ya-ebc-page .ya-panel{display:none;padding:105px 0 118px}
    .ya-ebc-page .ya-panel.is-active{display:block;animation:yaPanelIn .5s ease both}
    @keyframes yaPanelIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

    .ya-ebc-page .ya-section-heading{
      max-width:850px;
      margin-bottom:46px;
    }

    .ya-ebc-page .ya-section-heading .ya-script{
      margin:8px 0 12px;
      font-size:clamp(32px,4vw,48px);
      line-height:1;
      color:#3b5150;
    }

    .ya-ebc-page .ya-section-heading h2{
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(44px,5vw,76px);
      line-height:.92;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-section-heading p{
      max-width:720px;
      margin:19px 0 0;
      color:var(--muted);
      font-size:15.5px;
      line-height:1.85;
    }

    /* OVERVIEW */
    .ya-ebc-page .ya-overview-grid{
      display:grid;
      grid-template-columns:1.08fr .92fr;
      gap:74px;
      align-items:start;
    }

    .ya-ebc-page .ya-overview-copy h3{
      margin:0 0 24px;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(38px,4vw,60px);
      line-height:.98;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-overview-copy p{
      margin:0 0 20px;
      color:#546865;
      font-size:16px;
      line-height:1.9;
    }

    .ya-ebc-page .ya-highlight-box{
      margin:30px 0;
      padding:27px 28px;
      border-left:3px solid var(--gold);
      background:var(--ivory);
      font-family:"Cormorant Garamond",serif;
      font-size:24px;
      line-height:1.34;
      color:var(--green);
    }

    .ya-ebc-page .ya-overview-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
    .ya-ebc-page .ya-btn-secondary{border-color:var(--green-2);background:#fff;color:var(--green-2)}
    .ya-ebc-page .ya-btn-secondary:hover{background:var(--green-2);color:#fff}

    .ya-ebc-page .ya-map-card{
      position:sticky;
      top:180px;
      overflow:hidden;
      border-radius:30px;
      background:var(--cream);
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-map-image{
      position:relative;
      height:560px;
      overflow:hidden;
      background:#eef3ef;
    }

    .ya-ebc-page .ya-map-image img{
      width:100%;
      height:100%;
      object-fit:cover;
      object-position:center;
    }

    .ya-ebc-page .ya-map-image button{
      position:absolute;
      right:18px;
      bottom:18px;
      width:52px;
      height:52px;
      display:grid;
      place-items:center;
      border:0;
      border-radius:50%;
      background:#fff;
      color:var(--green);
      box-shadow:0 12px 34px rgba(0,0,0,.18);
      cursor:pointer;
    }

    .ya-ebc-page .ya-map-image button svg{width:21px;height:21px}
    .ya-ebc-page .ya-map-caption{padding:25px 27px}
    .ya-ebc-page .ya-map-caption small{
      display:block;
      margin-bottom:5px;
      color:var(--gold);
      font-size:9px;
      font-weight:800;
      letter-spacing:1.6px;
      text-transform:uppercase;
    }
    .ya-ebc-page .ya-map-caption h3{
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:31px;
      color:var(--green);
    }

    .ya-ebc-page .ya-inclusions{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:15px;
      margin-top:38px;
    }

    .ya-ebc-page .ya-inclusion{
      display:flex;
      align-items:flex-start;
      gap:12px;
      padding:17px;
      border:1px solid var(--line);
      border-radius:16px;
      background:#fff;
    }

    .ya-ebc-page .ya-inclusion svg{width:18px;height:18px;flex:0 0 18px;margin-top:2px;color:var(--green-2)}
    .ya-ebc-page .ya-inclusion span{font-size:13px;line-height:1.55;color:#526462}

    /* ITINERARY */
    .ya-ebc-page .ya-itinerary-shell{
      display:grid;
      grid-template-columns:310px 1fr;
      gap:42px;
      align-items:start;
    }

    .ya-ebc-page .ya-itinerary-side{
      position:sticky;
      top:178px;
      padding:30px;
      border-radius:26px;
      background:var(--green);
      color:#fff;
    }

    .ya-ebc-page .ya-itinerary-side .ya-script{
      font-size:36px;
      line-height:1;
      color:#fff;
    }

    .ya-ebc-page .ya-itinerary-side h3{
      margin:14px 0 16px;
      font-family:"Cormorant Garamond",serif;
      font-size:38px;
      line-height:1;
      color:#fff;
    }

    .ya-ebc-page .ya-itinerary-side p{margin:0 0 24px;color:rgba(255,255,255,.72);font-size:13.5px}
    .ya-ebc-page .ya-itinerary-side ul{list-style:none;margin:0;padding:0;display:grid;gap:12px}
    .ya-ebc-page .ya-itinerary-side li{display:flex;align-items:center;gap:10px;font-size:12px;color:rgba(255,255,255,.9)}
    .ya-ebc-page .ya-itinerary-side li svg{width:16px;height:16px;color:var(--gold);flex:0 0 16px}

    .ya-ebc-page .ya-days{display:grid;gap:13px}
    .ya-ebc-page .ya-day{
      overflow:hidden;
      border:1px solid var(--line);
      border-radius:20px;
      background:#fff;
      box-shadow:0 12px 34px rgba(18,52,50,.05);
    }

    .ya-ebc-page .ya-day-head{
      width:100%;
      display:grid;
      grid-template-columns:72px minmax(0,1fr) auto;
      align-items:center;
      gap:20px;
      padding:21px 24px;
      border:0;
      background:#fff;
      color:var(--green);
      text-align:left;
      cursor:pointer;
    }

    .ya-ebc-page .ya-day-number{
      width:58px;
      height:58px;
      display:grid;
      place-items:center;
      border-radius:18px;
      background:var(--ivory);
      font-family:"Cormorant Garamond",serif;
      font-size:26px;
      font-weight:700;
      color:var(--green-2);
    }

    .ya-ebc-page .ya-day-title small{
      display:block;
      margin-bottom:4px;
      color:var(--gold);
      font-size:9px;
      font-weight:800;
      letter-spacing:1.4px;
      text-transform:uppercase;
    }

    .ya-ebc-page .ya-day-title strong{
      display:block;
      font-family:"Cormorant Garamond",serif;
      font-size:25px;
      line-height:1.1;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-day-toggle{
      width:42px;
      height:42px;
      display:grid;
      place-items:center;
      border-radius:50%;
      background:var(--green);
      color:#fff;
      transition:transform .3s ease,background .3s ease;
    }

    .ya-ebc-page .ya-day-toggle svg{width:18px;height:18px;transition:transform .3s ease}
    .ya-ebc-page .ya-day.is-open .ya-day-toggle{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-day.is-open .ya-day-toggle svg{transform:rotate(45deg)}

    .ya-ebc-page .ya-day-body{
      display:grid;
      grid-template-rows:0fr;
      transition:grid-template-rows .4s ease;
    }

    .ya-ebc-page .ya-day.is-open .ya-day-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-day-body-inner{overflow:hidden}
    .ya-ebc-page .ya-day-content{padding:0 25px 26px 116px}
    .ya-ebc-page .ya-day-content p{margin:0 0 18px;color:#5b6c69;font-size:14px;line-height:1.85}

    .ya-ebc-page .ya-day-meta{
      display:flex;
      flex-wrap:wrap;
      gap:9px;
    }

    .ya-ebc-page .ya-meta-pill{
      display:inline-flex;
      align-items:center;
      gap:7px;
      min-height:34px;
      padding:0 12px;
      border-radius:999px;
      background:var(--ivory);
      color:#4d605e;
      font-size:10px;
      font-weight:700;
    }

    .ya-ebc-page .ya-meta-pill svg{width:14px;height:14px;color:var(--green-2)}

    /* GALLERY */
    .ya-ebc-page .ya-gallery-grid{
      display:grid;
      grid-template-columns:1.2fr .8fr .8fr;
      grid-template-rows:330px 330px;
      gap:16px;
    }

    .ya-ebc-page .ya-gallery-item{
      position:relative;
      overflow:hidden;
      border:0;
      border-radius:24px;
      padding:0;
      background:#e8efed;
      cursor:pointer;
    }

    .ya-ebc-page .ya-gallery-item:first-child{grid-row:1/3}
    .ya-ebc-page .ya-gallery-item:nth-child(4){grid-column:2/4}
    .ya-ebc-page .ya-gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease}
    .ya-ebc-page .ya-gallery-item:hover img{transform:scale(1.055)}
    .ya-ebc-page .ya-gallery-label{
      position:absolute;
      left:18px;
      right:18px;
      bottom:18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      padding:14px 16px;
      border-radius:15px;
      background:rgba(2,47,46,.88);
      color:#fff;
      text-align:left;
    }

    .ya-ebc-page .ya-gallery-label strong{font-family:"Cormorant Garamond",serif;font-size:21px;font-weight:600}
    .ya-ebc-page .ya-gallery-label svg{width:18px;height:18px;flex:0 0 auto}

    /* EXTENSION */
    .ya-ebc-page .ya-extension-intro{
      display:grid;
      grid-template-columns:.9fr 1.1fr;
      gap:64px;
      align-items:center;
      margin-bottom:54px;
    }

    .ya-ebc-page .ya-extension-photo{
      min-height:500px;
      border-radius:180px 180px 28px 28px;
      background:url("<?php echo esc_url( $ext_photo ); ?>") center/cover no-repeat;
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-extension-copy h3{
      margin:14px 0 20px;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(43px,5vw,70px);
      line-height:.92;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-extension-copy p{margin:0 0 18px;color:var(--muted);font-size:15.5px;line-height:1.85}

    .ya-ebc-page .ya-extension-grid{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:18px;
    }

    .ya-ebc-page .ya-extension-card{
      min-height:300px;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      padding:30px;
      border:1px solid var(--line);
      border-radius:24px;
      background:#fff;
      box-shadow:0 15px 42px rgba(16,50,48,.06);
    }

    .ya-ebc-page .ya-extension-icon{
      width:52px;
      height:52px;
      display:grid;
      place-items:center;
      border-radius:50%;
      background:var(--ivory);
      color:var(--green-2);
    }
    .ya-ebc-page .ya-extension-icon svg{width:22px;height:22px}
    .ya-ebc-page .ya-extension-card h4{margin:26px 0 10px;font-family:"Cormorant Garamond",serif;font-size:31px;line-height:1;color:var(--green)}
    .ya-ebc-page .ya-extension-card p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.75}
    .ya-ebc-page .ya-extension-card small{display:block;margin-top:20px;color:var(--gold);font-size:9px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase}

    /* ESSENTIAL INFO */
    .ya-ebc-page .ya-essential-layout{
      display:grid;
      grid-template-columns:1fr 380px;
      gap:42px;
      align-items:start;
    }

    .ya-ebc-page .ya-info-list{display:grid;gap:13px}
    .ya-ebc-page .ya-info-item{border:1px solid var(--line);border-radius:19px;overflow:hidden;background:#fff}
    .ya-ebc-page .ya-info-head{
      width:100%;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:20px;
      padding:21px 23px;
      border:0;
      background:#fff;
      color:var(--green);
      text-align:left;
      cursor:pointer;
    }
    .ya-ebc-page .ya-info-head span{display:flex;align-items:center;gap:13px;font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:600}
    .ya-ebc-page .ya-info-head span svg{width:21px;height:21px;color:var(--green-2)}
    .ya-ebc-page .ya-info-head>svg{width:18px;height:18px;transition:transform .3s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-head>svg{transform:rotate(45deg)}
    .ya-ebc-page .ya-info-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-info-body>div{overflow:hidden}
    .ya-ebc-page .ya-info-content{padding:0 24px 24px 57px;color:#5b6d6a;font-size:14px;line-height:1.85}
    .ya-ebc-page .ya-info-content p{margin:0 0 13px}
    .ya-ebc-page .ya-info-content ul{margin:0;padding-left:19px}
    .ya-ebc-page .ya-info-content li{margin:6px 0}

    .ya-ebc-page .ya-trip-note-card{
      position:sticky;
      top:180px;
      overflow:hidden;
      border-radius:28px;
      background:var(--green);
      color:#fff;
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-trip-note-image{
      height:230px;
      background:url("<?php echo esc_url( $card_img ); ?>") center/cover no-repeat;
    }

    .ya-ebc-page .ya-trip-note-copy{padding:29px}
    .ya-ebc-page .ya-trip-note-copy small{color:var(--gold);font-size:9px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}
    .ya-ebc-page .ya-trip-note-copy h3{margin:10px 0 14px;font-family:"Cormorant Garamond",serif;font-size:36px;line-height:1;color:#fff}
    .ya-ebc-page .ya-trip-note-copy p{margin:0 0 22px;color:rgba(255,255,255,.72);font-size:13.5px;line-height:1.75}

    /* REVIEWS */
    .ya-ebc-page .ya-review-layout{
      display:grid;
      grid-template-columns:.95fr 1.05fr;
      gap:54px;
      align-items:start;
    }

    .ya-ebc-page .ya-review-cards{display:grid;gap:17px}
    .ya-ebc-page .ya-review-card{
      padding:28px;
      border:1px solid var(--line);
      border-radius:24px;
      background:#fff;
      box-shadow:0 14px 38px rgba(16,50,48,.06);
    }

    .ya-ebc-page .ya-stars{display:flex;gap:3px;margin-bottom:16px;color:var(--gold)}
    .ya-ebc-page .ya-stars svg{width:17px;height:17px;fill:currentColor}
    .ya-ebc-page .ya-review-card blockquote{margin:0 0 20px;font-family:"Cormorant Garamond",serif;font-size:25px;line-height:1.35;color:var(--green)}
    .ya-ebc-page .ya-review-card strong{display:block;font-size:12px;color:var(--green)}
    .ya-ebc-page .ya-review-card small{color:#80908e;font-size:10px}

    .ya-ebc-page .ya-review-form{
      padding:34px;
      border-radius:28px;
      background:var(--cream);
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-review-form h3{margin:0 0 8px;font-family:"Cormorant Garamond",serif;font-size:39px;line-height:1;color:var(--green)}
    .ya-ebc-page .ya-review-form>p{margin:0 0 25px;color:var(--muted);font-size:13.5px}
    .ya-ebc-page .ya-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}
    .ya-ebc-page .ya-field{display:grid;gap:7px}
    .ya-ebc-page .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-field label{font-size:9px;font-weight:800;letter-spacing:1.15px;text-transform:uppercase;color:#415855}
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select,
    .ya-ebc-page .ya-field textarea{
      width:100%;
      border:1px solid #d7e0de;
      border-radius:13px;
      background:#fff;
      color:var(--ink);
      outline:none;
      box-sizing:border-box;
    }
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select{height:50px;padding:0 14px}
    .ya-ebc-page .ya-field textarea{min-height:120px;padding:13px 14px;resize:vertical}
    .ya-ebc-page .ya-field input:focus,
    .ya-ebc-page .ya-field select:focus,
    .ya-ebc-page .ya-field textarea:focus{border-color:var(--green-2);box-shadow:0 0 0 4px rgba(4,88,87,.1)}

    .ya-ebc-page .ya-rating-row{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px}
    .ya-ebc-page .ya-rating-row input{position:absolute;opacity:0;pointer-events:none}
    .ya-ebc-page .ya-rating-row label{cursor:pointer;color:#cbd5d2}
    .ya-ebc-page .ya-rating-row label svg{width:28px;height:28px;fill:currentColor}
    .ya-ebc-page .ya-rating-row label:hover,
    .ya-ebc-page .ya-rating-row label:hover~label,
    .ya-ebc-page .ya-rating-row input:checked~label{color:var(--gold)}

    .ya-review-alert{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:13px;display:none}
    .ya-review-alert--success{background:#e6f6ec;color:#0a7a3b;border:1px solid #a3e6be}
    .ya-review-alert--error{background:#fde8e8;color:#9b1c1c;border:1px solid #f8b4b4}

    /* DEPARTURES */
    .ya-ebc-page .ya-departure-box{
      display:grid;
      grid-template-columns:1fr .85fr;
      overflow:hidden;
      border-radius:32px;
      background:var(--green);
      color:#fff;
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-departure-copy{padding:54px}
    .ya-ebc-page .ya-departure-copy .ya-script{font-size:42px;line-height:1;color:#fff}
    .ya-ebc-page .ya-departure-copy h3{margin:19px 0 18px;font-family:"Cormorant Garamond",serif;font-size:52px;line-height:.94;color:#fff}
    .ya-ebc-page .ya-departure-copy p{margin:0 0 27px;color:rgba(255,255,255,.72);font-size:14.5px;line-height:1.8}
    .ya-ebc-page .ya-departure-form{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .ya-ebc-page .ya-departure-form .ya-field label{color:rgba(255,255,255,.72)}
    .ya-ebc-page .ya-departure-form .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-departure-photo{
      min-height:570px;
      background:url("<?php echo esc_url( $box_photo ); ?>") center/cover no-repeat;
    }

    .ya-ebc-page .ya-departure-note{
      margin-top:18px;
      padding:17px 18px;
      border:1px solid rgba(255,255,255,.16);
      border-radius:15px;
      background:rgba(255,255,255,.07);
      color:rgba(255,255,255,.73);
      font-size:11.5px;
      line-height:1.65;
    }

    /* RELATED */
    .ya-ebc-page .ya-related{
      padding:115px 0 125px;
      background:var(--cream);
    }

    .ya-ebc-page .ya-related-head{
      display:flex;
      align-items:end;
      justify-content:space-between;
      gap:30px;
      margin-bottom:42px;
    }

    .ya-ebc-page .ya-related-head .ya-script{font-size:42px;line-height:1;color:#3d5250}
    .ya-ebc-page .ya-related-head h2{margin:13px 0 0;font-family:"Cormorant Garamond",serif;font-size:59px;line-height:.92;color:var(--green)}
    .ya-ebc-page .ya-related-head p{max-width:460px;margin:0;color:var(--muted);font-size:14px;line-height:1.75}

    .ya-ebc-page .ya-related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:17px}
    .ya-ebc-page .ya-related-card{overflow:hidden;border-radius:24px;background:#fff;text-decoration:none;box-shadow:0 15px 42px rgba(16,50,48,.08);transition:transform .35s ease}
    .ya-ebc-page .ya-related-card:hover{transform:translateY(-8px)}
    .ya-ebc-page .ya-related-image{height:245px;overflow:hidden}
    .ya-ebc-page .ya-related-image img{width:100%;height:100%;object-fit:cover;transition:transform .65s ease}
    .ya-ebc-page .ya-related-card:hover img{transform:scale(1.06)}
    .ya-ebc-page .ya-related-copy{padding:23px}
    .ya-ebc-page .ya-related-copy small{display:block;margin-bottom:9px;color:var(--gold);font-size:9px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase}
    .ya-ebc-page .ya-related-copy h3{margin:0 0 18px;font-family:"Cormorant Garamond",serif;font-size:29px;line-height:1.05;font-weight:600;color:var(--green)}
    .ya-ebc-page .ya-related-link{display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--line);font-size:9px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--green-2)}
    .ya-ebc-page .ya-related-link svg{width:17px;height:17px}

    /* LIGHTBOX */
    .ya-ebc-page .ya-lightbox{
      position:fixed;
      inset:0;
      z-index:100000;
      display:none;
      align-items:center;
      justify-content:center;
      padding:34px;
      background:rgba(1,24,23,.94);
    }
    .ya-ebc-page .ya-lightbox.is-open{display:flex}
    .ya-ebc-page .ya-lightbox img{max-width:min(1100px,94vw);max-height:86vh;border-radius:18px;box-shadow:0 30px 100px rgba(0,0,0,.45)}
    .ya-ebc-page .ya-lightbox-close{
      position:absolute;
      top:22px;
      right:22px;
      width:48px;
      height:48px;
      display:grid;
      place-items:center;
      border:1px solid rgba(255,255,255,.3);
      border-radius:50%;
      background:rgba(255,255,255,.08);
      color:#fff;
      cursor:pointer;
    }
    .ya-ebc-page .ya-lightbox-close svg{width:21px;height:21px}

    @media(max-width:1100px){
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(3,1fr)}
      .ya-ebc-page .ya-fact:nth-child(3){border-right:0}
      .ya-ebc-page .ya-fact:nth-child(n+4){border-top:1px solid var(--line)}
      .ya-ebc-page .ya-overview-grid,
      .ya-ebc-page .ya-extension-intro,
      .ya-ebc-page .ya-review-layout,
      .ya-ebc-page .ya-departure-box{grid-template-columns:1fr}
      .ya-ebc-page .ya-map-card,
      .ya-ebc-page .ya-trip-note-card{position:relative;top:auto}
      .ya-ebc-page .ya-map-image{height:600px}
      .ya-ebc-page .ya-itinerary-shell{grid-template-columns:260px 1fr;gap:28px}
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr 330px}
      .ya-ebc-page .ya-extension-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-related-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-departure-photo{min-height:430px;order:-1}
      .ya-ebc-page .ya-hero-badge{right:24px}
    }

    @media(max-width:820px){
      .ya-ebc-page .ya-inner{width:min(100% - 30px,1220px)}
      .ya-ebc-page .ya-hero{min-height:92vh;min-height:92svh;padding:135px 0 78px;background-position:62% center}
      .ya-ebc-page .ya-hero .ya-script{font-size:48px}
      .ya-ebc-page .ya-hero h1{font-size:48px}
      .ya-ebc-page .ya-hero-subtitle{font-size:14px}
      .ya-ebc-page .ya-hero-badge{display:none}
      .ya-ebc-page .ya-facts{margin-top:-30px}
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-fact{min-height:96px;padding:17px;border-top:1px solid var(--line);border-right:1px solid var(--line)!important}
      .ya-ebc-page .ya-fact:nth-child(odd){border-right:1px solid var(--line)!important}
      .ya-ebc-page .ya-fact:nth-child(even){border-right:0!important}
      .ya-ebc-page .ya-fact:first-child,
      .ya-ebc-page .ya-fact:nth-child(2){border-top:0}
      .ya-ebc-page .ya-fact:last-child{grid-column:1/-1;border-right:0!important}
      .ya-ebc-page .ya-tabs-wrap{top:68px;margin-top:32px}
      .ya-ebc-page .ya-tab{min-height:60px;padding:0 18px}
      .ya-ebc-page .ya-panel{padding:78px 0 88px}
      .ya-ebc-page .ya-overview-grid,
      .ya-ebc-page .ya-itinerary-shell,
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr}
      .ya-ebc-page .ya-itinerary-side{position:relative;top:auto}
      .ya-ebc-page .ya-gallery-grid{grid-template-columns:1fr 1fr;grid-template-rows:360px 260px 260px}
      .ya-ebc-page .ya-gallery-item:first-child{grid-column:1/3;grid-row:auto}
      .ya-ebc-page .ya-gallery-item:nth-child(4){grid-column:auto}
      .ya-ebc-page .ya-extension-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-extension-photo{min-height:430px}
      .ya-ebc-page .ya-related-head{display:block}
      .ya-ebc-page .ya-related-head p{margin-top:18px}
      .ya-ebc-page .ya-departure-copy{padding:35px 25px}
    }

    @media(max-width:560px){
      .ya-ebc-page .ya-hero-actions{display:grid}
      .ya-ebc-page .ya-btn{width:100%}
      .ya-ebc-page .ya-hero h1{font-size:40px}
      .ya-ebc-page .ya-hero .ya-script{font-size:42px}
      .ya-ebc-page .ya-breadcrumbs{font-size:9px}
      .ya-ebc-page .ya-fact{gap:10px;padding:13px}
      .ya-ebc-page .ya-fact-icon{width:38px;height:38px;flex-basis:38px}
      .ya-ebc-page .ya-fact strong{font-size:11px}
      .ya-ebc-page .ya-section-heading .ya-script{font-size:34px}
      .ya-ebc-page .ya-section-heading h2{font-size:42px}
      .ya-ebc-page .ya-inclusions{grid-template-columns:1fr}
      .ya-ebc-page .ya-map-image{height:470px}
      .ya-ebc-page .ya-day-head{grid-template-columns:54px minmax(0,1fr) 38px;gap:12px;padding:16px}
      .ya-ebc-page .ya-day-number{width:48px;height:48px;border-radius:14px;font-size:22px}
      .ya-ebc-page .ya-day-title strong{font-size:20px}
      .ya-ebc-page .ya-day-toggle{width:36px;height:36px}
      .ya-ebc-page .ya-day-content{padding:0 17px 20px 17px}
      .ya-ebc-page .ya-gallery-grid{display:grid;grid-template-columns:1fr;grid-template-rows:none}
      .ya-ebc-page .ya-gallery-item,
      .ya-ebc-page .ya-gallery-item:first-child{grid-column:auto;grid-row:auto;height:300px}
      .ya-ebc-page .ya-extension-photo{min-height:360px;border-radius:120px 120px 25px 25px}
      .ya-ebc-page .ya-info-content{padding:0 19px 22px}
      .ya-ebc-page .ya-review-form{padding:25px 18px}
      .ya-ebc-page .ya-form-grid,
      .ya-ebc-page .ya-departure-form{grid-template-columns:1fr}
      .ya-ebc-page .ya-field-full,
      .ya-ebc-page .ya-departure-form .ya-field-full{grid-column:auto}
      .ya-ebc-page .ya-related-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-related-head .ya-script{font-size:36px}
      .ya-ebc-page .ya-related-head h2{font-size:43px}
      .ya-ebc-page .ya-lightbox{padding:18px}
    }

    @media(prefers-reduced-motion:reduce){
      .ya-ebc-page *{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}
      .ya-ebc-page .ya-reveal{opacity:1;transform:none}
    }
  </style>

  <!-- 1. Hero Section -->
  <div class="ya-hero">
    <div class="ya-inner">
      <div class="ya-hero-content ya-reveal">
        <div class="ya-breadcrumbs" aria-label="Breadcrumb">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
          <a href="<?php echo esc_url( home_url( '/trips/' ) ); ?>">All-Trips</a>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
          <a href="<?php echo esc_url( home_url( '/' . strtolower( sanitize_title( $breadcrumb_region ) ) . '/' ) ); ?>"><?php echo esc_html( $breadcrumb_region ); ?></a>
        </div>
        <div class="ya-script"><?php echo esc_html( $script_title ); ?></div>
        <h1><?php echo esc_html( $hero_title ); ?></h1>
        <p class="ya-hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <div class="ya-hero-actions">
          <button class="ya-btn ya-btn-light ya-jump-tab" type="button" data-target="itinerary">View Itinerary</button>
          <button class="ya-btn ya-btn-outline ya-jump-tab" type="button" data-target="fixed-departure">Check Departures</button>
        </div>
      </div>
      <div class="ya-hero-badge" aria-hidden="true">
        <div><strong><?php echo esc_html( $hero_days ); ?></strong><span><?php echo esc_html( $hero_badge_sub ); ?></span></div>
      </div>
    </div>
  </div>

  <!-- 2. Quick Facts Grid -->
  <div class="ya-facts">
    <div class="ya-inner ya-facts-grid ya-reveal">
      <div class="ya-fact">
        <span class="ya-fact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20L10 6l4 8 2-4 4 10H4z"/></svg></span>
        <div><small>Highest Point</small><strong><?php echo esc_html( $fact_high_point ); ?></strong></div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg></span>
        <div><small>Destination</small><strong><?php echo esc_html( $fact_region ); ?></strong></div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <div><small>Daily Walking</small><strong><?php echo esc_html( $fact_trekking ); ?></strong></div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 18V8l6-4 6 4v10"/><path d="M4 20h16M9 20v-6h6v6"/></svg></span>
        <div><small>Accommodation</small><strong><?php echo esc_html( $fact_accom ); ?></strong></div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 13h16M7 13v7m10-7v7M6 9h12l-1-5H7L6 9z"/></svg></span>
        <div><small>Trip Style</small><strong><?php echo esc_html( $fact_style ); ?></strong></div>
      </div>
    </div>
  </div>

  <!-- 3. Sticky Navigation Tabs -->
  <nav class="ya-tabs-wrap" aria-label="Trip information tabs">
    <div class="ya-inner ya-tabs" role="tablist">
      <button class="ya-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="ya-panel-overview" data-tab="overview">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h8"/></svg>Overview
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-itinerary" data-tab="itinerary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h12v16H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>Itinerary
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-gallery" data-tab="gallery">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 15l-5-5L5 20"/></svg>Gallery
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-extension" data-tab="extension">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M3 12h18"/></svg>Extension
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-essential-info" data-tab="essential-info">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>Essential Info
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-review" data-tab="review">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H8l-4 3V5z"/></svg>Review
      </button>
      <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-fixed-departure" data-tab="fixed-departure">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>Join Fixed Departure
      </button>
    </div>
  </nav>

  <div class="ya-tab-stage">
    <!-- Panel 1: Overview -->
    <section class="ya-panel is-active" id="ya-panel-overview" role="tabpanel" data-panel="overview">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $overview_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $overview_script ); ?></div>
          <h2><?php echo esc_html( $overview_heading ); ?></h2>
        </div>

        <div class="ya-overview-grid">
          <div class="ya-overview-copy ya-reveal">
            <h3><?php echo esc_html( $overview_subheading ); ?></h3>
            <div class="ya-overview-text">
              <?php echo wp_kses_post( wpautop( $overview_content ) ); ?>
            </div>

            <div class="ya-highlight-box">
              <?php echo wp_kses_post( $overview_highlight ); ?>
            </div>

            <div class="ya-inclusions">
              <?php foreach ( $inclusions as $inc_point ) : ?>
                <div class="ya-inclusion">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
                  <span><?php echo esc_html( $inc_point ); ?></span>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="ya-overview-actions">
              <a class="ya-btn ya-btn-primary" href="mailto:<?php echo esc_attr( $sales_email ); ?>?subject=<?php echo rawurlencode( 'Request for ' . $title . ' Trip Notes' ); ?>">Request Trip Notes
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
              </a>
              <a class="ya-btn ya-btn-secondary" href="<?php echo esc_url( home_url( '/bespoke-trips/' ) ); ?>">Make Itinerary Yours</a>
            </div>
          </div>

          <div class="ya-map-card ya-reveal">
            <div class="ya-map-image">
              <img src="<?php echo esc_url( $route_img ); ?>" alt="<?php echo esc_attr( $title ); ?> route map" loading="lazy">
              <button type="button" class="ya-open-lightbox" data-image="<?php echo esc_url( $route_img ); ?>" aria-label="Open route map">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4M11 8v6M8 11h6"/></svg>
              </button>
            </div>
            <div class="ya-map-caption">
              <small><?php echo esc_html( $route_caption_sub ); ?></small>
              <h3><?php echo esc_html( $route_caption_h3 ); ?></h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Panel 2: Itinerary -->
    <section class="ya-panel" id="ya-panel-itinerary" role="tabpanel" data-panel="itinerary">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $itin_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $itin_script ); ?></div>
          <h2><?php echo esc_html( $itin_heading ); ?></h2>
          <p><?php echo esc_html( $itin_subtext ); ?></p>
        </div>

        <div class="ya-itinerary-shell">
          <aside class="ya-itinerary-side ya-reveal">
            <div class="ya-script"><?php echo esc_html( $side_script ); ?></div>
            <h3><?php echo esc_html( $side_title ); ?></h3>
            <p><?php echo esc_html( $side_desc ); ?></p>
            <ul>
              <?php foreach ( $side_points as $s_pt ) : ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l4 4L19 6"/></svg>
                  <?php echo esc_html( $s_pt ); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </aside>

          <div class="ya-days">
            <?php foreach ( $itinerary as $i_idx => $day ) : ?>
              <article class="ya-day <?php echo 0 === $i_idx ? 'is-open' : ''; ?>">
                <button class="ya-day-head" type="button" aria-expanded="<?php echo 0 === $i_idx ? 'true' : 'false'; ?>">
                  <span class="ya-day-number"><?php echo esc_html( $day['day'] ?? sprintf( '%02d', $i_idx + 1 ) ); ?></span>
                  <span class="ya-day-title">
                    <?php if ( ! empty( $day['stage'] ) ) : ?>
                      <small><?php echo esc_html( $day['stage'] ); ?></small>
                    <?php endif; ?>
                    <strong><?php echo esc_html( $day['title'] ?? '' ); ?></strong>
                  </span>
                  <span class="ya-day-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></span>
                </button>
                <div class="ya-day-body"><div class="ya-day-body-inner"><div class="ya-day-content">
                  <div class="ya-day-desc">
                    <?php echo wp_kses_post( wpautop( $day['desc'] ?? '' ) ); ?>
                  </div>
                  <?php 
                  if ( ! empty( $day['pills'] ) ) : 
                    $pills_array = array_filter( array_map( 'trim', explode( ',', $day['pills'] ) ) );
                    if ( ! empty( $pills_array ) ) :
                  ?>
                    <div class="ya-day-meta">
                      <?php foreach ( $pills_array as $pill ) : ?>
                        <span class="ya-meta-pill"><?php echo esc_html( $pill ); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php 
                    endif;
                  endif; 
                  ?>
                </div></div></div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <!-- Panel 3: Gallery -->
    <section class="ya-panel" id="ya-panel-gallery" role="tabpanel" data-panel="gallery">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $gal_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $gal_script ); ?></div>
          <h2><?php echo esc_html( $gal_heading ); ?></h2>
          <p><?php echo esc_html( $gal_subtext ); ?></p>
        </div>

        <div class="ya-gallery-grid ya-reveal">
          <?php foreach ( $gallery as $g_idx => $g_item ) : ?>
            <button class="ya-gallery-item ya-open-lightbox" type="button" data-image="<?php echo esc_url( $g_item['url'] ); ?>">
              <img src="<?php echo esc_url( $g_item['url'] ); ?>" alt="<?php echo esc_attr( $g_item['label'] ?? $title ); ?>" loading="lazy">
              <span class="ya-gallery-label">
                <strong><?php echo esc_html( $g_item['label'] ?? '' ); ?></strong>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
              </span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Panel 4: Extension -->
    <section class="ya-panel" id="ya-panel-extension" role="tabpanel" data-panel="extension">
      <div class="ya-inner">
        <div class="ya-extension-intro">
          <div class="ya-extension-photo ya-reveal" role="img" aria-label="<?php echo esc_attr( $title ); ?> extensions"></div>
          <div class="ya-extension-copy ya-reveal">
            <span class="ya-kicker"><?php echo esc_html( $ext_kicker ); ?></span>
            <div class="ya-script"><?php echo esc_html( $ext_script ); ?></div>
            <h3><?php echo esc_html( $ext_heading ); ?></h3>
            <div class="ya-ext-intro-text">
              <?php echo wp_kses_post( wpautop( $ext_intro ) ); ?>
            </div>
            <a class="ya-btn ya-btn-primary" href="<?php echo esc_url( $ext_btn_url ); ?>"><?php echo esc_html( $ext_btn_text ); ?></a>
          </div>
        </div>

        <div class="ya-extension-grid ya-reveal">
          <?php foreach ( $extensions as $e_item ) : ?>
            <div class="ya-extension-card">
              <div>
                <span class="ya-extension-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h16M6 20V9l6-5 6 5v11M9 12h6M9 16h6"/></svg>
                </span>
                <h4><?php echo esc_html( $e_item['title'] ?? '' ); ?></h4>
                <p><?php echo esc_html( $e_item['desc'] ?? '' ); ?></p>
              </div>
              <small><?php echo esc_html( $e_item['suggested'] ?? 'Suggested: 1–2 extra days' ); ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- Panel 5: Essential Info -->
    <section class="ya-panel" id="ya-panel-essential-info" role="tabpanel" data-panel="essential-info">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $ess_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $ess_script ); ?></div>
          <h2><?php echo esc_html( $ess_heading ); ?></h2>
          <div class="ya-ess-intro-p">
            <?php echo wp_kses_post( wpautop( $ess_intro ) ); ?>
          </div>
        </div>

        <div class="ya-essential-layout">
          <div class="ya-info-list ya-reveal">
            <?php foreach ( $accordions as $a_idx => $acc ) : ?>
              <article class="ya-info-item <?php echo 0 === $a_idx ? 'is-open' : ''; ?>">
                <button class="ya-info-head" type="button" aria-expanded="<?php echo 0 === $a_idx ? 'true' : 'false'; ?>">
                  <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                    <?php echo esc_html( $acc['title'] ?? '' ); ?>
                  </span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                </button>
                <div class="ya-info-body"><div><div class="ya-info-content">
                  <?php echo wp_kses_post( $acc['content'] ?? '' ); ?>
                </div></div></div>
              </article>
            <?php endforeach; ?>
          </div>

          <aside class="ya-trip-note-card ya-reveal">
            <div class="ya-trip-note-image" role="img" aria-label="<?php echo esc_attr( $card_title ); ?>"></div>
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

    <!-- Panel 6: Review -->
    <section class="ya-panel" id="ya-panel-review" role="tabpanel" data-panel="review">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $rev_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $rev_script ); ?></div>
          <h2><?php echo esc_html( $rev_heading ); ?></h2>
          <p><?php echo esc_html( $rev_subtext ); ?></p>
        </div>

        <div class="ya-review-layout">
          <div class="ya-review-cards ya-reveal" id="ya-review-cards-list">
            <?php foreach ( $display_reviews as $rev ) : 
              $star_count = intval( $rev['rating'] ?? 5 );
            ?>
              <article class="ya-review-card">
                <div class="ya-stars" aria-label="<?php echo esc_attr( $star_count ); ?> out of 5 stars">
                  <?php for ( $s = 0; $s < $star_count; $s++ ) : ?>
                    <svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>
                  <?php endfor; ?>
                </div>
                <blockquote><?php echo esc_html( $rev['quote'] ?? '' ); ?></blockquote>
                <strong><?php echo esc_html( $rev['name'] ?? 'Traveller' ); ?></strong>
                <small><?php echo esc_html( $rev['location'] ?? '' ); ?></small>
              </article>
            <?php endforeach; ?>
          </div>

          <form class="ya-review-form ya-reveal" id="ya-review-form">
            <h3>Write a Review</h3>
            <p>Your review will be saved to our database and shared with our team.</p>
            
            <div class="ya-review-alert ya-review-alert--success" id="ya-rev-alert-success"></div>
            <div class="ya-review-alert ya-review-alert--error" id="ya-rev-alert-error"></div>

            <input type="hidden" id="ya-review-trek-id" value="<?php echo esc_attr( $post_id ); ?>">
            <input type="hidden" id="ya-review-trek-title" value="<?php echo esc_attr( $title ); ?>">

            <div class="ya-form-grid">
              <div class="ya-field"><label for="ya-review-name">Full Name *</label><input id="ya-review-name" type="text" required placeholder="e.g. John Doe"></div>
              <div class="ya-field"><label for="ya-review-email">Email *</label><input id="ya-review-email" type="email" required placeholder="e.g. john@example.com"></div>
              <div class="ya-field"><label for="ya-review-city">City</label><input id="ya-review-city" type="text" placeholder="e.g. London"></div>
              <div class="ya-field"><label for="ya-review-country">Country</label><input id="ya-review-country" type="text" placeholder="e.g. United Kingdom"></div>
              <div class="ya-field"><label for="ya-review-date">Tour Date</label><input id="ya-review-date" type="month"></div>
              <div class="ya-field"><label>Rating *</label>
                <div class="ya-rating-row" aria-label="Rating from 1 to 5">
                  <input type="radio" id="ya-star5" name="ya-rating" value="5" checked required><label for="ya-star5" aria-label="5 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                  <input type="radio" id="ya-star4" name="ya-rating" value="4"><label for="ya-star4" aria-label="4 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                  <input type="radio" id="ya-star3" name="ya-rating" value="3"><label for="ya-star3" aria-label="3 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                  <input type="radio" id="ya-star2" name="ya-rating" value="2"><label for="ya-star2" aria-label="2 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                  <input type="radio" id="ya-star1" name="ya-rating" value="1"><label for="ya-star1" aria-label="1 star"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                </div>
              </div>
              <div class="ya-field ya-field-full"><label for="ya-review-comment">Comment *</label><textarea id="ya-review-comment" required placeholder="Share your experience on this trek..."></textarea></div>
              <div class="ya-field ya-field-full"><label for="ya-review-moment">Inspirational moment of your trip</label><textarea id="ya-review-moment" placeholder="What stood out most?"></textarea></div>
              <div class="ya-field ya-field-full"><label for="ya-review-leader">What did you think of your trek leader?</label><textarea id="ya-review-leader" placeholder="Feedback on guide and porters..."></textarea></div>
              <div class="ya-field ya-field-full"><label for="ya-review-advice">Advice for future travellers</label><textarea id="ya-review-advice" placeholder="Helpful tips for future trekkers..."></textarea></div>
              <div class="ya-field ya-field-full" style="display:flex; gap:12px; flex-wrap:wrap;">
                <button class="ya-btn ya-btn-primary" type="submit" id="ya-submit-rev-btn">Submit Review to Database</button>
                <button class="ya-btn ya-btn-secondary" type="button" id="ya-email-rev-btn">Send via Email</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Panel 7: Join Fixed Departure -->
    <section class="ya-panel" id="ya-panel-fixed-departure" role="tabpanel" data-panel="fixed-departure">
      <div class="ya-inner">
        <div class="ya-section-heading ya-reveal">
          <span class="ya-kicker"><?php echo esc_html( $dep_kicker ); ?></span>
          <div class="ya-script"><?php echo esc_html( $dep_script ); ?></div>
          <h2><?php echo esc_html( $dep_heading ); ?></h2>
          <p><?php echo esc_html( $dep_subtext ); ?></p>
        </div>

        <div class="ya-departure-box ya-reveal">
          <div class="ya-departure-copy">
            <div class="ya-script"><?php echo esc_html( $box_script ); ?></div>
            <h3><?php echo esc_html( $box_title ); ?></h3>
            <p><?php echo esc_html( $box_desc ); ?></p>

            <form class="ya-departure-form" id="ya-departure-form">
              <div class="ya-field">
                <label for="ya-departure-month">Preferred Month *</label>
                <select id="ya-departure-month" required>
                  <option value="" selected disabled>Select month</option>
                  <option>January</option><option>February</option><option>March</option>
                  <option>April</option><option>May</option><option>June</option>
                  <option>July</option><option>August</option><option>September</option>
                  <option>October</option><option>November</option><option>December</option>
                </select>
              </div>
              <div class="ya-field">
                <label for="ya-departure-year">Preferred Year *</label>
                <select id="ya-departure-year" required></select>
              </div>
              <div class="ya-field">
                <label for="ya-departure-name">Full Name *</label>
                <input id="ya-departure-name" type="text" required placeholder="Your full name">
              </div>
              <div class="ya-field">
                <label for="ya-departure-people">Number of Travellers *</label>
                <select id="ya-departure-people" required>
                  <option>1 traveller</option><option>2 travellers</option><option>3–5 travellers</option>
                  <option>6–10 travellers</option><option>10+ travellers</option>
                </select>
              </div>
              <div class="ya-field ya-field-full">
                <label for="ya-departure-email">Email *</label>
                <input id="ya-departure-email" type="email" required placeholder="your.email@example.com">
              </div>
              <div class="ya-field ya-field-full">
                <label for="ya-departure-message">Message</label>
                <textarea id="ya-departure-message" placeholder="Tell us about your preferred timing, fitness or trip requirements."></textarea>
              </div>
              <div class="ya-field ya-field-full">
                <button class="ya-btn ya-btn-light" type="submit">Send WhatsApp Enquiry</button>
              </div>
            </form>

            <div class="ya-departure-note">
              <?php echo esc_html( $box_note ); ?>
            </div>
          </div>
          <div class="ya-departure-photo" role="img" aria-label="<?php echo esc_attr( $title ); ?> departure"></div>
        </div>
      </div>
    </section>
  </div>

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
            $r_id = get_the_ID();
            $r_days = get_post_meta( $r_id, '_trek_hero_days', true ) ?: '12';
            $r_region = get_post_meta( $r_id, '_trek_breadcrumb_region', true ) ?: 'Nepal';
            $r_thumb = get_the_post_thumbnail_url( $r_id, 'large' ) ?: get_post_meta( $r_id, '_trek_hero_bg_img', true ) ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85';
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( get_permalink() ); ?>">
            <div class="ya-related-image"><img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></div>
            <div class="ya-related-copy">
              <small><?php echo esc_html( $r_days ); ?> Days · <?php echo esc_html( $r_region ); ?></small>
              <h3><?php the_title(); ?></h3>
              <div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
            </div>
          </a>
        <?php 
          endwhile; 
          wp_reset_postdata();
        else : 
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>">
            <div class="ya-related-image"><img src="https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85" alt="Nepal landscape" loading="lazy"></div>
            <div class="ya-related-copy"><small>12 Days · Nepal</small><h3>Where Culture Meets the Wild: Nepal’s Treasures</h3><div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div></div>
          </a>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>">
            <div class="ya-related-image"><img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=85" alt="Mountain river" loading="lazy"></div>
            <div class="ya-related-copy"><small>12 Days · Nepal</small><h3>Nepal’s Wild Trio: Mountains, Rivers and Jungle</h3><div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div></div>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Fullscreen Image Lightbox -->
  <div class="ya-lightbox" role="dialog" aria-modal="true" aria-label="Image preview">
    <button class="ya-lightbox-close" type="button" aria-label="Close image preview"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    <img src="" alt="Expanded trip image">
  </div>

  <script>
    (()=>{
      const root = document.querySelector('.ya-ebc-page');
      if (!root) return;

      const restUrl = '<?php echo esc_url_raw( rest_url( 'trek-manager/v1/' ) ); ?>';
      const whatsappTarget = '<?php echo esc_js( preg_replace( '/[^0-9]/', '', $whatsapp_number ) ); ?>';
      const salesEmailTarget = '<?php echo esc_js( $sales_email ); ?>';
      const tripTitle = '<?php echo esc_js( $title ); ?>';

      // 1. Reveal on scroll animations
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
      const tabsWrap = root.querySelector('.ya-tabs-wrap');

      const activateTab = (name, scroll = true) => {
        const targetTab = tabs.find(tab => tab.dataset.tab === name);
        const targetPanel = panels.find(panel => panel.dataset.panel === name);
        if (!targetTab || !targetPanel) return;

        tabs.forEach(tab => {
          const active = tab === targetTab;
          tab.classList.toggle('is-active', active);
          tab.setAttribute('aria-selected', String(active));
        });
        panels.forEach(panel => panel.classList.toggle('is-active', panel === targetPanel));
        targetTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

        try { history.replaceState(null, '', `#${name}`); } catch (error) {}
        if (scroll) {
          const offset = (tabsWrap?.offsetHeight || 70) + 90;
          const top = (tabsWrap?.getBoundingClientRect().top || 0) + window.scrollY - offset + 1;
          window.scrollTo({ top, behavior: 'smooth' });
        }
      };

      tabs.forEach(tab => tab.addEventListener('click', () => activateTab(tab.dataset.tab, true)));
      root.querySelectorAll('.ya-jump-tab').forEach(button => button.addEventListener('click', () => activateTab(button.dataset.target, true)));

      const initialHash = window.location.hash.replace('#', '');
      if (tabs.some(tab => tab.dataset.tab === initialHash)) activateTab(initialHash, false);

      // 3. Day-by-Day Accordion
      root.querySelectorAll('.ya-day-head').forEach(button => {
        button.addEventListener('click', () => {
          const day = button.closest('.ya-day');
          const open = day.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(open));
        });
      });

      // 4. Essential Info Accordion
      root.querySelectorAll('.ya-info-head').forEach(button => {
        button.addEventListener('click', () => {
          const item = button.closest('.ya-info-item');
          const open = item.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(open));
        });
      });

      // 5. Image Lightbox
      const lightbox = root.querySelector('.ya-lightbox');
      const lightboxImage = lightbox?.querySelector('img');
      const closeLightbox = () => {
        lightbox?.classList.remove('is-open');
        document.body.style.overflow = '';
      };
      root.querySelectorAll('.ya-open-lightbox').forEach(button => {
        button.addEventListener('click', () => {
          if (!lightbox || !lightboxImage) return;
          lightboxImage.src = button.dataset.image || '';
          lightbox.classList.add('is-open');
          document.body.style.overflow = 'hidden';
        });
      });
      lightbox?.querySelector('.ya-lightbox-close')?.addEventListener('click', closeLightbox);
      lightbox?.addEventListener('click', event => { if (event.target === lightbox) closeLightbox(); });
      document.addEventListener('keydown', event => { if (event.key === 'Escape') closeLightbox(); });

      // 6. Preferred Year Generation
      const yearSelect = root.querySelector('#ya-departure-year');
      if (yearSelect) {
        const year = new Date().getFullYear();
        for (let i = 0; i < 4; i++) {
          const option = document.createElement('option');
          option.value = String(year + i);
          option.textContent = String(year + i);
          yearSelect.appendChild(option);
        }
      }

      const getValue = selector => root.querySelector(selector)?.value?.trim() || 'Not provided';

      // 7. Fixed Departure Form (WhatsApp)
      root.querySelector('#ya-departure-form')?.addEventListener('submit', event => {
        event.preventDefault();
        if (!event.currentTarget.reportValidity()) return;
        const message = [
          `Hello Yeti Adventure, I would like to ask about a fixed departure for ${tripTitle}.`,
          '',
          `Preferred month: ${getValue('#ya-departure-month')} ${getValue('#ya-departure-year')}`,
          `Name: ${getValue('#ya-departure-name')}`,
          `Travellers: ${getValue('#ya-departure-people')}`,
          `Email: ${getValue('#ya-departure-email')}`,
          `Message: ${getValue('#ya-departure-message')}`
        ].join('\n');
        window.open(`https://wa.me/${whatsappTarget}?text=${encodeURIComponent(message)}`, '_blank', 'noopener');
      });

      // 8. Review Submission (Save to Database via REST API & AJAX)
      const reviewForm = root.querySelector('#ya-review-form');
      const alertSuccess = root.querySelector('#ya-rev-alert-success');
      const alertError = root.querySelector('#ya-rev-alert-error');

      const buildReviewEmail = () => {
        const rating = root.querySelector('input[name="ya-rating"]:checked')?.value || '5';
        const body = [
          `${tripTitle} Trip Review`,
          '',
          `Name: ${getValue('#ya-review-name')}`,
          `Email: ${getValue('#ya-review-email')}`,
          `City: ${getValue('#ya-review-city')}`,
          `Country: ${getValue('#ya-review-country')}`,
          `Tour date: ${getValue('#ya-review-date')}`,
          `Rating: ${rating}/5`,
          '',
          `Comment: ${getValue('#ya-review-comment')}`,
          '',
          `Inspirational moment: ${getValue('#ya-review-moment')}`,
          '',
          `Trek leader: ${getValue('#ya-review-leader')}`,
          '',
          `Advice for travellers: ${getValue('#ya-review-advice')}`
        ].join('\n');
        window.location.href = `mailto:${salesEmailTarget}?subject=${encodeURIComponent(tripTitle + ' Trip Review')}&body=${encodeURIComponent(body)}`;
      };

      root.querySelector('#ya-email-rev-btn')?.addEventListener('click', () => {
        if (!reviewForm.reportValidity()) return;
        buildReviewEmail();
      });

      reviewForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!reviewForm.reportValidity()) return;

        const submitBtn = root.querySelector('#ya-submit-rev-btn');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Saving Review...';
        }

        if (alertSuccess) alertSuccess.style.display = 'none';
        if (alertError) alertError.style.display = 'none';

        const payload = {
          trek_id: getValue('#ya-review-trek-id'),
          name: getValue('#ya-review-name'),
          email: getValue('#ya-review-email'),
          city: getValue('#ya-review-city'),
          country: getValue('#ya-review-country'),
          tour_date: getValue('#ya-review-date'),
          rating: root.querySelector('input[name="ya-rating"]:checked')?.value || '5',
          comment: getValue('#ya-review-comment'),
          moment: getValue('#ya-review-moment'),
          leader: getValue('#ya-review-leader'),
          advice: getValue('#ya-review-advice'),
        };

        try {
          const response = await fetch(`${restUrl}reviews`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
          });

          const data = await response.json();

          if (response.ok && data.success) {
            if (alertSuccess) {
              alertSuccess.innerHTML = `<strong>✔ Review Submitted!</strong> Thank you ${payload.name}, your review has been saved in our database. <a href="${data.whatsapp_url}" target="_blank" style="text-decoration:underline; font-weight:bold; color:inherit; margin-left:6px;">Share on WhatsApp &rarr;</a>`;
              alertSuccess.style.display = 'block';
            }

            // Dynamically prepend review card to the list
            const reviewList = root.querySelector('#ya-review-cards-list');
            if (reviewList) {
              const stars = '<svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>'.repeat(parseInt(payload.rating) || 5);
              const card = document.createElement('article');
              card.className = 'ya-review-card';
              card.innerHTML = `
                <div class="ya-stars" aria-label="${payload.rating} out of 5 stars">${stars}</div>
                <blockquote>${payload.comment}</blockquote>
                <strong>${payload.name}</strong>
                <small>${payload.city ? payload.city + ', ' : ''}${payload.country || 'Verified Traveller'}</small>
              `;
              reviewList.insertBefore(card, reviewList.firstChild);
            }

            reviewForm.reset();
          } else {
            throw new Error(data.message || 'Failed to submit review.');
          }
        } catch (err) {
          if (alertError) {
            alertError.textContent = err.message || 'An error occurred while saving your review. You can also send it via email.';
            alertError.style.display = 'block';
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Review to Database';
          }
        }
      });
    })();
  </script>
</section>

<?php if ( ! $is_shortcode ) : ?>
  <?php get_footer(); ?>
<?php endif; ?>