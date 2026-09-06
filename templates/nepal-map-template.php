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
		'districts'   => array( 'solukhumbu', 'sagarmatha', 'khumbu', 'everest', 'lukla', 'gokyo' ),
		'icon_type'   => 'peak',
	),
	'annapurna' => array(
		'title'       => 'Annapurna Region',
		'subtitle'    => 'Annapurna 8,091m · Sanctuary & Circuit',
		'category'    => 'peaks',
		'cx'          => 770,
		'cy'          => 382,
		'elevation'   => '8,091 m',
		'districts'   => array( 'kaski', 'manang', 'myagdi', 'annapurna', 'abc', 'poon-hill', 'mardi' ),
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
		'districts'   => array( 'gorkha', 'manaslu', 'tsum', 'larke' ),
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
		'districts'   => array( 'rasuwa', 'sindhupalchok', 'langtang', 'gosaikunda', 'helambu' ),
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

foreach ( $destinations_config as $key => $config ) {
	$matched_data = null;

	// Check if direct key exists in $region_data
	if ( ! empty( $region_data[ $key ] ) ) {
		$matched_data = $region_data[ $key ];
	} else {
		// Check alias district keys
		foreach ( $config['districts'] as $alias ) {
			$norm_alias = ya_map_normalize_key( $alias );
			if ( ! empty( $region_data[ $norm_alias ] ) ) {
				$matched_data = $region_data[ $norm_alias ];
				break;
			}
		}
	}

	$has_trips    = ! empty( $matched_data['has_trips'] ) || ! empty( $matched_data['count'] );
	$count        = ! empty( $matched_data['count'] ) ? intval( $matched_data['count'] ) : 0;
	$title        = ! empty( $matched_data['title'] ) ? $matched_data['title'] : $config['title'];
	$days         = ! empty( $matched_data['days'] ) ? $matched_data['days'] : '';
	$image        = ! empty( $matched_data['image'] ) ? $matched_data['image'] : '';
	$permalink    = ! empty( $matched_data['permalink'] ) ? $matched_data['permalink'] : add_query_arg( 'region', $key, $default_url );
	$destinations = ! empty( $matched_data['destinations'] ) && is_array( $matched_data['destinations'] ) ? $matched_data['destinations'] : array();

	// If has_trips is true but destinations array is empty, construct one
	if ( empty( $destinations ) && $has_trips ) {
		$destinations = array( array(
			'title'     => $title,
			'days'      => $days,
			'image'     => $image,
			'permalink' => $permalink,
		) );
	}

	if ( $has_trips ) {
		$active_trip_count++;
		if ( $count === 0 ) {
			$count = count( $destinations );
		}
	}

	$rendered_hotspots[ $key ] = array_merge( $config, array(
		'has_trips'    => $has_trips,
		'count'        => $count,
		'trip_title'   => $title,
		'days'         => $days,
		'image'        => $image,
		'permalink'    => $permalink,
		'destinations' => $destinations,
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

		<!-- Category Filter Pills -->
		<nav class="ya-nepal-map__filters" aria-label="Filter map destinations by category">
			<button type="button" class="ya-map-filter-btn is-active" data-filter="all">
				All Highlights
			</button>
			<button type="button" class="ya-map-filter-btn" data-filter="peaks">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
				Himalayan Treks
			</button>
			<button type="button" class="ya-map-filter-btn" data-filter="wildlife">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
				Wildlife Reserves
			</button>
			<button type="button" class="ya-map-filter-btn" data-filter="culture">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
				Cultural & Heritage
			</button>
		</nav>

		<!-- Counter badge -->
		<div class="ya-nepal-map__badge">
			<span class="ya-badge-dot"></span>
			<span class="ya-badge-count"><?php echo count( $rendered_hotspots ); ?> Destinations</span>
			<?php if ( $active_trip_count > 0 ) : ?>
				<span class="ya-badge-active">(<?php echo intval( $active_trip_count ); ?> Featured)</span>
			<?php endif; ?>
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
					$cx           = $spot['cx'];
					$cy           = $spot['cy'];
					$has_trips    = $spot['has_trips'];
					$count        = $spot['count'];
					$cat          = esc_attr( $spot['category'] );
					$link_url     = esc_url( $spot['permalink'] );
					$title_text   = esc_attr( $spot['trip_title'] );
					$subtitle     = esc_attr( $spot['subtitle'] );
					$days_text    = esc_attr( $spot['days'] );
					$img_url      = esc_url( $spot['image'] );
					$elevation    = esc_attr( $spot['elevation'] );
					$dest_list    = ! empty( $spot['destinations'] ) ? $spot['destinations'] : array();
					$dest_json    = esc_attr( wp_json_encode( $dest_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
					$hotspot_cls  = 'ya-map-hotspot' . ( $has_trips ? ' has-trips is-featured' : '' ) . ' cat-' . $cat;
				?>
					<a
						class="<?php echo $hotspot_cls; ?>"
						href="<?php echo $link_url; ?>"
						data-key="<?php echo esc_attr( $key ); ?>"
						data-category="<?php echo $cat; ?>"
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
						role="link"
						aria-label="<?php echo $title_text . ( $count > 0 ? ' (' . $count . ' journeys)' : '' ); ?>"
					>
						<!-- Animated Radar Pulse Ring for Featured Journeys -->
						<?php if ( $has_trips ) : ?>
							<circle class="ya-pin-pulse" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="20" />
							<circle class="ya-pin-aura" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="14" />
						<?php endif; ?>

						<!-- Clickable Hit Target (Large for Touch & Mouse) -->
						<circle class="ya-pin-hit" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="28" />

						<!-- Hotspot Marker Outer Circle Ring -->
						<circle class="ya-pin-outer" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="11" />

						<!-- Hotspot Marker Inner Center Dot -->
						<circle class="ya-pin-core" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="5" />

						<!-- Active Journey Count Badge (shown if multiple trips exist in district) -->
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

		<!-- Smart Floating Luxury Preview Card (Non-blocking with Left/Right Trip Carousel) -->
		<div
			id="<?php echo esc_attr( $map_id ); ?>-preview"
			class="ya-map-preview-card"
			role="dialog"
			aria-modal="false"
			aria-hidden="true"
			data-map-preview
		>
			<!-- Card Close Button -->
			<button type="button" class="ya-map-card__close" aria-label="Close preview" data-card-close>
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
			</button>

			<!-- Thumbnail Image Container -->
			<div class="ya-map-card__thumb" data-card-thumb style="display:none;">
				<img src="" alt="" class="ya-map-card__img" data-card-img />
				<span class="ya-map-card__duration" data-card-days></span>
			</div>

			<!-- Card Content Body -->
			<div class="ya-map-card__body">
				<div class="ya-map-card__header">
					<span class="ya-map-card__category" data-card-kicker>Nepal Destination</span>
					<span class="ya-map-card__elevation" data-card-elevation></span>
				</div>

				<h4 class="ya-map-card__title" data-card-title>Destination Title</h4>
				<p class="ya-map-card__subtitle" data-card-subtitle>Region Subtitle</p>

				<!-- Multi-Trip Left/Right Carousel Bar (Active when multiple trips in 1 district) -->
				<div class="ya-map-card__carousel-bar" data-card-carousel style="display:none;">
					<button type="button" class="ya-carousel-btn ya-carousel-prev" aria-label="Previous trip" data-carousel-prev>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
					</button>
					<span class="ya-carousel-counter" data-carousel-counter>Trip 1 of 3</span>
					<button type="button" class="ya-carousel-btn ya-carousel-next" aria-label="Next trip" data-carousel-next>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
					</button>
				</div>

				<!-- Card Footer with CTA -->
				<div class="ya-map-card__footer">
					<span class="ya-map-card__count-label" data-card-count>1 Featured Journey</span>
					<a href="#" class="ya-map-card__btn" data-card-btn>
						<span>View Itinerary</span>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</a>
				</div>
			</div>
		</div>

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
	--ya-ink: #0c2d2d;
	--ya-ink-dark: #071f1f;
	--ya-gold: #c9974b;
	--ya-gold-light: #e5c48b;
	--ya-gold-dark: #91682b;
	--ya-emerald: #154c4c;
	--ya-border: rgba(22, 94, 91, 0.12);
	--ya-cream: #fafbf9;
	--ya-white: #ffffff;
	--ya-muted: #5e7774;

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
	background: var(--ya-white);
	border-color: var(--ya-gold);
	color: var(--ya-gold-dark);
	transform: translateY(-1px);
}

.ya-map-filter-btn.is-active {
	background: var(--ya-ink);
	border-color: var(--ya-ink);
	color: var(--ya-gold-light);
	box-shadow: 0 4px 12px rgba(12, 45, 45, 0.16);
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
	background: #22c55e;
	box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
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

/* Hotspot Outer Ring */
.ya-pin-outer {
	fill: var(--ya-white);
	stroke: var(--ya-emerald);
	stroke-width: 2.2px;
	transition: all 0.22s ease;
	filter: drop-shadow(0 2px 4px rgba(12, 45, 45, 0.22));
}

/* Hotspot Inner Dot */
.ya-pin-core {
	fill: var(--ya-emerald);
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
	filter: drop-shadow(0 0 10px rgba(201, 151, 75, 0.75));
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
   SMART NON-BLOCKING HOVER PREVIEW CARD WITH CAROUSEL
   --------------------------------------------------------- */
.ya-map-preview-card {
	position: absolute;
	z-index: 999;
	width: 270px;
	background: rgba(12, 45, 45, 0.96);
	backdrop-filter: blur(14px);
	-webkit-backdrop-filter: blur(14px);
	border: 1px solid rgba(229, 196, 139, 0.35);
	border-radius: 12px;
	box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
	overflow: hidden;
	color: #ffffff;
	pointer-events: none;
	opacity: 0;
	visibility: hidden;
	transform: scale(0.95);
	transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1),
	            transform 0.2s cubic-bezier(0.16, 1, 0.3, 1),
	            visibility 0.2s;
}

.ya-map-preview-card.is-visible {
	opacity: 1;
	visibility: visible;
	pointer-events: auto;
	transform: scale(1);
}

.ya-map-card__close {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 10;
	width: 24px;
	height: 24px;
	border-radius: 50%;
	background: rgba(0, 0, 0, 0.5);
	border: 1px solid rgba(255, 255, 255, 0.2);
	color: #ffffff;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	padding: 0;
	transition: background 0.2s;
}

.ya-map-card__close:hover {
	background: rgba(201, 151, 75, 0.85);
}

.ya-map-card__thumb {
	position: relative;
	width: 100%;
	height: 120px;
	background: #082121;
	overflow: hidden;
}

.ya-map-card__img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
	transition: transform 0.35s ease;
}

.ya-map-preview-card:hover .ya-map-card__img {
	transform: scale(1.04);
}

.ya-map-card__duration {
	position: absolute;
	bottom: 8px;
	right: 8px;
	background: rgba(12, 45, 45, 0.92);
	color: var(--ya-gold-light);
	font-size: 11px;
	font-weight: 700;
	padding: 3px 8px;
	border-radius: 16px;
	border: 1px solid rgba(201, 151, 75, 0.4);
}

.ya-map-card__body {
	padding: 12px 14px;
}

.ya-map-card__header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 4px;
}

.ya-map-card__category {
	font-size: 10px;
	text-transform: uppercase;
	letter-spacing: 0.1em;
	color: var(--ya-gold-light);
	font-weight: 700;
}

.ya-map-card__elevation {
	font-size: 10px;
	font-weight: 600;
	color: #9ac2be;
}

.ya-map-card__title {
	margin: 0 0 3px;
	font-size: 15px;
	font-weight: 700;
	color: #ffffff;
	line-height: 1.3;
	letter-spacing: -0.01em;
}

.ya-map-card__subtitle {
	margin: 0 0 10px;
	font-size: 11px;
	color: #b0cecb;
	line-height: 1.35;
}

/* Multi-Trip Left/Right Carousel Bar */
.ya-map-card__carousel-bar {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 6px 10px;
	margin-bottom: 10px;
	background: rgba(255, 255, 255, 0.08);
	border-radius: 6px;
	border: 1px solid rgba(201, 151, 75, 0.25);
}

.ya-carousel-btn {
	width: 22px;
	height: 22px;
	border-radius: 4px;
	border: 1px solid rgba(255, 255, 255, 0.2);
	background: rgba(0, 0, 0, 0.35);
	color: #ffffff;
	cursor: pointer;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	transition: all 0.18s ease;
}

.ya-carousel-btn:hover {
	background: var(--ya-gold);
	border-color: var(--ya-gold);
	color: #ffffff;
}

.ya-carousel-counter {
	font-size: 11px;
	font-weight: 700;
	color: var(--ya-gold-light);
	letter-spacing: 0.04em;
}

.ya-map-card__footer {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding-top: 8px;
	border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.ya-map-card__count-label {
	font-size: 11px;
	color: #d1e3e1;
	font-weight: 600;
}

.ya-map-card__btn {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	font-size: 11px;
	font-weight: 700;
	color: var(--ya-gold-light);
	text-decoration: none;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	transition: gap 0.2s ease, color 0.2s ease;
}

.ya-map-card__btn:hover {
	gap: 6px;
	color: #ffffff;
}

/* ---------------------------------------------------------
   RESPONSIVE LAYOUT (WINDOW & MOBILE VIEW)
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

/* Tablet & Smaller Desktop */
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

/* Mobile Devices */
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

	/* On mobile: Dock preview card at bottom so it never covers finger or map */
	.ya-map-preview-card {
		position: fixed !important;
		bottom: 16px !important;
		left: 16px !important;
		right: 16px !important;
		top: auto !important;
		width: auto !important;
		max-width: 380px !important;
		margin: 0 auto !important;
		transform: translateY(20px) !important;
		border-radius: 14px;
		box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
	}

	.ya-map-preview-card.is-visible {
		transform: translateY(0) !important;
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
	const cardClose = previewCard?.querySelector('[data-card-close]');
	const cardThumb = previewCard?.querySelector('[data-card-thumb]');
	const cardImg = previewCard?.querySelector('[data-card-img]');
	const cardDays = previewCard?.querySelector('[data-card-days]');
	const cardKicker = previewCard?.querySelector('[data-card-kicker]');
	const cardElevation = previewCard?.querySelector('[data-card-elevation]');
	const cardTitle = previewCard?.querySelector('[data-card-title]');
	const cardSubtitle = previewCard?.querySelector('[data-card-subtitle]');
	const cardCount = previewCard?.querySelector('[data-card-count]');
	const cardBtn = previewCard?.querySelector('[data-card-btn]');
	const carouselBar = previewCard?.querySelector('[data-card-carousel]');
	const carouselPrev = previewCard?.querySelector('[data-carousel-prev]');
	const carouselNext = previewCard?.querySelector('[data-carousel-next]');
	const carouselCounter = previewCard?.querySelector('[data-carousel-counter]');

	const hotspots = root.querySelectorAll('.ya-map-hotspot');
	const filterBtns = root.querySelectorAll('.ya-map-filter-btn');

	let activeHotspot = null;
	let hideTimeout = null;
	let currentTrips = [];
	let currentTripIndex = 0;

	/* ---------------------------------------------------------
	   SMART NON-BLOCKING POSITIONING (LEFT / RIGHT OF PIN)
	   --------------------------------------------------------- */
	function positionCardSmartly(pinX, pinY, spotCx, spotCy) {
		if (!previewCard || !viewport) return;

		// On mobile, card is fixed to bottom viewport via CSS
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

		const cardWidth = 270;
		const cardHeight = previewCard.offsetHeight || 220;

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

		if (cardTitle) cardTitle.textContent = trip.title || 'Journey';
		if (cardDays) {
			if (trip.days) {
				cardDays.textContent = trip.days;
				cardDays.style.display = 'inline-block';
			} else {
				cardDays.style.display = 'none';
			}
		}

		if (trip.image && cardImg && cardThumb) {
			cardImg.src = trip.image;
			cardThumb.style.display = 'block';
		} else if (cardThumb) {
			cardThumb.style.display = 'none';
		}

		if (cardBtn && trip.permalink) {
			cardBtn.setAttribute('href', trip.permalink);
		}

		if (carouselCounter) {
			carouselCounter.textContent = 'Trip ' + (currentTripIndex + 1) + ' of ' + currentTrips.length;
		}
	}

	/* ---------------------------------------------------------
	   SHOW PREVIEW CARD FOR A HOTSPOT
	   --------------------------------------------------------- */
	function showPreview(hotspot, event) {
		if (hideTimeout) clearTimeout(hideTimeout);
		activeHotspot = hotspot;

		const title = hotspot.getAttribute('data-title') || 'Nepal Region';
		const subtitle = hotspot.getAttribute('data-subtitle') || '';
		const elevation = hotspot.getAttribute('data-elevation') || '';
		const cx = parseFloat(hotspot.getAttribute('data-cx') || '0');
		const cy = parseFloat(hotspot.getAttribute('data-cy') || '0');
		const category = hotspot.getAttribute('data-category') || 'peaks';

		// Parse trips list
		let trips = [];
		try {
			const tripsAttr = hotspot.getAttribute('data-trips');
			if (tripsAttr) trips = JSON.parse(tripsAttr);
		} catch (err) {
			trips = [];
		}

		if (!trips || !trips.length) {
			trips = [{
				title: title,
				days: hotspot.getAttribute('data-days') || '',
				image: hotspot.getAttribute('data-img') || '',
				permalink: hotspot.getAttribute('href') || '#'
			}];
		}

		currentTrips = trips;
		currentTripIndex = 0;

		// Set Region & Elevation Header
		if (cardSubtitle) cardSubtitle.textContent = subtitle;
		if (cardElevation) cardElevation.textContent = elevation ? 'Alt: ' + elevation : '';

		let kicker = 'Himalayan Peak';
		if (category === 'wildlife') kicker = 'Wildlife Reserve';
		else if (category === 'culture') kicker = 'Cultural Landmark';
		if (cardKicker) cardKicker.textContent = kicker;

		// Show/hide multi-trip carousel bar
		if (currentTrips.length > 1) {
			if (carouselBar) carouselBar.style.display = 'flex';
			if (cardCount) cardCount.textContent = currentTrips.length + ' Journeys in Region';
		} else {
			if (carouselBar) carouselBar.style.display = 'none';
			if (cardCount) cardCount.textContent = '1 Featured Journey';
		}

		renderCurrentTrip();
		positionCardSmartly(event ? event.clientX : 0, event ? event.clientY : 0, cx, cy);

		previewCard.classList.add('is-visible');
		previewCard.setAttribute('aria-hidden', 'false');

		// Highlight active circle
		hotspots.forEach(sp => sp.classList.remove('is-active'));
		hotspot.classList.add('is-active');
	}

	/* ---------------------------------------------------------
	   HIDE PREVIEW CARD
	   --------------------------------------------------------- */
	function hidePreview() {
		hideTimeout = setTimeout(function() {
			if (previewCard) {
				previewCard.classList.remove('is-visible');
				previewCard.setAttribute('aria-hidden', 'true');
			}
			if (activeHotspot) {
				activeHotspot.classList.remove('is-active');
				activeHotspot = null;
			}
		}, 140);
	}

	// Carousel navigation events
	if (carouselPrev) {
		carouselPrev.addEventListener('click', function(e) {
			e.stopPropagation();
			if (!currentTrips || currentTrips.length <= 1) return;
			currentTripIndex = (currentTripIndex - 1 + currentTrips.length) % currentTrips.length;
			renderCurrentTrip();
		});
	}

	if (carouselNext) {
		carouselNext.addEventListener('click', function(e) {
			e.stopPropagation();
			if (!currentTrips || currentTrips.length <= 1) return;
			currentTripIndex = (currentTripIndex + 1) % currentTrips.length;
			renderCurrentTrip();
		});
	}

	// Close button
	if (cardClose) {
		cardClose.addEventListener('click', function(e) {
			e.stopPropagation();
			if (previewCard) {
				previewCard.classList.remove('is-visible');
				previewCard.setAttribute('aria-hidden', 'true');
			}
			if (activeHotspot) {
				activeHotspot.classList.remove('is-active');
				activeHotspot = null;
			}
		});
	}

	// Keep preview open when hovering into the card
	if (previewCard) {
		previewCard.addEventListener('mouseenter', function() {
			if (hideTimeout) clearTimeout(hideTimeout);
		});
		previewCard.addEventListener('mouseleave', function() {
			hidePreview();
		});
	}

	/* ---------------------------------------------------------
	   ATTACH HOTSPOT EVENTS
	   --------------------------------------------------------- */
	hotspots.forEach(function(spot) {
		spot.addEventListener('mouseenter', function(e) {
			showPreview(this, e);
		});

		spot.addEventListener('mousemove', function(e) {
			const cx = parseFloat(this.getAttribute('data-cx') || '0');
			const cy = parseFloat(this.getAttribute('data-cy') || '0');
			positionCardSmartly(e.clientX, e.clientY, cx, cy);
		});

		spot.addEventListener('mouseleave', function() {
			hidePreview();
		});

		spot.addEventListener('focus', function(e) {
			showPreview(this, e);
		});

		spot.addEventListener('blur', function() {
			hidePreview();
		});

		// Mobile tap
		spot.addEventListener('click', function(e) {
			if (window.innerWidth <= 768) {
				e.preventDefault();
				showPreview(this, null);
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
				const cat = spot.getAttribute('data-category');
				if (filter === 'all' || cat === filter) {
					spot.classList.remove('is-dimmed');
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