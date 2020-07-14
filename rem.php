<?php
/**
 * Plugin Name: Real Estate Manager Pro
 * Plugin URI: https://webcodingplace.com/real-estate-manager-wordpress-plugin/
 * Description: A complete solution for real estate management
 * Version: 10.7.4
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: real-estate-manager
 * Domain Path: /languages
 */

require_once('core.functions.php');
require_once( REM_PATH.'/classes/setup.class.php' );
require_once( REM_PATH.'/classes/shortcodes.class.php');
require_once( REM_PATH.'/classes/hooks.class.php');
require_once( REM_PATH.'/classes/routes.class.php');
require_once( REM_PATH.'/classes/emails.class.php');
require_once( REM_PATH.'/classes/blocks.class.php');
require_once( REM_PATH.'/classes/widgets/mortgage-calculator.php');
require_once( REM_PATH.'/classes/widgets/search-properties.php');
require_once( REM_PATH.'/classes/widgets/tags-cloud.php');
require_once( REM_PATH.'/classes/widgets/recent-properties.php');

/**
 * Iniliatizing main class object for setting up listing system
 */
if( class_exists('WCP_Real_Estate_Management')){
    $rem_ob = new WCP_Real_Estate_Management;
    register_activation_hook( __FILE__, array( 'WCP_Real_Estate_Management', 'rem_activated' ) );
}

/**
 * Initilaizing Shortcodes and WP Bakery Page Builder (Visual Composer) Components 
 */
if( class_exists('REM_Shortcodes')){
    $rem_sc_ob = new REM_Shortcodes;
}

/**
 * Initializing Custom hooks (actions + filters)
 */
if( class_exists('REM_Hooks')){
    $rem_hk_ob = new REM_Hooks;
}

/**
 * Initializing REST Routes
 */
if( class_exists('REM_REST_ROUTES')){
    $rem_rest_routes = new REM_REST_ROUTES;
}

/**
 * Initializing Emails
 */
if( class_exists('REM_Emails_Management')){
    $rem_emails_manager = new REM_Emails_Management;
}

/**
 * Initializing Gutenberg Blocks
 */
if( class_exists('REM_Gutenberg_Blocks')){
    $rem_blocks = new REM_Gutenberg_Blocks;
}
?>