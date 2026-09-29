<?php
/**
 * Trek Manager - Trip Planner Enquiries & Submissions Controller
 *
 * Handles the [trek_nepal_enquiry_form] shortcode, frontend validation,
 * dedicated custom database table storage, client relationship tracking
 * (email/phone match detection), CSV export, top-right Admin Bar Lead Data access,
 * and a dedicated, modern Admin Panel view for managing submissions.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Planner_Submissions {

    private static $instance_count = 0;

    /**
     * Initialize hooks
     */
    public static function init() {
        // Ensure custom table exists
        add_action( 'admin_init', array( __CLASS__, 'maybe_create_table' ) );

        // Register shortcodes
        add_shortcode( 'trek_nepal_enquiry_form', array( __CLASS__, 'render_shortcode' ) );
        add_shortcode( 'trek_trip_planner_form',  array( __CLASS__, 'render_shortcode' ) );

        // AJAX handlers for form submission (public & logged in)
        add_action( 'wp_ajax_trek_submit_planner_enquiry',        array( __CLASS__, 'ajax_submit_enquiry' ) );
        add_action( 'wp_ajax_nopriv_trek_submit_planner_enquiry', array( __CLASS__, 'ajax_submit_enquiry' ) );

        // Admin menu & actions
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ), 14 );
        add_action( 'wp_ajax_trek_update_planner_lead_status', array( __CLASS__, 'ajax_update_lead_status' ) );
        add_action( 'wp_ajax_trek_delete_planner_lead',        array( __CLASS__, 'ajax_delete_lead' ) );
        add_action( 'wp_ajax_trek_get_planner_lead_detail',    array( __CLASS__, 'ajax_get_lead_detail' ) );

        // CSV Export handler
        add_action( 'admin_post_trek_export_planner_leads_csv', array( __CLASS__, 'export_leads_csv' ) );

        // Top-Right Admin Bar button: "Lead Data"
        add_action( 'admin_bar_menu', array( __CLASS__, 'add_admin_bar_lead_button' ), 90 );

        // Admin Dashboard Widget: "Trip Planner Leads"
        add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_dashboard_widget' ) );
    }

    /**
     * Get custom database table name
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'trek_planner_leads';
    }

    /**
     * Ensure dedicated database table exists
     */
    public static function maybe_create_table() {
        global $wpdb;
        $table_name = self::get_table_name();

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
            self::create_custom_table();
            self::sync_existing_enquiries();
        }
    }

    /**
     * Create custom database table for planner leads
     */
    public static function create_custom_table() {
        global $wpdb;
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_uid varchar(64) NOT NULL DEFAULT '',
            post_id bigint(20) unsigned DEFAULT 0,
            name varchar(255) NOT NULL DEFAULT '',
            email varchar(255) NOT NULL DEFAULT '',
            phone_code varchar(16) NOT NULL DEFAULT '+977',
            phone varchar(64) NOT NULL DEFAULT '',
            clean_phone varchar(64) NOT NULL DEFAULT '',
            country varchar(128) NOT NULL DEFAULT '',
            adventure varchar(255) NOT NULL DEFAULT '',
            budget varchar(128) NOT NULL DEFAULT '',
            travellers varchar(64) NOT NULL DEFAULT '',
            travel_date varchar(64) NOT NULL DEFAULT '',
            source varchar(128) NOT NULL DEFAULT '',
            message text NOT NULL,
            status varchar(32) NOT NULL DEFAULT 'New',
            is_repeat_customer tinyint(1) NOT NULL DEFAULT 0,
            matched_lead_id bigint(20) unsigned DEFAULT 0,
            matched_count int(11) NOT NULL DEFAULT 0,
            matched_info text NOT NULL,
            ip_address varchar(64) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY clean_phone (clean_phone),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Sync any existing trek_enquiry posts into custom table on first initialization
     */
    public static function sync_existing_enquiries() {
        global $wpdb;
        $table_name = self::get_table_name();

        $count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name}" );
        if ( $count > 0 ) {
            return;
        }

        $posts = get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => 150,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ) );

        if ( empty( $posts ) ) {
            return;
        }

        foreach ( $posts as $p ) {
            $name        = get_post_meta( $p->ID, '_trek_enquiry_name', true ) ?: $p->post_title;
            $email       = get_post_meta( $p->ID, '_trek_enquiry_email', true ) ?: '';
            $phone       = get_post_meta( $p->ID, '_trek_enquiry_phone', true ) ?: '';
            $phone_code  = get_post_meta( $p->ID, '_trek_enquiry_phone_code', true ) ?: '+977';
            $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
            $country     = get_post_meta( $p->ID, '_trek_enquiry_country', true ) ?: '';
            $adventure   = get_post_meta( $p->ID, '_trek_enquiry_adventure', true ) ?: ( get_post_meta( $p->ID, '_trek_enquiry_trek_title', true ) ?: 'Nepal Expedition' );
            $budget      = get_post_meta( $p->ID, '_trek_enquiry_budget', true ) ?: 'Discuss after team call';
            $travellers  = get_post_meta( $p->ID, '_trek_enquiry_travellers', true ) ?: '2 People';
            $travel_date = get_post_meta( $p->ID, '_trek_enquiry_travel_date', true ) ?: ( get_post_meta( $p->ID, '_trek_enquiry_date', true ) ?: '' );
            $source      = get_post_meta( $p->ID, '_trek_enquiry_source', true ) ?: 'Website';
            $status      = get_post_meta( $p->ID, '_trek_enquiry_status', true ) ?: 'New';
            $uid         = 'PL-' . str_pad( (string) $p->ID, 5, '0', STR_PAD_LEFT );

            $wpdb->insert(
                $table_name,
                array(
                    'lead_uid'     => $uid,
                    'post_id'      => $p->ID,
                    'name'         => $name,
                    'email'        => $email,
                    'phone_code'   => $phone_code,
                    'phone'        => $phone,
                    'clean_phone'  => $clean_phone,
                    'country'      => $country,
                    'adventure'    => $adventure,
                    'budget'       => $budget,
                    'travellers'   => $travellers,
                    'travel_date'  => $travel_date,
                    'source'       => $source,
                    'message'      => $p->post_content ?: '',
                    'status'       => $status,
                    'created_at'   => $p->post_date,
                    'updated_at'   => $p->post_modified,
                )
            );
        }
    }

    /**
     * Check if email or phone matches an existing customer in database
     *
     * @param string $email
     * @param string $phone
     * @return array
     */
    public static function check_client_relationship( $email, $phone ) {
        global $wpdb;
        $table_name  = self::get_table_name();
        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
        $matches     = array();

        // 1. Check custom database table
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
            $where  = array();
            $params = array();

            if ( ! empty( $email ) && is_email( $email ) ) {
                $where[]  = "email = %s";
                $params[] = strtolower( trim( $email ) );
            }
            if ( ! empty( $clean_phone ) && strlen( $clean_phone ) >= 7 ) {
                // Match last 8 digits for phone number flexibility
                $phone_sub = substr( $clean_phone, -8 );
                $where[]   = "clean_phone LIKE %s";
                $params[]  = '%' . $phone_sub;
            }

            if ( ! empty( $where ) ) {
                $sql = "SELECT id, lead_uid, name, email, phone, adventure, status, created_at FROM {$table_name} WHERE (" . implode( ' OR ', $where ) . ") ORDER BY id DESC LIMIT 10";
                $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
                if ( ! empty( $results ) ) {
                    $matches = $results;
                }
            }
        }

        // 2. Also check postmeta of old trek_enquiry posts if no or few custom table matches
        if ( count( $matches ) < 2 ) {
            $meta_where  = array();
            $meta_params = array();

            if ( ! empty( $email ) && is_email( $email ) ) {
                $meta_where[]  = "(pm.meta_key = '_trek_enquiry_email' AND pm.meta_value = %s)";
                $meta_params[] = strtolower( trim( $email ) );
            }

            if ( ! empty( $clean_phone ) && strlen( $clean_phone ) >= 7 ) {
                $phone_sub     = substr( $clean_phone, -8 );
                $meta_where[]  = "(pm.meta_key = '_trek_enquiry_phone' AND pm.meta_value LIKE %s)";
                $meta_params[] = '%' . $phone_sub . '%';
            }

            if ( ! empty( $meta_where ) ) {
                $sql_posts = "SELECT p.ID as post_id, p.post_title, p.post_date 
                              FROM {$wpdb->posts} p 
                              INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                              WHERE p.post_type = 'trek_enquiry' AND (" . implode( ' OR ', $meta_where ) . ") 
                              GROUP BY p.ID ORDER BY p.ID DESC LIMIT 5";
                $post_results = $wpdb->get_results( $wpdb->prepare( $sql_posts, $meta_params ), ARRAY_A );

                if ( ! empty( $post_results ) ) {
                    foreach ( $post_results as $pr ) {
                        // Avoid duplicates if already in matches
                        $already = false;
                        foreach ( $matches as $m ) {
                            if ( isset( $m['post_id'] ) && (int) $m['post_id'] === (int) $pr['post_id'] ) {
                                $already = true;
                                break;
                            }
                        }
                        if ( ! $already ) {
                            $matches[] = array(
                                'id'         => $pr['post_id'],
                                'lead_uid'   => 'ENQ-' . $pr['post_id'],
                                'name'       => $pr['post_title'],
                                'email'      => $email,
                                'phone'      => $phone,
                                'adventure'  => get_post_meta( $pr['post_id'], '_trek_enquiry_adventure', true ) ?: 'Nepal Journey',
                                'status'     => get_post_meta( $pr['post_id'], '_trek_enquiry_status', true ) ?: 'Previous',
                                'created_at' => $pr['post_date'],
                            );
                        }
                    }
                }
            }
        }

        $is_repeat   = ! empty( $matches );
        $prior_count = count( $matches );

        return array(
            'is_repeat'   => $is_repeat,
            'prior_count' => $prior_count,
            'matches'     => $matches,
            'last_match'  => $is_repeat ? $matches[0] : null,
        );
    }

    /**
     * Register Admin Submenu Page for Trip Planner Leads
     */
    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'Trip Planner Leads', 'trek-content-manager' ),
            __( 'Trip Planner Leads', 'trek-content-manager' ),
            'edit_posts',
            'trek-trip-planner-submissions',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    /**
     * Add "Lead Data" button to the top-right Admin Bar
     */
    public static function add_admin_bar_lead_button( $admin_bar ) {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        global $wpdb;
        $table_name = self::get_table_name();
        $new_count  = 0;

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
            $new_count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE status = 'New'" );
        }

        $badge_html = $new_count > 0 ? ' <span style="background:#d1ad54;color:#012422;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px;margin-left:4px;display:inline-block;line-height:1;">' . $new_count . '</span>' : '';

        $admin_bar->add_node( array(
            'id'    => 'trek_lead_data_top',
            'title' => '<span class="ab-icon dashicons dashicons-id-alt" style="margin-top:2px;"></span> <span class="ab-label" style="font-weight:700;letter-spacing:0.5px;">' . esc_html__( 'Lead Data', 'trek-content-manager' ) . '</span>' . $badge_html,
            'href'  => admin_url( 'edit.php?post_type=trek_destination&page=trek-trip-planner-submissions' ),
            'meta'  => array(
                'title' => __( 'View Trip Planner Leads & Submissions', 'trek-content-manager' ),
                'class' => 'ya-lead-data-bar-node',
            ),
        ) );
    }

    /**
     * Add Dashboard Widget on WP Admin Home
     */
    public static function add_dashboard_widget() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        wp_add_dashboard_widget(
            'trek_planner_leads_dashboard_widget',
            __( 'Trip Planner Leads & Enquiries', 'trek-content-manager' ),
            array( __CLASS__, 'render_dashboard_widget' )
        );
    }

    /**
     * Render the Dashboard Widget
     */
    public static function render_dashboard_widget() {
        global $wpdb;
        $table_name = self::get_table_name();

        $total_count = 0;
        $new_count   = 0;
        $latest      = array();

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) {
            $total_count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name}" );
            $new_count   = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE status = 'New'" );
            $latest      = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 4", ARRAY_A );
        }
        ?>
        <div class="ya-db-widget-container" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;">
                <div>
                    <span style="font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:1px;font-weight:700;"><?php esc_html_e( 'Leads Overview', 'trek-content-manager' ); ?></span>
                    <div style="font-size:22px;font-weight:700;color:#0f172a;margin-top:2px;">
                        <?php echo esc_html( $total_count ); ?> <span style="font-size:13px;font-weight:500;color:#64748b;"><?php esc_html_e( 'Total Received', 'trek-content-manager' ); ?></span>
                        <?php if ( $new_count > 0 ) : ?>
                            <span style="background:#fee2e2;color:#991b1b;font-size:11px;padding:3px 8px;border-radius:12px;font-weight:700;margin-left:6px;"><?php echo esc_html( $new_count ); ?> New</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-trip-planner-submissions' ) ); ?>" class="button button-primary" style="background:#012422;border-color:#012422;font-weight:600;">
                        <?php esc_html_e( 'View Lead Data →', 'trek-content-manager' ); ?>
                    </a>
                </div>
            </div>

            <?php if ( ! empty( $latest ) ) : ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach ( $latest as $row ) :
                        $clean_ph = preg_replace( '/[^0-9]/', '', $row['phone'] );
                        $wa_url   = ! empty( $clean_ph ) ? 'https://wa.me/' . $clean_ph : '';
                    ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0;">
                            <div>
                                <strong style="font-size:14px;color:#0f172a;display:block;"><?php echo esc_html( $row['name'] ); ?></strong>
                                <span style="font-size:12px;color:#64748b;"><?php echo esc_html( $row['adventure'] ); ?> &bull; <?php echo esc_html( $row['travel_date'] ?: 'Flexible' ); ?></span>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <?php if ( ! empty( $row['is_repeat_customer'] ) ) : ?>
                                    <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;"><?php esc_html_e( 'Repeat Client', 'trek-content-manager' ); ?></span>
                                <?php endif; ?>
                                <?php if ( $wa_url ) : ?>
                                    <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="button button-small" style="background:#25D366;color:#fff;border-color:#25D366;font-weight:600;">
                                        WhatsApp
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p style="color:#64748b;font-size:13px;margin:10px 0;"><?php esc_html_e( 'No trip planner leads recorded yet. Submissions from the form will appear here.', 'trek-content-manager' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Export Leads to CSV File
     */
    public static function export_leads_csv() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'trek-content-manager' ) );
        }

        check_admin_referer( 'trek_export_leads_nonce', '_wpnonce' );

        global $wpdb;
        $table_name    = self::get_table_name();
        $status_filter = sanitize_text_field( $_GET['status'] ?? 'all' );

        $where = "WHERE 1=1";
        if ( 'all' !== $status_filter && ! empty( $status_filter ) ) {
            $target = ucwords( str_replace( '-', ' ', $status_filter ) );
            $where .= $wpdb->prepare( " AND status = %s", $target );
        }

        $leads = $wpdb->get_results( "SELECT * FROM {$table_name} {$where} ORDER BY id DESC", ARRAY_A );

        $filename = 'trip-planner-leads-' . gmdate( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Open output stream
        $out = fopen( 'php://output', 'w' );

        // Write UTF-8 BOM for Microsoft Excel compatibility
        fwrite( $out, "\xEF\xBB\xBF" );

        // Header Row
        fputcsv( $out, array(
            'ID',
            'Reference UID',
            'Submission Date',
            'Full Name',
            'Email',
            'WhatsApp / Phone',
            'Country of Residence',
            'Adventure (Experience)',
            'Estimated Budget',
            'Travellers',
            'Preferred Date',
            'Referral Source',
            'Message / Trip Notes',
            'Status',
            'Client Relationship',
            'Prior Enquiries Count',
        ) );

        if ( ! empty( $leads ) ) {
            foreach ( $leads as $lead ) {
                $relationship = ! empty( $lead['is_repeat_customer'] ) ? 'Returning Client' : 'New Client';
                fputcsv( $out, array(
                    $lead['id'],
                    $lead['lead_uid'],
                    $lead['created_at'],
                    $lead['name'],
                    $lead['email'],
                    $lead['phone'],
                    $lead['country'],
                    $lead['adventure'],
                    $lead['budget'],
                    $lead['travellers'],
                    $lead['travel_date'],
                    $lead['source'],
                    $lead['message'],
                    $lead['status'],
                    $relationship,
                    (int) $lead['matched_count'],
                ) );
            }
        }

        fclose( $out );
        exit;
    }

    /**
     * Get destinations and regions from database for dropdown with fallback
     */
    /**
     * Get destinations and regions ONLY from database (no hardcoded defaults)
     */
    public static function get_adventures_grouped() {
        $regions = array();

        // 1. Retrieve regions ONLY from database taxonomy trek_region
        $tax_regions = get_terms( array(
            'taxonomy'   => 'trek_region',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );
        if ( ! empty( $tax_regions ) && ! is_wp_error( $tax_regions ) ) {
            foreach ( $tax_regions as $term ) {
                $regions[] = $term->name;
            }
        }

        $destinations = array();

        // 2. Retrieve destinations ONLY from published trek_destination posts in database
        $posts = get_posts( array(
            'post_type'      => 'trek_destination',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $p ) {
                $destinations[] = $p->post_title;
            }
        }

        // Safety fallback: ensure regions and destinations are never empty on live sites
        if ( empty( $regions ) ) {
            $regions = array( 'Nepal', 'Bhutan', 'India', 'Tibet' );
        }
        if ( empty( $destinations ) ) {
            $destinations = array(
                'Everest Base Camp Trek',
                'Annapurna Circuit Trek',
                'Langtang Valley Trek',
                'Manaslu Circuit Trek',
                'Upper Mustang Trek',
                'Ghorepani Poon Hill Trek',
            );
        }

        return array(
            'regions'      => array_values( array_unique( $regions ) ),
            'destinations' => array_values( array_unique( $destinations ) ),
        );
    }

    public static function get_adventures_list() {
        $grouped = self::get_adventures_grouped();
        return array_merge( $grouped['regions'], $grouped['destinations'] );
    }

    /**
     * Render the Trip Planner Form shortcode
     */
    public static function render_shortcode( $atts = array() ) {
        self::$instance_count++;
        $instance_id = self::$instance_count;
        $form_uid    = 'ya-form-' . $instance_id . '-' . wp_rand( 100, 999 );

        $atts = shortcode_atts(
            array(
                'title'           => __( 'Tell us about your trip', 'trek-content-manager' ),
                'subtitle'        => __( 'A few details help us understand what you are looking for. Our team will review your request and get back to you with custom options.', 'trek-content-manager' ),
                'show_header'     => 'true',
                'whatsapp_number' => '9779803197006',
                'class'           => '',
            ),
            $atts,
            'trek_nepal_enquiry_form'
        );

        $show_header        = in_array( strtolower( (string) $atts['show_header'] ), array( 'true', '1', 'yes', 'on' ), true );
        $adventures_grouped = self::get_adventures_grouped();
        $nonce              = wp_create_nonce( 'trek_planner_form_nonce' );
        $ajax_url           = admin_url( 'admin-ajax.php' );

        ob_start();
        ?>
        <div class="ya-planner-form-container <?php echo esc_attr( $atts['class'] ); ?>" id="<?php echo esc_attr( $form_uid ); ?>-wrap" data-ajax-url="<?php echo esc_url( $ajax_url ); ?>">

            <?php if ( $show_header ) : ?>
                <div class="ya-form-head">
                    <h3><?php echo esc_html( $atts['title'] ); ?></h3>
                    <p><?php echo esc_html( $atts['subtitle'] ); ?></p>
                </div>
            <?php endif; ?>

            <div class="ya-form-feedback" style="display:none;" aria-live="polite"></div>

            <form class="ya-planner-form" id="<?php echo esc_attr( $form_uid ); ?>" method="post" novalidate>
                <input type="hidden" name="action" value="trek_submit_planner_enquiry">
                <input type="hidden" name="trek_nonce" value="<?php echo esc_attr( $nonce ); ?>">
                <input type="hidden" name="form_uid" value="<?php echo esc_attr( $form_uid ); ?>">
                <input type="hidden" name="whatsapp_target" value="<?php echo esc_attr( $atts['whatsapp_number'] ); ?>">

                <div class="ya-form-grid">
                    <!-- Adventure Selection (Both Regions & Dynamic Destinations Selectable) -->
                    <div class="ya-field ya-field-full">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-adventure">
                            <?php esc_html_e( 'What would you like to experience? *', 'trek-content-manager' ); ?>
                        </label>
                        <select id="<?php echo esc_attr( $form_uid ); ?>-adventure" name="adventure" required>
                            <option value="" selected disabled><?php esc_html_e( 'Select a Region or Destination *', 'trek-content-manager' ); ?></option>

                            <?php if ( ! empty( $adventures_grouped['regions'] ) ) : ?>
                                <optgroup label="<?php esc_attr_e( 'REGIONS', 'trek-content-manager' ); ?>">
                                    <?php foreach ( $adventures_grouped['regions'] as $reg ) : ?>
                                        <option value="<?php echo esc_attr( $reg ); ?>"><?php echo esc_html( $reg ); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <?php if ( ! empty( $adventures_grouped['destinations'] ) ) : ?>
                                <optgroup label="<?php esc_attr_e( 'DESTINATIONS', 'trek-content-manager' ); ?>">
                                    <?php foreach ( $adventures_grouped['destinations'] as $dest ) : ?>
                                        <option value="<?php echo esc_attr( $dest ); ?>"><?php echo esc_html( $dest ); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <?php if ( empty( $adventures_grouped['regions'] ) && empty( $adventures_grouped['destinations'] ) ) : ?>
                                <option value="" disabled><?php esc_html_e( 'No destinations found in database', 'trek-content-manager' ); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Estimated Budget (Optional, with 'After team call' option) -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-budget">
                            <?php esc_html_e( 'Estimated budget', 'trek-content-manager' ); ?> <span class="ya-field-opt" style="font-weight:400;opacity:0.75;font-size:0.85em;">(<?php esc_html_e( 'Optional', 'trek-content-manager' ); ?>)</span>
                        </label>
                        <select id="<?php echo esc_attr( $form_uid ); ?>-budget" name="budget">
                            <option value="" selected><?php esc_html_e( 'Discuss after team call / Not decided yet', 'trek-content-manager' ); ?></option>
                            <option value="Discuss after team call"><?php esc_html_e( 'Discuss after team call', 'trek-content-manager' ); ?></option>
                            <option value="USD 1,000 – 1,500">USD 1,000 – 1,500</option>
                            <option value="USD 1,500 – 2,500">USD 1,500 – 2,500</option>
                            <option value="USD 2,500 – 4,000">USD 2,500 – 4,000</option>
                            <option value="USD 4,000 – 6,000">USD 4,000 – 6,000</option>
                            <option value="USD 6,000+">USD 6,000+</option>
                        </select>
                    </div>

                    <!-- Travellers Count -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-people">
                            <?php esc_html_e( 'Travellers *', 'trek-content-manager' ); ?>
                        </label>
                        <select id="<?php echo esc_attr( $form_uid ); ?>-people" name="travellers" required>
                            <option value="Solo"><?php esc_html_e( 'Solo', 'trek-content-manager' ); ?></option>
                            <option value="2 People" selected><?php esc_html_e( '2 People', 'trek-content-manager' ); ?></option>
                            <option value="3–5 People"><?php esc_html_e( '3–5 People', 'trek-content-manager' ); ?></option>
                            <option value="6–10 People"><?php esc_html_e( '6–10 People', 'trek-content-manager' ); ?></option>
                            <option value="10+ People"><?php esc_html_e( '10+ People', 'trek-content-manager' ); ?></option>
                        </select>
                    </div>

                    <!-- Full Name -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-name">
                            <?php esc_html_e( 'Full name *', 'trek-content-manager' ); ?>
                        </label>
                        <input id="<?php echo esc_attr( $form_uid ); ?>-name" name="name" type="text"
                               placeholder="<?php esc_attr_e( 'Your full name', 'trek-content-manager' ); ?>" autocomplete="name" required>
                    </div>

                    <!-- Email -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-email">
                            <?php esc_html_e( 'Email *', 'trek-content-manager' ); ?>
                        </label>
                        <input id="<?php echo esc_attr( $form_uid ); ?>-email" name="email" type="email"
                               placeholder="you@example.com" autocomplete="email" required>
                    </div>

                    <!-- WhatsApp Number (10 digits only) -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-phone">
                            <?php esc_html_e( 'WhatsApp number *', 'trek-content-manager' ); ?>
                        </label>
                        <div class="ya-phone-row">
                            <select id="<?php echo esc_attr( $form_uid ); ?>-code" name="phone_code" aria-label="<?php esc_attr_e( 'Country dial code', 'trek-content-manager' ); ?>">
                                <option value="+977" selected>+977</option>
                                <option value="+61">+61</option>
                                <option value="+44">+44</option>
                                <option value="+1">+1</option>
                                <option value="+91">+91</option>
                                <option value="+64">+64</option>
                                <option value="+49">+49</option>
                                <option value="+33">+33</option>
                                <option value="+41">+41</option>
                                <option value="+81">+81</option>
                                <option value="+65">+65</option>
                            </select>
                            <input id="<?php echo esc_attr( $form_uid ); ?>-phone" name="phone" type="tel"
                                   placeholder="<?php esc_attr_e( '10-digit number', 'trek-content-manager' ); ?>" autocomplete="tel" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" required>
                        </div>
                        <div class="ya-phone-error" style="display:none; color:#c0392b; font-size:12px; font-weight:600; margin-top:4px; line-height:1.4;" aria-live="polite"></div>
                    </div>

                    <!-- Country -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-country">
                            <?php esc_html_e( 'Country *', 'trek-content-manager' ); ?>
                        </label>
                        <input id="<?php echo esc_attr( $form_uid ); ?>-country" name="country" type="text"
                               placeholder="<?php esc_attr_e( 'Country of residence', 'trek-content-manager' ); ?>" autocomplete="country-name" required>
                    </div>

                    <!-- Preferred Travel Date -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-date">
                            <?php esc_html_e( 'Preferred date *', 'trek-content-manager' ); ?>
                        </label>
                        <input id="<?php echo esc_attr( $form_uid ); ?>-date" name="travel_date" type="date"
                               min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" required>
                    </div>

                    <!-- Referral Source -->
                    <div class="ya-field">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-source">
                            <?php esc_html_e( 'How did you hear about us?', 'trek-content-manager' ); ?>
                        </label>
                        <select id="<?php echo esc_attr( $form_uid ); ?>-source" name="source">
                            <option value="Google"><?php esc_html_e( 'Google', 'trek-content-manager' ); ?></option>
                            <option value="Friend or family"><?php esc_html_e( 'Friend or family', 'trek-content-manager' ); ?></option>
                            <option value="Instagram"><?php esc_html_e( 'Instagram', 'trek-content-manager' ); ?></option>
                            <option value="Facebook"><?php esc_html_e( 'Facebook', 'trek-content-manager' ); ?></option>
                            <option value="Travel agent"><?php esc_html_e( 'Travel agent', 'trek-content-manager' ); ?></option>
                            <option value="Other"><?php esc_html_e( 'Other', 'trek-content-manager' ); ?></option>
                        </select>
                    </div>

                    <!-- Message / Additional Notes -->
                    <div class="ya-field ya-field-full">
                        <label for="<?php echo esc_attr( $form_uid ); ?>-message">
                            <?php esc_html_e( 'Tell us more *', 'trek-content-manager' ); ?>
                        </label>
                        <textarea id="<?php echo esc_attr( $form_uid ); ?>-message" name="message" maxlength="544"
                                  placeholder="<?php esc_attr_e( 'Tell us about your interests, preferred pace, group, questions or anything else that would help us understand your trip...', 'trek-content-manager' ); ?>" required></textarea>
                        <div class="ya-counter"><span class="ya-count">0</span> / 544</div>
                    </div>

                    <!-- Submit Button Only -->
                    <div class="ya-field ya-field-full">
                        <button class="ya-btn ya-btn-gold ya-form-submit" type="submit">
                            <span class="ya-btn-text"><?php esc_html_e( 'Submit Enquiry', 'trek-content-manager' ); ?></span>
                            <span class="ya-btn-spinner" style="display:none;" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="animation: yaSpin 0.8s linear infinite;">
                                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                                    <path d="M12 2a10 10 0 0 1 10 10"></path>
                                </svg>
                            </span>
                            <svg class="ya-btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                        <p class="ya-form-note"><?php esc_html_e( 'Your enquiry will be saved to our system. You can also chat with our specialists directly on WhatsApp anytime.', 'trek-content-manager' ); ?></p>
                    </div>
                </div>
            </form>
        </div>

        <style>
            @keyframes yaSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            @keyframes yaFadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

            /* Shortcode Form Container Styling */
            .ya-planner-form-container {
                position: relative;
                width: 100%;
                box-sizing: border-box;
                font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #ffffff;
                border-radius: 4px;
                padding: clamp(28px, 4.5vw, 48px) clamp(24px, 4vw, 44px);
                box-shadow: 0 30px 80px rgba(0, 0, 0, 0.16);
                color: #1a2a24;
            }

            /* Inherit clean layout when embedded inside theme wrapper (.ya-form-wrap) */
            .ya-form-wrap .ya-planner-form-container {
                background: transparent;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }

            .ya-planner-form-container .ya-form-head {
                margin-bottom: 26px;
            }

            .ya-planner-form-container .ya-form-head h3 {
                margin: 0 0 8px;
                color: #1e3d34 !important;
                font-family: 'Newsreader', Times, Georgia, serif;
                font-size: clamp(32px, 3.8vw, 44px);
                font-weight: 600;
                line-height: 1.05;
                letter-spacing: -0.01em;
            }

            .ya-planner-form-container .ya-form-head p {
                margin: 0;
                color: #637571;
                font-family: 'Open Sans', sans-serif;
                font-size: 13.5px;
                line-height: 1.65;
            }

            .ya-planner-form-container .ya-form-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 18px 16px;
            }

            .ya-planner-form-container .ya-field {
                display: flex;
                flex-direction: column;
                gap: 7px;
            }

            .ya-planner-form-container .ya-field-full {
                grid-column: 1 / -1;
            }

            .ya-planner-form-container .ya-field label {
                font-family: 'Open Sans', sans-serif;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 1.1px;
                text-transform: uppercase;
                color: #4b5e58;
                display: block;
                margin: 0;
            }

            .ya-planner-form-container .ya-field input,
            .ya-planner-form-container .ya-field select,
            .ya-planner-form-container .ya-field textarea {
                width: 100%;
                box-sizing: border-box;
                border: 1px solid #d4deda;
                border-radius: 4px;
                background: #ffffff;
                color: #1a342b;
                font-family: 'Open Sans', sans-serif;
                font-size: 14px;
                outline: none;
                transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            }

            .ya-planner-form-container .ya-field input,
            .ya-planner-form-container .ya-field select {
                height: 48px;
                padding: 0 14px;
            }

            .ya-planner-form-container .ya-field select {
                appearance: none;
                -webkit-appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8' fill='none'%3E%3Cpath d='M1.5 1.75L6 6.25L10.5 1.75' stroke='%234B5E58' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 14px center;
                background-size: 12px 8px;
                padding-right: 36px;
                cursor: pointer;
            }

            .ya-planner-form-container .ya-field textarea {
                min-height: 108px;
                padding: 12px 14px;
                line-height: 1.6;
                resize: vertical;
            }

            .ya-planner-form-container .ya-field input::placeholder,
            .ya-planner-form-container .ya-field textarea::placeholder {
                color: #93a29f;
                font-size: 13.5px;
            }

            .ya-planner-form-container .ya-field input:focus,
            .ya-planner-form-container .ya-field select:focus,
            .ya-planner-form-container .ya-field textarea:focus {
                border-color: #d1ad54;
                background: #ffffff;
                box-shadow: 0 0 0 3px rgba(209, 173, 84, 0.2);
            }

            .ya-planner-form-container .ya-phone-row {
                display: grid;
                grid-template-columns: 105px 1fr;
                gap: 10px;
            }

            .ya-planner-form-container .ya-counter {
                text-align: right;
                color: #8c9b97;
                font-size: 11px;
                margin-top: 5px;
                font-family: 'Open Sans', sans-serif;
            }

            .ya-planner-form-container .ya-form-submit {
                width: 100%;
                height: 52px;
                min-height: 52px;
                border-radius: 4px;
                background: #d1ad54;
                color: #ffffff;
                border: none;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                font-family: 'Open Sans', sans-serif;
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 0.5px;
                margin-top: 6px;
                transition: background 0.25s ease, transform 0.2s ease, box-shadow 0.25s ease;
            }

            .ya-planner-form-container .ya-form-submit:hover {
                background: #5f7554;
                transform: translateY(-2px);
                box-shadow: 0 8px 22px rgba(95, 117, 84, 0.35);
            }

            .ya-planner-form-container .ya-form-submit:active {
                transform: translateY(0);
            }

            .ya-planner-form-container .ya-form-submit:disabled {
                opacity: 0.65;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }

            .ya-planner-form-container .ya-form-submit .ya-btn-arrow {
                width: 18px;
                height: 18px;
                stroke: #ffffff;
                transition: transform 0.25s ease;
            }

            .ya-planner-form-container .ya-form-submit:hover .ya-btn-arrow {
                transform: translateX(4px);
            }

            .ya-planner-form-container .ya-form-note {
                margin: 14px 0 0;
                text-align: center;
                color: #8c9b97;
                font-size: 11.5px;
                line-height: 1.6;
                font-family: 'Open Sans', sans-serif;
            }

            .ya-planner-form-container .ya-form-feedback {
                padding: 24px;
                border-radius: 4px;
                margin-bottom: 24px;
                animation: yaFadeIn 0.35s ease;
            }

            .ya-planner-form-container .ya-feedback-success {
                background: #f3f8f1;
                border: 1px solid #c7e2bf;
                color: #244620;
            }

            .ya-planner-form-container .ya-feedback-error {
                background: #fdf3f2;
                border: 1px solid #f3c7c4;
                color: #7d1c16;
            }

            .ya-planner-form-container .ya-feedback-success h4 {
                margin: 0 0 8px;
                color: #275922;
                font-family: 'Newsreader', Times, Georgia, serif;
                font-size: 24px;
                font-weight: 600;
            }

            .ya-planner-form-container .ya-feedback-success p {
                margin: 0 0 16px;
                font-family: 'Open Sans', sans-serif;
                font-size: 14px;
                line-height: 1.6;
            }

            .ya-planner-form-container .ya-btn-whatsapp-chat {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 12px 22px;
                background: #25D366;
                color: #ffffff !important;
                border-radius: 999px;
                font-family: 'Open Sans', sans-serif;
                font-size: 13px;
                font-weight: 700;
                text-decoration: none;
                transition: transform 0.25s, box-shadow 0.25s;
            }

            .ya-planner-form-container .ya-btn-whatsapp-chat:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);
            }

            /* Responsive Adjustments */
            @media (max-width: 680px) {
                .ya-planner-form-container .ya-form-grid {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }

                .ya-planner-form-container .ya-form-head h3 {
                    font-size: 30px;
                }
            }

            @media (max-width: 440px) {
                .ya-planner-form-container {
                    padding: 24px 18px;
                }

                .ya-planner-form-container .ya-phone-row {
                    grid-template-columns: 92px 1fr;
                    gap: 8px;
                }
            }
        </style>

        <script>
        (function() {
            const container = document.getElementById('<?php echo esc_js( $form_uid ); ?>-wrap');
            if (!container) return;

            const form = container.querySelector('.ya-planner-form');
            const feedback = container.querySelector('.ya-form-feedback');
            const submitBtn = form ? form.querySelector('.ya-form-submit') : null;
            const btnText = submitBtn ? submitBtn.querySelector('.ya-btn-text') : null;
            const btnSpinner = submitBtn ? submitBtn.querySelector('.ya-btn-spinner') : null;
            const btnArrow = submitBtn ? submitBtn.querySelector('.ya-btn-arrow') : null;
            const textarea = form ? form.querySelector('textarea[name="message"]') : null;
            const countEl = form ? form.querySelector('.ya-count') : null;

            if (textarea && countEl) {
                textarea.addEventListener('input', function() {
                    countEl.textContent = this.value.length;
                });
            }

            const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
            const phoneError = container.querySelector('.ya-phone-error');

            if (phoneInput) {
                // Ensure only digits 0-9 and max 10 characters
                phoneInput.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 10);
                    if (this.value.length === 10) {
                        this.style.borderColor = '#27ae60';
                        this.style.boxShadow = '0 0 0 2px rgba(39, 174, 96, 0.18)';
                        if (phoneError) {
                            phoneError.style.display = 'none';
                            phoneError.textContent = '';
                        }
                    } else if (this.value.length > 0 && phoneError && phoneError.style.display !== 'none') {
                        phoneError.textContent = 'Please enter exactly 10 digits (' + this.value.length + '/10).';
                    } else {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                });

                phoneInput.addEventListener('blur', function() {
                    const clean = this.value.replace(/\D/g, '');
                    if (clean.length > 0 && clean.length < 10) {
                        this.style.borderColor = '#c0392b';
                        this.style.boxShadow = '0 0 0 2px rgba(192, 57, 43, 0.15)';
                        if (phoneError) {
                            phoneError.textContent = 'Please enter exactly 10 digits (' + clean.length + '/10 digits entered).';
                            phoneError.style.display = 'block';
                        }
                    }
                });
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // HTML5 report validity check
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Strict 10-digit WhatsApp validation without UI break
                    const cleanPhone = phoneInput ? phoneInput.value.replace(/\D/g, '') : '';
                    if (cleanPhone.length !== 10) {
                        if (phoneInput) {
                            phoneInput.style.borderColor = '#c0392b';
                            phoneInput.style.boxShadow = '0 0 0 3px rgba(192, 57, 43, 0.2)';
                            phoneInput.focus();
                        }
                        if (phoneError) {
                            phoneError.textContent = cleanPhone.length === 0
                                ? 'Please enter your 10-digit WhatsApp number.'
                                : 'Please enter exactly 10 digits (currently ' + cleanPhone.length + ' digits).';
                            phoneError.style.display = 'block';
                        }
                        return;
                    } else if (phoneError) {
                        phoneError.style.display = 'none';
                        phoneError.textContent = '';
                    }

                    // Loading State
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        if (btnText) btnText.textContent = 'Submitting...';
                        if (btnSpinner) btnSpinner.style.display = 'inline-block';
                        if (btnArrow) btnArrow.style.display = 'none';
                    }

                    if (feedback) {
                        feedback.style.display = 'none';
                        feedback.className = 'ya-form-feedback';
                        feedback.innerHTML = '';
                    }

                    const formData = new FormData(form);
                    const ajaxUrl = container.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res && res.success) {
                            const data = res.data || {};
                            const whatsappUrl = data.whatsapp_url || '';
                            const travellerName = data.name || 'Traveller';
                            const adventure = data.adventure || 'Nepal Journey';

                            let successHtml = `
                                <h4>✓ Enquiry Received!</h4>
                                <p>Thank you, <strong>${escapeHtml(travellerName)}</strong>. Your enquiry for <strong>${escapeHtml(adventure)}</strong> has been successfully saved to our database. Our expedition team will review your requirements and reach out promptly.</p>
                            `;

                            if (whatsappUrl) {
                                successHtml += `
                                    <div style="margin-top: 14px;">
                                        <a href="${whatsappUrl}" target="_blank" rel="noopener" class="ya-btn-whatsapp-chat">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm5.79 14.07c-.24.68-1.4 1.25-1.93 1.33-.51.08-1.18.11-3.41-.81-2.85-1.18-4.69-4.08-4.83-4.27-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07 1-2.35.26-.28.58-.35.77-.35.19 0 .39 0 .56.01.18.01.42-.07.66.5.24.58.83 2.03.9 2.18.07.15.12.33.02.53-.1.2-.15.32-.3.49-.15.17-.32.38-.45.51-.15.15-.31.32-.13.63.17.31.78 1.28 1.67 2.07 1.15 1.02 2.11 1.34 2.42 1.49.31.15.49.13.67-.08.18-.21.78-.91.99-1.22.21-.31.42-.26.7-.15.28.11 1.77.83 2.07.98.3.15.5.22.58.35.07.13.07.75-.17 1.43z"/></svg>
                                            <span>Chat with our team on WhatsApp now</span>
                                        </a>
                                    </div>
                                `;
                            }

                            feedback.className = 'ya-form-feedback ya-feedback-success';
                            feedback.innerHTML = successHtml;
                            feedback.style.display = 'block';

                            form.reset();
                            form.style.display = 'none';

                            feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else {
                            const errorMsg = (res && res.data && res.data.message) ? res.data.message : 'An error occurred while submitting your enquiry. Please try again.';
                            feedback.className = 'ya-form-feedback ya-feedback-error';
                            feedback.innerHTML = `<p><strong>Error:</strong> ${escapeHtml(errorMsg)}</p>`;
                            feedback.style.display = 'block';

                            if (submitBtn) {
                                submitBtn.disabled = false;
                                if (btnText) btnText.textContent = 'Submit Enquiry';
                                if (btnSpinner) btnSpinner.style.display = 'none';
                                if (btnArrow) btnArrow.style.display = 'inline-block';
                            }
                        }
                    })
                    .catch(err => {
                        feedback.className = 'ya-form-feedback ya-feedback-error';
                        feedback.innerHTML = `<p><strong>Error:</strong> Network error or server timeout. Please try again or reach us directly.</p>`;
                        feedback.style.display = 'block';

                        if (submitBtn) {
                            submitBtn.disabled = false;
                            if (btnText) btnText.textContent = 'Submit Enquiry';
                            if (btnSpinner) btnSpinner.style.display = 'none';
                            if (btnArrow) btnArrow.style.display = 'inline-block';
                        }
                    });
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                const p = document.createElement('p');
                p.appendChild(document.createTextNode(str));
                return p.innerHTML;
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Form Submission Handler (Frontend)
     */
    public static function ajax_submit_enquiry() {
        check_ajax_referer( 'trek_planner_form_nonce', 'trek_nonce' );

        // Sanitize input fields
        $name        = sanitize_text_field( $_POST['name'] ?? '' );
        $email       = sanitize_email( $_POST['email'] ?? '' );
        $phone_code  = sanitize_text_field( $_POST['phone_code'] ?? '+977' );
        $phone_num   = sanitize_text_field( $_POST['phone'] ?? '' );
        $country     = sanitize_text_field( $_POST['country'] ?? '' );
        $adventure   = sanitize_text_field( $_POST['adventure'] ?? '' );
        $budget      = sanitize_text_field( $_POST['budget'] ?? '' );
        $travellers  = sanitize_text_field( $_POST['travellers'] ?? '2 People' );
        $travel_date = sanitize_text_field( $_POST['travel_date'] ?? '' );
        $source      = sanitize_text_field( $_POST['source'] ?? 'Website' );
        $message     = sanitize_textarea_field( $_POST['message'] ?? '' );
        $wa_target   = sanitize_text_field( $_POST['whatsapp_target'] ?? '9779803197006' );

        // Backend Validation
        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide your full name.', 'trek-content-manager' ) ) );
        }
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'trek-content-manager' ) ) );
        }
        if ( empty( $phone_num ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide your WhatsApp or phone number.', 'trek-content-manager' ) ) );
        }
        $phone_clean_digits = preg_replace( '/[^0-9]/', '', $phone_num );
        if ( strlen( $phone_clean_digits ) !== 10 ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid 10-digit WhatsApp number (numbers only).', 'trek-content-manager' ) ) );
        }
        $phone_num = $phone_clean_digits;
        if ( empty( $adventure ) ) {
            wp_send_json_error( array( 'message' => __( 'Please select an adventure.', 'trek-content-manager' ) ) );
        }
        if ( empty( $travel_date ) ) {
            wp_send_json_error( array( 'message' => __( 'Please select your preferred travel date.', 'trek-content-manager' ) ) );
        }
        if ( empty( $message ) ) {
            wp_send_json_error( array( 'message' => __( 'Please tell us a few details about your trip in the message box.', 'trek-content-manager' ) ) );
        }

        $full_phone  = trim( $phone_code . ' ' . $phone_num );
        $clean_phone = preg_replace( '/[^0-9]/', '', $full_phone );

        if ( empty( $budget ) ) {
            $budget = 'Discuss after team call / Not decided yet';
        }

        // Check Relationship (Whether email or phone matches an existing client)
        $relationship_data = self::check_client_relationship( $email, $full_phone );
        $is_repeat         = $relationship_data['is_repeat'] ? 1 : 0;
        $matched_count     = (int) $relationship_data['prior_count'];
        $last_match_id     = ! empty( $relationship_data['last_match']['id'] ) ? (int) $relationship_data['last_match']['id'] : 0;
        $matched_info_json = ! empty( $relationship_data['matches'] ) ? wp_json_encode( $relationship_data['matches'] ) : '';

        // 1. Insert into Dedicated Database Table: wp_trek_planner_leads
        global $wpdb;
        $table_name = self::get_table_name();
        self::maybe_create_table();

        $uid = 'PL-' . wp_rand( 10000, 99999 );

        $inserted = $wpdb->insert(
            $table_name,
            array(
                'lead_uid'            => $uid,
                'name'                => $name,
                'email'               => $email,
                'phone_code'          => $phone_code,
                'phone'               => $full_phone,
                'clean_phone'         => $clean_phone,
                'country'             => $country,
                'adventure'           => $adventure,
                'budget'              => $budget,
                'travellers'          => $travellers,
                'travel_date'         => $travel_date,
                'source'              => $source,
                'message'             => $message,
                'status'              => 'New',
                'is_repeat_customer'  => $is_repeat,
                'matched_lead_id'     => $last_match_id,
                'matched_count'       => $matched_count,
                'matched_info'        => $matched_info_json,
                'ip_address'          => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' ),
                'created_at'          => current_time( 'mysql' ),
                'updated_at'          => current_time( 'mysql' ),
            ),
            array(
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s'
            )
        );

        $custom_id = $wpdb->insert_id;

        // 2. Also save to `trek_enquiry` CPT for 100% WordPress backward compatibility
        $post_title = $name . ' — ' . $adventure;
        $post_id    = wp_insert_post( array(
            'post_title'   => $post_title,
            'post_content' => $message,
            'post_type'    => 'trek_enquiry',
            'post_status'  => 'publish',
        ) );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_trek_enquiry_name', $name );
            update_post_meta( $post_id, '_trek_enquiry_email', $email );
            update_post_meta( $post_id, '_trek_enquiry_phone', $full_phone );
            update_post_meta( $post_id, '_trek_enquiry_phone_code', $phone_code );
            update_post_meta( $post_id, '_trek_enquiry_phone_number', $phone_num );
            update_post_meta( $post_id, '_trek_enquiry_country', $country );
            update_post_meta( $post_id, '_trek_enquiry_adventure', $adventure );
            update_post_meta( $post_id, '_trek_enquiry_trek_title', $adventure );
            update_post_meta( $post_id, '_trek_enquiry_budget', $budget );
            update_post_meta( $post_id, '_trek_enquiry_travellers', $travellers );
            update_post_meta( $post_id, '_trek_enquiry_travel_date', $travel_date );
            update_post_meta( $post_id, '_trek_enquiry_date', $travel_date );
            update_post_meta( $post_id, '_trek_enquiry_source', $source );
            update_post_meta( $post_id, '_trek_enquiry_status', 'New' );
            update_post_meta( $post_id, '_trek_enquiry_lead_uid', $uid );
            update_post_meta( $post_id, '_trek_planner_table_id', $custom_id );
            update_post_meta( $post_id, '_trek_is_repeat_customer', $is_repeat );

            if ( $custom_id ) {
                $wpdb->update( $table_name, array( 'post_id' => $post_id ), array( 'id' => $custom_id ) );
            }
        }

        // WhatsApp pre-filled message URL
        $clean_wa_target = preg_replace( '/[^0-9]/', '', $wa_target ) ?: '9779803197006';
        $wa_text_lines   = array(
            'Hello Yeti Adventure, I would like to plan a Nepal journey.',
            '',
            'TRIP DETAILS',
            'Adventure: ' . $adventure,
            'Budget: ' . $budget,
            'Travellers: ' . $travellers,
            'Preferred date: ' . $travel_date,
            '',
            'CONTACT DETAILS',
            'Name: ' . $name,
            'WhatsApp: ' . $full_phone,
            'Email: ' . $email,
            'Country: ' . $country,
            'Found you through: ' . $source,
            '',
            'MESSAGE',
            $message,
        );
        $wa_text         = implode( "\n", $wa_text_lines );
        $whatsapp_url    = 'https://wa.me/' . $clean_wa_target . '?text=' . rawurlencode( $wa_text );

        wp_send_json_success( array(
            'id'           => $custom_id ?: $post_id,
            'uid'          => $uid,
            'name'         => $name,
            'adventure'    => $adventure,
            'whatsapp_url' => $whatsapp_url,
            'is_repeat'    => (bool) $is_repeat,
            'message'      => __( 'Enquiry successfully saved.', 'trek-content-manager' ),
        ) );
    }

    /**
     * AJAX: Update Lead Status from Admin Panel
     */
    public static function ajax_update_lead_status() {
        check_ajax_referer( 'trek_planner_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $lead_id = absint( $_POST['lead_id'] ?? 0 );
        $status  = sanitize_text_field( $_POST['status'] ?? 'New' );

        $allowed = array( 'New', 'Contacted', 'In Progress', 'Confirmed', 'Closed' );
        if ( ! in_array( $status, $allowed, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid status.', 'trek-content-manager' ) ) );
        }

        if ( ! $lead_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid lead ID.', 'trek-content-manager' ) ) );
        }

        global $wpdb;
        $table_name = self::get_table_name();

        // Update in custom table
        $lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $lead_id ), ARRAY_A );
        if ( $lead ) {
            $wpdb->update(
                $table_name,
                array(
                    'status'     => $status,
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => $lead_id )
            );

            if ( ! empty( $lead['post_id'] ) ) {
                update_post_meta( $lead['post_id'], '_trek_enquiry_status', $status );
            }
        } else {
            // Check if passed lead_id was a post ID
            $post = get_post( $lead_id );
            if ( $post && 'trek_enquiry' === $post->post_type ) {
                update_post_meta( $lead_id, '_trek_enquiry_status', $status );
                $wpdb->update(
                    $table_name,
                    array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ),
                    array( 'post_id' => $lead_id )
                );
            }
        }

        wp_send_json_success( array(
            'message' => __( 'Status updated successfully.', 'trek-content-manager' ),
            'status'  => $status,
        ) );
    }

    /**
     * AJAX: Delete Lead from Admin Panel
     */
    public static function ajax_delete_lead() {
        check_ajax_referer( 'trek_planner_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'delete_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $lead_id = absint( $_POST['lead_id'] ?? 0 );
        if ( ! $lead_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid lead ID.', 'trek-content-manager' ) ) );
        }

        global $wpdb;
        $table_name = self::get_table_name();

        $lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $lead_id ), ARRAY_A );
        if ( $lead ) {
            $wpdb->delete( $table_name, array( 'id' => $lead_id ) );
            if ( ! empty( $lead['post_id'] ) ) {
                wp_trash_post( $lead['post_id'] );
            }
        } else {
            // If post ID passed
            $wpdb->delete( $table_name, array( 'post_id' => $lead_id ) );
            wp_trash_post( $lead_id );
        }

        wp_send_json_success( array( 'message' => __( 'Lead deleted successfully.', 'trek-content-manager' ) ) );
    }

    /**
     * AJAX: Get Full Lead Details for Slide-Over Drawer
     */
    public static function ajax_get_lead_detail() {
        check_ajax_referer( 'trek_planner_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $lead_id = absint( $_POST['lead_id'] ?? 0 );
        if ( ! $lead_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid lead ID.', 'trek-content-manager' ) ) );
        }

        global $wpdb;
        $table_name = self::get_table_name();

        $lead = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $lead_id ), ARRAY_A );

        // Fallback to postmeta if not found in custom table
        if ( ! $lead ) {
            $post = get_post( $lead_id );
            if ( ! $post || 'trek_enquiry' !== $post->post_type ) {
                wp_send_json_error( array( 'message' => __( 'Lead not found.', 'trek-content-manager' ) ) );
            }

            $lead = array(
                'id'                 => $post->ID,
                'lead_uid'           => get_post_meta( $post->ID, '_trek_enquiry_lead_uid', true ) ?: ( 'PL-' . $post->ID ),
                'name'               => get_post_meta( $post->ID, '_trek_enquiry_name', true ) ?: $post->post_title,
                'email'              => get_post_meta( $post->ID, '_trek_enquiry_email', true ) ?: '—',
                'phone'              => get_post_meta( $post->ID, '_trek_enquiry_phone', true ) ?: '—',
                'country'            => get_post_meta( $post->ID, '_trek_enquiry_country', true ) ?: '—',
                'adventure'          => get_post_meta( $post->ID, '_trek_enquiry_adventure', true ) ?: ( get_post_meta( $post->ID, '_trek_enquiry_trek_title', true ) ?: 'Nepal Expedition' ),
                'budget'             => get_post_meta( $post->ID, '_trek_enquiry_budget', true ) ?: 'Discuss after team call',
                'travellers'         => get_post_meta( $post->ID, '_trek_enquiry_travellers', true ) ?: '2 People',
                'travel_date'        => get_post_meta( $post->ID, '_trek_enquiry_travel_date', true ) ?: ( get_post_meta( $post->ID, '_trek_enquiry_date', true ) ?: 'Flexible' ),
                'source'             => get_post_meta( $post->ID, '_trek_enquiry_source', true ) ?: 'Website',
                'status'             => get_post_meta( $post->ID, '_trek_enquiry_status', true ) ?: 'New',
                'message'            => $post->post_content,
                'is_repeat_customer' => get_post_meta( $post->ID, '_trek_is_repeat_customer', true ) ?: 0,
                'matched_count'      => 0,
                'matched_info'       => '',
                'created_at'         => $post->post_date,
                'ip_address'         => get_post_meta( $post->ID, '_trek_enquiry_ip', true ) ?: '—',
            );
        }

        $clean_phone  = preg_replace( '/[^0-9]/', '', $lead['phone'] );
        $whatsapp_url = ! empty( $clean_phone ) ? 'https://wa.me/' . $clean_phone : '';

        // Decode matched past enquiries if any
        $matched_records = array();
        if ( ! empty( $lead['matched_info'] ) ) {
            $decoded = json_decode( $lead['matched_info'], true );
            if ( is_array( $decoded ) ) {
                $matched_records = $decoded;
            }
        }

        // Real-time relationship check for verification
        $rel_check = self::check_client_relationship( $lead['email'], $lead['phone'] );

        wp_send_json_success( array(
            'id'                 => $lead['id'],
            'lead_uid'           => $lead['lead_uid'],
            'name'               => $lead['name'],
            'email'              => $lead['email'],
            'phone'              => $lead['phone'],
            'whatsapp_url'       => $whatsapp_url,
            'country'            => $lead['country'],
            'adventure'          => $lead['adventure'],
            'budget'             => $lead['budget'],
            'travellers'         => $lead['travellers'],
            'travel_date'        => $lead['travel_date'],
            'source'             => $lead['source'],
            'status'             => $lead['status'],
            'message'            => nl2br( esc_html( $lead['message'] ) ) ?: '<em>No additional message provided.</em>',
            'created'            => mysql2date( 'F j, Y g:i a', $lead['created_at'] ),
            'ip'                 => $lead['ip_address'],
            'is_repeat_customer' => (bool) ( $lead['is_repeat_customer'] || $rel_check['is_repeat'] ),
            'matched_count'      => max( (int) $lead['matched_count'], (int) $rel_check['prior_count'] ),
            'matched_records'    => ! empty( $matched_records ) ? $matched_records : $rel_check['matches'],
        ) );
    }

    /**
     * Render the New Trip Planner Leads Admin Page
     */
    public static function render_admin_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        self::maybe_create_table();

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-planner-submissions.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Admin template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }
}
