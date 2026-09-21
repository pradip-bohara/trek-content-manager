<?php
/**
 * Trek Manager - Travellers & Contacts Controller
 *
 * Aggregates unique email addresses collected across enquiries, reviews,
 * and user accounts. Provides a dedicated management screen with editable
 * contact info, individual emailing, and bulk emailing.
 *
 * @package Trek_Content_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Trek_Travellers {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 11 );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

        // AJAX handlers
        add_action( 'wp_ajax_trek_save_traveller', array( __CLASS__, 'ajax_save_traveller' ) );
        add_action( 'wp_ajax_trek_send_traveller_email', array( __CLASS__, 'ajax_send_email' ) );
        add_action( 'wp_ajax_trek_bulk_send_email', array( __CLASS__, 'ajax_bulk_send_email' ) );
    }

    public static function register_menu() {
        add_submenu_page(
            'edit.php?post_type=trek_destination',
            __( 'All Travellers', 'trek-content-manager' ),
            __( 'All Travellers', 'trek-content-manager' ),
            'edit_posts',
            'trek-travellers',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( 'trek_destination_page_trek-travellers' !== $hook && ( ! isset( $_GET['page'] ) || 'trek-travellers' !== $_GET['page'] ) ) {
            return;
        }

        wp_enqueue_style(
            'trek-admin-travellers-css',
            TREK_MANAGER_PLUGIN_URL . 'assets/css/trek-admin-travellers.css',
            array(),
            TREK_MANAGER_VERSION
        );

        wp_enqueue_script(
            'trek-admin-travellers-js',
            TREK_MANAGER_PLUGIN_URL . 'assets/js/trek-admin-travellers.js',
            array( 'jquery' ),
            TREK_MANAGER_VERSION,
            true
        );

        wp_localize_script(
            'trek-admin-travellers-js',
            'TrekTravellersData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'trek_travellers_nonce' ),
                'i18n'    => array(
                    'selectPrompt' => __( 'Please select at least one traveller.', 'trek-content-manager' ),
                    'confirmBulk'  => __( 'Send this email to all selected recipients?', 'trek-content-manager' ),
                    'sending'      => __( 'Sending...', 'trek-content-manager' ),
                    'sent'         => __( 'Email sent successfully.', 'trek-content-manager' ),
                    'saved'        => __( 'Traveller details updated.', 'trek-content-manager' ),
                    'error'        => __( 'An error occurred. Please try again.', 'trek-content-manager' ),
                ),
            )
        );
    }

    /**
     * Aggregate unique travellers by email from enquiries, reviews, and wp_users
     *
     * @return array
     */
    public static function get_unique_travellers() {
        $travellers = array();

        // 1. Enquiries
        $enqs = get_posts( array(
            'post_type'      => 'trek_enquiry',
            'post_status'    => array( 'publish', 'private', 'draft' ),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        foreach ( $enqs as $e ) {
            $email = sanitize_email( get_post_meta( $e->ID, '_trek_enquiry_email', true ) );
            if ( ! is_email( $email ) ) {
                continue;
            }
            $email_key = strtolower( $email );

            if ( ! isset( $travellers[ $email_key ] ) ) {
                $travellers[ $email_key ] = array(
                    'email'         => $email,
                    'name'          => $e->post_title ?: __( 'Guest Traveller', 'trek-content-manager' ),
                    'phone'         => get_post_meta( $e->ID, '_trek_enquiry_phone', true ) ?: '',
                    'sources'       => array( 'Enquiry' ),
                    'enquiry_count' => 1,
                    'review_count'  => 0,
                    'last_active'   => get_the_date( 'M j, Y', $e->ID ),
                    'enquiry_ids'   => array( $e->ID ),
                );
            } else {
                $travellers[ $email_key ]['enquiry_count']++;
                if ( ! in_array( 'Enquiry', $travellers[ $email_key ]['sources'], true ) ) {
                    $travellers[ $email_key ]['sources'][] = 'Enquiry';
                }
                $travellers[ $email_key ]['enquiry_ids'][] = $e->ID;
                if ( empty( $travellers[ $email_key ]['phone'] ) ) {
                    $travellers[ $email_key ]['phone'] = get_post_meta( $e->ID, '_trek_enquiry_phone', true ) ?: '';
                }
            }
        }

        // 2. Reviews
        $revs = get_posts( array(
            'post_type'      => 'trek_review',
            'post_status'    => array( 'publish', 'pending', 'draft' ),
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        foreach ( $revs as $r ) {
            $email = sanitize_email( get_post_meta( $r->ID, '_trek_reviewer_email', true ) ?: get_post_meta( $r->ID, '_trek_email', true ) );
            if ( ! is_email( $email ) ) {
                continue;
            }
            $email_key = strtolower( $email );

            if ( ! isset( $travellers[ $email_key ] ) ) {
                $travellers[ $email_key ] = array(
                    'email'         => $email,
                    'name'          => $r->post_title ?: __( 'Reviewer', 'trek-content-manager' ),
                    'phone'         => get_post_meta( $r->ID, '_trek_phone', true ) ?: '',
                    'sources'       => array( 'Review' ),
                    'enquiry_count' => 0,
                    'review_count'  => 1,
                    'last_active'   => get_the_date( 'M j, Y', $r->ID ),
                    'review_ids'    => array( $r->ID ),
                );
            } else {
                $travellers[ $email_key ]['review_count']++;
                if ( ! in_array( 'Review', $travellers[ $email_key ]['sources'], true ) ) {
                    $travellers[ $email_key ]['sources'][] = 'Review';
                }
                $travellers[ $email_key ]['review_ids'][] = $r->ID;
            }
        }

        // 3. WordPress Users
        $users = get_users();
        foreach ( $users as $u ) {
            $email = sanitize_email( $u->user_email );
            if ( ! is_email( $email ) ) {
                continue;
            }
            $email_key = strtolower( $email );

            if ( ! isset( $travellers[ $email_key ] ) ) {
                $travellers[ $email_key ] = array(
                    'email'         => $email,
                    'name'          => $u->display_name ?: $u->user_login,
                    'phone'         => get_user_meta( $u->ID, 'phone', true ) ?: get_user_meta( $u->ID, 'billing_phone', true ) ?: '',
                    'sources'       => array( 'User' ),
                    'enquiry_count' => 0,
                    'review_count'  => 0,
                    'last_active'   => date_i18n( 'M j, Y', strtotime( $u->user_registered ) ),
                    'user_id'       => $u->ID,
                );
            } else {
                if ( ! in_array( 'User', $travellers[ $email_key ]['sources'], true ) ) {
                    $travellers[ $email_key ]['sources'][] = 'User';
                }
                $travellers[ $email_key ]['user_id'] = $u->ID;
            }
        }

        // 4. Merge Custom Saved Overrides
        $custom_overrides = get_option( 'trek_traveller_custom_contacts', array() );
        if ( is_array( $custom_overrides ) ) {
            foreach ( $custom_overrides as $email_key => $override ) {
                if ( isset( $travellers[ $email_key ] ) ) {
                    if ( ! empty( $override['name'] ) ) {
                        $travellers[ $email_key ]['name'] = $override['name'];
                    }
                    if ( ! empty( $override['phone'] ) ) {
                        $travellers[ $email_key ]['phone'] = $override['phone'];
                    }
                    if ( ! empty( $override['email'] ) && is_email( $override['email'] ) ) {
                        $travellers[ $email_key ]['email'] = $override['email'];
                    }
                }
            }
        }

        return array_values( $travellers );
    }

    /**
     * Get count of unique traveller emails
     *
     * @return int
     */
    public static function get_unique_travellers_count() {
        $travellers = self::get_unique_travellers();
        return count( $travellers );
    }

    /**
     * AJAX: Save/Edit traveller details
     */
    public static function ajax_save_traveller() {
        check_ajax_referer( 'trek_travellers_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $orig_email = sanitize_email( $_POST['original_email'] ?? '' );
        $new_name   = sanitize_text_field( $_POST['name'] ?? '' );
        $new_email  = sanitize_email( $_POST['email'] ?? '' );
        $new_phone  = sanitize_text_field( $_POST['phone'] ?? '' );

        if ( ! is_email( $orig_email ) || ! is_email( $new_email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'trek-content-manager' ) ) );
        }

        $overrides = get_option( 'trek_traveller_custom_contacts', array() );
        if ( ! is_array( $overrides ) ) {
            $overrides = array();
        }

        $overrides[ strtolower( $orig_email ) ] = array(
            'name'  => $new_name,
            'email' => $new_email,
            'phone' => $new_phone,
        );

        update_option( 'trek_traveller_custom_contacts', $overrides );

        wp_send_json_success( array(
            'message' => __( 'Traveller details updated.', 'trek-content-manager' ),
            'traveller' => array(
                'name'  => $new_name,
                'email' => $new_email,
                'phone' => $new_phone,
            ),
        ) );
    }

    /**
     * AJAX: Send individual email
     */
    public static function ajax_send_email() {
        check_ajax_referer( 'trek_travellers_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $to_email = sanitize_email( $_POST['to_email'] ?? '' );
        $subject  = sanitize_text_field( $_POST['subject'] ?? '' );
        $message  = wp_kses_post( $_POST['message'] ?? '' );

        if ( ! is_email( $to_email ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid recipient email address.', 'trek-content-manager' ) ) );
        }

        if ( empty( $subject ) || empty( $message ) ) {
            wp_send_json_error( array( 'message' => __( 'Subject and message are required.', 'trek-content-manager' ) ) );
        }

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $sent    = wp_mail( $to_email, $subject, wpautop( $message ), $headers );

        if ( $sent ) {
            wp_send_json_success( array( 'message' => __( 'Email sent successfully.', 'trek-content-manager' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to send email. Please check server mail settings.', 'trek-content-manager' ) ) );
        }
    }

    /**
     * AJAX: Bulk send email
     */
    public static function ajax_bulk_send_email() {
        check_ajax_referer( 'trek_travellers_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'trek-content-manager' ) ) );
        }

        $emails  = isset( $_POST['emails'] ) && is_array( $_POST['emails'] ) ? array_map( 'sanitize_email', $_POST['emails'] ) : array();
        $subject = sanitize_text_field( $_POST['subject'] ?? '' );
        $message = wp_kses_post( $_POST['message'] ?? '' );

        if ( empty( $emails ) ) {
            wp_send_json_error( array( 'message' => __( 'No recipients selected.', 'trek-content-manager' ) ) );
        }

        if ( empty( $subject ) || empty( $message ) ) {
            wp_send_json_error( array( 'message' => __( 'Subject and message are required.', 'trek-content-manager' ) ) );
        }

        $headers    = array( 'Content-Type: text/html; charset=UTF-8' );
        $sent_count = 0;

        foreach ( $emails as $em ) {
            if ( is_email( $em ) ) {
                if ( wp_mail( $em, $subject, wpautop( $message ), $headers ) ) {
                    $sent_count++;
                }
            }
        }

        wp_send_json_success( array(
            'message'    => sprintf( __( 'Email sent to %d recipients.', 'trek-content-manager' ), $sent_count ),
            'sent_count' => $sent_count,
        ) );
    }

    /**
     * Render the All Travellers screen
     */
    public static function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'trek-content-manager' ) );
        }

        $travellers = self::get_unique_travellers();
        $total_count = count( $travellers );

        $template = TREK_MANAGER_PLUGIN_DIR . 'templates/admin-travellers.php';
        if ( file_exists( $template ) ) {
            include $template;
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'Travellers template not found.', 'trek-content-manager' ) . '</p></div>';
        }
    }
}
