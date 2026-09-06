<?php
/**
 * Premium Editorial Single Post Template
 * Compatible with Default WordPress Posts
 * Loaded by Trek Content Manager
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ya_render_single_post_social_meta' ) ) {
	function ya_render_single_post_social_meta() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		$title   = get_the_title( $post_id );
		$url     = get_permalink( $post_id );
		$desc    = get_the_excerpt( $post_id );
		$image   = get_the_post_thumbnail_url( $post_id, 'full' );

		echo '<meta property="og:type" content="article">' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
		if ( $image ) {
			echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
		if ( $image ) {
			echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
		}
	}

	add_action( 'wp_head', 'ya_render_single_post_social_meta', 5 );
}


/* =========================================================
   AUTHOR IMAGE META BOX
   Shows in WordPress Admin > Posts > Edit Post > Sidebar
========================================================= */

if ( ! function_exists( 'ya_register_author_image_metabox' ) ) {

	function ya_register_author_image_metabox() {

		add_meta_box(
			'ya_post_author_image',
			'Author Profile Image',
			'ya_render_author_image_metabox',
			'post',
			'side',
			'default'
		);
	}

	add_action( 'add_meta_boxes', 'ya_register_author_image_metabox' );
}

if ( ! function_exists( 'ya_enqueue_author_image_media' ) ) {
	function ya_enqueue_author_image_media( $hook ) {
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			wp_enqueue_media();
		}
	}

	add_action( 'admin_enqueue_scripts', 'ya_enqueue_author_image_media' );
}


if ( ! function_exists( 'ya_render_author_image_metabox' ) ) {

	function ya_render_author_image_metabox( $post ) {

		wp_nonce_field(
			'ya_save_author_image',
			'ya_author_image_nonce'
		);

		$image_id = get_post_meta(
			$post->ID,
			'_ya_author_image_id',
			true
		);

		$image_url = '';
		if ( $image_id ) {
			$image_url = wp_get_attachment_image_url( $image_id, 'medium' );
		}

		$author_name = get_post_meta(
			$post->ID,
			'_ya_author_name',
			true
		);

		?>
		<div class="ya-admin-author-image">
			<p>
				<label for="ya-author-name"><strong>Author display name</strong></label>
				<input type="text" id="ya-author-name" name="ya_author_name" value="<?php echo esc_attr( $author_name ); ?>" class="widefat" placeholder="Uses WordPress author name if empty">
			</p>
			<div id="ya-author-image-preview" style="width:110px;height:110px;margin:0 auto 15px;border-radius:50%;overflow:hidden;background:#f1f1f1;display:flex;align-items:center;justify-content:center;border:1px solid #ddd;">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
				<?php else : ?>
					<span style="padding:15px;text-align:center;font-size:11px;color:#777;">No image selected</span>
				<?php endif; ?>
			</div>
			<input type="hidden" id="ya-author-image-id" name="ya_author_image_id" value="<?php echo esc_attr( $image_id ); ?>">
			<p><button type="button" class="button button-primary" id="ya-upload-author-image">Choose Author Image</button></p>
			<p><button type="button" class="button" id="ya-remove-author-image">Remove Image</button></p>
			<p style="font-size:11px;line-height:1.5;color:#777;">This image will be displayed in the hero and author profile for this specific post.</p>
		</div>
		<script>
		jQuery(function($){
			var mediaFrame;
			$('#ya-upload-author-image').on('click', function(e){
				e.preventDefault();
				if ( mediaFrame ) { mediaFrame.open(); return; }
				mediaFrame = wp.media({ title: 'Select Author Image', button: { text: 'Use Author Image' }, multiple: false });
				mediaFrame.on('select', function(){
					var attachment = mediaFrame.state().get('selection').first().toJSON();
					$('#ya-author-image-id').val(attachment.id);
					$('#ya-author-image-preview').html('<img src="' + attachment.url + '" alt="" style="width:100%;height:100%;object-fit:cover;">');
				});
				mediaFrame.open();
			});
			$('#ya-remove-author-image').on('click', function(){
				$('#ya-author-image-id').val('');
				$('#ya-author-image-preview').html('<span style="padding:15px;text-align:center;font-size:11px;color:#777;">No image selected</span>');
			});
		});
		</script>
		<?php
		return;
	}
}


if ( ! function_exists( 'ya_save_author_image' ) ) {

	function ya_save_author_image( $post_id ) {

		if (
			! isset( $_POST['ya_author_image_nonce'] )
		) {
			return;
		}


		$nonce = sanitize_text_field(
			wp_unslash(
				$_POST['ya_author_image_nonce']
			)
		);


		if (
			! wp_verify_nonce(
				$nonce,
				'ya_save_author_image'
			)
		) {
			return;
		}


		if (
			defined( 'DOING_AUTOSAVE' ) &&
			DOING_AUTOSAVE
		) {
			return;
		}


		if (
			! current_user_can(
				'edit_post',
				$post_id
			)
		) {
			return;
		}


		$image_id = isset(
			$_POST['ya_author_image_id']
		)
			? absint(
				$_POST['ya_author_image_id']
			)
			: 0;
		$author_name = isset( $_POST['ya_author_name'] )
			? sanitize_text_field( wp_unslash( $_POST['ya_author_name'] ) )
			: '';


		if ( $image_id ) {

			update_post_meta(
				$post_id,
				'_ya_author_image_id',
				$image_id
			);

		} else {

			delete_post_meta(
				$post_id,
				'_ya_author_image_id'
			);
		}

		if ( $author_name ) {
			update_post_meta( $post_id, '_ya_author_name', $author_name );
		} else {
			delete_post_meta( $post_id, '_ya_author_name' );
		}
	}

	add_action(
		'save_post_post',
		'ya_save_author_image'
	);
}


/* =========================================================
   TEMPLATE START
========================================================= */

get_header();


while ( have_posts() ) :

	the_post();


	$post_id = get_the_ID();


	/* -----------------------------------------------------
	   CATEGORY
	----------------------------------------------------- */

	$categories = get_the_category( $post_id );

	$trek_categories = get_the_terms(
		$post_id,
		'trek_category'
	);

	if ( is_wp_error( $trek_categories ) ) {

		$trek_categories = array();
	}


	if ( ! empty( $trek_categories ) ) {

		$category = strtoupper(
			$trek_categories[0]->name
		);

	} elseif ( ! empty( $categories ) ) {

		$category = strtoupper(
			$categories[0]->name
		);

	} else {

		$category = 'JOURNAL';
	}


	/* -----------------------------------------------------
	   BASIC POST DATA
	----------------------------------------------------- */

	$excerpt = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';

	$post_url = get_permalink( $post_id );

	$content_text = wp_strip_all_tags(
		get_post_field(
			'post_content',
			$post_id
		)
	);

	$word_count = str_word_count(
		$content_text
	);

	$reading_time = max(
		1,
		(int) ceil(
			$word_count / 200
		)
	);


	/* -----------------------------------------------------
	   AUTHOR
	----------------------------------------------------- */

	$author_id = (int) get_the_author_meta(
		'ID'
	);

	$author_name = get_the_author_meta(
		'display_name',
		$author_id
	);

	$custom_author_name = get_post_meta(
		$post_id,
		'_ya_author_name',
		true
	);

	if ( $custom_author_name ) {
		$author_name = $custom_author_name;
	}

	$author_bio = get_the_author_meta(
		'description',
		$author_id
	);

	$author_initial = ! empty( $author_name )
		? strtoupper(
			mb_substr(
				$author_name,
				0,
				1
			)
		)
		: 'A';


	$custom_author_image_id = get_post_meta(
		$post_id,
		'_ya_author_image_id',
		true
	);


	$custom_author_image = '';

	if ( $custom_author_image_id ) {

		$custom_author_image = wp_get_attachment_image_url(
			$custom_author_image_id,
			'medium'
		);
	}


	/* -----------------------------------------------------
	   FEATURED IMAGE
	----------------------------------------------------- */

	$featured_image = has_post_thumbnail( $post_id )
		? get_the_post_thumbnail_url(
			$post_id,
			'full'
		)
		: '';


	/* -----------------------------------------------------
	   JOURNAL PAGE
	----------------------------------------------------- */

	$journal_page = get_page_by_path(
		'journal'
	);

	$journal_url = $journal_page
		? get_permalink( $journal_page->ID )
		: home_url( '/journal/' );


	/* -----------------------------------------------------
	   HERO SUBTEXT
	   Uses excerpt, with a cleaner fallback
	----------------------------------------------------- */

	$hero_subtext = $excerpt;

	if ( empty( $hero_subtext ) ) {

		$hero_subtext =
			'Take your time, follow the story, and discover the places, details and moments that make every journey worth remembering.';
	}


	/* -----------------------------------------------------
	   ADD IDs TO H2 / H3
	----------------------------------------------------- */

	$raw_content = get_post_field(
		'post_content',
		$post_id
	);

	$heading_index = 0;


	$content_with_ids = preg_replace_callback(

		'/<h([2-3])([^>]*)>(.*?)<\/h[2-3]>/is',

		function( $matches ) use (
			&$heading_index
		) {

			$heading_index++;

			$tag   = $matches[1];
			$attrs = $matches[2];
			$text  = $matches[3];

			$section_id =
				'ya-story-section-' .
				$heading_index;


			if (
				stripos(
					$attrs,
					' id='
				) === false
			) {

				$attrs .=
					' id="' .
					esc_attr( $section_id ) .
					'"';
			}


			return
				'<h' .
				$tag .
				$attrs .
				'>' .
				$text .
				'</h' .
				$tag .
				'>';
		},

		$raw_content
	);


	$processed_content = apply_filters(
		'the_content',
		$content_with_ids
	);

	$article_intro = '';
	if ( preg_match( '/<p[^>]*>(.*?)<\/p>/is', $processed_content, $intro_match ) ) {
		$article_intro = trim( wp_strip_all_tags( $intro_match[1] ) );
		$hero_text      = trim( wp_strip_all_tags( $hero_subtext ) );

		if ( empty( $article_intro ) || 0 === strcasecmp( $article_intro, $hero_text ) ) {
			$article_intro = '';
		} else {
			$processed_content = preg_replace( '/<p[^>]*>.*?<\/p>/is', '', $processed_content, 1 );
		}
	}


	/* -----------------------------------------------------
	   TABLE OF CONTENTS
	----------------------------------------------------- */

	preg_match_all(

		'/<h([2-3])[^>]*id=["\']([^"\']+)["\'][^>]*>(.*?)<\/h[2-3]>/is',

		$processed_content,

		$toc_items,

		PREG_SET_ORDER
	);


	/* -----------------------------------------------------
	   RELATED POSTS
	----------------------------------------------------- */

	$related_args = array(

		'post_type'           => 'post',

		'posts_per_page'      => 3,

		'post__not_in'        => array(
			$post_id
		),

		'ignore_sticky_posts' => true,

		'post_status'         => 'publish'

	);


	if ( ! empty( $categories ) ) {

		$related_args['category__in'] = array(
			$categories[0]->term_id
		);
	}


	$related_posts = new WP_Query(
		$related_args
	);

?>


<style>

/* =========================================================
   ROOT
========================================================= */

.ya-single-post {

	--ya-bg: #f4f2ee;
	--ya-paper: #ffffff;
	--ya-text: #252525;
	--ya-muted: #77736d;
	--ya-line: #dedad3;
	--ya-accent: #d9a441;

	--ya-serif:
		Georgia,
		"Times New Roman",
		serif;

	--ya-sans:
		Inter,
		Arial,
		Helvetica,
		sans-serif;

	position: relative;

	background: var(--ya-paper);

	color: var(--ya-text);

	margin-top: -24px;

	overflow: hidden;
}


.ya-single-post *,
.ya-single-post *::before,
.ya-single-post *::after {

	box-sizing: border-box;
}


.ya-post-container {

	width: min(
		1280px,
		calc(100% - 64px)
	);

	margin: 0 auto;
}


/* =========================================================
   HERO
========================================================= */

.ya-post-hero {

	position: relative;

	min-height: 760px;

	display: flex;

	align-items: flex-start;

	background-color: #202020;

	background-size: cover;

	background-position: center;

	isolation: isolate;

	padding: 80px 0 70px;
}


.ya-post-hero::before {

	content: "";

	position: absolute;

	inset: 0;

	z-index: -1;

	background:

		linear-gradient(
			90deg,
			rgba(8,12,16,.82) 0%,
			rgba(8,12,16,.58) 43%,
			rgba(8,12,16,.16) 100%
		),

		linear-gradient(
			180deg,
			rgba(0,0,0,.25) 0%,
			rgba(0,0,0,.12) 42%,
			rgba(0,0,0,.82) 100%
		);
}


.ya-post-hero-inner {

	position: relative;

	width: 1250px;

	padding-top: 70px;
}


.ya-back-link {

	display: inline-flex;

	align-items: center;

	gap: 10px;

	color: rgba(255,255,255,.72);

	font-family: var(--ya-sans);

	font-size: 10px;

	font-weight: 700;

	letter-spacing: .16em;

	text-decoration: none;

	text-transform: uppercase;

	transition: opacity .25s ease;
}


.ya-back-link:hover {

	opacity: .55;
}


.ya-hero-content {

	max-width: 1000px;

	margin-top: 82px;
}


.ya-post-meta {

	display: flex;

	align-items: center;

	gap: 13px;

	margin-bottom: 25px;

	color: rgba(255,255,255,.85);

	font-family: var(--ya-sans);

	font-size: 10px;

	font-weight: 700;

	letter-spacing: .16em;

	text-transform: uppercase;
}


.ya-post-meta i {

	width: 4px;

	height: 4px;

	border-radius: 50%;

	background: var(--ya-accent);
}


.ya-post-title {

	max-width: 1050px;

	margin: 0;

	color: #fff;

	font-family: var(--ya-serif);

	font-size: clamp(58px, 6vw, 80px);

	font-weight: 400;

	line-height: .98;

	letter-spacing: -.045em;
}


.ya-post-title strong {

	font-weight: 700;
}


.ya-post-excerpt {

	max-width: 650px;

	margin-top: 28px;

	color: rgba(255,255,255,.88);

	font-family: var(--ya-sans);

	font-size: 16px;

	line-height: 1.8;

	letter-spacing: -.01em;
}


.ya-post-excerpt strong {

	color: #fff;

	font-weight: 700;
}


/* HERO AUTHOR */

.ya-post-author-row {

	display: flex;

	align-items: center;

	gap: 32px;

	margin-top: 38px;
}


.ya-post-author {

	display: flex;

	align-items: center;

	gap: 13px;

	color: #fff;
}


.ya-author-avatar {

	width: 46px;

	height: 46px;

	border-radius: 50%;

	overflow: hidden;

	background: rgba(255,255,255,.15);

	border: 1px solid rgba(255,255,255,.35);
}


.ya-author-avatar img {

	width: 100%;

	height: 100%;

	display: block;

	object-fit: cover;
}


.ya-author-avatar-initial {

	width: 100%;

	height: 100%;

	display: flex;

	align-items: center;

	justify-content: center;

	color: #fff;

	font-family: var(--ya-serif);

	font-size: 20px;
}


.ya-small-label {

	display: block;

	margin-bottom: 5px;

	color: rgba(255,255,255,.5);

	font-family: var(--ya-sans);

	font-size: 8px;

	font-weight: 700;

	letter-spacing: .16em;
}


.ya-post-author strong,
.ya-post-date strong {

	color: #fff;

	font-family: var(--ya-sans);

	font-size: 12px;

	font-weight: 700;
}


.ya-post-date {

	padding-left: 30px;

	border-left:
		1px solid
		rgba(255,255,255,.28);
}


/* =========================================================
   ARTICLE
========================================================= */

.ya-article-section {

	padding: 78px 0 128px;

	background: linear-gradient(180deg, #f8f7f3 0%, var(--ya-paper) 180px);
	border-top: 1px solid rgba(37,37,37,.08);
}


.ya-article-layout {

	width: min(
		930px,
		calc(100% - 64px)
	);

	margin: 0 auto;

	display: grid;

	grid-template-columns:
		52px
		minmax(0, 720px);

	gap: 72px;
}


/* =========================================================
   SHARE BUTTONS
========================================================= */

.ya-share-bar {

	position: sticky;

	top: 100px;

	height: max-content;

	display: flex;

	flex-direction: column;

	align-items: center;

	gap: 11px;
}


.ya-share-label {

	margin-bottom: 8px;

	color: var(--ya-muted);

	font-family: var(--ya-sans);

	font-size: 8px;

	font-weight: 700;

	letter-spacing: .16em;

	writing-mode: vertical-rl;

	transform: rotate(180deg);
}


.ya-share-button {

	width: 39px;

	height: 39px;

	display: flex;

	align-items: center;

	justify-content: center;

	border:
		1px solid
		var(--ya-line);

	border-radius: 50%;

	background: #fff;

	color: var(--ya-text);

	font-family: var(--ya-sans);

	font-size: 13px;

	font-weight: 700;

	text-decoration: none;

	cursor: pointer;

	appearance: none;

	transition:

		transform .25s ease,

		background .25s ease,

		color .25s ease,

		border-color .25s ease;
}


.ya-share-button:hover {

	transform: translateY(-3px);

	background: var(--ya-text);

	border-color: var(--ya-text);

	color: #fff;
}

.ya-post-like {
	width: auto;
	min-width: 64px;
	padding: 0 12px;
	border-radius: 22px;
	font-size: 11px;
}

.ya-post-like.is-liked {
	background: var(--ya-accent);
	border-color: var(--ya-accent);
	color: #fff;
}

.ya-share-card {
	position: fixed;
	inset: 0;
	z-index: 1000;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 24px;
	background: rgba(20, 22, 22, .62);
	backdrop-filter: blur(8px);
}

.ya-share-card[hidden] {
	display: none;
}

.ya-share-card-inner {
	position: relative;
	width: min(420px, 100%);
	padding: 26px;
	background: #fff;
	color: var(--ya-text);
	box-shadow: 0 24px 70px rgba(0, 0, 0, .22);
}

.ya-share-card-close {
	position: absolute;
	top: 14px;
	right: 14px;
	width: 32px;
	height: 32px;
	border: 1px solid var(--ya-line);
	border-radius: 50%;
	background: #fff;
	color: var(--ya-text);
	cursor: pointer;
}

.ya-share-card-image {
	width: 100%;
	height: 180px;
	margin-bottom: 20px;
	object-fit: cover;
	display: block;
}

.ya-share-card-kicker {
	margin: 0 0 8px;
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 9px;
	font-weight: 800;
	letter-spacing: .16em;
}

.ya-share-card-title {
	margin: 0 0 20px;
	font-family: var(--ya-serif);
	font-size: 30px;
	font-weight: 400;
	line-height: 1.08;
}

.ya-share-card-url {
	display: block;
	margin: -8px 0 22px;
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 11px;
	line-height: 1.5;
	overflow: hidden;
	text-overflow: ellipsis;
	text-decoration: underline;
	text-underline-offset: 3px;
	white-space: nowrap;
}

.ya-share-card-actions {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 8px;
}

.ya-share-card-actions a,
.ya-share-card-copy {
	min-height: 40px;
	display: flex;
	align-items: center;
	justify-content: center;
	border: 1px solid var(--ya-line);
	background: #fff;
	color: var(--ya-text);
	font-family: var(--ya-sans);
	font-size: 10px;
	font-weight: 700;
	text-decoration: none;
	cursor: pointer;
}

.ya-share-card-actions a:hover,
.ya-share-card-copy:hover {
	background: var(--ya-text);
	border-color: var(--ya-text);
	color: #fff;
}

@media (max-width: 520px) {
	.ya-share-card {
		padding: 16px;
	}

	.ya-share-card-inner {
		padding: 20px;
	}

	.ya-share-card-image {
		height: 140px;
	}

	.ya-share-card-actions {
		grid-template-columns: repeat(2, 1fr);
	}
}


.ya-share-facebook {

	font-family: Arial, sans-serif;

	font-size: 16px;
}


.ya-share-linkedin {

	font-size: 11px;
}


/* =========================================================
   TABLE OF CONTENTS
========================================================= */

.ya-toc {

	margin-bottom: 58px;

	padding: 22px 0 28px;

	border-top:
		1px solid
		var(--ya-text);

	border-bottom:
		1px solid
		var(--ya-line);
}


.ya-toc-title {

	display: block;

	margin-bottom: 20px;

	color: var(--ya-text);

	font-family: var(--ya-sans);

	font-size: 9px;

	font-weight: 800;

	letter-spacing: .18em;
}


.ya-toc-list {

	display: grid;

	grid-template-columns:
		repeat(2, 1fr);

	gap: 12px 35px;
}


.ya-toc-list a {

	color: var(--ya-text);

	font-family: var(--ya-sans);

	font-size: 12px;

	line-height: 1.5;

	text-decoration: none;

	transition:
		color .2s ease;
}


.ya-toc-list a:hover {

	color: var(--ya-accent);
}


/* =========================================================
   ITALIC LEAD BELOW IMAGE
   LEFT VERTICAL YELLOW ACCENT
========================================================= */

.ya-intro-lead {

	position: relative;

	max-width: 650px;
	margin: 0 0 64px;

	padding: 14px 0 14px 30px;

	border-left:
		2px solid
		var(--ya-accent);

	color: #303030;

	font-family: var(--ya-serif);

	font-size: clamp(25px, 2.7vw, 33px);

	font-style: italic;

	font-weight: 400;

	line-height: 1.42;

	letter-spacing: -.02em;
	text-wrap: pretty;
}


/* =========================================================
   WORDPRESS CONTENT
========================================================= */

.ya-wordpress-content {

	color: #363636;

	font-family: var(--ya-serif);

	font-size: 19px;

	line-height: 1.85;
}


.ya-wordpress-content p {

	margin:
		0
		0
		1.65em;
}


.ya-wordpress-content p strong {

	color: #202020;

	font-weight: 700;
}


.ya-wordpress-content h2 {

	margin:
		2.5em
		0
		.75em;

	color: #242424;

	font-family: var(--ya-serif);

	font-size:
		clamp(
			34px,
			3.8vw,
			48px
		);

	font-weight: 400;

	line-height: 1.08;

	letter-spacing: -.035em;

	scroll-margin-top: 110px;
}


.ya-wordpress-content h3 {

	margin:
		2.2em
		0
		.75em;

	color: #262626;

	font-family: var(--ya-serif);

	font-size: 30px;

	font-weight: 400;

	line-height: 1.15;

	scroll-margin-top: 110px;
}


.ya-wordpress-content ul,
.ya-wordpress-content ol {

	margin:
		0
		0
		1.7em;

	padding-left: 1.3em;
}


.ya-wordpress-content li {

	margin-bottom: .55em;
}


.ya-wordpress-content figure {

	margin:
		55px
		0;
}


.ya-wordpress-content img {

	width: 100%;

	height: auto;

	display: block;
}


.ya-wordpress-content figcaption {

	margin-top: 11px;

	color: var(--ya-muted);

	font-family: var(--ya-sans);

	font-size: 10px;

	line-height: 1.6;
}


.ya-wordpress-content blockquote {

	margin:
		60px
		0;

	padding:
		5px
		0
		5px
		30px;

	border-left:
		3px solid
		var(--ya-accent);

	font-family: var(--ya-serif);

	font-size:
		clamp(
			28px,
			3vw,
			42px
		);

	font-style: italic;

	line-height: 1.32;
}


/* =========================================================
   COMMENTS
========================================================= */

.ya-comments-section {
	padding: 82px 0 96px;
	background: #f0ede7;
	border-top: 1px solid var(--ya-line);
}

.ya-comments-inner {
	width: min(790px, calc(100% - 64px));
	margin: 0 auto;
}

.ya-comments-heading {
	margin: 0 0 36px;
	padding-top: 28px;
	border-top: 1px solid var(--ya-text);
}

.ya-comments-heading .ya-section-label {
	margin-bottom: 12px;
}

.ya-comments-heading h2 {
	margin: 0;
	color: var(--ya-text);
	font-family: var(--ya-serif);
	font-size: clamp(36px, 4.5vw, 54px);
	font-weight: 400;
	line-height: 1;
}

.ya-comment-list {
	margin: 0 0 46px;
	padding: 0;
	list-style: none;
}

.ya-comment-item {
	display: grid;
	grid-template-columns: 48px minmax(0, 1fr);
	gap: 16px;
	padding: 22px 0;
	border-bottom: 1px solid var(--ya-line);
}

.ya-comment-avatar img {
	width: 48px;
	height: 48px;
	border-radius: 50%;
	display: block;
}

.ya-comment-author {
	margin: 0 0 6px;
	font-family: var(--ya-sans);
	font-size: 12px;
	font-weight: 700;
}

.ya-comment-date {
	margin-left: 8px;
	color: var(--ya-muted);
	font-size: 10px;
	font-weight: 400;
}

.ya-comment-text {
	color: #4c4a46;
	font-family: var(--ya-serif);
	font-size: 18px;
	line-height: 1.6;
}

.ya-comment-text p:last-child {
	margin-bottom: 0;
}

.ya-comment-like {
	margin-top: 14px;
	padding: 0;
	border: 0;
	background: transparent;
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 11px;
	cursor: pointer;
}

.ya-comment-like span {
	font-size: 18px;
	line-height: 0;
	vertical-align: -2px;
}

.ya-comment-like:hover,
.ya-comment-like.is-liked {
	color: var(--ya-accent);
}

.ya-comments-form h3 {
	margin: 0 0 8px;
	font-family: var(--ya-serif);
	font-size: 30px;
	font-weight: 400;
}

.ya-comment-note,
.ya-comment-status {
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 12px;
	line-height: 1.6;
}

.ya-live-comment-form p {
	margin: 0 0 16px;
}

.ya-comment-website-field {
	position: absolute;
	width: 1px;
	height: 1px;
	overflow: hidden;
	clip: rect(0 0 0 0);
	white-space: nowrap;
}

.ya-live-comment-form label {
	display: block;
	margin-bottom: 7px;
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 10px;
	font-weight: 800;
	letter-spacing: .12em;
	text-transform: uppercase;
}

.ya-comments-form .comment-form-comment label,
.ya-comments-form .comment-form-author label,
.ya-comments-form .comment-form-email label {
	display: block;
	margin-bottom: 7px;
	color: var(--ya-muted);
	font-family: var(--ya-sans);
	font-size: 10px;
	font-weight: 800;
	letter-spacing: .12em;
	text-transform: uppercase;
}

.ya-live-comment-form textarea,
.ya-live-comment-form input[type="text"],
.ya-live-comment-form input[type="email"] {
	width: 100%;
	padding: 13px 14px;
	border: 1px solid #d4cec4;
	border-radius: 0;
	background: #fff;
	color: var(--ya-text);
	font: inherit;
}

.ya-live-comment-form textarea {
	min-height: 140px;
	resize: vertical;
}

.ya-live-comment-form textarea:focus,
.ya-live-comment-form input:focus {
	outline: 2px solid rgba(217,164,65,.45);
	outline-offset: 2px;
}

.ya-live-comment-form button[type="submit"] {
	padding: 13px 20px;
	border: 0;
	background: var(--ya-text);
	color: #fff;
	font-family: var(--ya-sans);
	font-size: 10px;
	font-weight: 800;
	letter-spacing: .14em;
	text-transform: uppercase;
	cursor: pointer;
}

.ya-live-comment-form button[type="submit"]:hover {
	background: var(--ya-accent);
}

.ya-section-label {

	display: block;

	margin-bottom: 11px;

	color: var(--ya-muted);

	font-family: var(--ya-sans);

	font-size: 9px;

	font-weight: 800;

	letter-spacing: .17em;

	text-transform: uppercase;
}


/* =========================================================
   MORE JOURNAL
   SIMPLIFIED - LIGHT BACKGROUND
========================================================= */

.ya-related-section {

	padding: 100px 0 115px;

	background: var(--ya-bg);

	color: var(--ya-text);
}


.ya-related-heading {

	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	gap: 30px;

	margin-bottom: 48px;
}


.ya-related-heading h2 {

	margin: 0;

	color: var(--ya-text);

	font-family: var(--ya-serif);

	font-size:
		clamp(
			44px,
			5vw,
			68px
		);

	font-weight: 400;

	line-height: .98;

	letter-spacing: -.045em;
}


.ya-related-heading h2 strong {

	font-weight: 700;
}

.ya-view-journal {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	flex: 0 0 auto;
	margin-bottom: 5px;
	color: var(--ya-text);
	font-family: var(--ya-sans);
	font-size: 10px;
	font-weight: 800;
	letter-spacing: .14em;
	text-decoration: none;
	text-transform: uppercase;
	white-space: nowrap;
}

.ya-view-journal svg {
	width: 14px;
	height: 14px;
	fill: none;
	stroke: currentColor;
	stroke-linecap: round;
	stroke-linejoin: round;
	stroke-width: 1.8;
	transition: transform .25s ease;
}

.ya-view-journal:hover svg {
	transform: translateX(4px);
}

.ya-view-journal:hover {
	color: var(--ya-accent);
}


.ya-related-grid {

	display: grid;

	grid-template-columns:
		repeat(3, 1fr);

	gap: 22px;
}


.ya-related-card {

	display: block;

	background: #fff;

	color: var(--ya-text);

	text-decoration: none;

	overflow: hidden;

	transition:

		transform .35s ease,

		box-shadow .35s ease;
}


.ya-related-card:hover {

	transform:
		translateY(-7px);

	box-shadow:
		0 18px 50px
		rgba(0,0,0,.08);
}


.ya-related-image {

	height: 275px;

	overflow: hidden;

	background: #dedbd5;
}


.ya-related-image img {

	width: 100%;

	height: 100%;

	display: block;

	object-fit: cover;

	transition:
		transform .65s ease;
}


.ya-related-card:hover
.ya-related-image img {

	transform: scale(1.06);
}


.ya-related-card-content {

	padding: 25px;
}


.ya-related-meta {

	display: flex;

	justify-content: space-between;

	gap: 15px;

	color: var(--ya-muted);

	font-family: var(--ya-sans);

	font-size: 8px;

	font-weight: 800;

	letter-spacing: .13em;
}


.ya-related-card h3 {

	margin:
		22px
		0
		27px;

	color: var(--ya-text);

	font-family: var(--ya-serif);

	font-size: 29px;

	font-weight: 400;

	line-height: 1.12;

	letter-spacing: -.02em;
}


.ya-read-story {

	display: inline-flex;
	align-items: center;
	gap: 8px;

	font-family: var(--ya-sans);

	font-size: 9px;

	font-weight: 800;

	letter-spacing: .15em;

	text-transform: uppercase;
}

.ya-read-story svg {
	width: 14px;
	height: 14px;
	fill: none;
	stroke: currentColor;
	stroke-linecap: round;
	stroke-linejoin: round;
	stroke-width: 1.8;
	transition: transform .25s ease;
}

.ya-related-card:hover .ya-read-story svg {
	transform: translateX(4px);
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

	.ya-post-hero {

		min-height: 700px;
	}


	.ya-related-grid {

		grid-template-columns:
			repeat(2, 1fr);
	}

}


@media (max-width: 760px) {

	.ya-single-post {

		margin-top: -12px;
	}


	.ya-post-container,
	.ya-article-layout {

		width:
			calc(
				100% - 36px
			);
	}


	.ya-post-hero {

		min-height: 680px;

		padding-top: 60px;
	}


	.ya-post-hero-inner {

		padding-top: 45px;
	}


	.ya-hero-content {

		margin-top: 65px;
	}


	.ya-post-title {

		font-size:
			clamp(
				50px,
				12vw,
				68px
			);
	}


	.ya-article-section {

		padding: 56px 0 86px;
	}


	.ya-article-layout {

		display: block;
	}


	.ya-share-bar {

		position: static;

		flex-direction: row;

		align-items: center;

		justify-content: flex-start;

		margin-bottom: 42px;
	}


	.ya-share-label {

		margin:
			0
			7px
			0
			0;

		writing-mode: horizontal-tb;

		transform: none;
	}


	.ya-toc-list {

		grid-template-columns: 1fr;
	}


	.ya-related-grid {

		grid-template-columns: 1fr;
	}


}


@media (max-width: 520px) {

	.ya-post-author-row {

		align-items: flex-start;

		flex-direction: column;

		gap: 18px;
	}


	.ya-post-date {

		padding-left: 58px;

		border-left: none;
	}

	.ya-intro-lead {

		max-width: none;
		margin: 0 0 48px;
		padding: 16px 0 0;
		border-top: 3px solid var(--ya-accent);
		border-left: none;
		font-size: 22px;
		line-height: 1.45;
	}


	.ya-wordpress-content {

		font-size: 18px;
	}


	.ya-related-heading h2 {

		font-size: 45px;
	}

	.ya-comments-inner {
		width: calc(100% - 36px);
	}

	.ya-comments-section {
		padding: 58px 0 72px;
	}

	.ya-comment-item {
		grid-template-columns: 38px minmax(0, 1fr);
		gap: 12px;
	}

	.ya-comment-avatar img {
		width: 38px;
		height: 38px;
	}

	.ya-related-heading {
		display: block;
	}

	.ya-view-journal {
		display: inline-flex;
		margin-top: 24px;
	}

}

</style>


<main class="ya-single-post">


	<!-- =====================================================
	     HERO
	====================================================== -->

	<section
		class="ya-post-hero"
		<?php if ( $featured_image ) : ?>
			style="
				background-image:
				url('<?php echo esc_url( $featured_image ); ?>');
			"
		<?php endif; ?>
	>

		<div class="ya-post-container ya-post-hero-inner">
			<div class="ya-hero-content">


				<div class="ya-post-meta">

					<span>
						<?php echo esc_html( $category ); ?>
					</span>

					<i></i>

					<span>
						<?php
						echo esc_html(
							$reading_time .
							' MIN READ'
						);
						?>
					</span>

				</div>


				<h1 class="ya-post-title">

					<?php the_title(); ?>

				</h1>


				<div class="ya-post-excerpt">

					<?php
					echo esc_html(
						$hero_subtext
					);
					?>

				</div>


				<div class="ya-post-author-row">


					<div class="ya-post-author">


						<div class="ya-author-avatar">

							<?php if ( $custom_author_image ) : ?>

								<img
									src="<?php echo esc_url( $custom_author_image ); ?>"
									alt="<?php echo esc_attr( $author_name ); ?>"
								>

							<?php else : ?>

								<div class="ya-author-avatar-initial">

									<?php
									echo esc_html(
										$author_initial
									);
									?>

								</div>

							<?php endif; ?>

						</div>


						<div>

							<span class="ya-small-label">
								AUTHOR
							</span>

							<strong>
								<?php
								echo esc_html(
									$author_name
								);
								?>
							</strong>

						</div>

					</div>


					<div class="ya-post-date">

						<span class="ya-small-label">
							PUBLISHED
						</span>

						<strong>
							<?php
							echo esc_html(
								get_the_date(
									'F j, Y'
								)
							);
							?>
						</strong>

					</div>


				</div>


			</div>


		</div>

	</section>

	<!-- =====================================================
	     ARTICLE
	====================================================== -->

	<section class="ya-article-section">


		<div class="ya-article-layout">


			<!-- SHARE -->

			<aside class="ya-share-bar">

				<span class="ya-share-label">
					SHARE
				</span>


				<a
					class="ya-share-button ya-share-facebook"
					href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $post_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="Share this post on Facebook"
				>
					f
				</a>


				<a
					class="ya-share-button"
					href="https://twitter.com/intent/tweet?text=<?php echo rawurlencode( get_the_title() ); ?>&url=<?php echo rawurlencode( $post_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="Share this post on X"
				>
					𝕏
				</a>


				<a
					class="ya-share-button ya-share-linkedin"
					href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( $post_url ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="Share this post on LinkedIn"
				>
					in
				</a>


				<button
					type="button"
					class="ya-share-button ya-native-share"
					aria-label="More sharing options"
				>
					↗
				</button>

				<button
					type="button"
					class="ya-share-button ya-post-like"
					data-post-id="<?php echo esc_attr( $post_id ); ?>"
					aria-label="Like this post"
				>
					<span aria-hidden="true">♡</span>
					<b><?php echo esc_html( (int) get_post_meta( $post_id, '_trek_like_count', true ) ); ?></b>
				</button>


			</aside>


			<!-- CONTENT -->

			<article class="ya-article-content">


				<?php if ( ! empty( $toc_items ) ) : ?>

					<nav
						class="ya-toc"
						aria-label="In this story"
					>

						<span class="ya-toc-title">
							IN THIS JOURNEY
						</span>


						<div class="ya-toc-list">

							<?php foreach ( $toc_items as $item ) : ?>

								<a
									href="#<?php echo esc_attr( $item[2] ); ?>"
								>

									<?php
									echo esc_html(
										wp_strip_all_tags(
											$item[3]
										)
									);
									?>

								</a>

							<?php endforeach; ?>

						</div>

					</nav>

				<?php endif; ?>


				<!-- DISTINCT ARTICLE INTRO -->

				<?php if ( $article_intro ) : ?>

					<div class="ya-intro-lead">

						<?php echo esc_html( $article_intro ); ?>

					</div>

				<?php endif; ?>


				<div class="ya-wordpress-content">

					<?php
					echo $processed_content;
					?>

				</div>


			</article>


		</div>


	</section>
	<?php
	$approved_comments = get_comments(
		array(
			'post_id' => $post_id,
			'status'  => 'approve',
			'order'   => 'ASC',
		)
	);
	?>

	<?php if ( comments_open( $post_id ) || ! empty( $approved_comments ) ) : ?>
		<section class="ya-comments-section">
			<div class="ya-comments-inner">
				<div class="ya-comments-heading">
					<span class="ya-section-label">THE CONVERSATION</span>
					<h2><?php echo esc_html( get_comments_number( $post_id ) . ' ' . _n( 'Comment', 'Comments', get_comments_number( $post_id ), 'trek-content-manager' ) ); ?></h2>
				</div>

				<?php if ( ! empty( $approved_comments ) ) : ?>
					<ol class="ya-comment-list">
						<?php foreach ( $approved_comments as $comment ) : ?>
							<?php $comment_likes = (int) get_comment_meta( $comment->comment_ID, '_trek_like_count', true ); ?>
							<li class="ya-comment-item">
								<div class="ya-comment-avatar">
									<?php echo wp_kses_post( get_avatar( $comment, 48 ) ); ?>
								</div>
								<div>
									<p class="ya-comment-author">
										<?php echo esc_html( get_comment_author( $comment ) ); ?>
										<span class="ya-comment-date"><?php echo esc_html( get_comment_date( 'F j, Y', $comment ) ); ?></span>
									</p>
									<div class="ya-comment-text"><?php echo wp_kses_post( wpautop( get_comment_text( $comment ) ) ); ?></div>
									<button type="button" class="ya-comment-like" data-comment-id="<?php echo esc_attr( $comment->comment_ID ); ?>" aria-label="Like this comment">
										<span aria-hidden="true">♡</span> <b><?php echo esc_html( $comment_likes ); ?></b>
									</button>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>

				<div class="ya-comments-form">
					<h3>Leave a comment</h3>
					<p class="ya-comment-note">Your email is required for moderation and will not be published.</p>
					<form class="ya-live-comment-form" novalidate>
						<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
						<p class="ya-comment-website-field" aria-hidden="true"><label for="ya-comment-website">Website</label><input id="ya-comment-website" name="website" type="text" tabindex="-1" autocomplete="off"></p>
						<p><label for="ya-comment-name">Name</label><input id="ya-comment-name" name="name" type="text" autocomplete="name" required></p>
						<p><label for="ya-comment-email">Email</label><input id="ya-comment-email" name="email" type="email" autocomplete="email" required></p>
						<p><label for="ya-comment-content">Comment</label><textarea id="ya-comment-content" name="content" required></textarea></p>
						<button type="submit">Publish comment</button>
						<p class="ya-comment-status" role="status" aria-live="polite"></p>
					</form>
				</div>
			</div>
		</section>
	<?php endif; ?>
	

	<!-- =====================================================
	     MORE JOURNAL
	====================================================== -->

	<?php if ( $related_posts->have_posts() ) : ?>

		<section class="ya-related-section">

			<div class="ya-post-container">


				<div class="ya-related-heading">
					<h2>
						More journal worth<br>
						your <strong>time.</strong>
					</h2>

					<a class="ya-view-journal" href="<?php echo esc_url( $journal_url ); ?>">
						View all journal
						<svg viewBox="0 0 18 18" aria-hidden="true" focusable="false">
							<path d="M3 9h11"></path>
							<path d="m9 4 5 5-5 5"></path>
						</svg>
					</a>

				</div>


				<div class="ya-related-grid">


					<?php
					while (
						$related_posts->have_posts()
					) :

						$related_posts->the_post();


						$related_id = get_the_ID();


						$related_categories =
							get_the_category(
								$related_id
							);


						$related_category =
							! empty(
								$related_categories
							)
								? strtoupper(
									$related_categories[0]->name
								)
								: 'JOURNAL';


						$related_words =
							str_word_count(
								wp_strip_all_tags(
									get_post_field(
										'post_content',
										$related_id
									)
								)
							);


						$related_time =
							max(
								1,
								(int) ceil(
									$related_words / 200
								)
							);
					?>


						<a
							href="<?php the_permalink(); ?>"
							class="ya-related-card"
						>


							<div class="ya-related-image">

								<?php if ( has_post_thumbnail() ) : ?>

									<?php
									the_post_thumbnail(
										'large',
										array(
											'loading' => 'lazy'
										)
									);
									?>

								<?php endif; ?>

							</div>


							<div class="ya-related-card-content">


								<div class="ya-related-meta">

									<span>
										<?php
										echo esc_html(
											$related_category
										);
										?>
									</span>


									<span>
										<?php
										echo esc_html(
											$related_time .
											' MIN READ'
										);
										?>
									</span>

								</div>


								<h3>

									<?php the_title(); ?>

								</h3>


								<span class="ya-read-story">
								Read Story
								<svg viewBox="0 0 18 18" aria-hidden="true" focusable="false">
									<path d="M3 9h11"></path>
									<path d="m9 4 5 5-5 5"></path>
								</svg>
								</span>


							</div>


						</a>


					<?php endwhile; ?>


				</div>


			</div>

		</section>


		<?php
		wp_reset_postdata();
		?>

	<?php endif; ?>


</main>

<div class="ya-share-card" hidden aria-hidden="true">
	<div class="ya-share-card-inner" role="dialog" aria-modal="true" aria-labelledby="ya-share-card-title">
		<button type="button" class="ya-share-card-close" aria-label="Close share options">&times;</button>
		<?php if ( $featured_image ) : ?>
			<img class="ya-share-card-image" src="<?php echo esc_url( $featured_image ); ?>" alt="">
		<?php endif; ?>
		<p class="ya-share-card-kicker">SHARE THIS JOURNEY</p>
		<h2 id="ya-share-card-title" class="ya-share-card-title"><?php echo esc_html( get_the_title() ); ?></h2>
		<a class="ya-share-card-url" href="<?php echo esc_url( $post_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $post_url ); ?></a>
		<div class="ya-share-card-actions">
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $post_url ); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
			<a href="https://twitter.com/intent/tweet?text=<?php echo rawurlencode( get_the_title() ); ?>&url=<?php echo rawurlencode( $post_url ); ?>" target="_blank" rel="noopener noreferrer">X</a>
			<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( $post_url ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
			<button type="button" class="ya-share-card-copy">Copy link</button>
		</div>
	</div>
</div>

<script>

document.addEventListener(
	'DOMContentLoaded',
	function() {

		var shareButton = document.querySelector(
			'.ya-native-share'
		);
		var shareCard = document.querySelector('.ya-share-card');
		var closeButton = document.querySelector('.ya-share-card-close');
		var copyButton = document.querySelector('.ya-share-card-copy');
		var shareUrl = <?php echo wp_json_encode( $post_url ); ?>;
		var shareTitle = <?php echo wp_json_encode( get_the_title() ); ?>;
		var shareText = <?php echo wp_json_encode( $hero_subtext ); ?>;
		var imageUrl = <?php echo wp_json_encode( $featured_image ); ?>;


		if ( ! shareButton || ! shareCard ) {
			return;
		}

		function closeShareCard() {
			shareCard.hidden = true;
			shareCard.setAttribute('aria-hidden', 'true');
		}


		shareButton.addEventListener(
			'click',
			async function() {

				var shareData = { title: shareTitle, text: shareText, url: shareUrl };


				/*
				 * Native social sharing:
				 * Opens the device/browser share UI
				 * where supported.
				 */

				if ( navigator.share ) {
					try {
						if ( imageUrl && navigator.canShare ) {
							var response = await fetch(imageUrl);
							var blob = await response.blob();
							var file = new File([blob], 'story-image.' + (blob.type.split('/')[1] || 'jpg'), { type: blob.type });
							if ( navigator.canShare({ files: [file] }) ) shareData.files = [file];
						}
						await navigator.share(shareData);
						return;
					} catch (error) {
						if ( error.name === 'AbortError' ) return;
					}
				}

				shareCard.hidden = false;
				shareCard.setAttribute('aria-hidden', 'false');


				/*
				 * Desktop fallback:
				 * Copy the post URL instead of
				 * opening a blank or black page.
				 */

			}
		);

		if ( closeButton ) closeButton.addEventListener('click', closeShareCard);
		shareCard.addEventListener('click', function (event) {
			if ( event.target === shareCard ) closeShareCard();
		});
		document.addEventListener('keydown', function (event) {
			if ( event.key === 'Escape' && ! shareCard.hidden ) closeShareCard();
		});
		if ( copyButton ) copyButton.addEventListener('click', async function () {
			try {
				await navigator.clipboard.writeText(shareUrl);
				copyButton.textContent = 'Copied';
			} catch (error) {
				window.prompt('Copy this link:', shareUrl);
			}
		});

	}
);

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var commentEndpoint = <?php echo wp_json_encode( rest_url( 'trek/v1/comments' ) ); ?>;
	var postLikeEndpoint = <?php echo wp_json_encode( rest_url( 'trek/v1/posts/' . $post_id . '/like' ) ); ?>;
	var commentNonce = <?php echo wp_json_encode( wp_create_nonce( 'ya_guest_comment' ) ); ?>;
	var commentForm = document.querySelector('.ya-live-comment-form');

	function bindLikeButton(button, endpoint) {
		button.addEventListener('click', async function () {
			var itemId = button.getAttribute('data-comment-id') || button.getAttribute('data-post-id');
			var likedKey = 'ya-liked-' + (button.hasAttribute('data-post-id') ? 'post' : 'comment') + '-' + itemId;
			try { if ( window.localStorage.getItem(likedKey) ) return; } catch (error) {}

			button.disabled = true;
			try {
				var response = await fetch(endpoint, {
					method: 'POST',
					headers: { 'X-Trek-Nonce': commentNonce }
				});
				var data = await response.json();
				if (!response.ok) throw new Error(data.message || 'Unable to like comment.');
				button.querySelector('b').textContent = data.likes;
				button.querySelector('span').textContent = '♥';
				button.classList.add('is-liked');
				try { window.localStorage.setItem(likedKey, '1'); } catch (error) {}
			} catch (error) {
				button.disabled = false;
				button.title = error.message;
			}
		});
	}

	document.querySelectorAll('.ya-comment-like').forEach(function (button) {
		bindLikeButton(button, commentEndpoint + '/' + button.getAttribute('data-comment-id') + '/like');
	});

	document.querySelectorAll('.ya-post-like').forEach(function (button) {
		bindLikeButton(button, postLikeEndpoint);
	});

	if (!commentForm) return;
	commentForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		var status = commentForm.querySelector('.ya-comment-status');
		var submit = commentForm.querySelector('button[type="submit"]');
		var payload = Object.fromEntries(new FormData(commentForm).entries());
		submit.disabled = true;
		status.textContent = 'Sending...';

		try {
			var response = await fetch(commentEndpoint, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-Trek-Nonce': commentNonce },
				body: JSON.stringify(payload)
			});
			var data = await response.json();
			if (!response.ok) throw new Error(data.message || 'Unable to publish comment.');

			status.textContent = data.message;
			commentForm.reset();
			if ('1' === String(data.status)) {
				var list = document.querySelector('.ya-comment-list');
				if (!list) {
					list = document.createElement('ol');
					list.className = 'ya-comment-list';
					commentForm.parentNode.insertBefore(list, commentForm.parentNode.querySelector('.ya-comments-form'));
				}
				var item = document.createElement('li');
				item.className = 'ya-comment-item';
				item.innerHTML = '<div class="ya-comment-avatar"><img src="' + data.avatar + '" alt=""></div><div><p class="ya-comment-author"></p><div class="ya-comment-text"></div><button type="button" class="ya-comment-like" data-comment-id="' + data.id + '"><span aria-hidden="true">♡</span> <b>0</b></button></div>';
				item.querySelector('.ya-comment-author').textContent = data.author + ' ' + data.date;
				item.querySelector('.ya-comment-text').innerHTML = data.content;
				list.appendChild(item);
				bindLikeButton(item.querySelector('.ya-comment-like'), commentEndpoint + '/' + data.id + '/like');
			}
		} catch (error) {
			status.textContent = error.message;
		} finally {
			submit.disabled = false;
		}
	});
});
</script>


<?php

endwhile;

get_footer();