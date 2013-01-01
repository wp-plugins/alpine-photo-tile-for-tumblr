<?php
/**
 * Alpine PhotoTile for Tumblr: Options Page
 *
 * @since 1.1.1
 *
 */

/**
 * Setup the Theme Admin Settings Page
 * 
 */
function APTFTbyTAP_admin_options() {
	$page = add_options_page(__('Alpine Tiles: Tumblr',APTFTbyTAP_SETTINGS), __('Alpine Tiles: Tumblr',APTFTbyTAP_SETTINGS), 'manage_options', APTFTbyTAP_SETTINGS , 'APTFTbyTAP_admin_options_page');
  
  /* Using registered $page handle to hook script load */
  add_action('admin_print_scripts-' . $page, 'APTFTbyTAP_enqueue_admin_scripts');
}
// Load the Admin Options page
add_action('admin_menu', 'APTFTbyTAP_admin_options');


/**
 * Settings Page Markup
 */
function APTFTbyTAP_admin_options_page() { 
  if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

	$currenttab = APTFTbyTAP_get_current_tab();
	$settings_section = 'APTFTbyTAP_' . $currenttab . '_tab';
  $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );
  
  if( 'generator' == $currenttab ){
    $options = get_option( APTFTbyTAP_SETTINGS."_generator" );
  }else{
    $options = get_option( APTFTbyTAP_SETTINGS );
  }
  
  if( $submitted ){
    $oldoptions = $options;
    $newoptions = $_POST;
    $optiondetails = APTFTbyTAP_option_defaults();  
    if ( function_exists( 'APTFTbyTAP_MenuOptionsValidate' ) ) {
      foreach( $optiondetails as $id=>$input ){
        $options[$id] = APTFTbyTAP_MenuOptionsValidate( $newoptions[$id],$oldoptions[$id],$optiondetails[$id] );
      }
    }else{
      $options = $newoptions;
    }
    
    if( 'generator' == $currenttab ){
      update_option( APTFTbyTAP_SETTINGS."_generator", $options);
    }else{
      update_option( APTFTbyTAP_SETTINGS, $options);
    }

    if( 'generator' == $currenttab ) {
      $short = APTFTbyTAP_generate_shortcode( $options, $optiondetails );
    }
  }
  
    
	?>

	<div class="wrap APTFTbyTAP_settings_wrap">
		<?php APTFTbyTAP_admin_options_page_tabs( $currenttab ); ?>
		<?php if ( isset( $_GET['settings-updated'] ) ) {
    			echo "<div class='updated'><p>Theme settings updated successfully.</p></div>";
		} ?>
    <?php if( 'general' == $currenttab ){ ?>
      <?php
      APTFTbyTAP_display_general();
      ?>
    <?php }else{ ?>
		<form action="" method="post">
    <input type="hidden" name="hidden" value="Y">
      <?php 
      APTFTbyTAP_display_options_form($options,$currenttab,$short);
      ?>
		</form>
    <?php } ?>
	</div>
<?php 
}

/**
 * Get current settings page tab
 */
function APTFTbyTAP_get_current_tab( $current = 'general' ) {
    if ( isset ( $_GET['tab'] ) ) :
        $current = $_GET['tab'];
    else:
        $current = 'general';
    endif;
	
	return $current;
}

/**
 * Define Settings Page Tab Markup
 * 
 * @link`http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs	Daniel Tara
 */
function APTFTbyTAP_admin_options_page_tabs( $current = 'general' ) {

    $tabs = APTFTbyTAP_get_settings_page_tabs();
    $links = array();
    
    foreach( $tabs as $tab ) :
		$tabname = $tab['name'];
		$tabtitle = $tab['title'];
        if ( $tabname == $current ) :
            $links[] = "<a class='nav-tab nav-tab-active' href='?page=".APTFTbyTAP_SETTINGS."&tab=$tabname'>$tabtitle</a>";
        else :
            $links[] = "<a class='nav-tab' href='?page=".APTFTbyTAP_SETTINGS."&tab=$tabname'>$tabtitle</a>";
        endif;
    endforeach;
    
    echo '<div style="width:100%;display:block;padding:0;line-height:2.6em;"><div class="icon32 icon-alpine"><br></div><h2>'.APTFTbyTAP_NAME.'</h2></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
        echo $link;
    echo '</h2>';
    
}


/**
 * Separate settings by tab
 */
function APTFTbyTAP_get_settings_by_tab() {
	$tabs = APTFTbyTAP_get_settings_page_tabs();
	$tabnames = array();
	foreach ( $tabs as $tab ) {
		$tabname = $tab['name'];
		$tabnames[] = $tabname;
	}
	$settingsbytab = $tabnames;
	$default_options = APTFTbyTAP_option_defaults();
	foreach ( $default_options as $default_option ) {
		if ( 'internal' != $default_option['type'] ) {
			$optiontab = $default_option['tab'];
			$optionname = $default_option['name'];
			$settingsbytab[$optiontab][] = $optionname;
		}
	}
	return $settingsbytab;
}


/**
 * Plugin Admin Settings Page Tabs
 * 
 */
function APTFTbyTAP_get_settings_page_tabs() {
	
	$tabs = array( 
    'general' => array(
			'name' => 'general',
			'title' => 'General',
		),
    'generator' => array(
			'name' => 'generator',
			'title' => 'Generator',
		),
    'plugin-settings' => array(
			'name' => 'plugin-settings',
			'title' => 'Plugin Settings',
		)
  );
	return $tabs;
}


function APTFTbyTAP_display_options_form($options,$currenttab,$short){
  $widget_container = ( 'APTFTbyTAP-tumblr' ); 
  $defaults = APTFTbyTAP_option_defaults();
  if( 'generator' == $currenttab ) {
    $positions = APTFTbyTAP_shortcode_option_positions();
    ?> 
    <br><input name="<?php echo APTFTbyTAP_SETTINGS.'_'.$currenttab ?>[submit-<?php echo $currenttab; ?>]" type="submit" class="button-primary" value="Generate Shortcode" /> 
    <?php
    if($short){
      echo '<div id="'.APTFTbyTAP_SETTINGS.'-shortcode" style="margin:10px 0 0 0;" ><div style="padding:5px;margin:10px 0;display:inline-block;position:relative;background-color:#FFFFE0;border:1px solid #E6DB55;"> Now, copy (Crtl+C) and paste (Crtl+P) the following shortcode into a page or post. </div>';
      
      echo '<div><textarea class="auto_select" style="height:auto;width:100%;max-width:700px;background:#E0E0E0;padding:10px;">'.$short.'</textarea></div><br clear="all"/></div>';
    }
  }elseif( 'plugin-settings' == $currenttab ){
    $positions = APTFTbyTAP_admin_option_positions();
  }
  ?>

  <div id="<?php echo $widget_container ?>" class="APTFTbyTAP-tumblr <?php echo $currenttab ?>">
  <?php
  

 
  if( count($positions) && function_exists( 'APTFTbyTAP_AdminDisplayCallback' ) ){
    foreach( $positions as $position=>$positionsinfo){
    ?>
      <div class="<?php echo $position ?>"> 
        <?php if( $positionsinfo['title'] ){ ?><h4><?php echo $positionsinfo['title']; ?></h4><?php } ?>
        <table class="form-table">
          <tbody>
            <?php
            if( count($positionsinfo['options']) ){
              foreach( $positionsinfo['options'] as $optionname ){
                $option = $defaults[$optionname];
                $fieldname = ( $option['name'] );
                $fieldid = ( $option['name'] );

                if( 'generator' == $currenttab ){
                  if($option['parent']){
                    $class = $option['parent'];
                  }elseif($option['child']){
                    $class =($option['child']);
                  }else{
                    $class = ('unlinked');
                  }
                  $trigger = ($option['trigger']?('data-trigger="'.(($option['trigger'])).'"'):'');
                  $hidden = ($option['hidden']?' '.$option['hidden']:'');
                  
                  ?> <tr valign="top"> <td class="<?php echo $class; ?><?php echo $hidden; ?>" <?php echo $trigger; ?>><?php
                    APTFTbyTAP_MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                  ?> </td></tr> <?php     
                }else{
                  ?> <tr valign="top"> <td><?php
                    APTFTbyTAP_AdminDisplayCallback($options,$option,$fieldname,$fieldid);
                  ?> </td></tr> <?php   
                }     
              }
            }?>
          </tbody>  
        </table>
      </div>
    <?php
    }
  }
  ?>
  <div class="help-link"><span><?php _e('Need Help? Visit ') ?><a href="<?php echo APTFTbyTAP_INFO; ?>" target="_blank">the Alpine Press</a> <?php _e('for more about this plugin.') ?></span></div>
  </div> 

  <?php
  if( 'generator' == $currenttab ) {
    ?> <input name="<?php echo APTFTbyTAP_SETTINGS.'_'.$currenttab ?>[submit-<?php echo $currenttab; ?>]" type="submit" class="button-primary" value="Generate Shortcode" /> <?php
  }elseif( 'plugin-settings' == $currenttab ){
    ?> <input name="<?php echo APTFTbyTAP_SETTINGS.'_'.$currenttab ?>[submit-<?php echo $currenttab; ?>]" type="submit" class="button-primary" value="Save Settings" /> <?php
  }

}


function APTFTbyTAP_display_general(){ 
  ?>
  <div class="APTFTbyTAP-tumblr" style="max-width:700px;padding:10px;">
    <p>
    <?php _e("Thank you for downloading the Alpine PhotoTile for Tumblr, a WordPress plugin by the Alpine Press. On the 'Generator' tab you will find an easy to use shortcode generator that will allow you to insert the PhotoTile plugin in posts and pages. The 'Plugin Settings' tab provides additional back-end options (currently limited to Cache Options). Finally, I am a one man programming team and so if you notice any errors or places for improvement, please let me know."); ?>
    <div><p><?php _e('If you liked this plugin, try out some of the other plugins by ') ?><a href="http://thealpinepress.com/category/plugins/" target="_blank">the Alpine Press</a><?php _e(' and rate us at ') ?><a href="http://wordpress.org/extend/plugins/alpine-photo-tile-for-tumblr/" target="_blank">WordPress.org</a>.</p></div>
    <div class="help-link"><p><?php _e('Need Help? Visit ') ?><a href="<?php echo APTFTbyTAP_INFO; ?>" target="_blank">the Alpine Press</a> <?php _e('for more about this plugin.') ?></p></div>
    </p>
  </div>
  <?php
}



?>