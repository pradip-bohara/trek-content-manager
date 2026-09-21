<?php
/**
 * Trek Manager - Custom All Reviews Management Template
 *
 * @package Trek_Content_Manager
 * @var WP_Query $query
 * @var string $status_filter
 * @var int $rating_filter
 * @var string $search_query
 * @var int $total_count
 * @var int $published_count
 * @var int $pending_count
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap trek-reviews-wrap">
    <!-- Header -->
    <div class="trek-reviews-header">
        <div class="trek-reviews-title-group">
            <h1 class="trek-reviews-title"><?php esc_html_e( 'All Reviews', 'trek-content-manager' ); ?></h1>
            <p class="trek-reviews-subtitle"><?php esc_html_e( 'Moderate, approve, and manage customer reviews for all trekking journeys.', 'trek-content-manager' ); ?></p>
        </div>
        <div class="trek-reviews-header-actions">
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=trek_review' ) ); ?>" class="button button-primary">
                + <?php esc_html_e( 'Add New Review', 'trek-content-manager' ); ?>
            </a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="trek-reviews-toolbar">
        <!-- Status Tabs -->
        <ul class="trek-filter-tabs">
            <li class="<?php echo 'all' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'all', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'All', 'trek-content-manager' ); ?>
                    <span class="trek-tab-count">(<?php echo esc_html( $total_count ); ?>)</span>
                </a>
            </li>
            <li class="<?php echo 'published' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'published', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'Published', 'trek-content-manager' ); ?>
                    <span class="trek-tab-count">(<?php echo esc_html( $published_count ); ?>)</span>
                </a>
            </li>
            <li class="<?php echo 'pending' === $status_filter ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( add_query_arg( array( 'status' => 'pending', 'paged' => 1 ) ) ); ?>">
                    <?php esc_html_e( 'Pending Moderation', 'trek-content-manager' ); ?>
                    <span class="trek-tab-count"><?php echo esc_html( $pending_count ); ?></span>
                </a>
            </li>
        </ul>

        <!-- Right Search & Rating Filter -->
        <form method="get" class="trek-toolbar-form">
            <input type="hidden" name="post_type" value="trek_destination" />
            <input type="hidden" name="page" value="trek-reviews" />
            <input type="hidden" name="status" value="<?php echo esc_attr( $status_filter ); ?>" />

            <select name="rating" class="trek-rating-select" onchange="this.form.submit()">
                <option value="0"><?php esc_html_e( 'All Ratings', 'trek-content-manager' ); ?></option>
                <option value="5" <?php selected( $rating_filter, 5 ); ?>>★★★★★ (5 Stars)</option>
                <option value="4" <?php selected( $rating_filter, 4 ); ?>>★★★★☆ (4 Stars)</option>
                <option value="3" <?php selected( $rating_filter, 3 ); ?>>★★★☆☆ (3 Stars)</option>
                <option value="2" <?php selected( $rating_filter, 2 ); ?>>★★☆☆☆ (2 Stars)</option>
                <option value="1" <?php selected( $rating_filter, 1 ); ?>>★☆☆☆☆ (1 Star)</option>
            </select>

            <div class="trek-search-input-wrap">
                <input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Search reviews...', 'trek-content-manager' ); ?>" class="trek-input-search" />
                <button type="submit" class="button"><?php esc_html_e( 'Search', 'trek-content-manager' ); ?></button>
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="trek-table-card">
        <table class="trek-custom-table trek-reviews-table">
            <thead>
                <tr>
                    <th style="width: 110px;"><?php esc_html_e( 'Rating', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Reviewer', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Email', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Destination', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Review Summary', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'trek-content-manager' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'trek-content-manager' ); ?></th>
                    <th class="text-right" style="width: 140px;"><?php esc_html_e( 'Actions', 'trek-content-manager' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $query->have_posts() ) : ?>
                    <?php while ( $query->have_posts() ) : $query->the_post(); 
                        $rev_id      = get_the_ID();
                        $rating      = floatval( get_post_meta( $rev_id, '_trek_rating', true ) ?: 5 );
                        $email       = get_post_meta( $rev_id, '_trek_reviewer_email', true ) ?: get_post_meta( $rev_id, '_trek_email', true );
                        $trek_id     = get_post_meta( $rev_id, '_trek_id', true );
                        $trek_title  = $trek_id && get_post( $trek_id ) ? get_the_title( $trek_id ) : __( 'General / Unlinked', 'trek-content-manager' );
                        $approved    = get_post_meta( $rev_id, '_trek_approved', true );
                        $is_approved = ( '1' === $approved && 'publish' === get_post_status( $rev_id ) );
                        $stars_full  = floor( $rating );
                        $stars_str   = str_repeat( '★', $stars_full ) . str_repeat( '☆', max( 0, 5 - $stars_full ) );
                    ?>
                    <tr class="trek-review-row" data-id="<?php echo esc_attr( $rev_id ); ?>">
                        <td>
                            <span class="trek-stars" title="<?php echo esc_attr( $rating . '/5' ); ?>">
                                <?php echo esc_html( $stars_str ); ?>
                            </span>
                        </td>
                        <td class="font-semibold">
                            <?php the_title(); ?>
                        </td>
                        <td>
                            <?php if ( $email ) : ?>
                                <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                            <?php else : ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $trek_id && get_post( $trek_id ) ) : ?>
                                <a href="<?php echo esc_url( get_edit_post_link( $trek_id ) ); ?>" class="font-medium">
                                    <?php echo esc_html( $trek_title ); ?>
                                </a>
                            <?php else : ?>
                                <em><?php echo esc_html( $trek_title ); ?></em>
                            <?php endif; ?>
                        </td>
                        <td class="trek-review-content-cell">
                            <?php 
                            $content = wp_strip_all_tags( get_the_content() );
                            echo esc_html( wp_trim_words( $content, 12, '...' ) ?: '—' ); 
                            ?>
                        </td>
                        <td class="text-muted">
                            <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                        </td>
                        <td>
                            <?php if ( $is_approved ) : ?>
                                <span class="trek-pill published"><?php esc_html_e( 'Published', 'trek-content-manager' ); ?></span>
                            <?php else : ?>
                                <span class="trek-pill pending"><?php esc_html_e( 'Pending', 'trek-content-manager' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="trek-td-actions text-right">
                            <?php if ( ! $is_approved ) : ?>
                                <button type="button" class="trek-btn-action approve trek-btn-approve-review" title="<?php esc_attr_e( 'Approve & Publish', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </button>
                            <?php else : ?>
                                <button type="button" class="trek-btn-action reject trek-btn-reject-review" title="<?php esc_attr_e( 'Move to Pending Moderation', 'trek-content-manager' ); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            <?php endif; ?>

                            <a href="<?php echo esc_url( get_edit_post_link( $rev_id ) ); ?>" class="trek-btn-action edit" title="<?php esc_attr_e( 'Edit in WordPress', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>

                            <button type="button" class="trek-btn-action delete trek-btn-delete-review" title="<?php esc_attr_e( 'Delete Review', 'trek-content-manager' ); ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 32px; color: #64748b;">
                            <?php esc_html_e( 'No reviews found matching your criteria.', 'trek-content-manager' ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $query->max_num_pages > 1 ) : ?>
        <div class="trek-pagination">
            <?php
            echo paginate_links( array(
                'total'   => $query->max_num_pages,
                'current' => $paged,
                'format'  => '&paged=%#%',
            ) );
            ?>
        </div>
        <?php endif; ?>
    </div>
</div>
