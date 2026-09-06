<?php
/**
 * Plugin Name: Trek Content Manager
 * Plugin URI: https://yetiadventures.com/trek-content-manager
 * Description: Dynamic, multi-role destination detail page framework for treks, expeditions, and outdoor journeys. Includes role-aware rendering, data quality audits, and interactive itinerary builder.
 * Version: 1.0.2
 * Author: Yeti Adventure Tech Architecture
 * Author URI: https://yetiadventures.com
 * Text Domain: trek-content-manager
 * Domain Path: /languages
 * License: GPLv2 or later
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TREK_MANAGER_VERSION', '1.0.0' );
define( 'TREK_MANAGER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TREK_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoload Required Classes
 */
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-cpt.php';
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-roles.php';
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-rest-api.php';
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-renderer.php';
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-shortcodes.php';
require_once TREK_MANAGER_PLUGIN_DIR . 'includes/class-trek-importer.php';
// require_once TREK_MANAGER_PLUGIN_DIR . 'includes/premium-stories.php';

/**
 * Main Plugin Initialization Class
 */
class Trek_Content_Manager_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize Core Components
        Trek_CPT::init();
        Trek_REST_API::init();
        Trek_Shortcodes::init();
        Trek_Importer::init();
        Trek_Renderer::init();

        // Enqueue frontend assets conditionally
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Load the single trek destination template ONLY for singular trek_destination posts.
        // Priority 999 ensures it runs after theme template resolution.
        add_filter( 'template_include', array( $this, 'load_trek_templates' ), 999 );

        // Register the [trek_destination id="123"] shortcode for embedding a single
        // trek detail block inside any regular WordPress page/post.
        add_shortcode( 'trek_destination', array( $this, 'render_trek_shortcode' ) );
    }

    /**
     * Plugin Activation Handler
     */
    public static function activate_plugin() {
        Trek_Roles::register_capabilities();
        Trek_CPT::register_post_types();
        Trek_CPT::register_taxonomies();
        flush_rewrite_rules();
    }

    /**
     * Enqueue CSS and JavaScript assets for single trek destination pages.
     */
    public function enqueue_frontend_assets() {
        $post         = get_post();
        $post_content = ( $post instanceof WP_Post ) ? $post->post_content : '';

        if ( is_singular( 'post' ) ) {
            wp_enqueue_style(
                'trek-google-fonts',
                'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap',
                array(),
                null
            );
            wp_enqueue_style(
                'trek-single-post-style',
                TREK_MANAGER_PLUGIN_URL . 'assets/css/single-post.css',
                array(),
                TREK_MANAGER_VERSION
            );
            return;
        }

        if ( is_singular( 'trek_destination' ) || has_shortcode( $post_content, 'trek_destination' ) ) {
            // Google Fonts
            wp_enqueue_style(
                'trek-google-fonts',
                'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap',
                array(),
                null
            );

            // Master CSS
            wp_enqueue_style(
                'trek-detail-style',
                TREK_MANAGER_PLUGIN_URL . 'assets/css/trek-detail.css',
                array(),
                TREK_MANAGER_VERSION
            );

            // Core JS Modules
            wp_enqueue_script(
                'trek-roles-js',
                TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-roles.js',
                array(),
                TREK_MANAGER_VERSION,
                true
            );

            wp_enqueue_script(
                'trek-validation-js',
                TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-validation.js',
                array(),
                TREK_MANAGER_VERSION,
                true
            );

            // NOTE: trek-sample-data.js is intentionally NOT enqueued on the frontend.
            // It is a development/demo-only file.

            wp_enqueue_script(
                'trek-engine-js',
                TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-engine.js',
                array( 'trek-roles-js', 'trek-validation-js' ),
                TREK_MANAGER_VERSION,
                true
            );

            // Inject WordPress context data into the page for the JS engine
            $current_user = wp_get_current_user();
            $user_role    = ! empty( $current_user->roles ) ? $current_user->roles[0] : 'public';

            $page_context = array(
                'userRole'     => Trek_Roles::map_wp_role_to_trek_role( $user_role ),
                'userId'       => get_current_user_id(),
                'nonce'        => wp_create_nonce( 'wp_rest' ),
                'restUrl'      => esc_url_raw( rest_url( 'trek-manager/v1/' ) ),
                'activeTrekId' => get_the_ID(),
            );

            wp_localize_script( 'trek-engine-js', 'PAGE_CONTEXT', $page_context );

            // The demo dataset is intentionally not loaded on production pages.
            // Provide the server-normalized CPT payload to the optional interaction engine.
            wp_localize_script(
                'trek-engine-js',
                'TREK_DATA',
                Trek_Renderer::get_full_trek_data( get_the_ID() )
            );
        }
    }

    /**
     * Load the plugin templates for single standard posts and trek destinations.
     *
     * @param string $template Default template path resolved by WordPress.
     * @return string Template path to use.
     */
    public function load_trek_templates( $template ) {
        if ( is_singular( 'post' ) ) {
            $plugin_template = TREK_MANAGER_PLUGIN_DIR . 'templates/single-post.php';
        } elseif ( is_singular( 'trek_destination' ) ) {
            $plugin_template = TREK_MANAGER_PLUGIN_DIR . 'templates/single-trek_destination.php';
        } else {
            return $template;
        }

        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return $template;
    }

    /**
     * Shortcode: [trek_destination id="123"]
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_trek_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => get_queried_object_id() ?: get_the_ID(),
            ),
            $atts,
            'trek_destination'
        );

        $post_id = absint( $atts['id'] );
        $post    = $post_id ? get_post( $post_id ) : null;

        if ( ! $post || 'trek_destination' !== $post->post_type ) {
            return '';
        }

        if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $post_id ) ) {
            return '';
        }

        $plugin_template = TREK_MANAGER_PLUGIN_DIR . 'templates/single-trek_destination.php';

        if ( ! file_exists( $plugin_template ) ) {
            return '<!-- Trek Content Manager: template file not found -->';
        }

        ob_start();
        include $plugin_template;
        return ob_get_clean();
    }
}

// BUG FIX: register_activation_hook() and register_deactivation_hook() MUST be called
// at plugin file scope (not inside a class constructor). WordPress hooks these to the
// 'activate_<plugin-file>' and 'deactivate_<plugin-file>' actions which only fire once
// during activation/deactivation — calling them inside __construct() means they never
// fire because the constructor runs AFTER the activation action already completed.
register_activation_hook( __FILE__, array( 'Trek_Content_Manager_Plugin', 'activate_plugin' ) );
register_deactivation_hook( __FILE__, array( 'Trek_Roles', 'remove_capabilities' ) );

// Bootstrap Plugin
Trek_Content_Manager_Plugin::get_instance();
