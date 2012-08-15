<?php
/*
Plugin Name: Alpine PhotoTile for Tumblr
Plugin URI: http://thealpinepress.com/alpine-phototile-for-tumblr/
Description: The Alpine PhotoTile for Tumblr is one plugin in a series that creates a way of retrieving photos from various popular sites and displaying them in a stylish and uniform way. The plugin is capable of retrieving photos from a particular Tumblr user or custom Tumblr URL. This lightweight but powerful widget takes advantage of WordPress's built in JQuery scripts to create a sleek presentation that I hope you will like.
Version: 1.0.2
Author: the Alpine Press
Author URI: http://thealpinepress.com/


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
define( 'APTFTbyTAP_VER', '1.0.2' );
define( 'APTFTbyTAP_DOMAIN', 'APTFTbyTAP_domain' );
define( 'APTFTbyTAP_HOOK', 'APTFTbyTAP_hook' );
define( 'APTFTbyTAP_INFO', 'http://thealpinepress.com/alpine-phototile-for-tumblr/' );

register_deactivation_hook( __FILE__, 'TAP_PhotoTile_Tumblr_remove' );
function TAP_PhotoTile_Tumblr_remove(){
  $cache = new theAlpinePressSimpleCache();  
  $cache->setCacheDir( APTFTbyTAP_CACHE );
  $cache->clearAll();
}

// Register Widget
function APTFTbyTAP_widget_register() {register_widget( 'Alpine_PhotoTile_for_Tumblr' );}
add_action('widgets_init','APTFTbyTAP_widget_register');
  
class Alpine_PhotoTile_for_Tumblr extends WP_Widget {

	function Alpine_PhotoTile_for_Tumblr() {
		$widget_ops = array('classname' => 'APTFTbyTAP_widget', 'description' => __('Add images from Tumblr to your sidebar'));
		$control_ops = array('width' => 550, 'height' => 350);
		$this->WP_Widget(APTFTbyTAP_DOMAIN, __('Alpine PhotoTile for Tumblr'), $widget_ops, $control_ops);
	}
  
	function widget( $args, $options ) {
		extract($args);
        
    // Set Important Widget Options    
    $id = $args["widget_id"];
    $defaults = APTFTbyTAP_option_defaults();
    
    $source_results = APTFTbyTAP_photo_retrieval($id, $options, $defaults);
    
    echo $before_widget . $before_title . $options['widget_title'] . $after_title;
    echo $source_results['hidden'];
    if( $source_results['continue'] ){  
      switch ($options['style_option']) {
        case "vertical":
          APTFTbyTAP_display_vertical($id, $options, $source_results);
        break;
        case "windows":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break; 
        case "bookshelf":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "rift":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "floor":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "wall":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "cascade":
          APTFTbyTAP_display_cascade($id, $options, $source_results);
        break;
        case "gallery":
          APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
      }
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    else{
      echo 'Sorry:<br>'.$source_results['message'];
    }
    echo $after_widget;
    
  }
    
	function update( $newoptions, $oldoptions ) {
    $optiondetails = APTFTbyTAP_option_defaults();
    foreach( $newoptions as $id=>$input ){
      $options[$id] = theAlpinePressMenuOptionsValidateV1( $input,$oldoptions[$id],$optiondetails[$id] );
    }
    return $options;
	}

	function form( $options ) {

    include( 'admin/widget-menu-form.php'); 

	}
}

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////  Safely Enqueue Scripts  and Register Widget  ////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  // Load Admin JS and CSS
	function APTFTbyTAP_admin_head_script(){ 
    // TODO - CREATE SEPERATE FUNCTIONS TO LOAD ADMIN PAGE AND WIDGET PAGE SCRIPTS
    wp_enqueue_script( 'jquery');
    // Replication Error caused by not loading new version of JS and CSS
    // Fix by always changing version number if changes were made
    wp_deregister_script('APTFTbyTAP_widget_menu');
    wp_register_script('APTFTbyTAP_widget_menu',APTFTbyTAP_URL.'/js/aptftbytap_widget_menu.js','',APTFTbyTAP_VER);
    wp_enqueue_script('APTFTbyTAP_widget_menu');
        
    wp_deregister_style('APTFTbyTAP_admin_css');   
    wp_register_style('APTFTbyTAP_admin_css',APTFTbyTAP_URL.'/css/aptftbytap_admin_style.css','',APTFTbyTAP_VER);
    wp_enqueue_style('APTFTbyTAP_admin_css');
    
    add_action('admin_print_footer_scripts', 'APTFTbyTAP_menu_toggles');
    
    // Only admin can trigger two week cache cleaning
    $cache = new theAlpinePressSimpleCacheV1();
    $cache->setCacheDir( APTFTbyTAP_CACHE );
    $cache->clean();
	}
  add_action('admin_enqueue_scripts', 'APTFTbyTAP_admin_head_script'); // admin_init so that it is ready when page loads
  
  function APTFTbyTAP_menu_toggles(){
    ?>
    <script type="text/javascript">
    if( jQuery().APTFTbyTAPWidgetMenuPlugin  ){
      jQuery(document).ready(function(){
        jQuery('.APTFTbyTAP-tumblr .APTFTbyTAP-parent').APTFTbyTAPWidgetMenuPlugin();
        
        jQuery(document).ajaxComplete(function() {
          jQuery('.APTFTbyTAP-tumblr .APTFTbyTAP-parent').APTFTbyTAPWidgetMenuPlugin();
        });
      });
    }
    </script>  
    <?php   
  }
  
  // Load Display JS and CSS
  function APTFTbyTAP_enqueue_display_scripts() {
    wp_enqueue_script( 'jquery' );
    
    wp_deregister_script('APTFTbyTAP_tiles');
    wp_enqueue_script('APTFTbyTAP_tiles',APTFTbyTAP_URL.'/js/aptftbytap_tiles.js','',APTFTbyTAP_VER);
    
    wp_deregister_style('APTFTbyTAP_widget_css'); // Since I wrote the scripts, deregistering and updating version are redundant in this case
    wp_register_style('APTFTbyTAP_widget_css',APTFTbyTAP_URL.'/css/aptftbytap_widget_style.css','',APTFTbyTAP_VER);
    wp_enqueue_style('APTFTbyTAP_widget_css');
    
  }
  add_action('wp_enqueue_scripts', 'APTFTbyTAP_enqueue_display_scripts');
   
  include_once( 'admin/widget-options.php');
  include_once( 'admin/function-options-display.php'); 
  include_once( 'admin/function-options-sanitize.php'); 
  include_once( 'gears/source-tumblr.php');
  include_once( 'gears/display-functions.php');
  include_once( 'gears/function-cache.php');
    
?>
