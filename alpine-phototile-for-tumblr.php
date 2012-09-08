<?php
/*
Plugin Name: Alpine PhotoTile for Tumblr
Plugin URI: http://thealpinepress.com/alpine-phototile-for-tumblr/
Description: The Alpine PhotoTile for Tumblr is the first plugin in a series intended to create a means of retrieving photos from various popular sites and displaying them in a stylish and uniform way. The plugin is capable of retrieving photos from a particular Tumblr user, a group, a set, or the Tumblr community. This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek presentation that I hope you will like.
Version: 1.1.1.3
Author: the Alpine Press
Author URI: http://thealpinepress.com/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html


*/

/* ******************** DO NOT edit below this line! ******************** */

/* Prevent direct access to the plugin */
if (!defined('ABSPATH')) {
	exit(__( "Sorry, you are not allowed to access this page directly.", APTFTbyTAP_DOMAIN ));
}

/* Pre-2.6 compatibility to find directories */
if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );


/* Set constants for plugin */
define( 'APTFTbyTAP_URL', WP_PLUGIN_URL.'/'. basename(dirname(__FILE__)) . '' );
define( 'APTFTbyTAP_DIR', WP_PLUGIN_DIR.'/'. basename(dirname(__FILE__)) . '' );
define( 'APTFTbyTAP_CACHE', WP_CONTENT_DIR . '/cache/' . basename(dirname(__FILE__)) . '' );
define( 'APTFTbyTAP_VER', '1.1.1.3' );
define( 'APTFTbyTAP_DOMAIN', 'APTFTbyTAP_domain' );
define( 'APTFTbyTAP_HOOK', 'APTFTbyTAP_hook' );
define( 'APTFTbyTAP_SETTINGS', basename(dirname(__FILE__)).'-settings' );
define( 'APTFTbyTAP_NAME', 'Alpine PhotoTile for Tumblr' );
//####### DO NOT CHANGE #######//
define( 'APTFTbyTAP_SHORT', 'alpine-phototile-for-tumblr' );
define( 'APTFTbyTAP_ID', 'APTFF_by_TAP' );
//#############################//
define( 'APTFTbyTAP_INFO', 'http://thealpinepress.com/alpine-phototile-for-tumblr/' );

register_deactivation_hook( __FILE__, 'TAP_PhotoTile_Tumblr_remove' );
function TAP_PhotoTile_Tumblr_remove(){
  if ( class_exists( 'theAlpinePressSimpleCacheV2' ) && APTFTbyTAP_CACHE ) {
    $cache = new theAlpinePressSimpleCacheV2();  
    $cache->setCacheDir( APTFTbyTAP_CACHE );
    $cache->clearAll();
  }
}
// Register Widget
function APTFTbyTAP_widget_register() {register_widget( 'Alpine_PhotoTile_for_Tumblr' );}
add_action('widgets_init','APTFTbyTAP_widget_register');

include_once( APTFTbyTAP_DIR.'/gears/function-display.php');
include_once( APTFTbyTAP_DIR.'/gears/source-tumblr.php');
include_once( APTFTbyTAP_DIR.'/gears/plugin-widget.php'); 
include_once( APTFTbyTAP_DIR.'/gears/plugin-shortcode.php');
include_once( APTFTbyTAP_DIR.'/gears/function-cache.php');
include_once( APTFTbyTAP_DIR.'/gears/plugin-scripts.php');
include_once( APTFTbyTAP_DIR.'/gears/plugin-options.php');
  
include_once( APTFTbyTAP_DIR.'/admin/functions-admin-options-page.php'); 
 
    
?>
