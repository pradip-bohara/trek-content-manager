<?php
/**
 * Trek Content Manager - High Performance JSON Importer & Schema Reference
 *
 * Allows bulk import of Trek Destinations from standard JSON files with:
 * - Automatic CPT creation/update
 * - Automatic taxonomy creation and assignment (trek_category, trek_region)
 * - Complete post meta field mapping
 * - Post thumbnail attachment
 * - Interactive JSON Schema documentation and downloadable sample template
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Importer {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ), 20 );
        add_action( 'admin_post_trek_import_json', array( __CLASS__, 'handle_json_import' ) );
        add_action( 'wp_ajax_trek_download_sample_json', array( __CLASS__, 'ajax_download_sample_json' ) );
    }

    private static function scalar_text( $value, $default = '' ) {
        return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $default;
    }

    private static function safe_url( $value ) {
        return is_string( $value ) ? esc_url_raw( trim( $value ) ) : '';
    }

    private static function term_id( $term ) {
        if ( is_array( $term ) && isset( $term['term_id'] ) ) {
            return absint( $term['term_id'] );
        }
        return is_numeric( $term ) ? absint( $term ) : 0;
    }

    private static function get_or_create_term_id( $name, $taxonomy, $create = false ) {
        $name = self::scalar_text( $name );
        if ( '' === $name ) {
            return 0;
        }

        $term_id = self::term_id( term_exists( $name, $taxonomy ) );
        if ( ! $term_id && $create ) {
            $term_id = self::term_id( wp_insert_term( $name, $taxonomy ) );
        }
        return $term_id;
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'JSON Importer & Schema Guide', 'trek-content-manager' ),
            __( '📥 JSON Importer', 'trek-content-manager' ),
            'edit_posts',
            'trek-json-importer',
            array( __CLASS__, 'render_importer_page' )
        );
    }

    /**
     * Main Admin Page Renderer
     */
    public static function render_importer_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        $import_status = isset( $_GET['import_status'] ) ? sanitize_text_field( $_GET['import_status'] ) : '';
        $import_count  = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 0;
        $updated_count = isset( $_GET['updated'] ) ? intval( $_GET['updated'] ) : 0;
        $error_msg     = isset( $_GET['error_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['error_msg'] ) ) : '';
        ?>
        <div class="wrap ya-importer-wrap">
            <style>
                .ya-importer-wrap {
                    max-width: 1280px;
                    margin: 20px auto 40px;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                }
                .ya-header-banner {
                    background: linear-gradient(135deg, #022f2e 0%, #045857 100%);
                    color: #fff;
                    padding: 32px 36px;
                    border-radius: 12px;
                    margin-bottom: 28px;
                    box-shadow: 0 10px 30px rgba(2, 47, 46, 0.18);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 20px;
                }
                .ya-header-banner h1 {
                    color: #fff;
                    font-size: 28px;
                    font-weight: 700;
                    margin: 0 0 8px 0;
                    letter-spacing: -0.02em;
                }
                .ya-header-banner p {
                    color: rgba(255, 255, 255, 0.85);
                    font-size: 14px;
                    margin: 0;
                    max-width: 680px;
                    line-height: 1.5;
                }
                .ya-header-actions {
                    display: flex;
                    gap: 12px;
                }
                .ya-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-size: 13px;
                    font-weight: 600;
                    text-decoration: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border: none;
                }
                .ya-btn-gold {
                    background: #c4a35f;
                    color: #022f2e;
                }
                .ya-btn-gold:hover {
                    background: #d4b574;
                    color: #011b1a;
                    transform: translateY(-1px);
                }
                .ya-btn-outline {
                    background: rgba(255, 255, 255, 0.12);
                    color: #fff;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }
                .ya-btn-outline:hover {
                    background: rgba(255, 255, 255, 0.22);
                    color: #fff;
                }
                .ya-nav-tabs {
                    display: flex;
                    gap: 8px;
                    border-bottom: 2px solid #e2e8f0;
                    margin-bottom: 24px;
                }
                .ya-nav-tab {
                    padding: 12px 22px;
                    font-size: 14px;
                    font-weight: 600;
                    color: #64748b;
                    text-decoration: none;
                    border-bottom: 3px solid transparent;
                    margin-bottom: -2px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                .ya-nav-tab.active, .ya-nav-tab:hover {
                    color: #045857;
                    border-bottom-color: #045857;
                }
                .ya-tab-content {
                    display: none;
                }
                .ya-tab-content.active {
                    display: block;
                }
                .ya-card {
                    background: #fff;
                    border-radius: 12px;
                    padding: 28px 32px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
                    border: 1px solid #e2e8f0;
                    margin-bottom: 24px;
                }
                .ya-card h2 {
                    font-size: 18px;
                    font-weight: 700;
                    color: #022f2e;
                    margin: 0 0 16px 0;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .ya-dropzone {
                    border: 2px dashed #cbd5e1;
                    border-radius: 10px;
                    padding: 40px 20px;
                    text-align: center;
                    background: #f8fafc;
                    transition: all 0.25s ease;
                    cursor: pointer;
                    margin-bottom: 20px;
                }
                .ya-dropzone:hover, .ya-dropzone.dragover {
                    border-color: #045857;
                    background: #f0fdfa;
                }
                .ya-dropzone svg {
                    width: 48px;
                    height: 48px;
                    color: #045857;
                    margin-bottom: 12px;
                }
                .ya-dropzone p {
                    margin: 0 0 6px 0;
                    font-size: 15px;
                    font-weight: 600;
                    color: #1e293b;
                }
                .ya-dropzone span {
                    font-size: 12px;
                    color: #64748b;
                }
                .ya-textarea {
                    width: 100%;
                    min-height: 220px;
                    font-family: Consolas, Monaco, monospace;
                    font-size: 12.5px;
                    padding: 14px;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    background: #f8fafc;
                    line-height: 1.5;
                    box-sizing: border-box;
                }
                .ya-options-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 18px;
                    margin: 20px 0;
                    padding: 16px;
                    background: #f8fafc;
                    border-radius: 8px;
                    border: 1px solid #e2e8f0;
                }
                .ya-schema-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 13px;
                    margin-top: 14px;
                }
                .ya-schema-table th {
                    background: #022f2e;
                    color: #fff;
                    text-align: left;
                    padding: 12px 16px;
                    font-weight: 600;
                }
                .ya-schema-table td {
                    padding: 12px 16px;
                    border-bottom: 1px solid #e2e8f0;
                    vertical-align: top;
                }
                .ya-schema-table tr:nth-child(even) {
                    background: #f8fafc;
                }
                .ya-badge {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                }
                .ya-badge-req { background: #fee2e2; color: #991b1b; }
                .ya-badge-opt { background: #e0f2fe; color: #075985; }
                .ya-badge-meta { background: #fef3c7; color: #92400e; font-family: monospace; }
                .ya-code-block {
                    background: #0f172a;
                    color: #e2e8f0;
                    padding: 20px;
                    border-radius: 8px;
                    font-family: Consolas, Monaco, monospace;
                    font-size: 12px;
                    line-height: 1.6;
                    overflow-x: auto;
                    max-height: 480px;
                    position: relative;
                }
                .ya-copy-btn {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    background: #334155;
                    color: #fff;
                    border: none;
                    padding: 6px 12px;
                    border-radius: 6px;
                    font-size: 11px;
                    cursor: pointer;
                }
                .ya-copy-btn:hover { background: #045857; }
                .ya-alert {
                    padding: 16px 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    font-size: 14px;
                }
                .ya-alert-success { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
                .ya-alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
            </style>

            <div class="ya-header-banner">
                <div>
                    <h1>🏔️ Trek Manager — JSON Bulk Importer & Data Guide</h1>
                    <p>Import all your Himalayan treks, luxury tours, and cultural adventures in one click using standard JSON. Supports automated taxonomy resolution, repeaters, hero sections, FAQs, and full meta field binding.</p>
                </div>
                <div class="ya-header-actions">
                    <button type="button" class="ya-btn ya-btn-gold" id="btnDownloadSampleTop">
                        <span class="dashicons dashicons-download"></span> Download Sample JSON
                    </button>
                </div>
            </div>

            <?php if ( 'success' === $import_status ) : ?>
                <div class="ya-alert ya-alert-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div>
                        <strong><?php esc_html_e( 'Import Completed Successfully!', 'trek-content-manager' ); ?></strong>
                        <p style="margin:4px 0 0 0;">
                            <?php
                            printf(
                                /* translators: 1: created count, 2: updated count */
                                esc_html__( 'Processed %1$d new destinations and updated %2$d existing destinations.', 'trek-content-manager' ),
                                $import_count,
                                $updated_count
                            );
                            ?>
                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination' ) ); ?>" style="color:#166534; font-weight:700; text-decoration:underline; margin-left:8px;">
                                <?php esc_html_e( 'View All Destinations →', 'trek-content-manager' ); ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php elseif ( 'error' === $import_status ) : ?>
                <div class="ya-alert ya-alert-error">
                    <span class="dashicons dashicons-warning"></span>
                    <div>
                        <strong><?php esc_html_e( 'Import Failed', 'trek-content-manager' ); ?></strong>
                        <p style="margin:4px 0 0 0;"><?php echo esc_html( $error_msg ?: __( 'An unknown error occurred during import. Please check your JSON format.', 'trek-content-manager' ) ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="ya-nav-tabs">
                <div class="ya-nav-tab active" data-tab="tab-upload">📥 1. Upload & Import</div>
                <div class="ya-nav-tab" data-tab="tab-schema">📋 2. Database Field Reference</div>
                <div class="ya-nav-tab" data-tab="tab-sample">📄 3. Sample JSON Template</div>
            </div>

            <!-- TAB 1: UPLOAD & IMPORT -->
            <div class="ya-tab-content active" id="tab-upload">
                <div class="ya-card">
                    <h2><span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Upload Destinations JSON File', 'trek-content-manager' ); ?></h2>
                    
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'trek_import_json_action', 'trek_import_nonce' ); ?>
                        <input type="hidden" name="action" value="trek_import_json">

                        <div class="ya-dropzone" id="yaDropzone">
                            <input type="file" name="trek_json_file" id="trek_json_file" accept=".json" style="display:none;">
                            <span class="dashicons dashicons-cloud-upload" style="font-size:48px; width:48px; height:48px; color:#045857;"></span>
                            <p><?php esc_html_e( 'Click to choose a JSON file or drag and drop it here', 'trek-content-manager' ); ?></p>
                            <span id="selectedFileName"><?php esc_html_e( 'Accepts valid .json file containing a single destination object or an array of destinations', 'trek-content-manager' ); ?></span>
                        </div>

                        <div style="margin: 20px 0 10px 0;">
                            <label for="trek_raw_json" style="font-weight:600; color:#1e293b; display:block; margin-bottom:8px;">
                                <?php esc_html_e( 'Or Paste Raw JSON Below:', 'trek-content-manager' ); ?>
                            </label>
                            <textarea name="trek_raw_json" id="trek_raw_json" class="ya-textarea" placeholder='[
  {
    "title": "The Ultimate Everest Base Camp Experience",
    "slug": "everest-basecamp-trek",
    "days": 15,
    "category": ["Trekking", "Himalayan"],
    "region": "Nepal",
    "hero_image": "https://...",
    "overview": "..."
  }
]'></textarea>
                        </div>

                        <div class="ya-options-grid">
                            <div>
                                <label style="font-weight:600; color:#1e293b;">
                                    <input type="checkbox" name="update_existing" value="1" checked>
                                    <?php esc_html_e( 'Update existing destination if matching slug or title exists', 'trek-content-manager' ); ?>
                                </label>
                                <p style="font-size:12px; color:#64748b; margin:4px 0 0 20px;">
                                    <?php esc_html_e( 'If unchecked, new posts will always be created with unique slugs.', 'trek-content-manager' ); ?>
                                </p>
                            </div>
                            <div>
                                <label style="font-weight:600; color:#1e293b;">
                                    <input type="checkbox" name="create_terms" value="1" checked>
                                    <?php esc_html_e( 'Automatically create new Categories & Regions if they do not exist', 'trek-content-manager' ); ?>
                                </label>
                                <p style="font-size:12px; color:#64748b; margin:4px 0 0 20px;">
                                    <?php esc_html_e( 'Taxonomies trek_category and trek_region will be created dynamically.', 'trek-content-manager' ); ?>
                                </p>
                            </div>
                            <div>
                                <label style="font-weight:600; color:#1e293b;">
                                    <input type="checkbox" name="publish_immediately" value="1" checked>
                                    <?php esc_html_e( 'Publish destinations immediately (Status: Publish)', 'trek-content-manager' ); ?>
                                </label>
                                <p style="font-size:12px; color:#64748b; margin:4px 0 0 20px;">
                                    <?php esc_html_e( 'Uncheck if you want to import as Drafts for review.', 'trek-content-manager' ); ?>
                                </p>
                            </div>
                        </div>

                        <div style="margin-top:24px; display:flex; gap:12px; align-items:center;">
                            <button type="submit" class="ya-btn ya-btn-gold" style="padding:12px 28px; font-size:14px;">
                                <span class="dashicons dashicons-database-import"></span> <?php esc_html_e( 'Start Bulk Import', 'trek-content-manager' ); ?>
                            </button>
                            <button type="button" class="ya-btn ya-btn-outline" id="btnFillSampleInTextarea" style="color:#045857; border-color:#045857;">
                                <?php esc_html_e( 'Paste Sample Data into Box', 'trek-content-manager' ); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 2: SCHEMA REFERENCE -->
            <div class="ya-tab-content" id="tab-schema">
                <div class="ya-card">
                    <h2><span class="dashicons dashicons-book"></span> <?php esc_html_e( 'Complete JSON Schema & Database Field Guide', 'trek-content-manager' ); ?></h2>
                    <p style="color:#64748b; font-size:14px; margin-top:0;">
                        <?php esc_html_e( 'Every destination object in your JSON array can contain any of the following keys. All fields are sanitized and safely stored into the WordPress database.', 'trek-content-manager' ); ?>
                    </p>

                    <table class="ya-schema-table">
                        <thead>
                            <tr>
                                <th style="width:20%;"><?php esc_html_e( 'JSON Key / Field', 'trek-content-manager' ); ?></th>
                                <th style="width:12%;"><?php esc_html_e( 'Type', 'trek-content-manager' ); ?></th>
                                <th style="width:12%;"><?php esc_html_e( 'Requirement', 'trek-content-manager' ); ?></th>
                                <th style="width:22%;"><?php esc_html_e( 'Database Meta Key / Taxonomy', 'trek-content-manager' ); ?></th>
                                <th style="width:34%;"><?php esc_html_e( 'Description & Example', 'trek-content-manager' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>title</strong> / name</td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-req">Required</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_title</span></td>
                                <td>Main destination post title. E.g. <code>"The Ultimate Everest Base Camp Experience"</code></td>
                            </tr>
                            <tr>
                                <td><strong>slug</strong></td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_name</span></td>
                                <td>URL permalink slug. E.g. <code>"everest-base-camp-experience"</code></td>
                            </tr>
                            <tr>
                                <td><strong>excerpt</strong></td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_excerpt</span></td>
                                <td>Short post excerpt / teaser summary.</td>
                            </tr>
                            <tr>
                                <td><strong>category</strong> / categories</td>
                                <td><code>string | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">trek_category</span></td>
                                <td>Taxonomy terms. E.g. <code>["Trekking", "Himalayan", "Signature Journey"]</code></td>
                            </tr>
                            <tr>
                                <td><strong>region</strong> / country</td>
                                <td><code>string | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">trek_region</span></td>
                                <td>Taxonomy terms. E.g. <code>["Nepal"]</code></td>
                            </tr>
                            <tr>
                                <td><strong>difficulty</strong></td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_difficulty</span></td>
                                <td>Difficulty level: <code>"Easy"</code>, <code>"Moderate"</code>, <code>"Demanding"</code>, <code>"Strenuous"</code>, <code>"Challenging"</code>, <code>"Technical"</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>featured</strong> / is_featured</td>
                                <td><code>boolean | number</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_is_featured</span></td>
                                <td>Set <code>true</code> or <code>1</code> to highlight in featured sliders and homepage showcase.</td>
                            </tr>
                            <tr>
                                <td><strong>hero</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_hero_*</span></td>
                                <td>Hero section object: <code>script_title</code>, <code>title</code>, <code>kicker</code>, <code>subtitle</code>, <code>days</code>, <code>badge_sub</code>, <code>image</code>, <code>slider_images[]</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>card</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_card_*</span></td>
                                <td>Card display settings: <code>title</code>, <code>meta</code> (duration pill), <code>tag</code>, <code>kicker</code>, <code>excerpt</code>, <code>image</code>, <code>order</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>quick_facts</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_quick_facts</span></td>
                                <td>Trip specifications: <code>high_point</code>, <code>low_point</code>, <code>region</code>, <code>season</code>, <code>group_size</code>, <code>trekking</code>, <code>accom</code>, <code>style</code>, <code>distance</code>, <code>start</code>, <code>end</code>, <code>status</code>, <code>price</code>, <code>currency</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>overview</strong></td>
                                <td><code>object | string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_content & meta</span></td>
                                <td>Overview section: <code>content</code> (WYSIWYG), <code>kicker</code>, <code>script</code>, <code>heading</code>, <code>subheading</code>, <code>highlight</code>, <code>highlights[]</code>, <code>route_img</code>, <code>map_file_url</code>, <code>map_embed_code</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>itinerary_section</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_itin_*</span></td>
                                <td>Itinerary section header settings: <code>kicker</code>, <code>script</code>, <code>heading</code>, <code>subtext</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>itinerary</strong></td>
                                <td><code>array of objects</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_itinerary</span></td>
                                <td>Day-by-day trail stages. Keys per day: <code>day</code>, <code>stage</code>, <code>title</code>, <code>description</code>, <code>pills</code>, <code>altitude</code>, <code>walking_hours</code>, <code>stay</code>, <code>meals</code>, <code>gallery[]</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>inex</strong> / inclusions_section</td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_inex_*</span></td>
                                <td>Inclusions & Exclusions section headers: <code>kicker</code>, <code>script</code>, <code>heading</code>, <code>subtext</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>inclusions</strong></td>
                                <td><code>array of objects | strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_inclusions</span></td>
                                <td>Included items. Array of objects: <code>[{"title": "...", "desc": "..."}]</code> or array of strings.</td>
                            </tr>
                            <tr>
                                <td><strong>exclusions</strong></td>
                                <td><code>array of objects | strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_exclusions</span></td>
                                <td>Excluded items. Array of objects: <code>[{"title": "...", "desc": "..."}]</code> or array of strings.</td>
                            </tr>
                            <tr>
                                <td><strong>gallery</strong></td>
                                <td><code>object | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_gallery_*</span></td>
                                <td>Photo gallery section with <code>kicker</code>, <code>script</code>, <code>heading</code>, <code>subtext</code>, and <code>images[]</code> (array of URLs or <code>{"url", "label"}</code>).</td>
                            </tr>
                            <tr>
                                <td><strong>essential</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_ess_*</span></td>
                                <td>Essential info section: <code>kicker</code>, <code>script</code>, <code>heading</code>, <code>intro</code>, <code>card_title</code>, <code>card_desc</code>, <code>card_btn</code>, <code>card_email</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>faqs</strong></td>
                                <td><code>array of objects</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_faqs</span></td>
                                <td>Interactive accordion FAQs: <code>[{"question": "...", "answer": "..."}]</code></td>
                            </tr>
                            <tr>
                                <td><strong>reviews_section</strong> / reviews</td>
                                <td><code>object | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_review_*</span></td>
                                <td>Reviews section header & curated testimonials: <code>[{"name": "...", "location": "...", "rating": "5", "quote": "..."}]</code></td>
                            </tr>
                            <tr>
                                <td><strong>contact</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_dep_*</span></td>
                                <td>Booking & WhatsApp settings: <code>dep_kicker</code>, <code>dep_script</code>, <code>dep_heading</code>, <code>dep_subtext</code>, <code>box_title</code>, <code>box_desc</code>, <code>box_photo</code>, <code>box_note</code>, <code>whatsapp</code>, <code>email</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>departures</strong></td>
                                <td><code>array of objects</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_dep_dates</span></td>
                                <td>Fixed departure schedules: <code>[{"date": "2026-03-15", "price": "2850", "currency": "USD", "seats": 8, "status": "open", "note": "..."}]</code></td>
                            </tr>
                            <tr>
                                <td><strong>pricing</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_price / currency</span></td>
                                <td>Pricing details: <code>{"price": "2850", "currency": "USD"}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: SAMPLE JSON TEMPLATE -->
            <div class="ya-tab-content" id="tab-sample">
                <div class="ya-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h2 style="margin:0;"><span class="dashicons dashicons-media-code"></span> <?php esc_html_e( 'Ready-to-Use Sample JSON Template', 'trek-content-manager' ); ?></h2>
                        <button type="button" class="ya-btn ya-btn-gold" id="btnDownloadSample">
                            <span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Download .json File', 'trek-content-manager' ); ?>
                        </button>
                    </div>

                    <div class="ya-code-block">
                        <button type="button" class="ya-copy-btn" id="btnCopyJson">Copy JSON</button>
                        <pre id="sampleJsonContent"><?php echo esc_html( self::get_sample_json_string() ); ?></pre>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (function() {
                // Tab switching
                const tabs = document.querySelectorAll('.ya-nav-tab');
                const contents = document.querySelectorAll('.ya-tab-content');

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        tab.classList.add('active');
                        const target = document.getElementById(tab.getAttribute('data-tab'));
                        if (target) target.classList.add('active');
                    });
                });

                // Dropzone handling
                const dropzone = document.getElementById('yaDropzone');
                const fileInput = document.getElementById('trek_json_file');
                const fileNameSpan = document.getElementById('selectedFileName');

                if (dropzone && fileInput) {
                    dropzone.addEventListener('click', () => fileInput.click());

                    fileInput.addEventListener('change', (e) => {
                        if (fileInput.files.length > 0) {
                            fileNameSpan.textContent = 'Selected: ' + fileInput.files[0].name + ' (' + Math.round(fileInput.files[0].size / 1024) + ' KB)';
                            fileNameSpan.style.color = '#045857';
                            fileNameSpan.style.fontWeight = '600';
                        }
                    });

                    ['dragenter', 'dragover'].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            dropzone.classList.add('dragover');
                        });
                    });

                    ['dragleave', 'drop'].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            dropzone.classList.remove('dragover');
                        });
                    });

                    dropzone.addEventListener('drop', (e) => {
                        if (e.dataTransfer.files.length > 0) {
                            fileInput.files = e.dataTransfer.files;
                            fileNameSpan.textContent = 'Selected: ' + e.dataTransfer.files[0].name + ' (' + Math.round(e.dataTransfer.files[0].size / 1024) + ' KB)';
                            fileNameSpan.style.color = '#045857';
                            fileNameSpan.style.fontWeight = '600';
                        }
                    });
                }

                // Sample JSON download & copy
                const sampleText = document.getElementById('sampleJsonContent')?.textContent || '';

                function triggerDownload() {
                    const blob = new Blob([sampleText], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'yeti-adventures-destinations-sample.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }

                document.getElementById('btnDownloadSample')?.addEventListener('click', triggerDownload);
                document.getElementById('btnDownloadSampleTop')?.addEventListener('click', triggerDownload);

                document.getElementById('btnCopyJson')?.addEventListener('click', function() {
                    navigator.clipboard.writeText(sampleText).then(() => {
                        this.textContent = 'Copied!';
                        setTimeout(() => { this.textContent = 'Copy JSON'; }, 2000);
                    });
                });

                document.getElementById('btnFillSampleInTextarea')?.addEventListener('click', function() {
                    const textarea = document.getElementById('trek_raw_json');
                    if (textarea) {
                        textarea.value = sampleText;
                        textarea.focus();
                    }
                });
            })();
        </script>
        <?php
    }

    /**
     * Handle JSON Import (Form Submission)
     */
    public static function handle_json_import() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Unauthorized user', 'trek-content-manager' ) );
        }

        check_admin_referer( 'trek_import_json_action', 'trek_import_nonce' );

        $json_raw = '';

        // 1. Check file upload first
        if ( ! empty( $_FILES['trek_json_file']['tmp_name'] ) && is_uploaded_file( $_FILES['trek_json_file']['tmp_name'] ) ) {
            $json_raw = file_get_contents( $_FILES['trek_json_file']['tmp_name'] );
        } elseif ( ! empty( $_POST['trek_raw_json'] ) ) {
            $json_raw = wp_unslash( $_POST['trek_raw_json'] );
        }

        if ( empty( trim( $json_raw ) ) ) {
            wp_safe_redirect( add_query_arg( array(
                'page'          => 'trek-json-importer',
                'import_status' => 'error',
                'error_msg'     => rawurlencode( __( 'No JSON file uploaded and no raw JSON provided.', 'trek-content-manager' ) )
            ), admin_url( 'edit.php?post_type=trek_destination' ) ) );
            exit;
        }

        $data = json_decode( $json_raw, true );
        if ( null === $data || ! is_array( $data ) ) {
            wp_safe_redirect( add_query_arg( array(
                'page'          => 'trek-json-importer',
                'import_status' => 'error',
                'error_msg'     => rawurlencode( __( 'Invalid JSON format. Please ensure valid JSON syntax.', 'trek-content-manager' ) )
            ), admin_url( 'edit.php?post_type=trek_destination' ) ) );
            exit;
        }

        // Standardize single item into an array of items
        if ( isset( $data['title'] ) || isset( $data['name'] ) ) {
            $data = array( $data );
        }

        $update_existing = ! empty( $_POST['update_existing'] );
        $create_terms    = ! empty( $_POST['create_terms'] );
        $publish_requested = ! empty( $_POST['publish_immediately'] );
        $publish_status = ( $publish_requested && current_user_can( 'publish_posts' ) ) ? 'publish' : 'draft';

        $created_count = 0;
        $updated_count = 0;

        foreach ( $data as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }

            $title = ! empty( $item['title'] ) ? sanitize_text_field( $item['title'] ) : ( ! empty( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '' );
            if ( empty( $title ) ) {
                continue;
            }

            $slug = ! empty( $item['slug'] ) ? sanitize_title( $item['slug'] ) : sanitize_title( $title );

            // Check if existing post exists by slug or title
            $existing_post_id = 0;
            if ( $update_existing ) {
                $existing_post = get_page_by_path( $slug, OBJECT, 'trek_destination' );
                if ( $existing_post ) {
                    $existing_post_id = $existing_post->ID;
                } else {
                    // Try by title
                    $by_title_query = new WP_Query( array(
                        'post_type'      => 'trek_destination',
                        'title'          => $title,
                        'posts_per_page' => 1,
                        'post_status'    => 'any',
                        'fields'         => 'ids',
                    ) );
                    if ( ! empty( $by_title_query->posts ) ) {
                        $existing_post_id = $by_title_query->posts[0];
                    }
                }
            }

            // Extract post_content safely from overview object/string, description, or content
            $raw_content = '';
            if ( ! empty( $item['overview'] ) ) {
                if ( is_array( $item['overview'] ) ) {
                    $raw_content = $item['overview']['content'] ?? ( $item['overview']['description'] ?? ( $item['overview']['highlight'] ?? '' ) );
                } else {
                    $raw_content = $item['overview'];
                }
            } elseif ( ! empty( $item['description'] ) ) {
                $raw_content = is_array( $item['description'] ) ? ( $item['description']['content'] ?? '' ) : $item['description'];
            } elseif ( ! empty( $item['content'] ) ) {
                $raw_content = is_array( $item['content'] ) ? ( $item['content']['content'] ?? '' ) : $item['content'];
            }
            $content = is_string( $raw_content ) ? wp_kses_post( $raw_content ) : '';

            // Extract post_excerpt safely
            $raw_excerpt = '';
            if ( ! empty( $item['excerpt'] ) ) {
                $raw_excerpt = is_array( $item['excerpt'] ) ? ( $item['excerpt']['text'] ?? '' ) : $item['excerpt'];
            } elseif ( ! empty( $item['card_excerpt'] ) ) {
                $raw_excerpt = is_array( $item['card_excerpt'] ) ? '' : $item['card_excerpt'];
            } elseif ( ! empty( $item['card']['excerpt'] ) ) {
                $raw_excerpt = is_array( $item['card']['excerpt'] ) ? '' : $item['card']['excerpt'];
            }
            $excerpt = is_string( $raw_excerpt ) ? sanitize_textarea_field( $raw_excerpt ) : '';

            $post_args = array(
                'post_type'    => 'trek_destination',
                'post_title'   => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status'  => $publish_status,
            );

            if ( $existing_post_id > 0 ) {
                $post_args['ID'] = $existing_post_id;
                $post_id = wp_update_post( $post_args );
                $updated_count++;
            } else {
                $post_args['post_name'] = $slug;
                $post_id = wp_insert_post( $post_args );
                $created_count++;
            }

            if ( ! $post_id || is_wp_error( $post_id ) ) {
                continue;
            }

            // 1. Taxonomies: Categories
            if ( ! empty( $item['category'] ) || ! empty( $item['categories'] ) ) {
                $raw_cats = ! empty( $item['category'] ) ? $item['category'] : $item['categories'];
                $cat_array = is_array( $raw_cats ) ? $raw_cats : array_map( 'trim', explode( ',', $raw_cats ) );
                $cat_ids = array();
                foreach ( $cat_array as $cat_name ) {
                    $cat_name = sanitize_text_field( $cat_name );
                    if ( empty( $cat_name ) ) continue;
                    $term_id = self::get_or_create_term_id( $cat_name, 'trek_category', $create_terms );
                    if ( $term_id ) {
                        $cat_ids[] = $term_id;
                    }
                }
                if ( ! empty( $cat_ids ) ) {
                    wp_set_object_terms( $post_id, $cat_ids, 'trek_category' );
                }
            }

            // 2. Taxonomies: Regions
            if ( ! empty( $item['region'] ) || ! empty( $item['regions'] ) || ! empty( $item['country'] ) ) {
                $raw_regs = ! empty( $item['region'] ) ? $item['region'] : ( ! empty( $item['regions'] ) ? $item['regions'] : $item['country'] );
                $reg_array = is_array( $raw_regs ) ? $raw_regs : array_map( 'trim', explode( ',', $raw_regs ) );
                $reg_ids = array();
                foreach ( $reg_array as $reg_name ) {
                    $reg_name = sanitize_text_field( $reg_name );
                    if ( empty( $reg_name ) ) continue;
                    $term_id = self::get_or_create_term_id( $reg_name, 'trek_region', $create_terms );
                    if ( $term_id ) {
                        $reg_ids[] = $term_id;
                    }
                }
                if ( ! empty( $reg_ids ) ) {
                    wp_set_object_terms( $post_id, $reg_ids, 'trek_region' );
                }
            }

            // 3. Post Meta - Hero & Card
            // Resolve days from hero object, top-level, or fallback
            $hero_obj = is_array( $item['hero'] ?? null ) ? $item['hero'] : array();
            $card_obj = is_array( $item['card'] ?? null ) ? $item['card'] : array();

            $days = ! empty( $hero_obj['days'] ) ? $hero_obj['days'] : ( ! empty( $item['days'] ) ? $item['days'] : ( ! empty( $item['duration'] ) ? $item['duration'] : ( ! empty( $item['hero_days'] ) ? $item['hero_days'] : '' ) ) );
            $days_clean = ! empty( $days ) ? ( is_numeric( $days ) ? $days . ' Days' : sanitize_text_field( $days ) ) : '';
            if ( ! empty( $days_clean ) ) {
                update_post_meta( $post_id, '_trek_hero_days', $days_clean );
            }

            // Hero sub-fields from hero{} object
            if ( ! empty( $hero_obj['script_title'] ) || ! empty( $item['script_title'] ) ) {
                update_post_meta( $post_id, '_trek_script_title', sanitize_text_field( $hero_obj['script_title'] ?? $item['script_title'] ) );
            }
            if ( ! empty( $hero_obj['title'] ) || ! empty( $item['hero_title'] ) ) {
                update_post_meta( $post_id, '_trek_hero_title', sanitize_text_field( $hero_obj['title'] ?? $item['hero_title'] ) );
            }
            if ( ! empty( $hero_obj['kicker'] ) || ! empty( $item['hero_kicker'] ) ) {
                update_post_meta( $post_id, '_trek_hero_kicker', sanitize_text_field( $hero_obj['kicker'] ?? $item['hero_kicker'] ) );
            }
            if ( ! empty( $hero_obj['subtitle'] ) || ! empty( $item['hero_subtitle'] ) || ! empty( $item['subtitle'] ) ) {
                update_post_meta( $post_id, '_trek_hero_subtitle', sanitize_textarea_field( $hero_obj['subtitle'] ?? $item['hero_subtitle'] ?? $item['subtitle'] ) );
            }
            if ( ! empty( $hero_obj['badge_sub'] ) || ! empty( $item['hero_badge_sub'] ) ) {
                update_post_meta( $post_id, '_trek_hero_badge_sub', sanitize_text_field( $hero_obj['badge_sub'] ?? $item['hero_badge_sub'] ) );
            }

            // Hero slider gallery images
            $slider_imgs_raw = $hero_obj['slider_images'] ?? ( $item['hero_slider_images'] ?? array() );
            if ( is_string( $slider_imgs_raw ) ) {
                $slider_imgs_raw = preg_split( '/\s*,\s*/', trim( $slider_imgs_raw ), -1, PREG_SPLIT_NO_EMPTY );
            }
            if ( is_array( $slider_imgs_raw ) ) {
                $slider_imgs_clean = array();
                foreach ( $slider_imgs_raw as $slider_url ) {
                    $slider_url = self::safe_url( $slider_url );
                    if ( '' !== $slider_url ) {
                        $slider_imgs_clean[] = $slider_url;
                    }
                }
                $slider_imgs_clean = array_values( array_unique( $slider_imgs_clean ) );
                if ( ! empty( $slider_imgs_clean ) ) {
                    update_post_meta( $post_id, '_trek_hero_slider_images', $slider_imgs_clean );
                } else {
                    delete_post_meta( $post_id, '_trek_hero_slider_images' );
                }
            }

            // Hero background image: from hero{} or top-level
            $hero_img_url = ! empty( $hero_obj['image'] ) ? $hero_obj['image'] : ( ! empty( $item['hero_image'] ) ? $item['hero_image'] : ( ! empty( $item['bg_image'] ) ? $item['bg_image'] : '' ) );
            if ( ! empty( $hero_img_url ) ) {
                update_post_meta( $post_id, '_trek_hero_bg_img', self::safe_url( $hero_img_url ) );
            }

            // Card image: from card{} or top-level
            $card_img_url = ! empty( $card_obj['image'] ) ? $card_obj['image'] : ( ! empty( $item['card_image'] ) ? $item['card_image'] : ( ! empty( $item['image'] ) ? $item['image'] : ( ! empty( $item['thumbnail'] ) ? $item['thumbnail'] : '' ) ) );
            if ( ! empty( $card_img_url ) ) {
                update_post_meta( $post_id, '_trek_card_image_url', self::safe_url( $card_img_url ) );
            }

            // Card sub-fields from card{} object
            $card_title = ! empty( $card_obj['title'] ) ? $card_obj['title'] : ( ! empty( $item['card_title'] ) ? $item['card_title'] : '' );
            if ( ! empty( $card_title ) ) {
                update_post_meta( $post_id, '_trek_card_title', sanitize_text_field( $card_title ) );
            }
            $card_meta = ! empty( $card_obj['meta'] ) ? $card_obj['meta'] : $days_clean;
            if ( ! empty( $card_meta ) ) {
                update_post_meta( $post_id, '_trek_card_meta', sanitize_text_field( $card_meta ) );
            }
            if ( ! empty( $card_obj['kicker'] ) || ! empty( $item['card_kicker'] ) ) {
                update_post_meta( $post_id, '_trek_card_kicker', sanitize_text_field( $card_obj['kicker'] ?? $item['card_kicker'] ) );
            }
            if ( isset( $card_obj['order'] ) || isset( $item['card_order'] ) ) {
                update_post_meta( $post_id, '_trek_card_order', intval( $card_obj['order'] ?? $item['card_order'] ) );
            }

            if ( ! empty( $item['tag'] ) || ! empty( $item['tag_label'] ) || ! empty( $item['card_tag'] ) ) {
                $tag_val = sanitize_text_field( ! empty( $item['tag'] ) ? $item['tag'] : ( ! empty( $item['tag_label'] ) ? $item['tag_label'] : $item['card_tag'] ) );
                update_post_meta( $post_id, '_trek_card_tag', $tag_val );
                update_post_meta( $post_id, '_trek_tag_label', $tag_val );
            }

            if ( ! empty( $item['primary_category'] ) ) {
                update_post_meta( $post_id, '_trek_primary_category', sanitize_text_field( $item['primary_category'] ) );
            }

            if ( ! empty( $item['map_region'] ) ) {
                update_post_meta( $post_id, '_trek_map_region', sanitize_text_field( $item['map_region'] ) );
            }
            if ( ! empty( $item['map_district'] ) ) {
                update_post_meta( $post_id, '_trek_map_district', sanitize_text_field( $item['map_district'] ) );
            }

            if ( ! empty( $item['region'] ) || ! empty( $item['country'] ) ) {
                $country_str = is_array( $item['region'] ?? $item['country'] ) ? implode( ', ', $item['region'] ?? $item['country'] ) : ( $item['region'] ?? $item['country'] );
                update_post_meta( $post_id, '_trek_breadcrumb_region', sanitize_text_field( $country_str ) );
            }

            if ( isset( $item['featured'] ) || isset( $item['is_featured'] ) ) {
                $is_feat = ( ! empty( $item['featured'] ) || ! empty( $item['is_featured'] ) ) ? '1' : '0';
                update_post_meta( $post_id, '_trek_is_featured', $is_feat );
            }

            // Difficulty & Taxonomy Term
            $difficulty = ! empty( $item['difficulty'] ) ? sanitize_text_field( $item['difficulty'] ) : ( ! empty( $item['quick_facts']['difficulty'] ) ? sanitize_text_field( $item['quick_facts']['difficulty'] ) : '' );
            if ( ! empty( $difficulty ) ) {
                update_post_meta( $post_id, '_trek_difficulty', $difficulty );
                $diff_term_id = self::get_or_create_term_id( $difficulty, 'trek_difficulty', $create_terms );
                if ( $diff_term_id ) {
                    wp_set_object_terms( $post_id, array( $diff_term_id ), 'trek_difficulty' );
                } else {
                    wp_set_object_terms( $post_id, array( $difficulty ), 'trek_difficulty' );
                }
            }

            // Pricing & Currency
            $price = ! empty( $item['price'] ) ? sanitize_text_field( $item['price'] ) : ( ! empty( $item['quick_facts']['price'] ) ? sanitize_text_field( $item['quick_facts']['price'] ) : '' );
            if ( ! empty( $price ) ) {
                update_post_meta( $post_id, '_trek_price', $price );
            }
            $currency = ! empty( $item['currency'] ) ? sanitize_text_field( $item['currency'] ) : ( ! empty( $item['quick_facts']['currency'] ) ? sanitize_text_field( $item['quick_facts']['currency'] ) : 'USD' );
            update_post_meta( $post_id, '_trek_currency', $currency );

            // 4. Quick Facts — full mapping including new fields (low_point, start, end, distance, status)
            $qf      = is_array( $item['quick_facts'] ?? null ) ? $item['quick_facts'] : array();
            $high_pt = sanitize_text_field( $qf['high_point'] ?? $qf['max_altitude'] ?? $qf['highest_point'] ?? '' );
            $low_pt  = sanitize_text_field( $qf['low_point']  ?? $qf['lowest_point']  ?? '' );
            $dest    = sanitize_text_field( $qf['region']      ?? $qf['destination']   ?? $qf['location'] ?? '' );
            $walk    = sanitize_text_field( $qf['trekking']    ?? $qf['daily_walking'] ?? $qf['walking_hours'] ?? '' );
            $accom   = sanitize_text_field( $qf['accom']       ?? $qf['accommodation'] ?? $qf['stay'] ?? '' );
            $style   = sanitize_text_field( $qf['style']       ?? $qf['travel_style']  ?? '' );
            $season  = sanitize_text_field( $qf['season']      ?? $qf['best_season']   ?? '' );
            $group   = sanitize_text_field( $qf['group_size']  ?? $qf['groupSize']     ?? '' );
            $dist    = sanitize_text_field( $qf['distance']    ?? '' );
            $start   = sanitize_text_field( $qf['start']       ?? $qf['starting_point'] ?? '' );
            $end     = sanitize_text_field( $qf['end']         ?? $qf['ending_point']   ?? '' );
            $qf_stat = sanitize_text_field( $qf['status']      ?? $item['status'] ?? 'open' );

            $facts = array(
                'high_point'    => $high_pt,
                'max_altitude'  => $high_pt,
                'low_point'     => $low_pt,
                'region'        => $dest,
                'destination'   => $dest,
                'duration'      => $days_clean,
                'trekking'      => $walk,
                'daily_walking' => $walk,
                'accom'         => $accom,
                'accommodation' => $accom,
                'style'         => $style,
                'season'        => $season,
                'group_size'    => $group,
                'distance'      => $dist,
                'start'         => $start,
                'end'           => $end,
                'status'        => $qf_stat,
                'difficulty'    => $difficulty,
                'price'         => $price,
                'currency'      => $currency,
            );
            update_post_meta( $post_id, '_trek_quick_facts', $facts );

            if ( ! empty( $high_pt ) ) update_post_meta( $post_id, '_trek_fact_high_point', $high_pt );
            if ( ! empty( $low_pt ) )  update_post_meta( $post_id, '_trek_fact_low_point',  $low_pt );
            if ( ! empty( $dest ) )    update_post_meta( $post_id, '_trek_fact_region',      $dest );
            if ( ! empty( $walk ) )    update_post_meta( $post_id, '_trek_fact_trekking',    $walk );
            if ( ! empty( $accom ) )   update_post_meta( $post_id, '_trek_fact_accom',       $accom );
            if ( ! empty( $style ) )   update_post_meta( $post_id, '_trek_fact_style',       $style );
            if ( ! empty( $season ) )  update_post_meta( $post_id, '_trek_fact_season',      $season );
            if ( ! empty( $group ) )   update_post_meta( $post_id, '_trek_fact_group_size',  $group );
            if ( ! empty( $dist ) )    update_post_meta( $post_id, '_trek_fact_distance',    $dist );
            if ( ! empty( $start ) )   update_post_meta( $post_id, '_trek_fact_start',       $start );
            if ( ! empty( $end ) )     update_post_meta( $post_id, '_trek_fact_end',         $end );
            if ( ! empty( $qf_stat ) ) update_post_meta( $post_id, '_trek_trip_status',      $qf_stat );

            // 5. Highlights Array
            if ( ! empty( $item['highlights'] ) && is_array( $item['highlights'] ) ) {
                $clean_hl = array_map( 'sanitize_text_field', $item['highlights'] );
                update_post_meta( $post_id, '_trek_highlights', $clean_hl );
            }

            // 5b. Overview & Map Section Meta
            $overview_sec = is_array( $item['overview'] ?? null ) ? $item['overview'] : ( $item['overview_section'] ?? array() );
            if ( ! empty( $item['overview_kicker'] ) || ! empty( $overview_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_overview_kicker', sanitize_text_field( $item['overview_kicker'] ?? $overview_sec['kicker'] ) );
            }
            if ( ! empty( $item['overview_script'] ) || ! empty( $overview_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_overview_script', sanitize_text_field( $item['overview_script'] ?? $overview_sec['script'] ) );
            }
            if ( ! empty( $item['overview_heading'] ) || ! empty( $overview_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_overview_heading', sanitize_text_field( $item['overview_heading'] ?? $overview_sec['heading'] ) );
            }
            if ( ! empty( $item['overview_subheading'] ) || ! empty( $overview_sec['subheading'] ) ) {
                update_post_meta( $post_id, '_trek_overview_subheading', sanitize_text_field( $item['overview_subheading'] ?? $overview_sec['subheading'] ) );
            }
            if ( ! empty( $item['overview_highlight'] ) || ! empty( $overview_sec['highlight'] ) ) {
                update_post_meta( $post_id, '_trek_overview_highlight', wp_kses_post( $item['overview_highlight'] ?? $overview_sec['highlight'] ) );
            }
            if ( ! empty( $item['overview_img'] ) || ! empty( $overview_sec['image'] ) ) {
                update_post_meta( $post_id, '_trek_overview_img', self::safe_url( $item['overview_img'] ?? $overview_sec['image'] ) );
            }
            if ( ! empty( $item['route_img'] ) || ! empty( $overview_sec['route_img'] ) ) {
                update_post_meta( $post_id, '_trek_route_img', self::safe_url( $item['route_img'] ?? $overview_sec['route_img'] ) );
            }
            if ( ! empty( $item['map_file_url'] ) || ! empty( $overview_sec['map_file_url'] ) ) {
                update_post_meta( $post_id, '_trek_map_file_url', self::safe_url( $item['map_file_url'] ?? $overview_sec['map_file_url'] ) );
            }
            if ( ! empty( $item['map_embed_code'] ) || ! empty( $overview_sec['map_embed_code'] ) ) {
                update_post_meta( $post_id, '_trek_map_embed_code', current_user_can( 'unfiltered_html' ) ? ( $item['map_embed_code'] ?? $overview_sec['map_embed_code'] ) : wp_kses_post( $item['map_embed_code'] ?? $overview_sec['map_embed_code'] ) );
            }
            if ( ! empty( $item['route_caption_sub'] ) || ! empty( $overview_sec['route_sub'] ) ) {
                update_post_meta( $post_id, '_trek_route_caption_sub', sanitize_text_field( $item['route_caption_sub'] ?? $overview_sec['route_sub'] ) );
            }
            if ( ! empty( $item['route_caption_h3'] ) || ! empty( $overview_sec['route_heading'] ) ) {
                update_post_meta( $post_id, '_trek_route_caption_h3', sanitize_text_field( $item['route_caption_h3'] ?? $overview_sec['route_heading'] ) );
            }

            // 6. Itinerary Section Headers & Day-by-Day Itinerary Array (Sync dual keys: _trek_itinerary & _trek_itinerary_list)
            $itin_sec = $item['itinerary_section'] ?? array();
            if ( ! empty( $item['itin_kicker'] ) || ! empty( $itin_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_itin_kicker', sanitize_text_field( $item['itin_kicker'] ?? $itin_sec['kicker'] ) );
            }
            if ( ! empty( $item['itin_script'] ) || ! empty( $itin_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_itin_script', sanitize_text_field( $item['itin_script'] ?? $itin_sec['script'] ) );
            }
            if ( ! empty( $item['itin_heading'] ) || ! empty( $itin_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_itin_heading', sanitize_text_field( $item['itin_heading'] ?? $itin_sec['heading'] ) );
            }
            if ( ! empty( $item['itin_subtext'] ) || ! empty( $itin_sec['subtext'] ) ) {
                update_post_meta( $post_id, '_trek_itin_subtext', sanitize_text_field( $item['itin_subtext'] ?? $itin_sec['subtext'] ) );
            }

            if ( ! empty( $item['itinerary'] ) && is_array( $item['itinerary'] ) ) {
                $clean_itin = array();
                $clean_itin_list = array();
                $idx = 1;
                foreach ( $item['itinerary'] as $day_data ) {
                    if ( ! is_array( $day_data ) ) continue;
                    $d_num   = sanitize_text_field( $day_data['day'] ?? sprintf( '%02d', $idx ) );
                    $d_title = sanitize_text_field( $day_data['title'] ?? '' );
                    $d_stage = sanitize_text_field( $day_data['stage'] ?? $d_title );
                    $d_desc  = wp_kses_post( $day_data['description'] ?? $day_data['desc'] ?? '' );
                    $d_pills = sanitize_text_field( $day_data['pills'] ?? ( ! empty( $day_data['altitude'] ) ? $day_data['altitude'] : '' ) );
                    $d_gal   = $day_data['gallery'] ?? ( $day_data['images'] ?? array() );
                    if ( is_string( $d_gal ) ) {
                        $d_gal = array_values( array_filter( array_map( 'esc_url_raw', array_map( 'trim', explode( ',', $d_gal ) ) ) ) );
                    } elseif ( ! is_array( $d_gal ) ) {
                        $d_gal = array();
                    }

                    $clean_itin[] = array(
                        'day'           => $d_num,
                        'title'         => $d_title,
                        'description'   => $d_desc,
                        'altitude'      => sanitize_text_field( $day_data['altitude'] ?? '' ),
                        'walking_hours' => sanitize_text_field( $day_data['walking_hours'] ?? '' ),
                        'stay'          => sanitize_text_field( $day_data['stay'] ?? $day_data['accommodation'] ?? '' ),
                        'meals'         => sanitize_text_field( $day_data['meals'] ?? '' ),
                        'gallery'       => $d_gal,
                    );

                    $clean_itin_list[] = array(
                        'day'     => $d_num,
                        'stage'   => $d_stage,
                        'title'   => $d_title,
                        'pills'   => $d_pills,
                        'desc'    => $d_desc,
                        'gallery' => $d_gal,
                    );
                    $idx++;
                }
                update_post_meta( $post_id, '_trek_itinerary', $clean_itin );
                update_post_meta( $post_id, '_trek_itinerary_list', $clean_itin_list );
            }

            // 7. Inclusions & Exclusions (Sync dual keys: _trek_inclusions & _trek_inclusions_full)
            $inex_sec = $item['inex'] ?? ( $item['inclusions_section'] ?? array() );
            if ( ! empty( $item['inex_kicker'] ) || ! empty( $inex_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_inex_kicker', sanitize_text_field( $item['inex_kicker'] ?? $inex_sec['kicker'] ) );
            }
            if ( ! empty( $item['inex_script'] ) || ! empty( $inex_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_inex_script', sanitize_text_field( $item['inex_script'] ?? $inex_sec['script'] ) );
            }
            if ( ! empty( $item['inex_heading'] ) || ! empty( $inex_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_inex_heading', sanitize_text_field( $item['inex_heading'] ?? $inex_sec['heading'] ) );
            }
            if ( ! empty( $item['inex_subtext'] ) || ! empty( $inex_sec['subtext'] ) ) {
                update_post_meta( $post_id, '_trek_inex_subtext', sanitize_text_field( $item['inex_subtext'] ?? $inex_sec['subtext'] ) );
            }

            $raw_inclusions = $item['inclusions'] ?? ( $inex_sec['inclusions'] ?? null );
            if ( ! empty( $raw_inclusions ) && is_array( $raw_inclusions ) ) {
                $clean_inc_full = array();
                foreach ( $raw_inclusions as $inc_item ) {
                    if ( is_array( $inc_item ) ) {
                        $clean_inc_full[] = array(
                            'title' => sanitize_text_field( $inc_item['title'] ?? ( $inc_item['heading'] ?? '' ) ),
                            'desc'  => wp_kses_post( $inc_item['desc'] ?? ( $inc_item['description'] ?? ( $inc_item['content'] ?? '' ) ) ),
                        );
                    } else {
                        $clean_inc_full[] = array(
                            'title' => sanitize_text_field( (string) $inc_item ),
                            'desc'  => '',
                        );
                    }
                }
                update_post_meta( $post_id, '_trek_inclusions_full', $clean_inc_full );
                update_post_meta( $post_id, '_trek_inclusions', $clean_inc_full );
            }

            $raw_exclusions = $item['exclusions'] ?? ( $inex_sec['exclusions'] ?? null );
            if ( ! empty( $raw_exclusions ) && is_array( $raw_exclusions ) ) {
                $clean_exc_full = array();
                foreach ( $raw_exclusions as $exc_item ) {
                    if ( is_array( $exc_item ) ) {
                        $clean_exc_full[] = array(
                            'title' => sanitize_text_field( $exc_item['title'] ?? ( $exc_item['heading'] ?? '' ) ),
                            'desc'  => wp_kses_post( $exc_item['desc'] ?? ( $exc_item['description'] ?? ( $exc_item['content'] ?? '' ) ) ),
                        );
                    } else {
                        $clean_exc_full[] = array(
                            'title' => sanitize_text_field( (string) $exc_item ),
                            'desc'  => '',
                        );
                    }
                }
                update_post_meta( $post_id, '_trek_exclusions_full', $clean_exc_full );
                update_post_meta( $post_id, '_trek_exclusions', $clean_exc_full );
            }

            // 8. Gallery Section & Photos (Sync dual keys: _trek_gallery_images & _trek_gallery_list)
            $gal_sec = $item['gallery_section'] ?? ( ( isset( $item['gallery'] ) && ! empty( $item['gallery']['images'] ) ) ? $item['gallery'] : array() );
            if ( ! empty( $item['gallery_kicker'] ) || ! empty( $gal_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_gallery_kicker', sanitize_text_field( $item['gallery_kicker'] ?? $gal_sec['kicker'] ) );
            }
            if ( ! empty( $item['gallery_script'] ) || ! empty( $gal_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_gallery_script', sanitize_text_field( $item['gallery_script'] ?? $gal_sec['script'] ) );
            }
            if ( ! empty( $item['gallery_heading'] ) || ! empty( $gal_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_gallery_heading', sanitize_text_field( $item['gallery_heading'] ?? $gal_sec['heading'] ) );
            }
            if ( ! empty( $item['gallery_subtext'] ) || ! empty( $gal_sec['subtext'] ) ) {
                update_post_meta( $post_id, '_trek_gallery_subtext', sanitize_text_field( $item['gallery_subtext'] ?? $gal_sec['subtext'] ) );
            }

            $raw_gallery = isset( $item['gallery'] ) && is_array( $item['gallery'] ) && ! isset( $item['gallery']['images'] ) ? $item['gallery'] : ( $gal_sec['images'] ?? ( $item['gallery_images'] ?? array() ) );
            if ( ! empty( $raw_gallery ) && is_array( $raw_gallery ) ) {
                $clean_gal = array();
                $clean_gal_list = array();
                foreach ( $raw_gallery as $g_item ) {
                    $url = is_array( $g_item ) ? ( $g_item['url'] ?? '' ) : $g_item;
                    $lbl = is_array( $g_item ) ? ( $g_item['label'] ?? $g_item['caption'] ?? '' ) : '';
                    $url = self::safe_url( $url );
                    if ( '' !== $url ) {
                        $clean_gal[] = $url;
                        $clean_gal_list[] = array(
                            'url'   => $url,
                            'label' => self::scalar_text( $lbl ),
                        );
                    }
                }
                update_post_meta( $post_id, '_trek_gallery_images', $clean_gal );
                update_post_meta( $post_id, '_trek_gallery_list', $clean_gal_list );
            }

            // 9. Essential Information & FAQs (Sync dual keys: _trek_faqs & _trek_essential_list)
            $ess_sec = $item['essential'] ?? array();
            if ( ! empty( $item['ess_kicker'] ) || ! empty( $ess_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_ess_kicker', sanitize_text_field( $item['ess_kicker'] ?? $ess_sec['kicker'] ) );
            }
            if ( ! empty( $item['ess_script'] ) || ! empty( $ess_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_ess_script', sanitize_text_field( $item['ess_script'] ?? $ess_sec['script'] ) );
            }
            if ( ! empty( $item['ess_heading'] ) || ! empty( $ess_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_ess_heading', sanitize_text_field( $item['ess_heading'] ?? $ess_sec['heading'] ) );
            }
            if ( ! empty( $item['ess_intro'] ) || ! empty( $ess_sec['intro'] ) ) {
                update_post_meta( $post_id, '_trek_ess_intro', wp_kses_post( $item['ess_intro'] ?? $ess_sec['intro'] ) );
            }
            if ( ! empty( $item['ess_card_img'] ) || ! empty( $ess_sec['card_img'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_img', self::safe_url( $item['ess_card_img'] ?? $ess_sec['card_img'] ) );
            }
            if ( ! empty( $item['ess_card_kicker'] ) || ! empty( $ess_sec['card_kicker'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_kicker', sanitize_text_field( $item['ess_card_kicker'] ?? $ess_sec['card_kicker'] ) );
            }
            if ( ! empty( $item['ess_card_title'] ) || ! empty( $ess_sec['card_title'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_title', sanitize_text_field( $item['ess_card_title'] ?? $ess_sec['card_title'] ) );
            }
            if ( ! empty( $item['ess_card_desc'] ) || ! empty( $ess_sec['card_desc'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_desc', sanitize_text_field( $item['ess_card_desc'] ?? $ess_sec['card_desc'] ) );
            }
            if ( ! empty( $item['ess_card_btn'] ) || ! empty( $ess_sec['card_btn'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_btn', sanitize_text_field( $item['ess_card_btn'] ?? $ess_sec['card_btn'] ) );
            }
            if ( ! empty( $item['ess_card_email'] ) || ! empty( $ess_sec['card_email'] ) ) {
                update_post_meta( $post_id, '_trek_ess_card_email', sanitize_email( $item['ess_card_email'] ?? $ess_sec['card_email'] ) );
            }

            $raw_faqs = $item['faqs'] ?? ( $item['essential_list'] ?? null );
            if ( ! empty( $raw_faqs ) && is_array( $raw_faqs ) ) {
                $clean_faqs = array();
                $clean_ess = array();
                foreach ( $raw_faqs as $faq ) {
                    if ( is_array( $faq ) ) {
                        $q = sanitize_text_field( $faq['question'] ?? ( $faq['title'] ?? '' ) );
                        $a = wp_kses_post( $faq['answer'] ?? ( $faq['content'] ?? '' ) );
                        if ( ! empty( $q ) ) {
                            $clean_faqs[] = array( 'question' => $q, 'answer' => $a );
                            $clean_ess[]  = array( 'title' => $q, 'content' => $a );
                        }
                    }
                }
                update_post_meta( $post_id, '_trek_faqs', $clean_faqs );
                update_post_meta( $post_id, '_trek_essential_list', $clean_ess );
            }

            // 10. Reviews Section & Curated Testimonials (Sync dual keys: _trek_curated_reviews & _trek_reviews_list)
            $rev_sec = $item['reviews_section'] ?? array();
            if ( ! empty( $item['review_kicker'] ) || ! empty( $rev_sec['kicker'] ) ) {
                update_post_meta( $post_id, '_trek_review_kicker', sanitize_text_field( $item['review_kicker'] ?? $rev_sec['kicker'] ) );
            }
            if ( ! empty( $item['review_script'] ) || ! empty( $rev_sec['script'] ) ) {
                update_post_meta( $post_id, '_trek_review_script', sanitize_text_field( $item['review_script'] ?? $rev_sec['script'] ) );
            }
            if ( ! empty( $item['review_heading'] ) || ! empty( $rev_sec['heading'] ) ) {
                update_post_meta( $post_id, '_trek_review_heading', sanitize_text_field( $item['review_heading'] ?? $rev_sec['heading'] ) );
            }
            if ( ! empty( $item['review_subtext'] ) || ! empty( $rev_sec['subtext'] ) ) {
                update_post_meta( $post_id, '_trek_review_subtext', sanitize_text_field( $item['review_subtext'] ?? $rev_sec['subtext'] ) );
            }

            $raw_revs = isset( $item['reviews'] ) && is_array( $item['reviews'] ) && ! isset( $item['reviews']['reviews'] ) ? $item['reviews'] : ( $rev_sec['reviews'] ?? ( $item['curated_reviews'] ?? array() ) );
            if ( ! empty( $raw_revs ) && is_array( $raw_revs ) ) {
                $clean_revs = array();
                foreach ( $raw_revs as $rev ) {
                    if ( is_array( $rev ) ) {
                        $clean_revs[] = array(
                            'name'     => sanitize_text_field( $rev['name'] ?? 'Traveler' ),
                            'location' => sanitize_text_field( $rev['location'] ?? $rev['country'] ?? '' ),
                            'rating'   => sanitize_text_field( $rev['rating'] ?? '5' ),
                            'quote'    => sanitize_textarea_field( $rev['quote'] ?? $rev['review'] ?? $rev['content'] ?? '' ),
                        );
                    }
                }
                if ( ! empty( $clean_revs ) ) {
                    update_post_meta( $post_id, '_trek_curated_reviews', $clean_revs );
                    update_post_meta( $post_id, '_trek_reviews_list', $clean_revs );
                }
            }

            // 11. Fixed Departures & Contact Settings
            $dep_sec = $item['contact'] ?? ( $item['departures'] ?? array() );
            if ( ! empty( $item['dep_kicker'] ) || ! empty( $dep_sec['dep_kicker'] ) ) {
                update_post_meta( $post_id, '_trek_dep_kicker', sanitize_text_field( $item['dep_kicker'] ?? $dep_sec['dep_kicker'] ) );
            }
            if ( ! empty( $item['dep_script'] ) || ! empty( $dep_sec['dep_script'] ) ) {
                update_post_meta( $post_id, '_trek_dep_script', sanitize_text_field( $item['dep_script'] ?? $dep_sec['dep_script'] ) );
            }
            if ( ! empty( $item['dep_heading'] ) || ! empty( $dep_sec['dep_heading'] ) ) {
                update_post_meta( $post_id, '_trek_dep_heading', sanitize_text_field( $item['dep_heading'] ?? $dep_sec['dep_heading'] ) );
            }
            if ( ! empty( $item['dep_subtext'] ) || ! empty( $dep_sec['dep_subtext'] ) ) {
                update_post_meta( $post_id, '_trek_dep_subtext', sanitize_text_field( $item['dep_subtext'] ?? $dep_sec['dep_subtext'] ) );
            }

            if ( ! empty( $item['dep_box_script'] ) || ! empty( $dep_sec['box_script'] ) ) {
                update_post_meta( $post_id, '_trek_dep_box_script', sanitize_text_field( $item['dep_box_script'] ?? $dep_sec['box_script'] ) );
            }
            if ( ! empty( $item['dep_box_title'] ) || ! empty( $dep_sec['box_title'] ) ) {
                update_post_meta( $post_id, '_trek_dep_box_title', sanitize_text_field( $item['dep_box_title'] ?? $dep_sec['box_title'] ) );
            }
            if ( ! empty( $item['dep_box_desc'] ) || ! empty( $dep_sec['box_desc'] ) ) {
                update_post_meta( $post_id, '_trek_dep_box_desc', sanitize_text_field( $item['dep_box_desc'] ?? $dep_sec['box_desc'] ) );
            }
            if ( ! empty( $item['dep_box_photo'] ) || ! empty( $dep_sec['box_photo'] ) ) {
                update_post_meta( $post_id, '_trek_dep_box_photo', self::safe_url( $item['dep_box_photo'] ?? $dep_sec['box_photo'] ) );
            }
            if ( ! empty( $item['dep_box_note'] ) || ! empty( $dep_sec['box_note'] ) ) {
                update_post_meta( $post_id, '_trek_dep_box_note', sanitize_text_field( $item['dep_box_note'] ?? $dep_sec['box_note'] ) );
            }

            if ( ! empty( $item['whatsapp'] ) || ! empty( $item['whatsapp_number'] ) || ! empty( $dep_sec['whatsapp'] ) ) {
                update_post_meta( $post_id, '_trek_whatsapp_number', sanitize_text_field( $item['whatsapp'] ?? ( $item['whatsapp_number'] ?? $dep_sec['whatsapp'] ) ) );
            }
            if ( ! empty( $item['sales_email'] ) || ! empty( $dep_sec['email'] ) ) {
                update_post_meta( $post_id, '_trek_sales_email', sanitize_email( $item['sales_email'] ?? $dep_sec['email'] ) );
            }

            // 11b. Fixed Departure Dates Array
            $raw_deps = $item['departures'] ?? ( $dep_sec['dates'] ?? array() );
            if ( ! empty( $raw_deps ) && is_array( $raw_deps ) ) {
                $clean_deps = array();
                foreach ( $raw_deps as $dep ) {
                    if ( ! is_array( $dep ) ) continue;
                    $clean_deps[] = array(
                        'date'     => sanitize_text_field( $dep['date'] ?? '' ),
                        'price'    => sanitize_text_field( $dep['price'] ?? $price ),
                        'currency' => sanitize_text_field( $dep['currency'] ?? $currency ),
                        'seats'    => intval( $dep['seats'] ?? 0 ),
                        'status'   => sanitize_text_field( $dep['status'] ?? 'open' ),
                        'note'     => sanitize_text_field( $dep['note'] ?? '' ),
                    );
                }
                if ( ! empty( $clean_deps ) ) {
                    update_post_meta( $post_id, '_trek_dep_dates', $clean_deps );
                    update_post_meta( $post_id, '_trek_departure_dates', $clean_deps );
                }
            }

            // 11c. Pricing block fallback (_trek_price / _trek_currency from pricing{})
            $pricing_obj = is_array( $item['pricing'] ?? null ) ? $item['pricing'] : array();
            if ( empty( $price ) && ! empty( $pricing_obj['price'] ) ) {
                update_post_meta( $post_id, '_trek_price', sanitize_text_field( $pricing_obj['price'] ) );
            }
            if ( ! empty( $pricing_obj['currency'] ) ) {
                update_post_meta( $post_id, '_trek_currency', sanitize_text_field( $pricing_obj['currency'] ) );
            }

            // 12. Compile & Save Master JSON Payload (_trek_structured_data)
            if ( class_exists( 'Trek_Renderer' ) ) {
                $full_data = Trek_Renderer::get_full_trek_data( $post_id );
                if ( ! empty( $full_data ) ) {
                    update_post_meta( $post_id, '_trek_structured_data', wp_slash( wp_json_encode( $full_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ) );
                }
            }
        }

        wp_safe_redirect( add_query_arg( array(
            'page'          => 'trek-json-importer',
            'import_status' => 'success',
            'count'         => $created_count,
            'updated'       => $updated_count,
        ), admin_url( 'edit.php?post_type=trek_destination' ) ) );
        exit;
    }

    /**
     * Get Complete Rich Sample JSON — loads the canonical template asset file.
     * Falls back to a minimal inline sample if the file is not found.
     */
    public static function get_sample_json_string() {
        $template_path = plugin_dir_path( dirname( __FILE__ ) ) . 'assets/canonical-trek-template.json';
        if ( file_exists( $template_path ) ) {
            $contents = file_get_contents( $template_path );
            // Re-encode with consistent formatting (removes the _comment key for clean output)
            $decoded = json_decode( $contents, true );
            if ( is_array( $decoded ) ) {
                foreach ( $decoded as &$item ) {
                    unset( $item['_comment'] );
                }
                unset( $item );
                return json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
            }
            return $contents;
        }
        // Inline minimal fallback
        $sample = array(
            array(
                "title"    => "The Ultimate Everest Base Camp Experience",
                "slug"     => "everest-base-camp-experience",
                "excerpt"  => "Journey through legendary Sherpa homelands to the foot of the world's highest mountain over 15 spectacular days.",
                "category" => array( "Trekking", "Himalayan", "Signature Journey" ),
                "region"   => array( "Nepal" ),
                "difficulty" => "Strenuous",
                "featured"   => true,
                "status"     => "open",
                "hero" => array(
                    "script_title" => "The Ultimate Trek",
                    "title"        => "Everest Base Camp Experience",
                    "kicker"       => "Khumbu · Nepal",
                    "subtitle"     => "A majestic 15-day high-altitude journey through legendary Sherpa homelands to the foot of Mount Everest.",
                    "days"         => 15,
                    "badge_sub"    => "Days in Nepal",
                    "image"        => "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1600&q=85",
                    "slider_images" => array(
                        "https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1600&q=85",
                        "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1600&q=85"
                    )
                ),
                "card" => array(
                    "title"   => "Everest Base Camp Experience",
                    "meta"    => "15 Days",
                    "tag"     => "Nepal · Signature Journey",
                    "kicker"  => "Signature Journey",
                    "excerpt" => "Ascend through legendary Sherpa homelands to the foot of Mount Everest.",
                    "image"   => "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1600&q=85",
                    "order"   => 1
                ),
                "quick_facts" => array(
                    "high_point"  => "5,550m / 18,209ft · Kala Patthar",
                    "low_point"   => "1,400m / 4,593ft · Kathmandu",
                    "region"      => "Everest Region (Khumbu), Nepal",
                    "trekking"    => "Usually 5–7 hours daily",
                    "accom"       => "Boutique Kathmandu Hotels & Mountain Lodges",
                    "style"       => "Privately Guided High-Altitude Trek",
                    "season"      => "Autumn (Sep–Nov) & Spring (Mar–May)",
                    "group_size"  => "2–12 Travellers",
                    "distance"    => "130 km round-trip",
                    "start"       => "Kathmandu / Lukla (Flight)",
                    "end"         => "Lukla / Kathmandu (Flight)",
                    "status"      => "open",
                    "price"       => "2850",
                    "currency"    => "USD"
                ),
                "overview" => array(
                    "kicker"      => "Nepal · 15 Days",
                    "script"      => "The Ultimate Trek",
                    "heading"     => "To the Foot of Mount Everest",
                    "subheading"  => "Through the spiritual heartland of the Sherpa people",
                    "content"     => "<p>The Everest Base Camp trek is one of the world's great mountain journeys — ascending through Sherpa homelands, glacial valleys, and high passes to stand at 5,364m before the Khumbu Icefall.</p>",
                    "highlight"   => "<p>A demanding Himalayan journey of altitude, shifting landscapes, rich Sherpa culture, and unforgettable views of Everest, Lhotse, Ama Dablam, and Nuptse.</p>",
                    "highlights"  => array(
                        "Stand at Everest Base Camp (5,364m) before the Khumbu Icefall",
                        "Dawn summit of Kala Patthar (5,550m) for panoramic Everest views",
                        "Two acclimatisation nights in Namche Bazaar",
                        "Visit Tengboche Monastery with Ama Dablam backdrop"
                    ),
                    "route_sub"     => "Route Overview",
                    "route_heading" => "Kathmandu → Lukla → EBC → Kala Patthar → Kathmandu"
                ),
                "itinerary_section" => array(
                    "kicker"  => "Day by Day",
                    "script"  => "Your Journey",
                    "heading" => "15 Days Through the Khumbu Valley",
                    "subtext" => "Open each day to inspect the trail route, elevation, meals, accommodation, and walking times."
                ),
                "itinerary" => array(
                    array( "day" => "01", "stage" => "Kathmandu (1,400m)", "title" => "Arrive in Kathmandu & Hotel Transfer", "description" => "<p>Welcome to Nepal! Private transfer to your boutique heritage hotel. Pre-trek briefing and welcome dinner.</p>", "pills" => "Meals: Welcome Dinner · Boutique Hotel", "altitude" => "1,400m", "walking_hours" => "Transfer only", "stay" => "Dwarika's Heritage Hotel", "meals" => "Dinner", "gallery" => array() ),
                    array( "day" => "02", "stage" => "Lukla (2,840m) → Phakding (2,610m)", "title" => "Scenic Mountain Flight to Lukla & Trek to Phakding", "description" => "<p>35-minute scenic flight to Tenzing-Hillary Airport. Meet your Sherpa crew and begin the gentle riverside trail to Phakding.</p>", "pills" => "Meals: B, L, D · Teahouse · Approx. 3–4 hrs", "altitude" => "2,610m", "walking_hours" => "3–4 hrs", "stay" => "Phakding Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "03", "stage" => "Phakding → Namche Bazaar (3,440m)", "title" => "Ascend to the Sherpa Capital of Namche Bazaar", "description" => "<p>Cross the Hillary Suspension Bridge and enter Sagarmatha National Park. Ascend to Namche Bazaar with first glimpses of Everest.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · Approx. 5–6 hrs", "altitude" => "3,440m", "walking_hours" => "5–6 hrs", "stay" => "Namche Lodge", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "04", "stage" => "Namche Bazaar — Acclimatisation", "title" => "Acclimatisation Day: Everest View Hotel Hike", "description" => "<p>Climb to the Everest View Hotel (3,880m) for panoramic views. Explore the Sherpa Culture Museum and weekend market.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · 3–4 hrs hike", "altitude" => "3,880m (hike high, sleep 3,440m)", "walking_hours" => "3–4 hrs", "stay" => "Namche Lodge", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "05", "stage" => "Namche → Tengboche (3,860m)", "title" => "Trail to Tengboche Monastery with Ama Dablam Views", "description" => "<p>Contour above the Imja Khola, descend to Phunki Tenga and climb through birch forest to Tengboche Monastery with its dramatic Ama Dablam backdrop.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · Approx. 5–6 hrs", "altitude" => "3,860m", "walking_hours" => "5–6 hrs", "stay" => "Tengboche Lodge", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "06", "stage" => "Tengboche → Dingboche (4,410m)", "title" => "Trek Through Pangboche to High-Altitude Dingboche", "description" => "<p>Visit the ancient Pangboche Monastery, cross the Imja Khola and ascend steeply into the broad hanging valley of Dingboche.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · Approx. 5–6 hrs", "altitude" => "4,410m", "walking_hours" => "5–6 hrs", "stay" => "Dingboche Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "07", "stage" => "Dingboche — Acclimatisation", "title" => "Acclimatisation Day: Nangkartshang Peak Hike", "description" => "<p>Hike up to Nangkartshang Peak (5,083m) for views of Island Peak, Makalu, and the Lhotse-Nuptse wall. Critical acclimatisation for Base Camp days ahead.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · 4–5 hrs hike", "altitude" => "5,083m (hike high, sleep 4,410m)", "walking_hours" => "4–5 hrs", "stay" => "Dingboche Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "08", "stage" => "Dingboche → Lobuche (4,940m)", "title" => "Trek to Lobuche Past Memorial Chortens", "description" => "<p>Cross the Khumbu moraine past the Chukpilhara climbers' memorial chortens to Lobuche. Air is noticeably thinner; stay hydrated.</p>", "pills" => "Meals: B, L, D · Teahouse · Approx. 4–5 hrs", "altitude" => "4,940m", "walking_hours" => "4–5 hrs", "stay" => "Lobuche Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "09", "stage" => "Lobuche → Gorak Shep (5,140m) → EBC (5,364m)", "title" => "The Day You've Trained For: Everest Base Camp!", "description" => "<p>Cross the Khumbu Glacier to Gorak Shep. Continue over the glacier to Everest Base Camp (5,364m) — standing at the foot of the Khumbu Icefall and the world's highest peak.</p>", "pills" => "Meals: B, L, D · Gorak Shep Teahouse · 7–8 hrs total", "altitude" => "5,364m (EBC)", "walking_hours" => "7–8 hrs", "stay" => "Gorak Shep Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "10", "stage" => "Gorak Shep → Kala Patthar (5,550m) → Pheriche (4,280m)", "title" => "Dawn Summit of Kala Patthar & Descent to Pheriche", "description" => "<p>Pre-dawn ascent by headtorch to Kala Patthar (5,550m) for the greatest Himalayan panorama: Everest, Lhotse, Nuptse, Pumori and Changtse at sunrise. Long descent to Pheriche.</p>", "pills" => "Meals: B, L, D · Village Teahouse · 8–9 hrs total", "altitude" => "5,550m (Kala Patthar summit)", "walking_hours" => "8–9 hrs", "stay" => "Pheriche Teahouse", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "11", "stage" => "Pheriche → Namche Bazaar (3,440m)", "title" => "Long Descent Back to Namche Bazaar", "description" => "<p>Retrace the trail through Pangboche and Tengboche, descending through birch forest back to Namche Bazaar. Celebrate with a sunset dinner at a Namche restaurant.</p>", "pills" => "Meals: B, L, D · Mountain Lodge · 7–8 hrs", "altitude" => "3,440m", "walking_hours" => "7–8 hrs", "stay" => "Namche Lodge", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "12", "stage" => "Namche → Phakding → Lukla (2,840m)", "title" => "Final Trail Day: Namche to Lukla", "description" => "<p>The final trekking day — efficient descent to Lukla for a traditional crew celebration dinner with guides, Sherpas, and porters.</p>", "pills" => "Meals: B, L, D · Lukla Lodge · 6–7 hrs", "altitude" => "2,840m", "walking_hours" => "6–7 hrs", "stay" => "Lukla Mountain Lodge", "meals" => "Breakfast, Lunch, Dinner", "gallery" => array() ),
                    array( "day" => "13", "stage" => "Lukla → Kathmandu (1,400m)", "title" => "Scenic Mountain Flight Back to Kathmandu", "description" => "<p>Return scenic mountain flight to Kathmandu. Transfer to hotel. Afternoon free for Thamel shopping and Kathmandu exploration.</p>", "pills" => "Meals: B · Boutique Hotel · Mountain Flight", "altitude" => "1,400m", "walking_hours" => "Flight and transfers", "stay" => "Dwarika's Heritage Hotel", "meals" => "Breakfast", "gallery" => array() ),
                    array( "day" => "14", "stage" => "Kathmandu — Free Day", "title" => "Free Day in Kathmandu: Heritage & Culture", "description" => "<p>Optional guided heritage tour to Bhaktapur Durbar Square, Boudhanath Stupa, and Swayambhunath (Monkey Temple). Complimentary post-trek health consultation available.</p>", "pills" => "Meals: B · Boutique Hotel · Free Day", "altitude" => "1,400m", "walking_hours" => "At leisure", "stay" => "Dwarika's Heritage Hotel", "meals" => "Breakfast", "gallery" => array() ),
                    array( "day" => "15", "stage" => "Kathmandu — Departure", "title" => "Farewell & International Departure", "description" => "<p>Final breakfast in Nepal before private transfer to Tribhuvan International Airport for your onward flight. Safe travels!</p>", "pills" => "Meals: B · Airport Transfer · Departure", "altitude" => "1,400m", "walking_hours" => "Transfer only", "stay" => "Airport Departure", "meals" => "Breakfast", "gallery" => array() )
                ),
                "inex" => array(
                    "kicker"  => "What is Covered",
                    "script"  => "Clear & Transparent",
                    "heading" => "Inclusions & Exclusions",
                    "subtext" => "Everything included in your bespoke Himalayan journey, with no hidden surprises."
                ),
                "inclusions" => array(
                    array( "title" => "All private airport and domestic transfers", "desc" => "<p>Private chauffeur transfers in air-conditioned vehicles between Kathmandu airport, hotels, and trailheads.</p>" ),
                    array( "title" => "Return scenic mountain flights (Kathmandu–Lukla–Kathmandu)", "desc" => "<p>Twin Otter scenic mountain flights with 15kg baggage allowance per traveller.</p>" ),
                    array( "title" => "All government permits, TIMS card & National Park entry fees", "desc" => "<p>Sagarmatha National Park entry, TIMS registration, and Khumbu rural municipality permits.</p>" ),
                    array( "title" => "Licensed English-speaking senior mountain guide", "desc" => "<p>Government-licensed high-altitude guide with wilderness first aid certification and pulse oximeter monitoring.</p>" ),
                    array( "title" => "Experienced Sherpa porters (1 per 2 trekkers, 12kg limit)", "desc" => "<p>Porter wages, insurance, and equipment fully covered per Sherpa welfare industry standards.</p>" ),
                    array( "title" => "Boutique hotel stays in Kathmandu", "desc" => "<p>4/5-star boutique heritage hotel, twin-share, daily breakfast included.</p>" ),
                    array( "title" => "Comfortable mountain teahouse lodges throughout the trek", "desc" => "<p>Pre-selected best-standard teahouses with three meals daily cooked fresh to order.</p>" )
                ),
                "exclusions" => array(
                    array( "title" => "International flights to and from Kathmandu", "desc" => "<p>Long-haul airfares and associated international baggage overages.</p>" ),
                    array( "title" => "Nepal tourist visa fees", "desc" => "<p>Payable on arrival at Kathmandu Airport in cash USD: USD 30 (15 days), USD 50 (30 days).</p>" ),
                    array( "title" => "High-altitude emergency travel & evacuation insurance", "desc" => "<p>Mandatory insurance explicitly covering helicopter evacuation to 6,000m. Non-negotiable safety requirement.</p>" ),
                    array( "title" => "Personal trekking equipment & apparel", "desc" => "<p>Four-season sleeping bag, down jacket, hiking boots, trekking poles, and headlamp.</p>" ),
                    array( "title" => "Bottled beverages, hot showers & device charging", "desc" => "<p>Bottled water, hot showers, device charging fees, and Wi-Fi at teahouses.</p>" ),
                    array( "title" => "Gratuities for expedition crew", "desc" => "<p>Tips for guides and porters are customary. Recommended: Guide USD 10–15/day, Porter USD 5–8/day.</p>" )
                ),
                "gallery" => array(
                    "kicker"  => "Visual Moments",
                    "script"  => "In Pictures",
                    "heading" => "Glimpses of Everest",
                    "subtext" => "A curated collection of Khumbu landscapes, mountain trails, and authentic Sherpa encounters.",
                    "images"  => array(
                        array( "url" => "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1600&q=85", "label" => "Everest Base Camp at dawn" ),
                        array( "url" => "https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1600&q=85", "label" => "Namche Bazaar amphitheatre" ),
                        array( "url" => "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1600&q=85", "label" => "Prayer flags over the Khumbu Valley" )
                    )
                ),
                "essential" => array(
                    "kicker"      => "Travel Advice",
                    "script"      => "Good to Know",
                    "heading"     => "Essential Information",
                    "intro"       => "<p>Careful preparation ensures a safe and rewarding Himalayan journey. Review our key recommendations below.</p>",
                    "card_kicker" => "Trip Dossier",
                    "card_title"  => "Download Your Complete Route Notes",
                    "card_desc"   => "Packing checklist, altitude schedule, visa guidelines, and weather data.",
                    "card_btn"    => "Request Trip Notes",
                    "card_email"  => "sales@yetiadventure.com"
                ),
                "faqs" => array(
                    array( "question" => "What is the best season to undertake the Everest Base Camp trek?", "answer" => "<p><strong>Autumn (Sep–Nov)</strong>: Clearest skies, crisp visibility. <strong>Spring (Mar–May)</strong>: Warmer, rhododendrons in bloom, longer days. Both seasons are outstanding. Winter is possible but very cold; monsoon (Jun–Aug) is not recommended.</p>" ),
                    array( "question" => "How fit do I need to be, and how is altitude sickness managed?", "answer" => "<p>Solid cardiovascular fitness is essential. Train with 8–12 weeks of hilly hiking with a loaded pack. Our itinerary includes two acclimatisation days (Namche & Dingboche), strict ascent rate limits, twice-daily pulse oximeter readings, and emergency evacuation protocols.</p>" ),
                    array( "question" => "What type of accommodation and food can I expect?", "answer" => "<p>Kathmandu: 4/5-star boutique heritage hotels. Trek route: comfortable twin-share teahouse lodges with hearty fresh menus. All dietary requirements (vegetarian, vegan, gluten-free) accommodated with advance notice.</p>" )
                ),
                "reviews_section" => array(
                    "kicker"  => "Guest Stories",
                    "script"  => "Traveller Feedback",
                    "heading" => "Words from Fellow Adventurers",
                    "subtext" => "Genuine reflections from travellers who have walked the high trails with us.",
                    "reviews" => array(
                        array( "name" => "Sarah & David Jenkins", "location" => "United Kingdom", "rating" => "5", "quote" => "Standing beneath Everest was breathtaking. The warmth and professionalism of our Sherpa team made this a truly unforgettable experience." ),
                        array( "name" => "Marcus Vance", "location" => "Melbourne, Australia", "rating" => "5", "quote" => "The two acclimatisation days made an enormous difference. I felt genuinely strong at Base Camp and Kala Patthar. Our guide Dawa was exceptional." ),
                        array( "name" => "Priya Mehta", "location" => "Mumbai, India", "rating" => "5", "quote" => "As a solo female trekker I felt completely safe and welcomed throughout. A truly transformative Himalayan journey." )
                    )
                ),
                "contact" => array(
                    "dep_kicker"  => "Travel Together",
                    "dep_script"  => "Choose Your Month",
                    "dep_heading" => "Join a Fixed Departure or Customise Your Dates",
                    "dep_subtext" => "Share your preferred travel window and group size; our expedition planners confirm availability within 24 hours.",
                    "box_script"  => "Everest Awaits",
                    "box_title"   => "Ask about upcoming departure dates",
                    "box_desc"    => "Send your dates, party size, or questions directly to our trek director via WhatsApp.",
                    "box_note"    => "Private departures confirmed prior to booking. Group discounts for 4+ travellers.",
                    "whatsapp"    => "9779803197006",
                    "email"       => "sales@yetiadventure.com"
                ),
                "departures" => array(
                    array( "date" => "2026-03-15", "price" => "2850", "currency" => "USD", "seats" => 8,  "status" => "open",    "note" => "Spring departure — rhododendron season" ),
                    array( "date" => "2026-04-12", "price" => "2850", "currency" => "USD", "seats" => 4,  "status" => "limited", "note" => "Peak spring season — limited seats remaining" ),
                    array( "date" => "2026-09-27", "price" => "2850", "currency" => "USD", "seats" => 12, "status" => "open",    "note" => "Autumn departure — clearest skies of the year" ),
                    array( "date" => "2026-10-18", "price" => "2850", "currency" => "USD", "seats" => 6,  "status" => "limited", "note" => "Peak autumn season — filling fast" ),
                    array( "date" => "2026-11-08", "price" => "2750", "currency" => "USD", "seats" => 10, "status" => "open",    "note" => "Late autumn — colder but quieter trails" )
                ),
                "pricing" => array(
                    "price"         => "2850",
                    "numeric_price" => 2850,
                    "currency"      => "USD",
                    "formatted"     => "USD 2,850"
                )
            )
        );

        return json_encode( $sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
