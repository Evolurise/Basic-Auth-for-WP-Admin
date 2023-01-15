<?php
/*
Plugin Name: Basic Auth for WP-Admin
Plugin URI: https://www.evolurise.com/
Description: Add an additionnal layer of security with this super light plugin that adds a basic authentication HTTP to the wp-admin and wp-login pages.
Version: 1.0.0
Author: Evolurise - Walid SADFI
text-domain: evolurise-basic-auth-for-wpadmin
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

function basic_auth_for_wp_admin() {
    if ( ! is_user_logged_in() ) {
        $options = get_option( 'basic_auth_for_wp_admin_options' );
        $user = $options['username'];
        $pass = $options['password'];
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
            die( 'Access denied' );
        }
        if ( $_SERVER['PHP_AUTH_USER'] != $user || $_SERVER['PHP_AUTH_PW'] != $pass ) {
            header( 'WWW-Authenticate: Basic realm="Access denied"' );
            header( 'HTTP/1.0 401 Unauthorized' );
            die( 'Access denied' );
        }
    }
}
add_action( 'init', 'basic_auth_for_wp_admin' );

function basic_auth_for_wp_admin_options_init() {
    register_setting( 'basic_auth_for_wp_admin_options', 'basic_auth_for_wp_admin_options', 'basic_auth_for_wp_admin_options_validate' );
    add_settings_section( 'basic_auth_for_wp_admin_section', 'Basic Auth for WP-Admin Settings', 'basic_auth_for_wp_admin_section_callback', 'basic_auth_for_wp_admin' );
    add_settings_field( 'basic_auth_for_wp_admin_username', 'Username', 'basic_auth_for_wp_admin_username_callback', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_section' );
    add_settings_field( 'basic_auth_for_wp_admin_password', 'Password', 'basic_auth_for_wp_admin_password_callback', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_section' );
}
add_action( 'admin_init', 'basic_auth_for_wp_admin_options_init' );

function basic_auth_for_wp_admin_section_callback() {
    echo '<p>Enter a username and password to use for basic authentication on the wp-admin and wp-login pages.</p>';
}

function basic_auth_for_wp_admin_username_callback() {
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    echo '<input id="basic_auth_for_wp_admin_username" name="basic_auth_for_wp_admin_options[username]" size="40" type="text" value="' . $options['username'] . '" />';
}

function basic_auth_for_wp_admin_password_callback() {
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    echo '<input id="basic_auth_for_wp_admin_password" name="basic_auth_for_wp_admin_options[password]" size="40" type="password" value="' . $options['password'] . '" />';
    echo '<input type="checkbox" id="basic_auth_for_wp_admin_show_password" name="basic_auth_for_wp_admin_options[show_password]"' . checked( $options['show_password'], 1, false ) . ' value="1"> Show Password</input>';
    wp_enqueue_script( 'basic_auth_for_wp_admin_options', plugin_dir_url( __FILE__ ) . 'password_checker.js', array(), '1.0.0', true );

    ?>
   
<?php
}

function basic_auth_for_wp_admin_options_validate( $input ) {
    $options = get_option( 'basic_auth_for_wp_admin_options' );
    $options['username'] = trim( $input['username'] );
    $options['password'] = trim( $input['password'] );
    $input['show_password'] = sanitize_text_field( $input['show_password'] );
    return $options;
}

function basic_auth_for_wp_admin_menu() {
    add_options_page( 'Basic Auth for WP-Admin Options', 'Basic Auth for WP-Admin', 'manage_options', 'basic_auth_for_wp_admin', 'basic_auth_for_wp_admin_options_page' );
}
add_action( 'admin_menu', 'basic_auth_for_wp_admin_menu' );

function basic_auth_for_wp_admin_options_page() {
    wp_enqueue_style( 'basic-auth-for-wp-admin-style', plugin_dir_url( __FILE__ ) . 'styles_admin.css' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have sufficient permissions to access this page.' );
    }

    echo '<div class="wrap">';
    echo '<img width="20%" src="' . plugin_dir_url( __FILE__ ) . '/img/evolurise_logo.png' . '"</img>';
    echo '<h2>Welcome to the Basic Auth for WP-Admin settings page</h2>';
    echo '<form action="options.php" method="post">';
    settings_fields( 'basic_auth_for_wp_admin_options' );
    do_settings_sections( 'basic_auth_for_wp_admin' );
    submit_button();
    echo 'Thank you for using our plugin, please rate it and visit our website <a href="https://www.evolurise.com">evolurise.com</a>';
    echo '</form>';
    echo '</div>';
}

