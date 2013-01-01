<?php


class PhotoTileForTumblrBot extends PhotoTileForTumblrBasic{  

   /**
   * Alpine PhotoTile for Tumblr: Photo Retrieval Function
   * The PHP for retrieving content from Tumblr.
   *
   * @since 1.0.0
   * @updated 1.2.1
   */
  function fetch_tumblr_feed($request){
    // No longer write out curl_init and user WP API instead
    $response = wp_remote_get($request,
      array(
        'method' => 'GET',
        'timeout' => 20
      )
    );
    if( is_wp_error( $response ) || !isset($response['body']) ) {
      return false;
    }else{
      return $response['body'];
    }
  }
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ///////////////////////////////////////////    Generate Image Content    ////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  function photo_retrieval($id, $tumblr_options){
    $defaults = $this->option_defaults();
    
    $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_user_id']) ? 'uid' : $tumblr_options['tumblr_user_id'], $tumblr_options );
    $tumblr_uid = @ereg_replace('[[:cntrl:]]', '', $tumblr_uid ); // remove ASCII's control characters
    $tumblr_custom_url = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? 'groupid' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
    $tumblr_custom_url = @ereg_replace('[[:cntrl:]]', '', $tumblr_custom_url ); // remove ASCII's control characters

    $tumblr_custom_url_safe = str_replace( array(".","\'","/"),'',$tumblr_custom_url);
    
    $key = 'tumblr-'.$this->vers.'-'.$tumblr_options['tumblr_source'].'-'.$tumblr_uid.'-'.$tumblr_custom_url_safe.'-'.$tumblr_options['tumblr_photo_number'].'-'.$tumblr_options['tumblr_display_link'].'-'.$tumblr_options['tumblr_photo_size'];

    
    $disablecache = $this->get_option( 'cache_disable' );
    if ( !$disablecache ) {
      if( $this->cacheExists($key) ) {
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          return $results;
        }
      }
    }
    
    $message = '';
    $hidden = '';
    $continue = false;
    $feed_found = false;
    $linkurl = array();
    $photocap = array();
    $photourl = array();
            
    // Determine image size id
    $size_id = 2;
    switch ($tumblr_options['tumblr_photo_size']) {
      case 75:
        $size_id = 5;
      break;
      case 100:
        $size_id = 4;
      break;
      case 250:
        $size_id = 3;
      break;
      case 400:
        $size_id = 2;
      break;
      case 500:
        $size_id = 1;
      break;
    } 
    
    
    // Retrieve content using wp_remote_get and JSON
    if ( function_exists('json_decode') ) {
      // @ is shut-up operator
      // For reference: http://www.tumblr.com/services/feeds/
      $repeats = floor($tumblr_options['tumblr_photo_number']/20);

      switch ($tumblr_options['tumblr_source']) {
      case 'user':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
        $request = 'http://api.tumblr.com/v2/blog/'.$tumblr_uid.'.tumblr.com/posts?api_key=GhKB8A19ZFhO3rWpBhjKfJUistNDgQwIYu6tHlzzg4pPT3WZwH';
      break;
      case 'custom':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $request = 'http://api.tumblr.com/v2/blog/'.$tumblr_uid.'/posts?api_key=GhKB8A19ZFhO3rWpBhjKfJUistNDgQwIYu6tHlzzg4pPT3WZwH';
      break;
      } 

      $result = $this->fetch_tumblr_feed($request);
      $_tumblr_json = array();
      if( $result ){ $_tumblr_json = @json_decode( $result ); }
      
      if( empty($_tumblr_json) || 200 != $_tumblr_json->meta->status ){
        $hidden .= '<!-- Failed using wp_remote_get and JSON @ '.$request.' -->';
        $continue = false;
      }else{
        for($loop=0;$loop<=$repeats;$loop++){
          $response = $_tumblr_json->response;
          $response_blog = $response->blog;
          $title = $response_blog->title;
          $link = $response_blog->url;
          $found = $response_blog->posts;
          
          $content =  $response->posts;

          $i = $loop*20;
          foreach($content as $post){
            if( $i<$tumblr_options['tumblr_photo_number'] ){
              $post_url = $post->post_url;
              $post_cap = $post->caption;
              if( $post_url && count($post->photos) ){
                foreach( $post->photos as $photo ){
                  if( $i<$tumblr_options['tumblr_photo_number'] ){
                    $sizes = $photo->alt_sizes;
                    $linkurl[$i] = $post_url;
                    $photocap[$i] = ''; //$post_cap;
                    $photourl[$i] = $sizes[$size_id]->url;
                    
                    $originalurl[$i] = $sizes[1]->url;
                    $photourl[$i] = $sizes[0]->url;
                    
                    foreach( $sizes as $currentsize ){
                      if( $currentsize->width >= $tumblr_options['tumblr_photo_size'] && $currentsize->url ){
                        $photourl[$i] = $currentsize->url;
                      }
                    }

                    $i++;
                  }
                }
              }
            }else{
              break;
            }
          }
          // Try another request
          if($loop<$repeats){
            $next_request = $request.'&offset='.(($loop+1)*20);
            $result = $this->fetch_tumblr_feed($next_request);
            $_tumblr_json = array();
            if( $result ){ $_tumblr_json = @json_decode( $result ); }

            if(empty($_tumblr_json) || 200 != $_tumblr_json->meta->status ){
              $hidden .= '<!-- Failed on loop '.$loop.' with '.$next_request.' -->';
              $loop = $repeats;
            }
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          if( $tumblr_options['tumblr_display_link'] ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link" >';
            $user_link .='<a href="'.$link.'" target="_blank" >';
            $user_link .= $title;
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;
          $hidden  .= '<!-- Success using wp_remote_get and JSON -->';
        }else{
          $hidden .= '<!-- No photos found using wp_remote_get and JSON @ '.$request.' -->';  
          $continue = false;
          $feed_found = true;
        }
      }
    }
    ///////////////////////////////////////////////////
    /// If nothing found, try using xml and rss_200 ///
    ///////////////////////////////////////////////////

    if ( $continue == false && function_exists('simplexml_load_file') ) {
      // Determine image size id
      $size_id = 2;
      switch ($tumblr_options['tumblr_photo_size']) {
        case 75:
          $size_id = 5;
        break;
        case 100:
          $size_id = 4;
        break;
        case 250:
          $size_id = 3;
        break;
        case 400:
          $size_id = 2;
        break;
        case 500:
          $size_id = 1;
        break;
      }  
      
      $repeats = floor($tumblr_options['tumblr_photo_number']/20);
      
      switch ($tumblr_options['tumblr_source']) {
      case 'user':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
        $request = 'http://' . $tumblr_uid . '.tumblr.com/api/read?number=' .$tumblr_options['tumblr_photo_number']. '&type=photo';
      break;
      case 'custom':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $request = 'http://' . $tumblr_uid . '/api/read?number=' .$tumblr_options['tumblr_photo_number']. '&type=photo';
      break;
      } 

      // XML doesn't seem to care if "www" is present or not
      $_tumblr_request  = @urlencode( $request );	// just for compatibility
      $_tumblr_xml = @simplexml_load_file( $_tumblr_request); // @ is shut-up operator

      if($_tumblr_xml===false){ 
        $hidden .= '<!-- Failed using simplexml_load_file() and XML @ '.$request.' -->';
        $continue = false;
      }else{
        $s = 0; // simple counter
        
        for($loop=0;$loop<=$repeats;$loop++){
          if( $_tumblr_xml && $_tumblr_xml->posts[0]) {
            foreach( $_tumblr_xml->posts[0]->post as $p ) {
              if( $s<$tumblr_options['tumblr_photo_number'] ){     
                // list of link urls
                $linkurl[$s] = (string) $p['url'];
                // list of photo urls
                $photourl[$s] = (string) $p->{"photo-url"}[$size_id];
                $originalurl[$s] = (string) $p->{"photo-url"}[1];
                $photocap[$s] = (string) $p["slug"];
                $s++;
              }else{
                break;
              }
            }
          }
          // Try another request
          if($loop<$repeats){
            $next_request = $request.'&start='.(($loop+1)*20);
            $_tumblr_request  = @urlencode( $next_request );	// just for compatibility
            $_tumblr_xml = @simplexml_load_file( $_tumblr_request); // @ is shut-up operator
            if($_tumblr_xml===false){ 
              $hidden .= '<!-- Failed on loop '.$loop.' with '.$next_request.' -->';
              $loop = $repeats;
            }          
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          // If set, generate tumblr link
          if( $tumblr_options['display-link'] ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link">';
            if( 'custom' == $tumblr_options['tumblr_source'] ){
              $user_link .='<a href="http://' . $tumblr_uid . '/" target="_blank" >';          
            }else{
              $user_link .='<a href="http://' . $tumblr_uid . '.tumblr.com/" target="_blank" >';
            }
            $user_link .= $_tumblr_xml->tumblelog[title];
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;    
          $hidden .= '<!-- Success using simplexml_load_file() and XML -->';
        }else{
          $hidden .= '<!-- No photos found using simplexml_load_file() and XML @ '.$request.' -->';  
          $continue = false;
        }
      }
    }
    
    ////////////////////////////////////////////////////////
    ////      If still nothing found, try using RSS      ///
    ////////////////////////////////////////////////////////
    if( $continue == false ) {
      // RSS may actually be safest approach since it does not require PHP server extensions,
      // but I had to build my own method for parsing SimplePie Object so I will keep it as the last option.
      
      if(!function_exists(APTFTbyTAP_specialarraysearch)){
        function APTFTbyTAP_specialarraysearch($array, $find){
          foreach ($array as $key=>$value){
            if( is_string($key) && $key==$find){
              return $value;
            }
            elseif(is_array($value)){
              $results = APTFTbyTAP_specialarraysearch($value, $find);
            }
            elseif(is_object($value)){
              $sub = $array->$key;
              $results = APTFTbyTAP_specialarraysearch($sub, $find);
            }
            // If found, return
            if(!empty($results)){return $results;}
          }
          return $results;
        }
      }
      
      switch ($tumblr_options['tumblr_source']) {
      case 'user':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
        $request = 'http://' . $tumblr_uid . '.tumblr.com/rss';
      break;
      case 'custom':
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
        $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
        $tumblr_uid = str_replace('http:','',$tumblr_uid );
        $request = 'http://' . $tumblr_uid . '/rss';
      break;
      } 
      
      include_once(ABSPATH . WPINC . '/feed.php');
      
      if( !function_exists('return_noCache') ){
        function return_noCache( $seconds ){
          // change the default feed cache recreation period to 30 seconds
          return 30;
        }
      }

      add_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );
      $rss = @fetch_feed( $request );
      remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );

      if (!is_wp_error( $rss ) && $rss != NULL ){ // Check that the object is created correctly 
        // Bulldoze through the feed to find the items 
        $results = array();
        $title = @APTFTbyTAP_specialarraysearch($rss,'title');
        $title = $title['0']['data'];
        $link = @APTFTbyTAP_specialarraysearch($rss,'link');
        $link = $link['0']['data'];
        $rss_data = @APTFTbyTAP_specialarraysearch($rss,'item');

        $s = 0; // simple counter
        if ($rss_data != NULL ){ // Check again
          foreach ( $rss_data as $item ) {
            if( $s<$tumblr_options['tumblr_photo_number'] ){
              $linkurl[$s] = $item['child']['']['link']['0']['data'];    
              $content = $item['child']['']['description']['0']['data'];     
              
              if($content){
                // For Reference: regex references and http://php.net/manual/en/function.preg-match.php
                // Using the RSS feed will require some manipulation to get the image url from tumblr;
                // preg_replace is bad at skipping lines so we'll start with preg_match
                // i sets letters in upper or lower case, s sets . to anything
                @preg_match("/<IMG.+?SRC=[\"']([^\"']+)/si",$content,$matches); // First, get image from feed.
                if($matches[ 0 ]){
                  // Next, strip away everything surrounding the source url.
                  // . means any expression and + means repeat previous
                  
                  $photourl_current = @preg_replace(array('/(.+)src="/i','/"(.+)/') , '',$matches[ 0 ]);
    
                  // Finally, change the size. 
                    // [] specifies single character and \w is any word character
                  $photourl[$s] = $photourl_current; //@preg_replace('/[_]500[.]/', "_".$tumblr_options['tumblr_photo_size'].".", $photourl_current );
                  $originalurl[$s] = $photourl_current;
                  // Could set the caption as blank instead of default "Photo", but currently not doing so.
                  $photocap[$s] = '';//$item['child']['']['title']['0']['data'];
                  $s++;
                }
              }
            }
            else{
              break;
            }
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          if( $tumblr_options['tumblr_display_link'] ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link" >';
            $user_link .='<a href="'.$link.'" target="_blank" >';
            $user_link .= $title;
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;
          $hidden .= '<!-- Success using fetch_feed() and RSS -->';
        }else{
          $hidden .= '<!-- No photos found using fetch_feed() and RSS @ '.$request.' -->';  
          $continue = false;
          $feed_found = true;
        }
      }
      else{
        $hidden .= '<!-- Failed using fetch_feed() and RSS @ '.$request.' -->';
        $continue = false;
      }      
    }
      
    ///////////////////////////////////////////////////////////////////////
    //// If STILL!!! nothing found, report that Tumblr ID must be wrong ///
    ///////////////////////////////////////////////////////////////////////
    if( false == $continue ) {
      if($feed_found ){
        $message .= '- Tumblr feed was successfully retrieved, but no photos found.';
      }else{
        $message .= '- Tumblr feed not found. Please recheck your ID.';
      }
    }
      
    $results = array('continue'=>$continue,'message'=>$message,'hidden'=>$hidden,'user_link'=>$user_link,'image_captions'=>$photocap,'image_urls'=>$photourl,'image_perms'=>$linkurl,'image_originals'=>$originalurl);
    
    if( true == $continue && !$disablecache ){     
      $cache_results = $results;
      if(!is_serialized( $cache_results  )) { $cache_results  = maybe_serialize( $cache_results ); }
      $this->putCache($key, $cache_results);
      $cachetime = $this->get_option( 'cache_time' );
      if( $cachetime && is_numeric($cachetime) ){
        $this->setExpiryInterval( $cachetime*60*60 );
      }
    }
    return $results;
  }
  
  
  
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 */
  function display_vertical($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['tumblr_photo_number'] != count($linkurl)){$options['tumblr_photo_number']=count($linkurl);}
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                      
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $highlight = ($options['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    for($i = 0;$i<$options['tumblr_photo_number'];$i++){
      $has_link = false;
      $link = $options['tumblr_image_link_option'];
      if( 'original' == $link && !empty($photourl[$i]) ){
        $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( ('tumblr' == $link || '1' == $link)&& !empty($linkurl[$i]) ){
        $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
        $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
        $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }     
      $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $photourl[$i] . '" ';
      $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
      $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme

      if( $has_link ){ $output .= '</a>'; }
    }
    
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $output .=  $by_link;    
    }          
    // Close vertical-parent
    $output .= '</div>';    

    if($userlink){ 
      $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
      $output .= 'style="text-align:' . $options['widget_alignment'] . ';">'.$userlink.'</div>'; // Only breakline if floating
    }

    // Close container
    $output .= '</div>';
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');

    if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight']  ){
      $output .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$id.'-vertical-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'",
                });
              }  
            });
          </script>';  
    }   
    if( $options['tumblr_image_link_option'] == "fancybox"  ){
      $output .= '<script>
                  jQuery(window).load(function() {
                    jQuery( "a[rel^=\'fancybox-'.$id.'\']" ).fancybox( { titleShow: false, overlayOpacity: .8, overlayColor: "#000" } );
                  })
                </script>';  
    } 
    return $output;
    
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 */
  function display_cascade($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['tumblr_photo_number'] != count($linkurl)){$options['tumblr_photo_number']= count($linkurl);}
    
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="width:100%;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners'); 
    $highlight = ($options['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    for($col = 0; $col<$options['style_column_number'];$col++){
      $output .= '<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$options['style_column_number']).'%;float:left;margin:0;">';
      $output .= '<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
      for($i = $col;$i<$options['tumblr_photo_number'];$i+=$options['style_column_number']){
        $has_link = false;
        $link = $options['tumblr_image_link_option'];
        if( 'original' == $link && !empty($photourl[$i]) ){
          $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
          $has_link = true;
        }elseif( ('tumblr' == $link || '1' == $link)&& !empty($linkurl[$i]) ){
          $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
          $has_link = true;
        }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
          $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
          $has_link = true;
        }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
          $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
          $has_link = true;
        }   
      
        $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.' '.$highlight.'" src="' . $photourl[$i] . '" ';
        $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
        $output .= 'border="0" hspace="0" vspace="0" style="margin:1px 0 5px 0;padding:0;max-width:100%;"/>'; // Override the max-width set by theme
        if( $has_link ){ $output .= '</a>'; }
      }
      $output .= '</div></div>';
    }
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
      
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';      
      $output .=  $by_link;    
    }          
    // Close cascade-parent
    $output .= '</div>';    

    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    if($userlink){ 
      if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; // Only breakline if floating
      } 
    }

    // Close container
    $output .= '</div>';
    $output .= '<div class="AlpinePhotoTiles_breakline"></div>';
   
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
        
    if( $options['style_shadow'] || $options['style_border'] || $options['style_highlight']  ){
      $output .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$id.'-cascade-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'",
                });
              }  
            });
          </script>';  
    }   
    if( $options['tumblr_image_link_option'] == "fancybox"  ){
      $output .= '<script>
                  jQuery(window).load(function() {
                    jQuery( "a[rel^=\'fancybox-'.$id.'\']" ).fancybox( { titleShow: false, overlayOpacity: .8, overlayColor: "#000" } );
                  })
                </script>';  
    } 
    return $output;
  }

/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 */
  function display_hidden($id, $options, $source_results){
    $linkurl = $source_results['image_perms'];
    $photocap = $source_results['image_captions'];
    $photourl = $source_results['image_urls'];
    $userlink = $source_results['user_link'];
    $originalurl = $source_results['image_originals'];

  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////////       Check Content      /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if($options['tumblr_photo_number'] != count($linkurl)){$options['tumblr_photo_number']=count($linkurl);}
        
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////   Begin the Content   /////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
    $output .= '<div id="'.$id.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
    // Align photos
    $output .= '<div id="'.$id.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;padding:0px;';
    if( 'center' == $options['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $output .= 'margin:0px auto;text-align:center;';
    }
    else{
      $output .= 'float:' . $options['widget_alignment'] . ';text-align:' . $options['widget_alignment'] . ';';
    } 
    $output .= '">';
    
    $output .= '<div id="'.$id.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'; 
    
    $shadow = ($options['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $border = ($options['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $curves = ($options['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    
    for($i = 0;$i<$options['tumblr_photo_number'];$i++){
      $has_link = false;
      $link = $options['tumblr_image_link_option'];
      if( 'original' == $link && !empty($photourl[$i]) ){
        $output .= '<a href="' . $photourl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( ('tumblr' == $link || '1' == $link)&& !empty($linkurl[$i]) ){
        $output .= '<a href="' . $linkurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>';
        $has_link = true;
      }elseif( 'link' == $link && !empty($options['custom_link_url']) ){
        $output .= '<a href="' . $options['custom_link_url'] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }elseif( 'fancybox' == $link && !empty($originalurl[$i]) ){
        $output .= '<a href="' . $originalurl[$i] . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap[$i] ."'".'>'; 
        $has_link = true;
      }  
      $output .= '<img id="'.$id.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$shadow.' '.$border.' '.$curves.'" src="' . $photourl[$i] . '" ';
      $output .= 'title='."'". $photocap[$i] ."'".' alt='."'". $photocap[$i] ."' "; // Careful about caps with ""
      $output .= 'border="0" hspace="0" vspace="0" />'; // Override the max-width set by theme
      
      // Load original image size
      if( "gallery" == $options['style_option'] && $originalurl[$i] ){
        $output .= '<img class="AlpinePhotoTiles-original-image" src="' . $originalurl[$i]. '" />';
      }
      if( $has_link ){ $output .= '</a>'; }
    }
    $output .= '</div>';
    
    if( !$options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$id.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $output .=  $by_link;    
    }          
    // Close vertical-parent
    $output .= '</div>';      

    if($userlink){ 
      if($options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $output .= '<div id="'.$id.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $output .= 'style="float:' . $options['widget_alignment'] . ';width:'.$options['tumblr_photo_size'].'px;max-width:'.$options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; // Only breakline if floating
      } 
    }

    // Close container
    $output .= '</div>';
    $disable = $this->get_option("general_loader");
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $output .= '<script>';
    
    if(!$disable){
      $output .= '
             jQuery(document).ready(function() {
              jQuery("#'.$id.'-AlpinePhotoTiles_container").addClass("loading"); 
             });';
    }
    $output .= '
           jQuery(window).load(function() {
            jQuery("#'.$id.'-AlpinePhotoTiles_container").removeClass("loading");
            if( jQuery().AlpinePhotoTilesPlugin ){
              jQuery("#'.$id.'-hidden-parent").AlpinePhotoTilesPlugin({
                id:"'.$id.'",
                style:"'.($options['style_option']?$options['style_option']:'windows').'",
                shape:"'.($options['style_shape']?$options['style_shape']:'square').'",
                perRow:"'.($options['style_photo_per_row']?$options['style_photo_per_row']:'3').'",
                imageLink:'.($options['tumblr_image_link']?'1':'0').',
                imageBorder:'.($options['style_border']?'1':'0').',
                imageShadow:'.($options['style_shadow']?'1':'0').',
                imageCurve:'.($options['style_curve_corners']?'1':'0').',
                imageHighlight:'.($options['style_highlight']?'1':'0').',
                fancybox:'.($options['tumblr_image_link_option'] == "fancybox"?'1':'0').',
                galleryHeight:'.($options['style_gallery_height']?$options['style_gallery_height']:'3').',
                highlight:"'.$highlight.'",
                pinIt:'.($options['pinterest_pin_it_button']?'1':'0').',
                siteURL:"'.get_option( 'siteurl' ).'"
              });
            }
          });
        </script>';
        
    return $output; 
  }
 
}

?>
