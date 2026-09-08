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

    public static function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'JSON Importer & Schema Guide', 'trek-content-manager' ),
            __( '📥 JSON Importer', 'trek-content-manager' ),
            'manage_trek_content',
            'trek-json-importer',
            array( __CLASS__, 'render_importer_page' )
        );
    }

    /**
     * Main Admin Page Renderer
     */
    public static function render_importer_page() {
        if ( ! current_user_can( 'manage_trek_content' ) ) {
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
                                <td>Main destination title. E.g. <code>"The Ultimate Everest Base Camp Experience"</code></td>
                            </tr>
                            <tr>
                                <td><strong>slug</strong></td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_name</span></td>
                                <td>URL permalink slug. E.g. <code>"everest-basecamp-trek"</code></td>
                            </tr>
                            <tr>
                                <td><strong>excerpt</strong> / card_excerpt</td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_card_excerpt</span></td>
                                <td>Card short description shown on listing grid and carousel cards.</td>
                            </tr>
                            <tr>
                                <td><strong>overview</strong> / description / content</td>
                                <td><code>string (HTML)</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">post_content</span></td>
                                <td>Full rich text narrative overview for the single trek page.</td>
                            </tr>
                            <tr>
                                <td><strong>days</strong> / duration / hero_days</td>
                                <td><code>number | string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_hero_days</span></td>
                                <td>Number of days or string duration badge. E.g. <code>15</code> or <code>"15 Days"</code></td>
                            </tr>
                            <tr>
                                <td><strong>category</strong> / categories</td>
                                <td><code>string | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">trek_category</span></td>
                                <td>Categories. E.g. <code>["Trekking", "Himalayan"]</code> or <code>"Culture"</code></td>
                            </tr>
                            <tr>
                                <td><strong>region</strong> / country</td>
                                <td><code>string | array</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">trek_region</span></td>
                                <td>Country / Region name. E.g. <code>"Nepal"</code> or <code>["Nepal", "Everest"]</code></td>
                            </tr>
                            <tr>
                                <td><strong>hero_image</strong> / bg_image</td>
                                <td><code>string (URL)</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_hero_bg_img</span></td>
                                <td>High-resolution hero header background image URL.</td>
                            </tr>
                            <tr>
                                <td><strong>card_image</strong> / image / thumbnail</td>
                                <td><code>string (URL)</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_card_image_url</span></td>
                                <td>Card thumbnail URL. Will also be set as WordPress Featured Image.</td>
                            </tr>
                            <tr>
                                <td><strong>tag</strong> / tag_label / card_tag</td>
                                <td><code>string</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_card_tag</span></td>
                                <td>Pill tag displayed on card. E.g. <code>"Nepal · Signature Trek"</code></td>
                            </tr>
                            <tr>
                                <td><strong>featured</strong> / is_featured</td>
                                <td><code>boolean | number</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_is_featured</span></td>
                                <td>Set <code>true</code> or <code>1</code> to highlight in featured carousel and signature journeys.</td>
                            </tr>
                            <tr>
                                <td><strong>quick_facts</strong></td>
                                <td><code>object</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_quick_facts</span></td>
                                <td>Object with keys: <code>max_altitude</code>, <code>destination</code>, <code>daily_walking</code>, <code>accommodation</code>, <code>style</code>, <code>season</code>, <code>group_size</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>highlights</strong></td>
                                <td><code>array of strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_highlights</span></td>
                                <td>List of journey bullet point highlights.</td>
                            </tr>
                            <tr>
                                <td><strong>itinerary</strong></td>
                                <td><code>array of objects</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_itinerary</span></td>
                                <td>Day-by-day itinerary. Keys per day: <code>day</code>, <code>title</code>, <code>description</code>, <code>altitude</code>, <code>walking_hours</code>, <code>stay</code>, <code>meals</code>.</td>
                            </tr>
                            <tr>
                                <td><strong>inclusions</strong></td>
                                <td><code>array of strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_inclusions</span></td>
                                <td>What is included in this journey.</td>
                            </tr>
                            <tr>
                                <td><strong>exclusions</strong></td>
                                <td><code>array of strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_exclusions</span></td>
                                <td>What is not included in this journey.</td>
                            </tr>
                            <tr>
                                <td><strong>gallery</strong></td>
                                <td><code>array of strings</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_gallery_images</span></td>
                                <td>Array of photo URLs for the interactive journey photo gallery.</td>
                            </tr>
                            <tr>
                                <td><strong>faqs</strong></td>
                                <td><code>array of objects</code></td>
                                <td><span class="ya-badge ya-badge-opt">Optional</span></td>
                                <td><span class="ya-badge ya-badge-meta">_trek_faqs</span></td>
                                <td>Array of FAQ items: <code>[{"question": "...", "answer": "..."}]</code></td>
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
        if ( ! current_user_can( 'manage_trek_content' ) ) {
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
        $publish_status  = ! empty( $_POST['publish_immediately'] ) ? 'publish' : 'draft';

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

            $content = ! empty( $item['overview'] ) ? wp_kses_post( $item['overview'] ) : ( ! empty( $item['description'] ) ? wp_kses_post( $item['description'] ) : ( ! empty( $item['content'] ) ? wp_kses_post( $item['content'] ) : '' ) );
            $excerpt = ! empty( $item['excerpt'] ) ? sanitize_textarea_field( $item['excerpt'] ) : ( ! empty( $item['card_excerpt'] ) ? sanitize_textarea_field( $item['card_excerpt'] ) : '' );

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
                    $term = term_exists( $cat_name, 'trek_category' );
                    if ( ! $term && $create_terms ) {
                        $term = wp_insert_term( $cat_name, 'trek_category' );
                    }
                    if ( ! is_wp_error( $term ) && ! empty( $term['term_id'] ) ) {
                        $cat_ids[] = (int) $term['term_id'];
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
                    $term = term_exists( $reg_name, 'trek_region' );
                    if ( ! $term && $create_terms ) {
                        $term = wp_insert_term( $reg_name, 'trek_region' );
                    }
                    if ( ! is_wp_error( $term ) && ! empty( $term['term_id'] ) ) {
                        $reg_ids[] = (int) $term['term_id'];
                    }
                }
                if ( ! empty( $reg_ids ) ) {
                    wp_set_object_terms( $post_id, $reg_ids, 'trek_region' );
                }
            }

            // 3. Post Meta - Hero & Card
            $days = ! empty( $item['days'] ) ? $item['days'] : ( ! empty( $item['duration'] ) ? $item['duration'] : ( ! empty( $item['hero_days'] ) ? $item['hero_days'] : '12 Days' ) );
            $days_clean = is_numeric( $days ) ? $days . ' Days' : sanitize_text_field( $days );
            update_post_meta( $post_id, '_trek_hero_days', $days_clean );
            update_post_meta( $post_id, '_trek_card_meta', $days_clean );

            if ( ! empty( $item['hero_image'] ) || ! empty( $item['bg_image'] ) ) {
                $hero_img = esc_url_raw( ! empty( $item['hero_image'] ) ? $item['hero_image'] : $item['bg_image'] );
                update_post_meta( $post_id, '_trek_hero_bg_img', $hero_img );
            }
            if ( ! empty( $item['card_image'] ) || ! empty( $item['image'] ) || ! empty( $item['thumbnail'] ) ) {
                $card_img = esc_url_raw( ! empty( $item['card_image'] ) ? $item['card_image'] : ( ! empty( $item['image'] ) ? $item['image'] : $item['thumbnail'] ) );
                update_post_meta( $post_id, '_trek_card_image_url', $card_img );
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
                $diff_term = term_exists( $difficulty, 'trek_difficulty' );
                if ( ! $diff_term && $create_terms ) {
                    $diff_term = wp_insert_term( $difficulty, 'trek_difficulty' );
                }
                if ( ! is_wp_error( $diff_term ) && ! empty( $diff_term['term_id'] ) ) {
                    wp_set_object_terms( $post_id, array( (int) $diff_term['term_id'] ), 'trek_difficulty' );
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

            // 4. Quick Facts
            $high_pt = sanitize_text_field( $item['quick_facts']['max_altitude'] ?? $item['quick_facts']['high_point'] ?? '' );
            $dest    = sanitize_text_field( $item['quick_facts']['destination'] ?? $item['quick_facts']['location'] ?? '' );
            $walk    = sanitize_text_field( $item['quick_facts']['daily_walking'] ?? $item['quick_facts']['walking_hours'] ?? '' );
            $accom   = sanitize_text_field( $item['quick_facts']['accommodation'] ?? $item['quick_facts']['stay'] ?? '' );
            $style   = sanitize_text_field( $item['quick_facts']['style'] ?? $item['quick_facts']['travel_style'] ?? '' );
            $season  = sanitize_text_field( $item['quick_facts']['season'] ?? $item['quick_facts']['best_season'] ?? '' );
            $group   = sanitize_text_field( $item['quick_facts']['group_size'] ?? '' );

            $facts = array(
                'max_altitude'  => $high_pt,
                'high_point'    => $high_pt,
                'destination'   => $dest,
                'region'        => $dest,
                'duration'      => $days_clean,
                'daily_walking' => $walk,
                'trekking'      => $walk,
                'accommodation' => $accom,
                'accom'         => $accom,
                'style'         => $style,
                'season'        => $season,
                'group_size'    => $group,
                'difficulty'    => $difficulty,
                'price'         => $price,
                'currency'      => $currency,
            );
            update_post_meta( $post_id, '_trek_quick_facts', $facts );

            if ( ! empty( $high_pt ) ) update_post_meta( $post_id, '_trek_fact_high_point', $high_pt );
            if ( ! empty( $dest ) )    update_post_meta( $post_id, '_trek_fact_region', $dest );
            if ( ! empty( $walk ) )    update_post_meta( $post_id, '_trek_fact_trekking', $walk );
            if ( ! empty( $accom ) )   update_post_meta( $post_id, '_trek_fact_accom', $accom );
            if ( ! empty( $style ) )   update_post_meta( $post_id, '_trek_fact_style', $style );
            if ( ! empty( $season ) )  update_post_meta( $post_id, '_trek_fact_season', $season );
            if ( ! empty( $group ) )   update_post_meta( $post_id, '_trek_fact_group_size', $group );

            // 5. Highlights Array
            if ( ! empty( $item['highlights'] ) && is_array( $item['highlights'] ) ) {
                $clean_hl = array_map( 'sanitize_text_field', $item['highlights'] );
                update_post_meta( $post_id, '_trek_highlights', $clean_hl );
            }

            // 6. Day-by-Day Itinerary Array (Sync dual keys: _trek_itinerary & _trek_itinerary_list)
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

                    $clean_itin[] = array(
                        'day'           => $d_num,
                        'title'         => $d_title,
                        'description'   => $d_desc,
                        'altitude'      => sanitize_text_field( $day_data['altitude'] ?? '' ),
                        'walking_hours' => sanitize_text_field( $day_data['walking_hours'] ?? '' ),
                        'stay'          => sanitize_text_field( $day_data['stay'] ?? $day_data['accommodation'] ?? '' ),
                        'meals'         => sanitize_text_field( $day_data['meals'] ?? '' ),
                    );

                    $clean_itin_list[] = array(
                        'day'   => $d_num,
                        'stage' => $d_stage,
                        'title' => $d_title,
                        'pills' => $d_pills,
                        'desc'  => $d_desc,
                    );
                    $idx++;
                }
                update_post_meta( $post_id, '_trek_itinerary', $clean_itin );
                update_post_meta( $post_id, '_trek_itinerary_list', $clean_itin_list );
            }

            // 7. Inclusions & Exclusions (Sync dual keys: _trek_inclusions & _trek_inclusions_full)
            if ( ! empty( $item['inclusions'] ) && is_array( $item['inclusions'] ) ) {
                $clean_inc = array_map( 'sanitize_text_field', $item['inclusions'] );
                update_post_meta( $post_id, '_trek_inclusions', $clean_inc );
                update_post_meta( $post_id, '_trek_inclusions_full', $clean_inc );
            }
            if ( ! empty( $item['exclusions'] ) && is_array( $item['exclusions'] ) ) {
                $clean_exc = array_map( 'sanitize_text_field', $item['exclusions'] );
                update_post_meta( $post_id, '_trek_exclusions', $clean_exc );
                update_post_meta( $post_id, '_trek_exclusions_full', $clean_exc );
            }

            // 8. Gallery Images (Sync dual keys: _trek_gallery_images & _trek_gallery_list)
            if ( ! empty( $item['gallery'] ) && is_array( $item['gallery'] ) ) {
                $clean_gal = array();
                $clean_gal_list = array();
                foreach ( $item['gallery'] as $g_item ) {
                    $url = is_array( $g_item ) ? ( $g_item['url'] ?? '' ) : $g_item;
                    $lbl = is_array( $g_item ) ? ( $g_item['label'] ?? $g_item['caption'] ?? '' ) : '';
                    if ( ! empty( $url ) ) {
                        $clean_gal[] = esc_url_raw( $url );
                        $clean_gal_list[] = array(
                            'url'   => esc_url_raw( $url ),
                            'label' => sanitize_text_field( $lbl ),
                        );
                    }
                }
                update_post_meta( $post_id, '_trek_gallery_images', $clean_gal );
                update_post_meta( $post_id, '_trek_gallery_list', $clean_gal_list );
            }

            // 9. FAQs (Sync dual keys: _trek_faqs & _trek_essential_list)
            if ( ! empty( $item['faqs'] ) && is_array( $item['faqs'] ) ) {
                $clean_faqs = array();
                $clean_ess = array();
                foreach ( $item['faqs'] as $faq ) {
                    if ( is_array( $faq ) && ! empty( $faq['question'] ) ) {
                        $q = sanitize_text_field( $faq['question'] );
                        $a = wp_kses_post( $faq['answer'] ?? '' );
                        $clean_faqs[] = array(
                            'question' => $q,
                            'answer'   => $a,
                        );
                        $clean_ess[] = array(
                            'title'   => $q,
                            'content' => $a,
                        );
                    }
                }
                update_post_meta( $post_id, '_trek_faqs', $clean_faqs );
                update_post_meta( $post_id, '_trek_essential_list', $clean_ess );
            }

            // 10. Curated Reviews (Sync dual keys: _trek_curated_reviews & _trek_reviews_list)
            if ( ! empty( $item['reviews'] ) && is_array( $item['reviews'] ) ) {
                $clean_revs = array();
                foreach ( $item['reviews'] as $rev ) {
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

            // 11. Compile & Save Master JSON Payload (_trek_structured_data)
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
     * Get Complete Rich Sample JSON
     */
    public static function get_sample_json_string() {
        $sample = array(
            array(
                "title" => "The Ultimate Trek: Everest Base Camp Experience",
                "slug" => "everest-basecamp-trek",
                "excerpt" => "Journey through Sherpa country, high mountain villages and the dramatic Khumbu landscape to the foot of Everest.",
                "days" => 15,
                "difficulty" => "Strenuous",
                "price" => "2,850",
                "currency" => "USD",
                "category" => array("Trekking", "Himalayan"),
                "primary_category" => "Trekking",
                "region" => "Nepal",
                "map_region" => "Khumbu",
                "map_district" => "Solukhumbu",
                "tag" => "Trekking · Everest",
                "featured" => true,
                "hero_image" => "https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/AERIAL-VIEW-OF-THE-EVEREST-BASE-CAMP.jpg",
                "card_image" => "https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/AERIAL-VIEW-OF-THE-EVEREST-BASE-CAMP.jpg",
                "overview" => "<p>Experience the majesty of the Khumbu region on this classic Everest Base Camp trek. Stand at the base of the world's highest peak and immerse yourself in Sherpa mountain culture.</p>",
                "quick_facts" => array(
                    "max_altitude" => "5,364m / 17,598ft",
                    "destination" => "Everest Region, Nepal",
                    "daily_walking" => "5 - 7 Hours",
                    "accommodation" => "Comfortable Mountain Lodges & Teahouses",
                    "style" => "Privately Guided High-Altitude Trek",
                    "season" => "March to May & September to December",
                    "group_size" => "2 - 12 Travelers",
                    "difficulty" => "Strenuous",
                    "price" => "2,850",
                    "currency" => "USD"
                ),
                "highlights" => array(
                    "Stand at Everest Base Camp (5,364m) and the foot of the Khumbu Icefall",
                    "Sunrise panoramic views from Kala Patthar (5,545m)",
                    "Explore ancient monasteries in Tengboche and Khumjung",
                    "Walk through legendary Sherpa capitals Namche Bazaar and Dingboche"
                ),
                "reviews" => array(
                    array(
                        "name" => "Sarah Jenkins",
                        "location" => "United Kingdom",
                        "rating" => "5",
                        "quote" => "An unforgettable, life-changing trek to Everest Base Camp. The guides and Sherpa team were exceptional."
                    )
                ),
                "itinerary" => array(
                    array(
                        "day" => "01",
                        "title" => "Arrive in Kathmandu",
                        "description" => "Private transfer to your boutique hotel. Welcome briefing and evening heritage dinner.",
                        "altitude" => "1,400m",
                        "walking_hours" => "Rest day",
                        "stay" => "Dwarika's Heritage Hotel",
                        "meals" => "Dinner"
                    ),
                    array(
                        "day" => "02",
                        "title" => "Fly to Lukla & Trek to Phakding",
                        "description" => "Scenic mountain flight into Lukla (2,840m) and begin hiking along the Dudh Koshi river valley.",
                        "altitude" => "2,610m",
                        "walking_hours" => "3 - 4 hrs",
                        "stay" => "Yeti Mountain Home Phakding",
                        "meals" => "Breakfast, Lunch, Dinner"
                    ),
                    array(
                        "day" => "03",
                        "title" => "Trek to Namche Bazaar",
                        "description" => "Cross suspension bridges adorned with prayer flags and ascend through pine forests into Namche Bazaar.",
                        "altitude" => "3,440m",
                        "walking_hours" => "5 - 6 hrs",
                        "stay" => "Luxury Lodge Namche",
                        "meals" => "Breakfast, Lunch, Dinner"
                    )
                ),
                "inclusions" => array(
                    "All private airport and domestic transfers",
                    "Return scenic flights Kathmandu / Ramechhap to Lukla",
                    "Sagarmatha National Park entry permits & TIMS card",
                    "Experienced high-altitude English-speaking Sherpa guide & porters",
                    "All boutique accommodation and three meals daily during trek"
                ),
                "exclusions" => array(
                    "International airfare to/from Kathmandu",
                    "Nepal tourist visa",
                    "Personal travel and emergency helicopter evacuation insurance",
                    "Personal gear and alcoholic beverages"
                )
            ),
            array(
                "title" => "Where Culture Meets the Wild: Nepal's Treasures",
                "slug" => "nepal-wild-trio",
                "excerpt" => "Combine Kathmandu's living heritage, Himalayan scenery and the wildlife-rich lowlands in one varied journey.",
                "days" => 12,
                "category" => array("Culture", "Wildlife", "Soft Adventure"),
                "region" => "Nepal",
                "tag" => "Culture · Wildlife",
                "featured" => true,
                "hero_image" => "https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/Bhaktapur-e1656397818692.jpg",
                "card_image" => "https://peru-oryx-583767.hostingersite.com/wp-content/uploads/2026/07/Bhaktapur-e1656397818692.jpg",
                "overview" => "<p>A seamless journey connecting the ancient artistic kingdoms of Kathmandu Valley, the peaceful lakeside serenity of Pokhara, and private safari wildlife drives in Chitwan National Park.</p>",
                "quick_facts" => array(
                    "max_altitude" => "2,175m (Nagarkot)",
                    "destination" => "Kathmandu, Pokhara & Chitwan",
                    "daily_walking" => "2 - 4 Hours Leisure Walking",
                    "accommodation" => "5-Star Boutique & Luxury Safari Lodges",
                    "style" => "Private Luxury Cultural & Wildlife Expedition",
                    "season" => "September to May",
                    "group_size" => "2 - 10 Travelers"
                ),
                "highlights" => array(
                    "Private guided tours of Bhaktapur Durbar Square and Patan",
                    "Sunrise mountain views over Annapurna range from Sarangkot",
                    "Exclusive jeep safaris in Chitwan tracking one-horned rhinos & royal Bengal tigers",
                    "Luxury boat cruise on Phewa Lake in Pokhara"
                )
            )
        );

        return json_encode( $sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
