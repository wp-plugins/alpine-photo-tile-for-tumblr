<?php
/**
 * Alpine PhotoTile for Tumblr: Widget Setup
 *
 * @since 1.1.1
 *
 */
 

class Alpine_PhotoTile_for_Tumblr extends WP_Widget {

	function Alpine_PhotoTile_for_Tumblr() {
		$widget_ops = array('classname' => 'APTFTbyTAP_widget', 'description' => __('Add images from Tumblr to your sidebar'));
		$control_ops = array('width' => 550, 'height' => 350);
		$this->WP_Widget(APTFTbyTAP_DOMAIN, __('Alpine PhotoTile for Tumblr'), $widget_ops, $control_ops);
	}
  
	function widget( $args, $options ) {
		extract($args);
    wp_enqueue_style('APTFTbyTAP_widget_css');
    wp_enqueue_script('APTFTbyTAP_tiles');
    
    // Set Important Widget Options    
    $id = $args["widget_id"];
    $defaults = APTFTbyTAP_option_defaults();
    
    $source_results = APTFTbyTAP_photo_retrieval($id, $options, $defaults);
    
    echo $before_widget . $before_title . $options['widget_title'] . $after_title;
    echo $source_results['hidden'];
    if( $source_results['continue'] ){  
      switch ($options['style_option']) {
        case "vertical":
          echo APTFTbyTAP_display_vertical($id, $options, $source_results);
        break;
        case "windows":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
        break; 
        case "bookshelf":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "rift":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "floor":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "wall":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
        break;
        case "cascade":
          echo APTFTbyTAP_display_cascade($id, $options, $source_results);
        break;
        case "gallery":
          echo APTFTbyTAP_display_hidden($id, $options, $source_results);
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
    if ( function_exists( 'APTFTbyTAP_MenuOptionsValidate' ) ) {
      foreach( $newoptions as $id=>$input ){
        $options[$id] = APTFTbyTAP_MenuOptionsValidate( $input,$oldoptions[$id],$optiondetails[$id] );
      }
    }else{
      $options = $newoptions;
    }
    return $options;
	}

	function form( $options ) {

    $widget_container = $this->get_field_id( 'APTFTbyTAP-tumblr' ); ?>

    <div id="<?php echo $widget_container ?>" class="APTFTbyTAP-tumblr">
    <?php
      $defaults = APTFTbyTAP_option_defaults();
      $positions = APTFTbyTAP_option_positions();
   
    if( count($positions) && function_exists( 'APTFTbyTAP_MenuDisplayCallback' ) ){
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
                  $fieldname = $this->get_field_name( $option['name'] );
                  $fieldid = $this->get_field_id( $option['name'] );
                  if($option['parent']){
                    $class = $option['parent'];
                  }elseif($option['child']){
                    $class = $this->get_field_id($option['child']);
                  }else{
                    $class = $this->get_field_id('unlinked');
                  }
                  $trigger = ($option['trigger']?('data-trigger="'.($this->get_field_id($option['trigger'])).'"'):'');
                  $hidden = ($option['hidden']?' '.$option['hidden']:'');
                  
                  ?> <tr valign="top"> <td class="<?php echo $class; ?><?php echo $hidden; ?>" <?php echo $trigger; ?>><?php
                    APTFTbyTAP_MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                  ?> </td></tr> <?php
                }
              }?>
            </tbody>  
          </table>
        </div>
      <?php
      }
    }
    ?>
    </div> 
    <div><span><?php _e('Need Help? Visit ') ?><a href="<?php echo APTFTbyTAP_INFO; ?>" target="_blank">the Alpine Press</a> <?php _e('for more about this plugin.') ?></span></div> 
    
    <?php
    
	}
}
  
  ?>
