<?php
/**
 * Yeti Adventure — Interactive Nepal Tourism Map Template
 *
 * Rendered by Trek Content Manager: [ya_nepal_map], [trek_nepal_map], [nepal_tourism_map]
 * Dynamically binds published trek_destination CPT data to the authentic
 * clean illustrated Nepal Tourism Map (assets/images/nepal_map_clear.svg).
 *
 * @var array  $region_data Array of destination/district data from WordPress
 * @var string $class       Optional custom wrapper class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * MAP INSTANCE ID & URL SETUP
 * ========================================================= */

$map_id        = 'ya-nepal-map-' . wp_unique_id();
$default_url   = home_url( '/all-trips/' );
$custom_class  = ! empty( $class ) ? ' ' . esc_attr( sanitize_html_class( $class ) ) : '';
$map_image_url = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/images/nepal_map_clear.svg';

/* =========================================================
 * HELPER: NORMALIZE STRINGS FOR LOOKUP
 * ========================================================= */

if ( ! function_exists( 'ya_map_normalize_key' ) ) {
	function ya_map_normalize_key( $value ) {
		$value = wp_strip_all_tags( (string) $value );
		$value = remove_accents( $value );
		$value = strtolower( trim( $value ) );
		$value = preg_replace( '/[^a-z0-9]+/', '-', $value );
		return trim( $value, '-' );
	}
}

/* =========================================================
 * MASTER DESTINATION DEFINITIONS (Calibrated to 1610x914)
 * ========================================================= */

$destinations_config = array(
	'everest-region' => array(
		'title'       => 'Everest Region (Khumbu)',
		'subtitle'    => 'Mt. Everest 8,848m · Sagarmatha National Park',
		'category'    => 'peaks',
		'cx'          => 1335,
		'cy'          => 545,
		'elevation'   => '8,848 m',
		'districts'   => array( 'solukhumbu', 'sagarmatha', 'khumbu', 'everest', 'lukla', 'gokyo', 'everest-base-camp', 'ebc' ),
		'icon_type'   => 'peak',
	),
	'annapurna' => array(
		'title'       => 'Annapurna Region',
		'subtitle'    => 'Annapurna 8,091m · Sanctuary & Circuit',
		'category'    => 'peaks',
		'cx'          => 770,
		'cy'          => 382,
		'elevation'   => '8,091 m',
		'districts'   => array( 'kaski', 'manang', 'myagdi', 'annapurna', 'abc', 'poon-hill', 'mardi', 'ghorepani', 'annapurna-region', 'annapurna-sanctuary', 'ghorepani-poon-hill' ),
		'icon_type'   => 'peak',
	),
	'mustang' => array(
		'title'       => 'Mustang (Kingdom of Lo)',
		'subtitle'    => 'Walled City of Lo Manthang · Jomsom',
		'category'    => 'culture',
		'cx'          => 752,
		'cy'          => 302,
		'elevation'   => '3,840 m',
		'districts'   => array( 'mustang', 'lo-manthang', 'jomsom', 'upper-mustang' ),
		'icon_type'   => 'fort',
	),
	'manaslu' => array(
		'title'       => 'Manaslu Region',
		'subtitle'    => 'Manaslu 8,163m · Tsum Valley Sanctuary',
		'category'    => 'peaks',
		'cx'          => 901,
		'cy'          => 426,
		'elevation'   => '8,163 m',
		'districts'   => array( 'gorkha', 'manaslu', 'tsum', 'larke', 'tsum-valley', 'mansiri', 'mansiri-himal' ),
		'icon_type'   => 'peak',
	),
	'dhaulagiri' => array(
		'title'       => 'Dhaulagiri Region',
		'subtitle'    => 'Dhaulagiri 8,167m · White Mountain Wilds',
		'category'    => 'peaks',
		'cx'          => 667,
		'cy'          => 386,
		'elevation'   => '8,167 m',
		'districts'   => array( 'myagdi', 'baglung', 'dhaulagiri' ),
		'icon_type'   => 'peak',
	),
	'ganesh-himal' => array(
		'title'       => 'Ganesh Himal',
		'subtitle'    => 'Ganesh Himal 7,046m · Ruby Valley Treks',
		'category'    => 'peaks',
		'cx'          => 1045,
		'cy'          => 466,
		'elevation'   => '7,046 m',
		'districts'   => array( 'dhading', 'rasuwa', 'ganesh', 'ruby-valley' ),
		'icon_type'   => 'peak',
	),
	'langtang' => array(
		'title'       => 'Langtang & Gosaikunda',
		'subtitle'    => 'Langtang Lirung 7,234m · Sacred Lakes',
		'category'    => 'peaks',
		'cx'          => 1095,
		'cy'          => 490,
		'elevation'   => '7,234 m',
		'districts'   => array( 'rasuwa', 'sindhupalchok', 'langtang', 'gosaikunda', 'helambu', 'kyanjin', 'langtang-kyanjin' ),
		'icon_type'   => 'peak',
	),
	'kanchenjunga' => array(
		'title'       => 'Kanchenjunga',
		'subtitle'    => 'Kanchenjunga 8,586m · Eastern Wilderness',
		'category'    => 'peaks',
		'cx'          => 1532,
		'cy'          => 635,
		'elevation'   => '8,586 m',
		'districts'   => array( 'taplejung', 'kanchenjunga', 'kangchenjunga' ),
		'icon_type'   => 'peak',
	),
	'api-himal' => array(
		'title'       => 'Api Himal & Saipal',
		'subtitle'    => 'Api Himal 7,132m · Api Nampa Conservation',
		'category'    => 'peaks',
		'cx'          => 135,
		'cy'          => 111,
		'elevation'   => '7,132 m',
		'districts'   => array( 'darchula', 'bajhang', 'api', 'api-himal', 'saipal' ),
		'icon_type'   => 'peak',
	),
	'rara' => array(
		'title'       => 'Rara Lake National Park',
		'subtitle'    => 'Nepal’s Queen of Lakes · Jumla & Mugu',
		'category'    => 'wildlife',
		'cx'          => 383,
		'cy'          => 204,
		'elevation'   => '2,990 m',
		'districts'   => array( 'mugu', 'jumla', 'rara' ),
		'icon_type'   => 'lake',
	),
	'dolpa' => array(
		'title'       => 'Shey Phoksundo & Dolpa',
		'subtitle'    => 'Deep Blue Phoksundo Lake · Crystal Mountain',
		'category'    => 'wildlife',
		'cx'          => 554,
		'cy'          => 249,
		'elevation'   => '3,611 m',
		'districts'   => array( 'dolpa', 'phoksundo', 'shey-phoksundo' ),
		'icon_type'   => 'lake',
	),
	'pokhara' => array(
		'title'       => 'Pokhara & Lakes',
		'subtitle'    => 'Phewa & Begnas Lakes · Gateway to Annapurna',
		'category'    => 'culture',
		'cx'          => 806,
		'cy'          => 510,
		'elevation'   => '822 m',
		'districts'   => array( 'kaski', 'pokhara', 'phewa' ),
		'icon_type'   => 'city',
	),
	'kathmandu' => array(
		'title'       => 'Kathmandu Valley',
		'subtitle'    => 'UNESCO World Heritage · Patan & Bhaktapur',
		'category'    => 'culture',
		'cx'          => 1045,
		'cy'          => 590,
		'elevation'   => '1,400 m',
		'districts'   => array( 'kathmandu', 'bhaktapur', 'lalitpur' ),
		'icon_type'   => 'temple',
	),
	'lumbini' => array(
		'title'       => 'Lumbini (Buddha Circuit)',
		'subtitle'    => 'Sacred Birthplace of Lord Buddha · Peace Garden',
		'category'    => 'culture',
		'cx'          => 613,
		'cy'          => 635,
		'elevation'   => '150 m',
		'districts'   => array( 'rupandehi', 'kapilvastu', 'lumbini', 'buddha' ),
		'icon_type'   => 'buddha',
	),
	'chitwan-national-park' => array(
		'title'       => 'Chitwan National Park',
		'subtitle'    => 'One-Horned Rhinoceros & Royal Bengal Tiger Safari',
		'category'    => 'wildlife',
		'cx'          => 883,
		'cy'          => 648,
		'elevation'   => '150 m',
		'districts'   => array( 'chitwan', 'makwanpur', 'nawalpur', 'bharatpur' ),
		'icon_type'   => 'rhino',
	),
	'bardia-national-park' => array(
		'title'       => 'Bardia National Park',
		'subtitle'    => 'Pristine Wilderness · Wild Elephants & Tigers',
		'category'    => 'wildlife',
		'cx'          => 279,
		'cy'          => 422,
		'elevation'   => '152 m',
		'districts'   => array( 'bardiya', 'banke', 'surkhet', 'bardia' ),
		'icon_type'   => 'safari',
	),
	'koshi-tappu-wildlife' => array(
		'title'       => 'Koshi Tappu Wildlife',
		'subtitle'    => 'Ramsar Wetland Reserve · Wild Water Buffaloes',
		'category'    => 'wildlife',
		'cx'          => 1397,
		'cy'          => 794,
		'elevation'   => '80 m',
		'districts'   => array( 'sunsari', 'saptari', 'koshi', 'tappu' ),
		'icon_type'   => 'safari',
	),
	'ilam' => array(
		'title'       => 'Ilam Tea Gardens',
		'subtitle'    => 'Rolling Green Hills · Orthodox Himalayan Tea',
		'category'    => 'culture',
		'cx'          => 1559,
		'cy'          => 772,
		'elevation'   => '1,208 m',
		'districts'   => array( 'ilam', 'panchthar' ),
		'icon_type'   => 'tea',
	),
);

/* =========================================================
 * BIND WORDPRESS LIVE TRIP DATA ($region_data)
 * ========================================================= */

$active_trip_count = 0;
$rendered_hotspots = array();
$active_categories_map = array();

// Known human-friendly category names
$known_category_names = array(
	'peaks'             => __( 'Himalayan Treks', 'trek-content-manager' ),
	'himalayan'         => __( 'Himalayan Treks', 'trek-content-manager' ),
	'trekking'          => __( 'Trekking Journeys', 'trek-content-manager' ),
	'wildlife'          => __( 'Wildlife & Nature', 'trek-content-manager' ),
	'nature'            => __( 'Nature & Escapes', 'trek-content-manager' ),
	'culture'           => __( 'Cultural & Heritage', 'trek-content-manager' ),
	'cultural-journeys' => __( 'Cultural Journeys', 'trek-content-manager' ),
	'heritage'          => __( 'Heritage Wonders', 'trek-content-manager' ),
	'soft-adventure'    => __( 'Soft Adventure', 'trek-content-manager' ),
	'adventure'         => __( 'Adventure Treks', 'trek-content-manager' ),
	'luxury'            => __( 'Luxury Journeys', 'trek-content-manager' ),
	'wellness'          => __( 'Wellness Retreats', 'trek-content-manager' ),
	'spiritual'         => __( 'Spiritual & Sacred', 'trek-content-manager' ),
	'signature-journey' => __( 'Signature Journeys', 'trek-content-manager' ),
);

foreach ( $destinations_config as $key => $config ) {
	$matched_destinations = array();
	$spot_cats = array();
	$norm_key  = ya_map_normalize_key( $key );

	// Build alias search list
	$aliases = array_map( 'ya_map_normalize_key', $config['districts'] );
	$aliases[] = $norm_key;

	if ( ! empty( $region_data ) ) {
		foreach ( $region_data as $reg_district => $reg_item ) {
			$norm_reg = ya_map_normalize_key( $reg_district );
			$is_match = false;

			foreach ( $aliases as $alias ) {
				if ( empty( $alias ) ) continue;
				if ( $norm_reg === $alias || strpos( $norm_reg, $alias ) !== false ) {
					$is_match = true;
					break;
				}
			}

			if ( $is_match && ! empty( $reg_item['destinations'] ) ) {
				foreach ( $reg_item['destinations'] as $dest_trip ) {
					$matched_destinations[] = $dest_trip;
					if ( ! empty( $dest_trip['categories'] ) ) {
						foreach ( $dest_trip['categories'] as $c_slug => $c_name ) {
							$clean_slug = ya_map_normalize_key( $c_slug );
							$display_name = ! empty( $known_category_names[ $clean_slug ] ) ? $known_category_names[ $clean_slug ] : $c_name;
							$spot_cats[ $clean_slug ] = $display_name;
							$active_categories_map[ $clean_slug ] = $display_name;
						}
					}
				}
			}
		}
	}

	$has_trips = ! empty( $matched_destinations );
	$count     = count( $matched_destinations );

	if ( $has_trips ) {
		$active_trip_count++;
		$first_trip = $matched_destinations[0];
		$title      = ! empty( $first_trip['title'] ) ? $first_trip['title'] : $config['title'];
		$days       = ! empty( $first_trip['days'] ) ? $first_trip['days'] : '';
		$image      = ! empty( $first_trip['image'] ) ? $first_trip['image'] : '';
		$permalink  = ! empty( $first_trip['permalink'] ) ? $first_trip['permalink'] : add_query_arg( 'region', $key, $default_url );
	} else {
		$title      = $config['title'];
		$days       = '';
		$image      = '';
		$permalink  = add_query_arg( 'region', $key, $default_url );
	}

	// Also add default config category as fallback
	if ( ! empty( $config['category'] ) ) {
		$cfg_cat = ya_map_normalize_key( $config['category'] );
		$cfg_name = ! empty( $known_category_names[ $cfg_cat ] ) ? $known_category_names[ $cfg_cat ] : ucfirst( str_replace( '-', ' ', $cfg_cat ) );
		if ( empty( $spot_cats ) && $has_trips ) {
			$spot_cats[ $cfg_cat ] = $cfg_name;
			$active_categories_map[ $cfg_cat ] = $cfg_name;
		} else {
			$spot_cats[ $cfg_cat ] = $cfg_name;
		}
	}

	$rendered_hotspots[ $key ] = array_merge( $config, array(
		'has_trips'       => $has_trips,
		'count'           => $count,
		'trip_title'      => $title,
		'days'            => $days,
		'image'           => $image,
		'permalink'       => $permalink,
		'destinations'    => $matched_destinations,
		'category_slugs'  => array_keys( $spot_cats ),
		'categories_list' => $spot_cats,
	) );
}

?>

<!-- =========================================================
     INTERACTIVE NEPAL TOURISM MAP (CLEAN ARTWORK)
     ========================================================= -->
<section
	id="<?php echo esc_attr( $map_id ); ?>"
	class="ya-nepal-map<?php echo $custom_class; ?>"
	aria-label="Interactive tourism map of Nepal"
>
	<!-- Top Interactive Navigation & Status Toolbar -->
	<header class="ya-nepal-map__toolbar">
		<div class="ya-nepal-map__branding">
			<span class="ya-nepal-map__kicker">Nepal Tourism Map</span>
			<h3 class="ya-nepal-map__title">Explore Destinations</h3>
		</div>

		<!-- Dynamic Category Filter Pills -->
		<nav class="ya-nepal-map__filters" aria-label="Filter map destinations by category">
			<button type="button" class="ya-map-filter-btn is-active" data-filter="all">
				All Highlights
			</button>
			<?php
			$known_category_icons = array(
				'peaks'             => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>',
				'himalayan'         => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>',
				'trekking'          => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>',
				'wildlife'          => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
				'nature'            => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
				'culture'           => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
				'cultural-journeys' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
				'heritage'          => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
				'soft-adventure'    => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
				'adventure'         => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
				'luxury'            => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
			);

			foreach ( $active_categories_map as $cat_slug => $cat_name ) :
				$icon = ! empty( $known_category_icons[ $cat_slug ] )
					? $known_category_icons[ $cat_slug ]
					: '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>';
			?>
				<button type="button" class="ya-map-filter-btn" data-filter="<?php echo esc_attr( $cat_slug ); ?>">
					<?php echo $icon; ?>
					<?php echo esc_html( $cat_name ); ?>
				</button>
			<?php endforeach; ?>
		</nav>

		<!-- Counter badge -->
		<div class="ya-nepal-map__badge">
			<span class="ya-badge-dot"></span>
			<span class="ya-badge-count"><?php echo intval( $active_trip_count ); ?> Active Destinations</span>
		</div>
	</header>

	<!-- Scrollable / Responsive Viewport Container -->
	<div class="ya-nepal-map__viewport" data-map-viewport>
		<svg
			class="ya-nepal-map__svg"
			viewBox="0 0 1610 914"
			preserveAspectRatio="xMidYMid meet"
			role="img"
			aria-labelledby="<?php echo esc_attr( $map_id ); ?>-title <?php echo esc_attr( $map_id ); ?>-desc"
		>
			<title id="<?php echo esc_attr( $map_id ); ?>-title">Nepal Tourism Map with Mountain Peaks, Icons & Itineraries</title>
			<desc id="<?php echo esc_attr( $map_id ); ?>-desc">Detailed illustrated map of Nepal featuring Mount Everest, Annapurna, Mustang, Chitwan, and major tourism destinations.</desc>

			<!-- Clean Base Illustrated Tourism Map Artwork (Without road clutter) -->
			<image
				id="ya-reference-map-artwork"
				class="ya-nepal-map__artwork"
				href="<?php echo esc_url( $map_image_url ); ?>"
				x="0"
				y="0"
				width="1610"
				height="914"
				preserveAspectRatio="xMidYMid meet"
			/>

			<!-- Interactive SVG Hotspots Overlay Layer (Only Clean Glowing Circles - No Duplicate Text Overlays) -->
			<g class="ya-nepal-map__hotspots-layer" role="group" aria-label="Interactive destination hotspots">
				<?php foreach ( $rendered_hotspots as $key => $spot ) :
					$has_trips    = $spot['has_trips'];
					// Only render pins for destinations that have real DB data set
					if ( ! $has_trips ) continue;
					$cx           = $spot['cx'];
					$cy           = $spot['cy'];
					$count        = $spot['count'];
					$cat_slugs    = ! empty( $spot['category_slugs'] ) ? $spot['category_slugs'] : array( $spot['category'] );
					$cat_string   = esc_attr( implode( ' ', $cat_slugs ) );
					$cat_classes  = implode( ' ', array_map( function( $c ) { return 'cat-' . esc_attr( $c ); }, $cat_slugs ) );
					$link_url     = esc_url( $spot['permalink'] );
					$title_text   = esc_attr( $spot['trip_title'] );
					$subtitle     = esc_attr( $spot['subtitle'] );
					$days_text    = esc_attr( $spot['days'] );
					$img_url      = esc_url( $spot['image'] );
					$elevation    = esc_attr( $spot['elevation'] );
					$dest_list    = ! empty( $spot['destinations'] ) ? $spot['destinations'] : array();
					$dest_json    = esc_attr( wp_json_encode( $dest_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
					$hotspot_cls  = 'ya-map-hotspot has-trips is-featured ' . $cat_classes;
				?>
					<a
						class="<?php echo $hotspot_cls; ?>"
						href="javascript:void(0);"
						data-permalink="<?php echo esc_url( $link_url ); ?>"
						data-key="<?php echo esc_attr( $key ); ?>"
						data-category="<?php echo $cat_string; ?>"
						data-title="<?php echo $title_text; ?>"
						data-subtitle="<?php echo $subtitle; ?>"
						data-days="<?php echo $days_text; ?>"
						data-img="<?php echo $img_url; ?>"
						data-elevation="<?php echo $elevation; ?>"
						data-count="<?php echo intval( $count ); ?>"
						data-cx="<?php echo $cx; ?>"
						data-cy="<?php echo $cy; ?>"
						data-trips="<?php echo $dest_json; ?>"
						tabindex="0"
						role="button"
						aria-label="<?php echo $title_text . ( $count > 0 ? ' (' . $count . ' journeys)' : '' ); ?>"
					>
						<!-- Animated Radar Pulse Ring -->
						<circle class="ya-pin-pulse" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="20" />
						<circle class="ya-pin-aura" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="14" />

						<!-- Clickable Hit Target -->
						<circle class="ya-pin-hit" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="28" />

						<!-- Outer Ring -->
						<circle class="ya-pin-outer" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="11" />

						<!-- Center Dot -->
						<circle class="ya-pin-core" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="5" />

						<!-- Count Badge for multiple trips -->
						<?php if ( $count > 1 ) : ?>
							<g class="ya-pin-badge" transform="translate(<?php echo $cx + 7; ?>, <?php echo $cy - 11; ?>)">
								<rect x="-8" y="-7" width="16" height="14" rx="7" class="ya-badge-bg" />
								<text x="0" y="3.5" text-anchor="middle" class="ya-badge-text"><?php echo intval( $count ); ?></text>
							</g>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</g>
		</svg>

		<!-- Mobile Dim Backdrop for Centered Modal -->
		<div class="ya-map-mobile-backdrop" data-map-backdrop></div>

		<!-- Minimal Clickable Image + Name Card (Navigates to dedicated page on click) -->
		<a
			id="<?php echo esc_attr( $map_id ); ?>-preview"
			class="ya-map-preview-card"
			role="link"
			aria-hidden="true"
			href="#"
			data-map-preview
			data-card-btn
		>
			<!-- Close Button -->
			<button type="button" class="ya-map-card__close" aria-label="Close" data-card-close>
				<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
			</button>

			<!-- Image -->
			<div class="ya-map-card__img-wrap" data-card-thumb>
				<img src="" alt="" class="ya-map-card__img" data-card-img />

				<!-- Centered carousel arrows (only for multi-destination) -->
				<div class="ya-map-card__carousel-arrows" data-card-carousel style="display:none;">
					<button type="button" class="ya-img-arrow" aria-label="Previous" data-carousel-prev>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<span class="ya-img-counter" data-carousel-counter>1/2</span>
					<button type="button" class="ya-img-arrow" aria-label="Next" data-carousel-next>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>
			</div>

			<!-- Name bar at bottom with destination title and view CTA -->
			<div class="ya-map-card__name-bar">
				<span class="ya-map-card__name" data-card-title>Destination</span>
				<span class="ya-map-card__cta">View Journey &rarr;</span>
			</div>

			<!-- 10-Second Auto-Close Progress Bar -->
			<div class="ya-map-card__timer-bar" data-card-timer></div>
		</a>

		<!-- Mobile Swipe / Pan Hint -->
		<div class="ya-nepal-map__touch-hint" aria-hidden="true">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8L22 12L18 16"/><path d="M6 8L2 12L6 16"/><path d="M2 12H22"/></svg>
			<span>Swipe to explore all Nepal regions</span>
		</div>
	</div>
</section>

<!-- =========================================================
     LUXURY STYLING FOR NEPAL TOURISM MAP
     ========================================================= -->
<style>
/* ---------------------------------------------------------
   ROOT & CSS VARIABLES
   --------------------------------------------------------- */
#<?php echo esc_attr( $map_id ); ?> {
	--ya-ink: #112822;
	--ya-ink-dark: #0a1c17;
	--ya-gold: #d1ad54;
	--ya-gold-hover: #5f7554;
	--ya-gold-light: #fdfaf2;
	--ya-gold-dark: #b8933b;
	--ya-emerald: #5f7554;
	--ya-border: rgba(95, 117, 84, 0.2);
	--ya-cream: #fafbf9;
	--ya-white: #ffffff;
	--ya-muted: #5f7554;

	position: relative;
	width: 100%;
	max-width: 100%;
	margin: 0 auto;
	background: linear-gradient(180deg, #fbfdfa 0%, #f0f4f1 100%);
	border-radius: 14px;
	box-shadow: 0 16px 44px rgba(12, 45, 45, 0.08);
	overflow: hidden;
	font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	color: var(--ya-ink);
	border: 1px solid rgba(22, 94, 91, 0.14);
	box-sizing: border-box;
}

#<?php echo esc_attr( $map_id ); ?> * {
	box-sizing: border-box;
}

/* ---------------------------------------------------------
   TOOLBAR & FILTER BAR (Clean layout, no clipping)
   --------------------------------------------------------- */
.ya-nepal-map__toolbar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 12px 18px;
	padding: 16px 24px;
	background: var(--ya-white);
	border-bottom: 1px solid var(--ya-border);
}

.ya-nepal-map__branding {
	display: flex;
	flex-direction: column;
}

.ya-nepal-map__kicker {
	font-size: 10.5px;
	text-transform: uppercase;
	letter-spacing: 0.12em;
	font-weight: 700;
	color: var(--ya-gold);
	margin-bottom: 2px;
	line-height: 1;
}

.ya-nepal-map__title {
	margin: 0;
	font-size: 18px;
	font-weight: 700;
	letter-spacing: -0.02em;
	color: var(--ya-ink);
	line-height: 1.25;
}

.ya-nepal-map__filters {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-wrap: wrap;
}

.ya-map-filter-btn {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 6px 14px;
	border-radius: 30px;
	font-size: 12px;
	font-weight: 600;
	border: 1px solid var(--ya-border);
	background: #f4f7f5;
	color: var(--ya-ink);
	cursor: pointer;
	white-space: nowrap;
	transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
	outline: none;
	line-height: 1.4;
}

.ya-map-filter-btn:hover {
	background: #5f7554;
	border-color: #5f7554;
	color: #ffffff;
	transform: translateY(-1px);
}

.ya-map-filter-btn.is-active {
	background: #d1ad54;
	border-color: #d1ad54;
	color: #ffffff;
	box-shadow: 0 4px 12px rgba(209, 173, 84, 0.3);
}

.ya-nepal-map__badge {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 12px;
	color: var(--ya-muted);
	font-weight: 500;
	white-space: nowrap;
}

.ya-badge-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #d1ad54;
	box-shadow: 0 0 0 3px rgba(209, 173, 84, 0.25);
}

.ya-badge-count {
	font-weight: 600;
	color: var(--ya-ink);
}

.ya-badge-active {
	color: var(--ya-gold);
	font-weight: 700;
}

/* ---------------------------------------------------------
   MAP VIEWPORT & SVG CANVAS
   --------------------------------------------------------- */
.ya-nepal-map__viewport {
	position: relative;
	width: 100%;
	overflow: hidden;
	background: transparent;
}

.ya-nepal-map__svg {
	display: block;
	width: 100%;
	height: auto;
	max-width: 100%;
	aspect-ratio: 1610 / 914;
	background: transparent;
	overflow: visible;
}

.ya-nepal-map__artwork {
	pointer-events: none;
	user-select: none;
}

/* ---------------------------------------------------------
   HOTSPOTS: ONLY CLEAN GLOWING CIRCLES (NO TEXT OVERLAYS)
   --------------------------------------------------------- */
.ya-map-hotspot {
	cursor: pointer;
	outline: none;
	transition: transform 0.2s ease, opacity 0.3s ease;
	transform-origin: center;
}

.ya-map-hotspot:focus-visible {
	outline: 2px solid var(--ya-gold);
	outline-offset: 3px;
}

.ya-pin-hit {
	fill: #ffffff;
	fill-opacity: 0.001;
	stroke: transparent;
	stroke-width: 0;
	pointer-events: all;
}

/* Hotspot Outer Ring - Real Yellow Only (#d1ad54) */
.ya-pin-outer {
	fill: var(--ya-white);
	stroke: var(--ya-gold);
	stroke-width: 2.4px;
	transition: all 0.22s ease;
	filter: drop-shadow(0 2px 5px rgba(209, 173, 84, 0.4));
}

/* Hotspot Inner Dot - Real Yellow Only (#d1ad54) */
.ya-pin-core {
	fill: var(--ya-gold);
	transition: all 0.22s ease;
}

/* Active / Featured Pulse & Styling */
.ya-map-hotspot.has-trips .ya-pin-outer {
	stroke: var(--ya-gold);
	stroke-width: 2.4px;
}

.ya-map-hotspot.has-trips .ya-pin-core {
	fill: var(--ya-gold);
}

.ya-pin-aura {
	fill: var(--ya-gold);
	fill-opacity: 0.25;
	pointer-events: none;
}

.ya-pin-pulse {
	fill: none;
	stroke: var(--ya-gold);
	stroke-width: 2px;
	opacity: 0.8;
	pointer-events: none;
	animation: yaMapPulseAnim 2.5s cubic-bezier(0.25, 1, 0.5, 1) infinite;
	transform-origin: center;
}

@keyframes yaMapPulseAnim {
	0% {
		r: 11px;
		opacity: 0.85;
		stroke-width: 2px;
	}
	65% {
		r: 25px;
		opacity: 0.15;
		stroke-width: 1.2px;
	}
	100% {
		r: 30px;
		opacity: 0;
		stroke-width: 0.5px;
	}
}

/* Badge for Multiple Trips */
.ya-badge-bg {
	fill: var(--ya-gold);
	filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.35));
}

.ya-badge-text {
	fill: var(--ya-white);
	font-size: 8px;
	font-weight: 700;
	font-family: inherit;
}

/* HOVER / ACTIVE STATES ON HOTSPOT CIRCLE */
.ya-map-hotspot:hover .ya-pin-outer,
.ya-map-hotspot:focus-visible .ya-pin-outer,
.ya-map-hotspot.is-active .ya-pin-outer {
	r: 15px;
	fill: var(--ya-gold);
	stroke: var(--ya-white);
	stroke-width: 2.6px;
	filter: drop-shadow(0 0 12px rgba(209, 173, 84, 0.85));
}

.ya-map-hotspot:hover .ya-pin-core,
.ya-map-hotspot:focus-visible .ya-pin-core,
.ya-map-hotspot.is-active .ya-pin-core {
	r: 7px;
	fill: var(--ya-white);
}

.ya-map-hotspot.is-dimmed {
	opacity: 0.2;
	filter: grayscale(80%);
}

/* ---------------------------------------------------------
   CLICKABLE IMAGE + NAME PREVIEW CARD
   --------------------------------------------------------- */
.ya-map-preview-card {
	position: absolute;
	z-index: 999;
	width: 320px;
	border-radius: 4px;
	overflow: hidden;
	background: #ffffff;
	box-shadow: 0 10px 36px rgba(0, 0, 0, 0.18), 0 2px 8px rgba(0,0,0,0.08);
	pointer-events: none;
	opacity: 0;
	visibility: hidden;
	text-decoration: none;
	color: inherit;
	display: block;
	cursor: pointer;
	transform: scale(0.94) translateY(6px);
	transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1),
	            transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
	            visibility 0.22s;
}

.ya-map-preview-card.is-visible {
	opacity: 1;
	visibility: visible;
	pointer-events: auto;
	transform: scale(1) translateY(0);
}

.ya-map-preview-card:hover {
	text-decoration: none;
	color: inherit;
}

.ya-map-card__close {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 20;
	width: 22px;
	height: 22px;
	border-radius: 50%;
	background: rgba(255, 255, 255, 0.85);
	backdrop-filter: blur(4px);
	border: none;
	color: #333;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	padding: 0;
	transition: background 0.2s, color 0.2s;
}

.ya-map-card__close:hover {
	background: var(--ya-gold);
	color: #fff;
}

/* Image wrap */
.ya-map-card__img-wrap {
	position: relative;
	width: 100%;
	height: 200px;
	overflow: hidden;
	background: #e8ece8;
}

.ya-map-card__img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	transition: transform 0.4s ease;
}

.ya-map-preview-card:hover .ya-map-card__img {
	transform: scale(1.05);
}

/* Centered carousel arrows row (top of image) */
.ya-map-card__carousel-arrows {
	position: absolute;
	top: 50%;
	left: 0;
	right: 0;
	transform: translateY(-50%);
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 10px;
	z-index: 10;
	pointer-events: none;
}

.ya-map-card__carousel-arrows button {
	pointer-events: auto;
}

.ya-img-counter {
	position: absolute;
	top: 10px;
	left: 50%;
	transform: translateX(-50%);
	font-size: 10px;
	font-weight: 700;
	color: #ffffff;
	background: rgba(0, 0, 0, 0.5);
	padding: 2px 8px;
	border-radius: 20px;
	backdrop-filter: blur(4px);
	border: 1px solid rgba(255,255,255,0.2);
	white-space: nowrap;
}

.ya-img-arrow {
	width: 32px;
	height: 32px;
	border-radius: 50%;
	border: none;
	background: rgba(255, 255, 255, 0.9);
	backdrop-filter: blur(6px);
	color: #222;
	cursor: pointer;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	transition: background 0.2s ease, color 0.2s ease;
	box-shadow: 0 2px 8px rgba(0,0,0,0.18);
}

.ya-img-arrow:hover {
	background: var(--ya-gold);
	color: #ffffff;
}

/* Name bar — white background, clean with CTA link */
.ya-map-card__name-bar {
	background: #ffffff;
	padding: 12px 14px;
	border-top: none;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
}

.ya-map-card__name {
	display: block;
	font-size: 13px;
	font-weight: 700;
	color: #1a2e2e;
	line-height: 1.35;
	letter-spacing: -0.01em;
	flex: 1;
}

.ya-map-card__cta {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 11px;
	font-weight: 700;
	color: #d1ad54;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	white-space: nowrap;
	transition: transform 0.2s ease, color 0.2s ease;
}

.ya-map-preview-card:hover .ya-map-card__cta {
	color: #5f7554;
	transform: translateX(3px);
}

/* 10-Second Auto-Close Progress Bar */
.ya-map-card__timer-bar {
	position: absolute;
	bottom: 0;
	left: 0;
	height: 3px;
	width: 100%;
	background: linear-gradient(90deg, #d1ad54, #5f7554);
	transform-origin: left center;
	transform: scaleX(0);
	z-index: 25;
	pointer-events: none;
}

.ya-map-preview-card.is-visible .ya-map-card__timer-bar.is-animating {
	animation: yaTimerCountdown 10s linear forwards;
}

@keyframes yaTimerCountdown {
	0% {
		transform: scaleX(1);
	}
	100% {
		transform: scaleX(0);
	}
}

/* ---------------------------------------------------------
   RESPONSIVE LAYOUT
   --------------------------------------------------------- */
.ya-nepal-map__touch-hint {
	display: none;
	align-items: center;
	justify-content: center;
	gap: 8px;
	padding: 8px 16px;
	background: rgba(22, 94, 91, 0.06);
	color: var(--ya-muted);
	font-size: 11px;
	font-weight: 600;
	border-top: 1px solid var(--ya-border);
}

@media (max-width: 1024px) {
	.ya-nepal-map__viewport {
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
	}
	.ya-nepal-map__svg {
		min-width: 960px;
	}
	.ya-nepal-map__touch-hint {
		display: flex;
	}
}

/* Mobile Backdrop & Centered Card */
.ya-map-mobile-backdrop {
	display: none;
}

@media (max-width: 768px) {
	.ya-nepal-map__toolbar {
		flex-direction: column;
		align-items: flex-start;
		gap: 10px;
		padding: 14px 16px;
	}

	.ya-nepal-map__filters {
		width: 100%;
		overflow-x: auto;
		flex-wrap: nowrap;
		padding-bottom: 4px;
		scrollbar-width: none;
	}

	.ya-nepal-map__filters::-webkit-scrollbar {
		display: none;
	}

	/* Mobile Dim Backdrop */
	.ya-map-mobile-backdrop {
		display: none;
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(8, 28, 25, 0.65);
		backdrop-filter: blur(4px);
		-webkit-backdrop-filter: blur(4px);
		z-index: 99990;
		opacity: 0;
		transition: opacity 0.22s ease;
		pointer-events: none;
	}

	.ya-map-mobile-backdrop.is-visible {
		display: block;
		opacity: 1;
		pointer-events: auto;
	}

	/* Mobile: centered dead-middle of screen (not bottom, not top) */
	.ya-map-preview-card {
		position: fixed !important;
		top: 50% !important;
		left: 50% !important;
		bottom: auto !important;
		right: auto !important;
		transform: translate(-50%, -50%) scale(0.92) !important;
		width: min(320px, calc(100vw - 36px)) !important;
		max-height: 85vh;
		box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
		z-index: 99999 !important;
	}

	.ya-map-preview-card.is-visible {
		transform: translate(-50%, -50%) scale(1) !important;
	}
}
</style>

<!-- =========================================================
     VANILLA JAVASCRIPT FOR MAP INTERACTIVITY & CAROUSEL
     ========================================================= -->
<script>
(function() {
	'use strict';

	const root = document.getElementById('<?php echo esc_js( $map_id ); ?>');
	if (!root) return;

	const viewport = root.querySelector('[data-map-viewport]');
	const previewCard = root.querySelector('[data-map-preview]');
	const backdrop = root.querySelector('[data-map-backdrop]');
	const cardClose = previewCard?.querySelector('[data-card-close]');
	const cardTitle = previewCard?.querySelector('[data-card-title]');
	const cardImg = previewCard?.querySelector('[data-card-img]');
	const timerBar = previewCard?.querySelector('[data-card-timer]');
	const carouselBar = previewCard?.querySelector('[data-card-carousel]');
	const carouselPrev = previewCard?.querySelector('[data-carousel-prev]');
	const carouselNext = previewCard?.querySelector('[data-carousel-next]');
	const carouselCounter = previewCard?.querySelector('[data-carousel-counter]');

	const hotspots = root.querySelectorAll('.ya-map-hotspot');
	const filterBtns = root.querySelectorAll('.ya-map-filter-btn');

	let activeHotspot = null;
	let autoCloseTimer = null;
	let currentTrips = [];
	let currentTripIndex = 0;

	/* ---------------------------------------------------------
	   SMART NON-BLOCKING POSITIONING (LEFT / RIGHT OF PIN)
	   --------------------------------------------------------- */
	function positionCardSmartly(pinX, pinY, spotCx, spotCy) {
		if (!previewCard || !viewport) return;

		// On mobile, card is centered via CSS fixed positioning — clear inline styles
		if (window.innerWidth <= 768) {
			previewCard.style.left = '';
			previewCard.style.top = '';
			previewCard.style.transform = '';
			return;
		}

		const bounds = viewport.getBoundingClientRect();
		const svg = viewport.querySelector('svg');
		if (!svg) return;

		const svgRect = svg.getBoundingClientRect();
		const scaleX = svgRect.width / 1610;
		const scaleY = svgRect.height / 914;

		// Calculate hotspot center in viewport pixels
		const currentPinX = (spotCx * scaleX) + svgRect.left - bounds.left;
		const currentPinY = (spotCy * scaleY) + svgRect.top - bounds.top;

		const cardWidth = 320;
		const cardHeight = previewCard.offsetHeight || 250;

		let left = 0;
		let top = currentPinY - (cardHeight / 2);

		// If hotspot is on the right side of map, position card to the LEFT
		if (spotCx > 850) {
			left = currentPinX - cardWidth - 24;
		} else {
			// If on the left side, position card to the RIGHT
			left = currentPinX + 24;
		}

		// Horizontal safety clamp within viewport
		if (left < 14) {
			left = currentPinX + 24;
		}
		if (left + cardWidth > viewport.clientWidth - 14) {
			left = currentPinX - cardWidth - 24;
		}
		left = Math.max(14, Math.min(viewport.clientWidth - cardWidth - 14, left));

		// Vertical safety clamp
		top = Math.max(12, Math.min(viewport.clientHeight - cardHeight - 12, top));

		previewCard.style.left = Math.round(left) + 'px';
		previewCard.style.top = Math.round(top) + 'px';
	}

	/* ---------------------------------------------------------
	   UPDATE CAROUSEL SLIDE (FOR MULTIPLE TRIPS IN 1 DISTRICT)
	   --------------------------------------------------------- */
	function renderCurrentTrip() {
		if (!currentTrips || !currentTrips.length) return;
		const trip = currentTrips[currentTripIndex] || currentTrips[0];

		// Name
		if (cardTitle) cardTitle.textContent = trip.title || 'Journey';

		// Image
		if (trip.image && cardImg) {
			cardImg.src = trip.image;
			cardImg.alt = trip.title || '';
		}

		// Update card <a> href — clicking this card navigates to dedicated page
		if (trip.permalink && previewCard) {
			previewCard.setAttribute('href', trip.permalink);
		}

		// Counter
		if (carouselCounter && currentTrips.length > 1) {
			carouselCounter.textContent = (currentTripIndex + 1) + '/' + currentTrips.length;
		}
	}

	/* ---------------------------------------------------------
	   SHOW PREVIEW CARD FOR A HOTSPOT & LOCK FOR 10 SECONDS
	   --------------------------------------------------------- */
	function openMarkerPopup(hotspot, event) {
		// Clear any existing auto-close timer
		if (autoCloseTimer) {
			clearTimeout(autoCloseTimer);
			autoCloseTimer = null;
		}

		activeHotspot = hotspot;

		const title = hotspot.getAttribute('data-title') || 'Nepal Region';
		const cx = parseFloat(hotspot.getAttribute('data-cx') || '0');
		const cy = parseFloat(hotspot.getAttribute('data-cy') || '0');

		// Parse trips
		let trips = [];
		try {
			const attr = hotspot.getAttribute('data-trips');
			if (attr) trips = JSON.parse(attr);
		} catch(e) { trips = []; }

		if (!trips || !trips.length) {
			trips = [{
				title: title,
				image: hotspot.getAttribute('data-img') || '',
				permalink: hotspot.getAttribute('data-permalink') || hotspot.getAttribute('href') || '#'
			}];
		}

		currentTrips = trips;
		currentTripIndex = 0;

		// Show carousel arrows only for multi-destination districts
		if (carouselBar) carouselBar.style.display = currentTrips.length > 1 ? 'flex' : 'none';

		renderCurrentTrip();
		positionCardSmartly(event ? event.clientX : 0, event ? event.clientY : 0, cx, cy);

		previewCard.classList.add('is-visible');
		previewCard.setAttribute('aria-hidden', 'false');

		// Reset & trigger 10-second countdown animation bar
		if (timerBar) {
			timerBar.classList.remove('is-animating');
			void timerBar.offsetWidth; // Force CSS reflow
			timerBar.classList.add('is-animating');
		}

		if (backdrop && window.innerWidth <= 768) {
			backdrop.classList.add('is-visible');
		}

		hotspots.forEach(sp => sp.classList.remove('is-active'));
		hotspot.classList.add('is-active');

		// The popup card must stay open for 10 seconds or until user clicks different marker or close button
		autoCloseTimer = setTimeout(function() {
			closeMarkerPopup();
		}, 10000);
	}

	/* ---------------------------------------------------------
	   CLOSE PREVIEW CARD
	   --------------------------------------------------------- */
	function closeMarkerPopup() {
		if (autoCloseTimer) {
			clearTimeout(autoCloseTimer);
			autoCloseTimer = null;
		}

		if (previewCard) {
			previewCard.classList.remove('is-visible');
			previewCard.setAttribute('aria-hidden', 'true');
		}
		if (timerBar) {
			timerBar.classList.remove('is-animating');
		}
		if (backdrop) {
			backdrop.classList.remove('is-visible');
		}
		if (activeHotspot) {
			activeHotspot.classList.remove('is-active');
			activeHotspot = null;
		}
	}

	// Carousel navigation events (prevent card <a> navigation)
	if (carouselPrev) {
		carouselPrev.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (!currentTrips || currentTrips.length <= 1) return;
			currentTripIndex = (currentTripIndex - 1 + currentTrips.length) % currentTrips.length;
			renderCurrentTrip();
		});
	}

	if (carouselNext) {
		carouselNext.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (!currentTrips || currentTrips.length <= 1) return;
			currentTripIndex = (currentTripIndex + 1) % currentTrips.length;
			renderCurrentTrip();
		});
	}

	// Close button — dismiss popup immediately & clear 10s timer
	if (cardClose) {
		cardClose.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeMarkerPopup();
		});
	}

	// Backdrop tap — dismiss on mobile
	if (backdrop) {
		backdrop.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeMarkerPopup();
		});
	}

	// ESC key closes popup
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && previewCard && previewCard.classList.contains('is-visible')) {
			closeMarkerPopup();
		}
	});

	/* ---------------------------------------------------------
	   ATTACH HOTSPOT EVENTS:
	   - Disable direct navigation on map markers
	   - Clicking marker opens popup card
	   - Popup stays open for 10s or until another marker / close clicked
	   - Clicking popup card navigates to dedicated page
	   --------------------------------------------------------- */
	hotspots.forEach(function(spot) {
		// Disable direct navigation on click across all devices
		spot.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			openMarkerPopup(this, e);
		});

		// Accessible Enter/Space trigger
		spot.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				e.stopPropagation();
				openMarkerPopup(this, null);
			}
		});
	});

	/* ---------------------------------------------------------
	   CATEGORY FILTERS
	   --------------------------------------------------------- */
	filterBtns.forEach(function(btn) {
		btn.addEventListener('click', function() {
			const filter = this.getAttribute('data-filter');
			filterBtns.forEach(b => b.classList.remove('is-active'));
			this.classList.add('is-active');

			hotspots.forEach(function(spot) {
				const catAttr = spot.getAttribute('data-category') || '';
				const cats = catAttr.split(/\s+/).filter(Boolean);
				if (filter === 'all' || cats.includes(filter)) {
					spot.classList.remove('is-dimmed');
					spot.style.display = '';
				} else {
					spot.classList.add('is-dimmed');
				}
			});
		});
	});

	// Initial center on mobile
	if (viewport && window.innerWidth <= 1024) {
		const targetX = (viewport.scrollWidth - viewport.clientWidth) * 0.48;
		viewport.scrollLeft = targetX;
	}
})();
</script>