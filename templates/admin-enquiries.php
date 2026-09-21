<?php
/**
 * Trek Manager - Custom All Enquiries Management Template
 *
 * @package Trek_Content_Manager
 * @var WP_Query $query
 * @var string $status_filter
 * @var string $search_query
 * @var int $total_count
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap trek-enquiries-wrap">
    <!-- Header -->
    <div class="trek-enquiries-header">
        <div class="trek-enquiries-title-group">
            <h1 class="trek-enquiries-title"><?php esc_html_e( 'All Enquiries', 'trek-content-manager' ); ?></h1>
            <p class="trek-enquiries-subtitle"><?php esc_html_e( 'Manage traveller booking requests, lead contacts, and customer itineraries.', 'trek-content-manager' ); ?></p>
        </div>
        <div class="trek-enquiries-header-actions">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=trek_destination&page=trek-export-enquiries' ) ); ?>" class="button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <?php esc_html_e( 'Export CSV', 'trek-content-manager' ); ?>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="trek-enquiries-toolbar">
        <!-- Status Tabs -->
        <ul class="trek-filter-tabs">
            <li class="<?php echo 'all' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'all', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'All', 'trek-content-manager' ); ?>
                    <span class="trek-tab-count">(<?php echo esc_html( $total_count ); ?>)</span>
                </a>
            </li>
            <li class="<?php echo 'new' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'new', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'New', 'trek-content-manager' ); ?>
                </a>
            </li>
            <li class="<?php echo 'contacted' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'contacted', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?>
                </a>
            </li>
            <li class="<?php echo 'follow-up' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'follow-up', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?>
                </a>
            </li>
            <li class="<?php echo 'closed' === strtolower( $status_filter ) ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'closed', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'Closed', 'trek-content-manager' ); ?>
                </a>
            </li>
        </ul>

        <!-- Search Form -->
        <form method="get" class="trek-toolbar-form">
            <input type="hidden" name="post_type" value="trek_destination" />
            <input type="hidden" name="page" value="trek-enquiries" />
            <input type="hidden" name="status" value="<?php echo esc_attr( $status_filter ); ?>" />

            <div class="trek-search-input-wrap">
                <input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search enquiries...', 'trek-content-manager' ); ?>" class="trek-input-search" />
                <button type="submit" class="button"><?php esc_html_e( 'Search', 'trek-content-manager' ); ?></button>
            </div>
        </form>
    </div>

    <!-- Enquiries Table Card -->
    <div class="trek-table-card">
        <table class="trek-custom-table trek-enquiries-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Traveller Name', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Destination / Trip', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Message Preview', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                    <th class="text-right" style="width: 130px;"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $query->have_posts() ) : ?>
                    <?php while ( $query->have_posts() ) : $query->the_post(); 
                        $enq_id     = get_the_ID();
                        $email      = get_post_meta( $enq_id, '_trek_enquiry_email', true );
                        $phone      = get_post_meta( $enq_id, '_trek_enquiry_phone', true );
                        $status     = get_post_meta( $enq_id, '_trek_enquiry_status', true ) ?: 'New';
                        $status_slug = sanitize_title( $status );
                        $trek_id    = get_post_meta( $enq_id, '_trek_enquiry_trek_id', true );
                        $trip_title = get_post_meta( $enq_id, '_trek_enquiry_trek_title', true );
                        if ( ! $trip_title && $trek_id ) {
                            $trip_title = get_the_title( $trek_id );
                        }
                    ?>
                    <tr class="trek-enquiry-row" data-id="<?php echo esc_attr( $enq_id ); ?>">
                        <td class="font-semibold">
                            <a href="#" class="trek-btn-view-detail" data-id="<?php echo esc_attr( $enq_id ); ?>">
                                <?php the_title(); ?>
                            </a>
                        </td>
                        <td>
                            <?php if ( $email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                            <?php else : ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $phone ) : 
                                $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
                            ?>
                                <?php echo esc_html( $phone ); ?>
                                <?php if ( ! empty( $clean_phone ) ) : ?>
                                    <a href="https://wa.me/<?php echo esc_attr( $clean_phone ); ?>" target="_blank" title="Chat on WhatsApp" class="trek-wa-link">💬</a>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $trek_id && get_post( $trek_id ) ) : ?>
                                <a href="<?php echo esc_url( get_edit_post_link( $trek_id ) ); ?>" class="font-medium">
                                    <?php echo esc_html( $trip_title ?: get_the_title( $trek_id ) ); ?>
                                </a>
                            <?php else : ?>
                                <span><?php echo esc_html( $trip_title ?: '—' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="trek-message-preview-cell">
                            <?php
                            $msg = wp_strip_all_tags( get_the_content() );
                            echo esc_html( wp_trim_words( $msg, 10, '...' ) ?: '—' );
                            ?>
                        </td>
                        <td class="text-muted">
                            <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                        </td>
                        <td>
                            <select class="trek-select-status trek-status-pill <?php echo esc_attr( $status_slug ); ?>" data-id="<?php echo esc_attr( $enq_id ); ?>">
                                <option value="New" <?php selected( $status, 'New' ); ?>><?php esc_html_e( 'New', 'trek-content-manager' ); ?></option>
                                <option value="Contacted" <?php selected( $status, 'Contacted' ); ?>><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></option>
                                <option value="Follow Up" <?php selected( $status, 'Follow Up' ); ?>><?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?></option>
                                <option value="Closed" <?php selected( $status, 'Closed' ); ?>><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></option>
                            </select>
                        </td>
                        <td class="trek-td-actions text-right">
                            <button type="button" class="trek-btn-action view trek-btn-view-detail" data-id="<?php echo esc_attr( $enq_id ); ?>" title="<?php esc_attr_e( 'View Details', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>

                            <a href="<?php echo esc_url( get_edit_post_link( $enq_id ) ); ?>" class="trek-btn-action edit" title="<?php esc_attr_e( 'Edit in WordPress', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>

                            <button type="button" class="trek-btn-action delete trek-btn-delete-enquiry" data-id="<?php echo esc_attr( $enq_id ); ?>" title="<?php esc_attr_e( 'Delete Enquiry', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 32px; color: #64748b;">
                            <?php esc_html_e( 'No enquiries found matching your criteria.', 'trek-content-manager' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Slide-over Detail Drawer & Backdrop -->
    <div id="trek-enquiry-backdrop" class="trek-drawer-backdrop trek-hidden"></div>
    <div id="trek-enquiry-drawer" class="trek-drawer trek-hidden">
        <div class="trek-drawer-header">
            <h2 class="trek-drawer-title" id="drawer-traveller-name"><?php esc_html_e( 'Enquiry Details', 'trek-content-manager' ); ?></h2>
            <button type="button" id="trek-drawer-close" class="trek-drawer-close-btn" aria-label="<?php esc_attr_e( 'Close', 'trek-content-manager' ); ?>">✕</button>
        </div>
        <div class="trek-drawer-body">
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Trip / Destination', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val font-semibold" id="drawer-trip"></span>
            </div>
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Email', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val" id="drawer-email"></span>
            </div>
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val" id="drawer-phone"></span>
            </div>
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Travellers', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val" id="drawer-travellers"></span>
            </div>
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Source', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val" id="drawer-source"></span>
            </div>
            <div class="trek-drawer-field">
                <span class="trek-drawer-label"><?php esc_html_e( 'Date Received', 'trek-content-manager' ); ?>:</span>
                <span class="trek-drawer-val" id="drawer-date"></span>
            </div>

            <hr class="trek-drawer-divider" />

            <div class="trek-drawer-field-block">
                <span class="trek-drawer-label"><?php esc_html_e( 'Status', 'trek-content-manager' ); ?>:</span>
                <div class="trek-drawer-status-group">
                    <select id="drawer-status-select" class="trek-select-status">
                        <option value="New"><?php esc_html_e( 'New', 'trek-content-manager' ); ?></option>
                        <option value="Contacted"><?php esc_html_e( 'Contacted', 'trek-content-manager' ); ?></option>
                        <option value="Follow Up"><?php esc_html_e( 'Follow Up', 'trek-content-manager' ); ?></option>
                        <option value="Closed"><?php esc_html_e( 'Closed', 'trek-content-manager' ); ?></option>
                    </select>
                    <button type="button" id="drawer-save-status-btn" class="button button-primary"><?php esc_html_e( 'Save Status', 'trek-content-manager' ); ?></button>
                </div>
            </div>

            <div class="trek-drawer-field-block" style="margin-top: 20px;">
                <span class="trek-drawer-label"><?php esc_html_e( 'Message / Details', 'trek-content-manager' ); ?>:</span>
                <div class="trek-drawer-message-box" id="drawer-message"></div>
            </div>
        </div>
    </div>
</div>
