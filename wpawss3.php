<?php
/** 
 * Plugin Name: WP Aws S3
 * Plugin URI: https://citrusbug.com/
 * Description: Just another another plugin.
 * Author: Ishan Vyas
 * Author URI: https://citrusbug.com/
 * Text Domain: citrusbug.com
 * Version: 1.0
*/

define( 'WPS3_VERSION', '1.0' );

define( 'WPS3_REQUIRED_WP_VERSION', '4.9' );

define( 'WPS3_PLUGIN', __FILE__ );

define( 'WPS3_PLUGIN_BASENAME', plugin_basename( WPS3_PLUGIN ) );

define( 'WPS3_PLUGIN_NAME', trim( dirname( WPS3_PLUGIN_BASENAME ), '/' ) );

define( 'WPS3_PLUGIN_DIR', untrailingslashit( dirname( WPS3_PLUGIN ) ) );

function __construct() {
	add_action('init', array($this, 'wps3_records_modifymenu'));
}
register_activation_hook(__FILE__, 'wps3_records_modifymenu');

add_action('admin_menu','wps3_records_modifymenu');

function wps3_records_modifymenu() {
    add_menu_page('WP AWS S3', 'WP AWS S3', 'manage_options', 'wpawss3', 'wpawss3_shortcodes');
    add_options_page('WP AWS S3 Setting', 'WP AWS S3 Setting', 'manage_options', 'wpawss3-setting', 'wpawss3_options_page');
}

function wpawss3_register_settings() {
    add_option( 'wpawss3_db_name');
    add_option( 'wpawss3_host');
    add_option( 'wpawss3_username');
    add_option( 'wpawss3_password');
    add_option( 'wpawss3_aws_key');
    add_option( 'wpawss3_aws_secret_key');
    add_option( 'wpawss3_aws_region');
    add_option( 'wpawss3_aws_version');
    add_option( 'wpawss3_s3_bucket');
    add_option( 'wpawss3_identity_pool_id');
    add_option( 'wpawss3_s3_page_link');
    register_setting( 'wpawss3_options_group', 'wpawss3_db_name', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_host', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_username', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_password', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_aws_key', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_aws_secret_key', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_aws_region', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_aws_version', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_s3_bucket', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_identity_pool_id', 'wpawss3_callback' );
    register_setting( 'wpawss3_options_group', 'wpawss3_s3_page_link', 'wpawss3_callback' );
 }
 add_action( 'wp_enqueue_scripts', 'wpawss3_register_settings' );

function register_stylesheet() {
    if ( is_admin() ) {
        wp_enqueue_style( 'wpawss3-style', plugins_url( '/assets/css/bootstrap.min.css', __FILE__ ) );
        wp_enqueue_style( 'wpawss3-style1', plugins_url( '/assets/css/dataTables.bootstrap4.min.css', __FILE__ ) );
        wp_enqueue_style( 'wpawss3-style2', plugins_url( '/assets/css/toastr.min.css', __FILE__ ) );
        wp_enqueue_style( 'wpawss3-style3', 'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css' );
        wp_enqueue_style( 'wpawss3-style4', plugins_url( '/assets/css/editor.dataTables.min.css', __FILE__ ) );
        wp_enqueue_style( 'wpawss3-style5', plugins_url( '/assets/css/font-awesome.min.css', __FILE__ ) );
        wp_enqueue_style( 'wpawss3-style6', plugins_url( '/assets/css/style.css', __FILE__ ) );
    }
}
add_action('wp_enqueue_scripts', 'register_stylesheet');

function register_script() {
    if ( is_admin() ) {
        wp_enqueue_script( 'wpawss3-script', plugins_url( '/assets/js/popper.min.js', __FILE__ ) );
        wp_enqueue_script( 'wpawss3-script1', plugins_url( '/assets/js/bootstrap.min.js', __FILE__ ) );
        wp_enqueue_script( 'wpawss3-script2', plugins_url('/assets/js/toastr.min.js', __FILE__ ) );
        wp_enqueue_script( 'wpawss3-script3', 'https://code.jquery.com/jquery-3.5.1.js', array('jQuery') );
        wp_enqueue_script( 'wpawss3-script4', 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', array('jQuery') );
        wp_enqueue_script( 'wpawss3-script5', 'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.j', array('jQuery') );
        wp_enqueue_script( 'wpawss3-script6', 'https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js', array('jQuery') );
        wp_enqueue_script( 'wpawss3-script7', 'https://editor.datatables.net/extensions/Editor/js/dataTables.editor.min.js', array('jQuery') );
        wp_enqueue_script( 'wpawss3-script17', 'https://sdk.amazonaws.com/js/aws-sdk-2.1.24.min.js', array('jQuery') );        
        wp_enqueue_script( 'wpawss3-script8', plugins_url( '/assets/js/main.js', __FILE__ ) );
    }
}
add_action('wp_enqueue_scripts', 'register_script');


function wpawss3_ajax_load_scripts() {
    wp_enqueue_script('wpawss3-script1', 'https://code.jquery.com/jquery-3.5.1.js', array('jquery'));
    wp_localize_script('wpawss3-script1', 'pw1_script_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('wpawss3'),
    )); 
}
add_action('wp_print_scripts', 'wpawss3_ajax_load_scripts');

require_once WPS3_PLUGIN_DIR . '/includes/details.php';
require_once WPS3_PLUGIN_DIR . '/includes/settings.php';
require_once WPS3_PLUGIN_DIR . '/includes/functions.php';
require_once WPS3_PLUGIN_DIR . '/front/index.php';
require_once WPS3_PLUGIN_DIR . '/front/folder-list.php';
require_once WPS3_PLUGIN_DIR . '/front/process.php';
require_once WPS3_PLUGIN_DIR . '/classes/magic.php';
