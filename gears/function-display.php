<?php
/**
 * Alpine PhotoTile for Tumblr: Style Display Functions
 *
 * @since 1.0.0
 */
 
 
function APTFTbyTAP_display_vertical($id, $options, $source_results){
  $APTFTbyTAP_linkurl = $source_results['image_perms'];
  $APTFTbyTAP_photocap = $source_results['image_captions'];
  $APTFTbyTAP_photourl = $source_results['image_urls'];
  $APTFTbyTAP_user_link = $source_results['user_link'];

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  if($options['tumblr_photo_number'] != count($APTFTbyTAP_linkurl)){$options['tumblr_photo_number']=count($APTFTbyTAP_linkurl);}
  
  for($i = 0;$i<count($APTFTbyTAP_photocap);$i++){
    $APTFTbyTAP_photocap[$i] = str_replace('"','',$APTFTbyTAP_photocap[$i]);
  }
  
  if($APTFTbyTAP_reduced_width && $APTFTbyTAP_reduced_width<$APTFTbyTAP_size ){
    $APTFTbyTAP_style_width = $APTFTbyTAP_reduced_width."px";   }
  else{   $APTFTbyTAP_style_width = $APTFTbyTAP_size."px";    }
  
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    
  $output .= '<div id="'.$id.'-APTFTbyTAP_container" class="APTFTbyTAP_container_class">';     
  
  // Align photos
  $output .= '<div id="'.$id.'-vertical-parent" class="APTFTbyTAP_parent_class" style="width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
  if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
    $output .= 'margin:0px auto;text-align:center;';
  }
  else{
    $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
  } 
  $output .= '">';
  
  $shadow = ($options['style_shadow']?'APTFTbyTAP-img-shadow':'APTFTbyTAP-img-noshadow');
  $border = ($options['style_border']?'APTFTbyTAP-img-border':'APTFTbyTAP-img-noborder');
  $curves = ($options['style_curve_corners']?'APTFTbyTAP-img-corners':'APTFTbyTAP-img-nocorners');
  $highlight = ($options['style_highlight']?'APTFTbyTAP-img-highlight':'APTFTbyTAP-img-nohighlight');
  
  for($i = 0;$i<$options['tumblr_photo_number'];$i++){
    if( $options['tumblr_image_link'] ){ $output .= '<a href="' . $APTFTbyTAP_linkurl[$i] . '" class="APTFTbyTAP-vertical-link" target="_blank" title='."'". $APTFTbyTAP_photocap[$i] ."'".'>'; }
    $output .= '<img id="'.$id.'-tile-'.$i.'" class="APTFTbyTAP-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $APTFTbyTAP_photourl[$i] . '" ';
    $output .= 'title='."'". $APTFTbyTAP_photocap[$i] ."'".' alt='."'". $APTFTbyTAP_photocap[$i] ."' "; // Careful about caps with ""
    $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme
    if( $options['tumblr_image_link'] ){ $output .= '</a>'; }
  }
  
  $APTFTbyTAP_by_link  =  '<div id="'.$id.'-by-link" class="APTFTbyTAP-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
  if( !$options['widget_disable_credit_link'] ){
    $output .=  $APTFTbyTAP_by_link;    
  }          
  // Close vertical-parent
  $output .= '</div>';    

  if($APTFTbyTAP_user_link){ 
    $output .= '<div id="'.$id.'-display-link" class="APTFTbyTAP-display-link-container" ';
    $output .= 'style="text-align:' . $options['widget_alignment'] . ';">'.$APTFTbyTAP_user_link.'</div>'; // Only breakline if floating
  }

  // Close container
  $output .= '</div>';
  $output .= '<div class="APTFTbyTAP_breakline"></div>';
  
  $highlight = APTFTbyTAP_get_option("general_highlight_color");
  $highlight = ($highlight?$highlight:'#64a2d8');

  if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight'] ){
    $output .= '<script>
         jQuery(window).load(function() {
            if(jQuery().APTFTbyTAPAdjustBordersPlugin ){
              jQuery("#'.$id.'-vertical-parent").APTFTbyTAPAdjustBordersPlugin({
                highlight:"'.$highlight.'",
              });
            }  
          });
        </script>';  
  }
    
  return $output;
}  

function APTFTbyTAP_display_cascade($id, $options, $source_results){
  $APTFTbyTAP_linkurl = $source_results['image_perms'];
  $APTFTbyTAP_photocap = $source_results['image_captions'];
  $APTFTbyTAP_photourl = $source_results['image_urls'];
  $APTFTbyTAP_user_link = $source_results['user_link'];

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  if($options['tumblr_photo_number'] != count($APTFTbyTAP_linkurl)){$options['tumblr_photo_number']=count($APTFTbyTAP_linkurl);}
  
  for($i = 0;$i<count($APTFTbyTAP_photocap);$i++){
    $APTFTbyTAP_photocap[$i] = str_replace('"','',$APTFTbyTAP_photocap[$i]);
  }
  
  if($APTFTbyTAP_reduced_width && $APTFTbyTAP_reduced_width<$APTFTbyTAP_size ){
    $APTFTbyTAP_style_width = $APTFTbyTAP_reduced_width."px";   }
  else{   $APTFTbyTAP_style_width = $APTFTbyTAP_size."px";    }
  
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          
  $output .= '<div id="'.$id.'-APTFTbyTAP_container" class="APTFTbyTAP_container_class">';     
  
  // Align photos
  $output .= '<div id="'.$id.'-cascade-parent" class="APTFTbyTAP_parent_class" style="width:100%;max-width:'.$options['widget_max_width'].'%;padding:0px;';
  if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
    $output .= 'margin:0px auto;text-align:center;';
  }
  else{
    $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
  } 
  $output .= '">';
  
  $shadow = ($options['style_shadow']?'APTFTbyTAP-img-shadow':'APTFTbyTAP-img-noshadow');
  $border = ($options['style_border']?'APTFTbyTAP-img-border':'APTFTbyTAP-img-noborder');
  $curves = ($options['style_curve_corners']?'APTFTbyTAP-img-corners':'APTFTbyTAP-img-nocorners'); 
  $highlight = ($options['style_highlight']?'APTFTbyTAP-img-highlight':'APTFTbyTAP-img-nohighlight');
  
  for($col = 0; $col<$options['style_column_number'];$col++){
    $output .= '<div class="APTFTbyTAP_cascade_column" style="width:'.(100/$options['style_column_number']).'%;float:left;margin:0;">';
    $output .= '<div class="APTFTbyTAP_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
    for($i = $col;$i<$options['tumblr_photo_number'];$i+=$options['style_column_number']){
      if( $options['tumblr_image_link'] ){ $output .= '<a href="' . $APTFTbyTAP_linkurl[$i] . '" class="APTFTbyTAP-cascade-link" target="_blank" title='."'". $APTFTbyTAP_photocap[$i] ."'".'>'; }
      $output .= '<img id="'.$id.'-tile-'.$i.'" class="APTFTbyTAP-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $APTFTbyTAP_photourl[$i] . '" ';
      $output .= 'title='."'". $APTFTbyTAP_photocap[$i] ."'".' alt='."'". $APTFTbyTAP_photocap[$i] ."' "; // Careful about caps with ""
      $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme
      if( $options['tumblr_image_link'] ){ $output .= '</a>'; }
    }
    $output .= '</div></div>';
  }
  
  $output .= '<div class="APTFTbyTAP_breakline"></div>';
    
  $APTFTbyTAP_by_link  =  '<div id="'.$id.'-by-link" class="APTFTbyTAP-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';      
  if( !$options['widget_disable_credit_link'] ){
    $output .=  $APTFTbyTAP_by_link;    
  }          
  // Close cascade-parent
  $output .= '</div>';    

  $output .= '<div class="APTFTbyTAP_breakline"></div>';
  
  if($APTFTbyTAP_user_link){ 
    if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
      $output .= '<div id="'.$id.'-display-link" class="APTFTbyTAP-display-link-container" ';
      $output .= 'style="width:100%;margin:0px auto;">'.$APTFTbyTAP_user_link.'</div>';
    }
    else{
      $output .= '<div id="'.$id.'-display-link" class="APTFTbyTAP-display-link-container" ';
      $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$APTFTbyTAP_user_link.'</center></div>'; // Only breakline if floating
    } 
  }

  // Close container
  $output .= '</div>';
  $output .= '<div class="APTFTbyTAP_breakline"></div>';
 
  $highlight = APTFTbyTAP_get_option("general_highlight_color");
  $highlight = ($highlight?$highlight:'#64a2d8');
  
  if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight'] ){
    $output .= '<script>
            jQuery(window).load(function() {
              if(jQuery().APTFTbyTAPAdjustBordersPlugin ){
                jQuery("#'.$id.'-cascade-parent").APTFTbyTAPAdjustBordersPlugin({
                  highlight:"'.$highlight.'",
                });
              }  
            });
          </script>';
  }
  return $output;  
}


function APTFTbyTAP_display_hidden($id, $options, $source_results){
  $APTFTbyTAP_linkurl = $source_results['image_perms'];
  $APTFTbyTAP_photocap = $source_results['image_captions'];
  $APTFTbyTAP_photourl = $source_results['image_urls'];
  $APTFTbyTAP_user_link = $source_results['user_link'];
  $APTFTbyTAP_originalurl = $source_results['image_originals'];

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  if($options['tumblr_photo_number'] != count($APTFTbyTAP_linkurl)){$options['tumblr_photo_number']=count($APTFTbyTAP_linkurl);}
  
  for($i = 0;$i<count($APTFTbyTAP_photocap);$i++){
    $APTFTbyTAP_photocap[$i] = str_replace('"','',$APTFTbyTAP_photocap[$i]);
  }
  
  if($APTFTbyTAP_reduced_width && $APTFTbyTAP_reduced_width<$APTFTbyTAP_size ){
    $APTFTbyTAP_style_width = $APTFTbyTAP_reduced_width."px";   }
  else{   $APTFTbyTAP_style_width = $APTFTbyTAP_size."px";    }
  
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
          
  $output .= '<div id="'.$id.'-APTFTbyTAP_container" class="APTFTbyTAP_container_class">';     
  
  // Align photos
  $output .= '<div id="'.$id.'-hidden-parent" class="APTFTbyTAP_parent_class" style="width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
  if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
    $output .= 'margin:0px auto;text-align:center;';
  }
  else{
    $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
  } 
  $output .= '">';
  
  $output .= '<div id="'.$id.'-image-list" class="APTFTbyTAP_image_list_class" style="display:none;visibility:hidden;">'; 
  
  $shadow = ($options['style_shadow']?'APTFTbyTAP-img-shadow':'APTFTbyTAP-img-noshadow');
  $border = ($options['style_border']?'APTFTbyTAP-img-border':'APTFTbyTAP-img-noborder');
  $curves = ($options['style_curve_corners']?'APTFTbyTAP-img-corners':'APTFTbyTAP-img-nocorners');
  
  for($i = 0;$i<$options['tumblr_photo_number'];$i++){
    if( $options['tumblr_image_link'] ){ $output .= '<a href="' . $APTFTbyTAP_linkurl[$i] . '" class="APTFTbyTAP-link" target="_blank" title='."'". $APTFTbyTAP_photocap[$i] ."'".'>'; }
    $output .= '<img id="'.$id.'-tile-'.$i.'" class="APTFTbyTAP-image '.$shadow.' '.$border.' '.$curves.'" src="' . $APTFTbyTAP_photourl[$i] . '" ';
    $output .= 'title='."'". $APTFTbyTAP_photocap[$i] ."'".' alt='."'". $APTFTbyTAP_photocap[$i] ."' "; // Careful about caps with ""
    $output .= 'border="0" hspace="0" vspace="0" />'; // Override the max-width set by theme
    
    // Load original image size
    if( "gallery" == $options['style_option'] && $APTFTbyTAP_originalurl[$i] ){
      $output .= '<img class="APTFTbyTAP-original-image" src="' . $APTFTbyTAP_originalurl[$i]. '" />';
    }
    
    if( $options['tumblr_image_link'] ){ $output .= '</a>'; }
  }
  $output .= '</div>';
  
  $APTFTbyTAP_by_link  =  '<div id="'.$id.'-by-link" class="APTFTbyTAP-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
  if( !$options['widget_disable_credit_link'] ){
    $output .=  $APTFTbyTAP_by_link;    
  }          
  // Close vertical-parent
  $output .= '</div>';      

  if($APTFTbyTAP_user_link){ 
    if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
      $output .= '<div id="'.$id.'-display-link" class="APTFTbyTAP-display-link-container" ';
      $output .= 'style="width:100%;margin:0px auto;">'.$APTFTbyTAP_user_link.'</div>';
    }
    else{
      $output .= '<div id="'.$id.'-display-link" class="APTFTbyTAP-display-link-container" ';
      $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$APTFTbyTAP_user_link.'</center></div>'; // Only breakline if floating
    } 
  }

  // Close container
  $output .= '</div>';
  $disable = APTFTbyTAP_get_option('general_loader');
  $highlight = APTFTbyTAP_get_option("general_highlight_color");
  $highlight = ($highlight?$highlight:'#64a2d8');
  
  $output .= '<script>';
  
  if(!$disable){
    $output .= '
           jQuery(document).ready(function() {
            jQuery("#'.$id.'-APTFTbyTAP_container").addClass("loading"); 
           });';
  }
  $output .= '
         jQuery(window).load(function() {
          jQuery("#'.$id.'-APTFTbyTAP_container").removeClass("loading");
          if( jQuery().APTFTbyTAPTilesPlugin ){
            jQuery("#'.$id.'-hidden-parent").APTFTbyTAPTilesPlugin({
              style:"'.($options['style_option']?$options['style_option']:'windows').'",
              shape:"'.($options['style_shape']?$options['style_shape']:'square').'",
              perRow:"'.($options['style_photo_per_row']?$options['style_photo_per_row']:'3').'",
              imageLink:'.($options['tumblr_image_link']?'1':'0').',
              imageBorder:'.($options['style_border']?'1':'0').',
              imageShadow:'.($options['style_shadow']?'1':'0').',
              imageCurve:'.($options['style_curve_corners']?'1':'0').',
              imageHighlight:'.($options['style_highlight']?'1':'0').',
              galleryHeight:'.($options['style_gallery_height']?$options['style_gallery_height']:'3').',
              highlight:"'.$highlight.'",
            });
          }
        });
      </script>';
      
  return $output; 
}

?>