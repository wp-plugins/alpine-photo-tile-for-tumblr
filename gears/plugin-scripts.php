<?php
/**
 * Alpine PhotoTile for Tumblr: Styles and Scripts
 *
 * @since 1.1.1
 *
 */
 
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////  Safely Enqueue Scripts  and Register Widget  ////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
  // Load Admin JS and CSS
	function APTFTbyTAP_admin_widget_script($hook){ 

    wp_deregister_script('APTFTbyTAP_widget_menu');
    wp_register_script('APTFTbyTAP_widget_menu',APTFTbyTAP_URL.'/js/aptftbytap_widget_menu.js','',APTFTbyTAP_VER);

    wp_deregister_style('APTFTbyTAP_admin_css');   
    wp_register_style('APTFTbyTAP_admin_css',APTFTbyTAP_URL.'/css/aptftbytap_admin_style.css','',APTFTbyTAP_VER);
        
    if( 'widgets.php' != $hook )
      return;
      
    wp_enqueue_script( 'jquery');
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
    
    wp_enqueue_script('APTFTbyTAP_widget_menu');
        
    wp_enqueue_style('APTFTbyTAP_admin_css');
    
    add_action('admin_print_footer_scripts', 'APTFTbyTAP_menu_toggles');
    
    // Only admin can trigger two week cache cleaning by visiting widgets.php
    $disablecache = APTFTbyTAP_get_option( 'cache_disable' );
    if ( class_exists( 'theAlpinePressSimpleCacheV2' ) && APTFTbyTAP_CACHE && !$disablecache ) {
      $cache = new theAlpinePressSimpleCacheV2();
      $cache->setCacheDir( APTFTbyTAP_CACHE );
      $cache->clean();
    }
	}
  add_action('admin_enqueue_scripts', 'APTFTbyTAP_admin_widget_script'); 
  
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
  function APTFTbyTAP_shortcode_select(){
    ?>
    <script type="text/javascript">
     jQuery(".auto_select").mouseenter(function(){
        jQuery(this).select();
      }); 
      if( jQuery('#<?php echo APTFTbyTAP_SETTINGS; ?>-shortcode') ){

        jQuery("html,body").animate({ scrollTop: (jQuery('#<?php echo APTFTbyTAP_SETTINGS; ?>-shortcode').offset().top-70) }, 2000);
      
      }

    </script>  
    <?php
  }
  // Load Display JS and CSS
  function APTFTbyTAP_enqueue_display_scripts() {
    wp_enqueue_script( 'jquery' );
    
    wp_deregister_script('APTFTbyTAP_tiles');
    wp_register_script('APTFTbyTAP_tiles',APTFTbyTAP_URL.'/js/aptftbytap_tiles.js','',APTFTbyTAP_VER);
    
    wp_deregister_style('APTFTbyTAP_widget_css'); // Since I wrote the scripts, deregistering and updating version are redundant in this case
    wp_register_style('APTFTbyTAP_widget_css',APTFTbyTAP_URL.'/css/aptftbytap_widget_style.css','',APTFTbyTAP_VER);
        
  }
  add_action('wp_enqueue_scripts', 'APTFTbyTAP_enqueue_display_scripts');
  
  
/**
 * Enqueue admin scripts (and related stylesheets)
 */
  function APTFTbyTAP_enqueue_admin_scripts() {

    wp_enqueue_script( 'jquery' );
    
    wp_enqueue_script('APTFTbyTAP_widget_menu');
    wp_enqueue_style('APTFTbyTAP_admin_css');
    
    add_action('admin_print_footer_scripts', 'APTFTbyTAP_menu_toggles'); 
    add_action('admin_print_footer_scripts', 'APTFTbyTAP_shortcode_select'); 
  }
?>