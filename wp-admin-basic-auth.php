<?php
/*
Plugin Name: Basic Auth for WP-Admin
Plugin URI: https://www.evolurise.com/
Description: Add an additionnal layer of security with this super light plugin that adds a basic authentication HTTP to the wp-admin and wp-login pages.
Version: 1.0
Author: Evolurise - Walid SADFI
text-domain: basic-auth-for-wp-admin
License: GPL2
*/

/*  Copyright 2023 Evolurise  (email : hello@evolurise.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'init', 'basic_auth_for_wp_admin' );
add_action( 'admin_init', 'basic_auth_for_wp_admin_options_init' );
echo '<link rel="stylesheet" type="text/css" href="' . esc_url( plugin_dir_url( __FILE__ ) . 'styles_admin.css' ) . '">';


function basic_auth_for_wp_admin() {
    if ( ! is_user_logged_in() ) {
        $options = get_option( 'basic_auth_for_wp_admin_options' );
        $user = esc_attr( $options['username'] );
        $pass = esc_attr( $options['password'] );
        $valid_pages = array( '/wp-admin/', '/wp-login.php' );
        $valid = false;
        foreach ( $valid_pages as $page ) {
            if ( strpos( $_SERVER['PHP_SELF'], $page ) !== false ) {
                $valid = true;
                break;
            }
        }
        if ( ! $valid ) {
            return;
        }
        if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) || ! isset( $_SERVER['PHP_AUTH_PW'] ) ) {
            header( 'WWW-Authenticate: Basic realm="Access denied"' );
            header( 'HTTP/1.0 401 Unauthorized' );
            die( esc_html( 'Access denied' ) );
        }
        if ( $_SERVER['PHP_AUTH_USER'] != $user || !wp_check_password($_SERVER['PHP_AUTH_PW'],$pass,$user) ) {
            header( 'WWW-Authenticate: Basic realm="Access denied"' );
            header( 'HTTP/1.0 401 Unauthorized' );
            die( esc_html( 'Access denied' ) );
        }
    }
}

function basic_auth_for_wp_admin_options_init() {
    register_setting( 'basic_auth_for_wp_admin_options', 'basic_auth_for_wp_admin_options', 'basic_auth_for_wp_admin_options_validate' );
    add_settings_section( 'basic_auth_for_wp_admin_section', esc_html__( 'Basic Auth for WP-Admin Settings', 'basic-auth-for-wp-admin' ), 'basic_auth_for_wp_admin_section_callback', 'basic_auth_for_wp_admin' );
    add_settings_field( 'basic_auth_for_wp_admin_username', esc_html__( 'Username', 'basic-auth-for-wp-admin' ), 'basic_auth_for_wp_admin_username_callback', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_section' );
    add_settings_field( 'basic_auth_for_wp_admin_password', esc_html__( 'Password', 'basic-auth-for-wp-admin' ), 'basic_auth_for_wp_admin_password_callback', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_section' );
}

function basic_auth_for_wp_admin_section_callback() {
    echo '<p>' . esc_html__( 'Enter a username and password to use for basic authentication on the wp-admin and wp-login pages.', 'basic-auth-for-wp-admin' ) . '</p>';
}

function basic_auth_for_wp_admin_username_callback() {
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    echo '<input id="basic_auth_for_wp_admin_username" name="basic_auth_for_wp_admin_options[username]" size="40" type="text" value="' . esc_attr( $options['username'] ) . '" />';
}

function basic_auth_for_wp_admin_password_callback() {
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    echo '<input id="basic_auth_for_wp_admin_password" name="basic_auth_for_wp_admin_options[password]" size="40" type="password" value="' . esc_attr( $options['password'] ) . '" />';
}

function basic_auth_for_wp_admin_options_validate( $input ) {
    $input['username'] = sanitize_text_field( $input['username'] );
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    if ($input['password'] != $options['password']) {
        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
    }
    return $input;
}
function basic_auth_for_wp_admin_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'basic-auth-for-wp-admin' ) );
    }
    ?>
    <div class="wrap_basic_auth">
        <img width="20%" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '/img/evolurise_logo.png' ); ?>" alt="Evolurise logo">
        <h2><?php echo esc_html__( 'Welcome to the Basic Auth for WP-Admin settings page', 'basic-auth-for-wp-admin' ); ?></h2>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'basic_auth_for_wp_admin_options' );
            do_settings_sections( 'basic_auth_for_wp_admin' );
            submit_button();
            ?>
        </form>
        <p>Thank you for using our plugin, please rate it and visit our website <a href="https://www.evolurise.com">evolurise.com</a></p>
    </div>
    <?php
}

function basic_auth_for_wp_admin_menu() {
    add_options_page( esc_html__( 'Basic Auth for WP-Admin', 'basic-auth-for-wp-admin' ), esc_html__( 'Basic Auth for WP-Admin', 'basic-auth-for-wp-admin' ), 'manage_options', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_options_page' );
}

add_action( 'admin_menu', 'basic_auth_for_wp_admin_menu' );


