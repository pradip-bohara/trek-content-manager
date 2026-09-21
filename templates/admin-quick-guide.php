<?php
/**
 * Trek Manager - Quick Guide Custom Admin Screen
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap trek-guide-wrap" style="max-width: 1100px; margin: 20px 20px 48px 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">
            <?php esc_html_e( 'Trek Manager Quick Guide', 'trek-content-manager' ); ?>
        </h1>
        <p style="font-size: 13.5px; color: #64748b; margin: 6px 0 0;">
            <?php esc_html_e( 'Comprehensive architecture guide and instructions for managing destinations, travellers, reviews, and enquiries.', 'trek-content-manager' ); ?>
        </p>
    </div>

    <!-- Accordion Sections -->
    <div style="display: flex; flex-direction: column; gap: 14px;">

        <!-- Section 1 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" open>
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                🧭 1. <?php esc_html_e( 'Getting Started & Architecture Overview', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    <strong>Trek Content Manager</strong> is a dedicated enterprise travel management framework built natively inside WordPress.
                    It organizes your travel business into four primary entities:
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Destinations</strong>: High-altitude treks, expeditions, and cultural tours managed as a Custom Post Type with rich metadata (itineraries, gear, map coordinates, altitudes).</li>
                    <li><strong>Reviews</strong>: Customer feedback and star ratings tied to specific trips, complete with a moderation and approval workflow.</li>
                    <li><strong>Enquiries</strong>: Booking leads submitted through frontend trip modals and planning forms, with direct WhatsApp links and status tracking.</li>
                    <li><strong>Taxonomies</strong>: Standardized categorization by <em>Trip Categories</em>, <em>Regions</em>, and <em>Difficulties</em>.</li>
                </ul>
            </div>
        </details>

        <!-- Section 2 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                🏔️ 2. <?php esc_html_e( 'Managing Destinations & Taxonomies', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    Use the native WordPress CPT screens under <strong>Trek Manager → All Destinations</strong> to manage your trips:
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Featured Image & Hero</strong>: Always upload a high-resolution landscape photo (1920×1080) for the hero banner.</li>
                    <li><strong>Taxonomies</strong>: Assign at least one Region (e.g. <em>Everest, Annapurna, Langtang</em>), Category (e.g. <em>Trekking, Peak Climbing</em>), and Difficulty (e.g. <em>Moderate, Strenuous</em>).</li>
                    <li><strong>Live Previews</strong>: The plugin template auto-loads on frontend singular trip URLs and supports shortcode embedding: <code>[trek_destination id="123"]</code>.</li>
                </ul>
            </div>
        </details>

        <!-- Section 3 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                ⭐ 3. <?php esc_html_e( 'Review Moderation & Star Ratings', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    The <strong>All Reviews</strong> screen lets you moderate incoming customer reviews:
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Pending Moderation</strong>: Reviews submitted by travellers on the website appear as Pending until approved.</li>
                    <li><strong>One-Click Approval</strong>: Click the green checkmark icon to approve and publish a review immediately.</li>
                    <li><strong>Star Ratings</strong>: Stored as 1 to 5 numeric values and displayed with subtle golden stars.</li>
                </ul>
            </div>
        </details>

        <!-- Section 4 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                📬 4. <?php esc_html_e( 'Enquiries & Lead Management', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    The <strong>All Enquiries</strong> screen is your booking inbox:
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Slide-over Detail Drawer</strong>: Click any enquiry name or the eye icon to view full traveller contact information, party size, source, and custom message.</li>
                    <li><strong>Direct WhatsApp Chat</strong>: If the traveller provided a phone number, a green WhatsApp button lets your sales team chat with one click.</li>
                    <li><strong>Status Tracking</strong>: Transition leads from <em>New</em> → <em>Contacted</em> → <em>Follow Up</em> → <em>Closed</em>.</li>
                </ul>
            </div>
        </details>

        <!-- Section 5 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                📥 5. <?php esc_html_e( 'JSON Importer & Schema Migration', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    Import comprehensive itineraries and canonical trip data using the <strong>JSON Importer</strong>:
                </p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>Upload or paste a JSON payload matching the canonical schema located in <code>assets/canonical-trek-template.json</code>.</li>
                    <li>Automatically creates destination CPTs, links taxonomies, creates itineraries, and populates meta fields with zero manual copy-pasting.</li>
                </ul>
            </div>
        </details>

        <!-- Section 6 -->
        <details style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <summary style="font-size: 16px; font-weight: 700; color: #0f172a; cursor: pointer; user-select: none;">
                📊 6. <?php esc_html_e( 'Exporting Enquiries & Offline CRM', 'trek-content-manager' ); ?>
            </summary>
            <div style="margin-top: 14px; font-size: 13.5px; line-height: 1.6; color: #334155;">
                <p>
                    Under <strong>Export Enquiries</strong>, you can download all lead records as a clean UTF-8 CSV file ready for Microsoft Excel, Google Sheets, or import into HubSpot/Mailchimp.
                </p>
            </div>
        </details>

    </div>
</div>
