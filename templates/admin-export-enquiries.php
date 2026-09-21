<?php
/**
 * Trek Manager - Export Enquiries Custom Admin Screen
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$count = wp_count_posts( 'trek_enquiry' );
$total = intval( $count->private ?? 0 ) + intval( $count->publish ?? 0 ) + intval( $count->draft ?? 0 );
$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=trek_export_enquiries' ), 'trek_export_enquiries' );
?>

<div class="wrap trek-export-wrap" style="max-width: 900px; margin: 20px 20px 48px 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">
            <?php esc_html_e( 'Export Enquiries', 'trek-content-manager' ); ?>
        </h1>
        <p style="font-size: 13.5px; color: #64748b; margin: 6px 0 0;">
            <?php esc_html_e( 'Download customer enquiry and booking leads as a CSV spreadsheet for offline CRM, email marketing, or accounting.', 'trek-content-manager' ); ?>
        </p>
    </div>

    <!-- Export Card -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 24px;">
            <div>
                <span style="font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php esc_html_e( 'Total Stored Records', 'trek-content-manager' ); ?>
                </span>
                <div style="font-size: 32px; font-weight: 800; color: #0f172a; margin-top: 4px;">
                    <?php echo esc_html( $total ); ?> <?php esc_html_e( 'Enquiries', 'trek-content-manager' ); ?>
                </div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            </div>
        </div>

        <form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="trek_export_enquiries" />
            <?php wp_nonce_field( 'trek_export_enquiries' ); ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px;">
                        <?php esc_html_e( 'Date Filter', 'trek-content-manager' ); ?>
                    </label>
                    <select name="date_range" style="width: 100%; height: 38px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0 12px; font-size: 13px; background: #ffffff;">
                        <option value="all"><?php esc_html_e( 'All Dates (Complete History)', 'trek-content-manager' ); ?></option>
                        <option value="30"><?php esc_html_e( 'Last 30 Days', 'trek-content-manager' ); ?></option>
                        <option value="90"><?php esc_html_e( 'Last 90 Days', 'trek-content-manager' ); ?></option>
                        <option value="365"><?php esc_html_e( 'Last 365 Days', 'trek-content-manager' ); ?></option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px;">
                        <?php esc_html_e( 'Status Filter', 'trek-content-manager' ); ?>
                    </label>
                    <select name="status_filter" style="width: 100%; height: 38px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0 12px; font-size: 13px; background: #ffffff;">
                        <option value="all"><?php esc_html_e( 'All Statuses (New, Contacted, Follow Up, Closed)', 'trek-content-manager' ); ?></option>
                        <option value="New"><?php esc_html_e( 'New Only', 'trek-content-manager' ); ?></option>
                        <option value="Contacted"><?php esc_html_e( 'Contacted Only', 'trek-content-manager' ); ?></option>
                        <option value="Follow Up"><?php esc_html_e( 'Follow Up Only', 'trek-content-manager' ); ?></option>
                        <option value="Closed"><?php esc_html_e( 'Closed Only', 'trek-content-manager' ); ?></option>
                    </select>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 24px; font-size: 12.5px; color: #64748b;">
                <strong><?php esc_html_e( 'CSV Export Format Includes', 'trek-content-manager' ); ?>:</strong>
                ID, Traveller Name, Email, Phone / WhatsApp, Trip Destination, Travellers Count, Lead Source, Submission Date, Current Status, and Message Details.
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="button button-primary button-large" style="height: 42px; padding: 0 24px; font-size: 14px; font-weight: 600; border-radius: 8px; background: #2563eb; border-color: #2563eb;">
                    📥 <?php esc_html_e( 'Download CSV Export', 'trek-content-manager' ); ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-enquiries' ) ); ?>" class="button button-large" style="height: 42px; line-height: 40px; padding: 0 18px; font-size: 13px; border-radius: 8px;">
                    <?php esc_html_e( 'Back to Enquiries', 'trek-content-manager' ); ?>
                </a>
            </div>
        </form>
    </div>
</div>
