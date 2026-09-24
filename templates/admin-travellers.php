<?php
/**
 * Trek Manager - All Travellers Management Template
 *
 * Dedicated unified contacts screen aggregating unique travellers from
 * enquiries, reviews, and WordPress users.
 *
 * @package Trek_Content_Manager
 * @var array $travellers List of aggregated unique travellers
 * @var int   $total_count Total count of travellers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap trek-travellers-wrap">
    <!-- Header -->
    <div class="trek-travellers-header">
        <div class="trek-travellers-title-group">
            <h1 class="trek-travellers-title"><?php esc_html_e( 'All Travellers', 'trek-content-manager' ); ?></h1>
            <p class="trek-travellers-subtitle"><?php esc_html_e( 'Manage unified contacts, guest travellers, and registered users collected across enquiries and reviews.', 'trek-content-manager' ); ?></p>
        </div>
        <div class="trek-travellers-header-actions">
            <button type="button" class="button button-primary" id="trek-btn-export-travellers">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <?php esc_html_e( 'Export CSV', 'trek-content-manager' ); ?>
            </button>
        </div>
    </div>

    <!-- Toolbar: Tabs & Live Search -->
    <div class="trek-travellers-toolbar">
        <ul class="trek-filter-tabs">
            <li class="active" data-filter="all">
                <a href="#all">
                    <?php esc_html_e( 'All Travellers', 'trek-content-manager' ); ?>
                    <span class="trek-tab-count">(<?php echo esc_html( $total_count ); ?>)</span>
                </a>
            </li>
            <li data-filter="enquiry">
                <a href="#enquiry"><?php esc_html_e( 'Enquiry Leads', 'trek-content-manager' ); ?></a>
            </li>
            <li data-filter="review">
                <a href="#review"><?php esc_html_e( 'Reviewers', 'trek-content-manager' ); ?></a>
            </li>
            <li data-filter="user">
                <a href="#user"><?php esc_html_e( 'Registered Users', 'trek-content-manager' ); ?></a>
            </li>
        </ul>

        <div class="trek-search-input-wrap">
            <input type="search" id="trek-travellers-search" placeholder="<?php esc_attr_e( 'Search by name, email, or phone...', 'trek-content-manager' ); ?>" class="trek-input-search" />
        </div>
    </div>

    <!-- Travellers Table Card -->
    <div class="trek-table-card">
        <table class="trek-custom-table trek-travellers-table" id="trek-travellers-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="check-column">
                        <input type="checkbox" id="trek-select-all-travellers" title="<?php esc_attr_e( 'Select All', 'trek-content-manager' ); ?>" />
                    </th>
                    <th><?php esc_html_e( 'Traveller', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Email Address', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Phone / WhatsApp', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Source', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Activity', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Last Active', 'trek-content-manager' ); ?></th>
                    <th class="text-right" style="width: 90px;"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $travellers ) ) : ?>
                    <?php foreach ( $travellers as $t ) : 
                        $email       = $t['email'];
                        $name        = $t['name'] ?: __( 'Guest Traveller', 'trek-content-manager' );
                        $phone       = $t['phone'] ?: '';
                        $sources     = $t['sources'] ?? array();
                        $source_data = implode( ' ', array_map( 'strtolower', $sources ) );
                        $last_active = $t['last_active'] ?: '—';
                        $avatar_url  = get_avatar_url( $email, array( 'size' => 48 ) );
                    ?>
                        <tr class="trek-traveller-row" 
                            data-email="<?php echo esc_attr( strtolower( $email ) ); ?>"
                            data-name="<?php echo esc_attr( strtolower( $name ) ); ?>"
                            data-phone="<?php echo esc_attr( strtolower( $phone ) ); ?>"
                            data-sources="<?php echo esc_attr( $source_data ); ?>">
                            
                            <td class="check-column">
                                <input type="checkbox" class="trek-traveller-checkbox" value="<?php echo esc_attr( $email ); ?>" data-name="<?php echo esc_attr( $name ); ?>" />
                            </td>

                            <!-- Name with Avatar -->
                            <td class="trek-td-traveller">
                                <div class="trek-traveller-info">
                                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="" class="trek-traveller-avatar" />
                                    <div class="trek-traveller-name-wrap">
                                        <strong class="trek-traveller-name"><?php echo esc_html( $name ); ?></strong>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="trek-td-email">
                                <a href="mailto:<?php echo esc_attr( $email ); ?>" class="trek-traveller-link email">
                                    <?php echo esc_html( $email ); ?>
                                </a>
                            </td>

                            <!-- Phone / WhatsApp -->
                            <td class="trek-td-phone">
                                <?php if ( ! empty( $phone ) ) : 
                                    $clean_phone = preg_replace( '/[^0-9+]/', '', $phone );
                                ?>
                                    <div class="trek-phone-group">
                                        <a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="trek-traveller-link phone">
                                            <?php echo esc_html( $phone ); ?>
                                        </a>
                                        <a href="https://wa.me/<?php echo esc_attr( ltrim( $clean_phone, '+' ) ); ?>" target="_blank" class="trek-wa-icon" title="<?php esc_attr_e( 'Message on WhatsApp', 'trek-content-manager' ); ?>">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#10b981"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        </a>
                                    </div>
                                <?php else : ?>
                                    <span class="trek-text-muted"><?php esc_html_e( 'No phone added', 'trek-content-manager' ); ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Lead Source -->
                            <td class="trek-td-sources">
                                <div class="trek-source-badges">
                                    <?php foreach ( $sources as $src ) : 
                                        $badge_class = strtolower( $src );
                                    ?>
                                        <span class="trek-source-badge trek-badge-<?php echo esc_attr( $badge_class ); ?>">
                                            <?php echo esc_html( $src ); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <!-- Activity Counts -->
                            <td class="trek-td-activity">
                                <span class="trek-activity-stats">
                                    <?php 
                                    $act_parts = array();
                                    if ( ! empty( $t['enquiry_count'] ) ) {
                                        $act_parts[] = sprintf( _n( '%d Enquiry', '%d Enquiries', $t['enquiry_count'], 'trek-content-manager' ), $t['enquiry_count'] );
                                    }
                                    if ( ! empty( $t['review_count'] ) ) {
                                        $act_parts[] = sprintf( _n( '%d Review', '%d Reviews', $t['review_count'], 'trek-content-manager' ), $t['review_count'] );
                                    }
                                    if ( in_array( 'User', $sources, true ) ) {
                                        $act_parts[] = __( 'Account', 'trek-content-manager' );
                                    }
                                    echo esc_html( implode( ' • ', $act_parts ) ?: '—' );
                                    ?>
                                </span>
                            </td>

                            <!-- Last Active -->
                            <td class="trek-td-date">
                                <span class="trek-date-text"><?php echo esc_html( $last_active ); ?></span>
                            </td>

                            <!-- Actions -->
                            <td class="trek-td-actions text-right">
                                <button type="button" class="button button-small trek-btn-edit-traveller" 
                                    data-original-email="<?php echo esc_attr( $email ); ?>"
                                    data-name="<?php echo esc_attr( $name ); ?>"
                                    data-email="<?php echo esc_attr( $email ); ?>"
                                    data-phone="<?php echo esc_attr( $phone ); ?>"
                                    title="<?php esc_attr_e( 'Edit Contact Info', 'trek-content-manager' ); ?>">
                                    <?php esc_html_e( 'Edit', 'trek-content-manager' ); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="trek-empty-row">
                        <td colspan="8" class="text-center" style="padding: 40px;">
                            <p class="trek-text-muted"><?php esc_html_e( 'No travellers or contact records found.', 'trek-content-manager' ); ?></p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================================================================
         MODAL 1: EDIT TRAVELLER DETAILS
         ================================================================== -->
    <div id="trek-edit-traveller-modal" class="trek-modal-backdrop trek-hidden">
        <div class="trek-modal-dialog">
            <div class="trek-modal-header">
                <h3 class="trek-modal-title"><?php esc_html_e( 'Edit Traveller Details', 'trek-content-manager' ); ?></h3>
                <button type="button" class="trek-modal-close" data-close-modal>&times;</button>
            </div>
            <form id="trek-edit-traveller-form">
                <input type="hidden" id="edit-original-email" name="original_email" />
                <div class="trek-modal-body">
                    <div class="trek-form-field">
                        <label for="edit-traveller-name"><?php esc_html_e( 'Full Name', 'trek-content-manager' ); ?> <span class="required">*</span></label>
                        <input type="text" id="edit-traveller-name" name="name" class="widefat" required />
                    </div>
                    <div class="trek-form-field">
                        <label for="edit-traveller-email"><?php esc_html_e( 'Email Address', 'trek-content-manager' ); ?> <span class="required">*</span></label>
                        <input type="email" id="edit-traveller-email" name="email" class="widefat" required />
                    </div>
                    <div class="trek-form-field">
                        <label for="edit-traveller-phone"><?php esc_html_e( 'Phone / WhatsApp Number', 'trek-content-manager' ); ?></label>
                        <input type="text" id="edit-traveller-phone" name="phone" class="widefat" placeholder="+977 9800000000" />
                    </div>
                    <div id="trek-edit-status" class="trek-modal-status trek-hidden"></div>
                </div>
                <div class="trek-modal-footer">
                    <button type="button" class="button" data-close-modal><?php esc_html_e( 'Cancel', 'trek-content-manager' ); ?></button>
                    <button type="submit" class="button button-primary" id="trek-btn-save-traveller"><?php esc_html_e( 'Save Changes', 'trek-content-manager' ); ?></button>
                </div>
            </form>
        </div>
    </div>

</div>

