<?php


class PhotoTileForTumblrBot extends PhotoTileForTumblrBasic{  

/**
 *  Create constants for storing info 
 *  
 *  @ Since 1.2.2
 */
   public $out = "";
   public $options;
   public $wid; // Widget id
   public $results;
   public $shadow;
   public $border;
   public $curves;
   public $highlight;
   public $rel;
   
/**
 *  Function for creating cache key
 *  
 *  @ Since 1.2.2
 */
   function key_maker( $array ){
    if( isset($array['name']) && is_array( $array['info'] ) ){
      $return = $array['name'];
      foreach( $array['info'] as $key=>$val ){
        $return = $return."-".($val?$val:$key);
      }
      $return = @ereg_replace('[[:cntrl:]]', '', $return ); // remove ASCII's control characters
      $bad = array_merge(
        array_map('chr', range(0,31)),
        array("<",">",":",'"',"/","\\","|","?","*"," ",",","\'",".")); 
      $return = str_replace($bad, "", $return); // Remove Windows filename prohibited characters
      return $return;
    }
  }
  
/**
 * Alpine PhotoTile for Tumblr: Photo Retrieval Function
 * The PHP for retrieving content from Tumblr.
 *
 * @ Since 1.0.0
 * @ Updated 1.2.3
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

  function photo_retrieval(){
    $tumblr_options = $this->options;
    $defaults = $this->option_defaults();
    
    $key_input = array(
      'name' => 'tumblr',
      'info' => array(
        'vers' => $this->vers,
        'src' => $tumblr_options['tumblr_source'],
        'uid' => $tumblr_options['tumblr_user_id'],
        'curl' => $tumblr_options['tumblr_custom_url'],
        'num' => $tumblr_options['tumblr_photo_number'],
        'link' => $tumblr_options['tumblr_display_link'],
        'text' => $tumblr_options['tumblr_display_link_text'],
        'size' => $tumblr_options['tumblr_photo_size'],
        )
      );
    $key = $this->key_maker( $key_input );
        
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
        // Check for shortcode mistake (2 curl's)
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? (empty($tumblr_options['custom_link_url'])?'':$tumblr_options['custom_link_url']) : $tumblr_options['tumblr_custom_url'], $tumblr_options );
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
        // Check for shortcode mistake (2 curl's)
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? (empty($tumblr_options['custom_link_url'])?'':$tumblr_options['custom_link_url']) : $tumblr_options['tumblr_custom_url'], $tumblr_options );
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
        // Check for shortcode mistake (2 curl's)
        $tumblr_uid = apply_filters( $this->hook, empty($tumblr_options['tumblr_custom_url']) ? (empty($tumblr_options['custom_link_url'])?'':$tumblr_options['custom_link_url']) : $tumblr_options['tumblr_custom_url'], $tumblr_options );
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
    $this->results = $results;
  }
  
  
/**
 *  Get Image Link
 *  
 *  @ Since 1.2.2
 */
  function get_link($i){
    $link = $this->options['tumblr_image_link_option'];
    $photocap = $this->results['image_captions'][$i];
    $photourl = $this->results['image_urls'][$i];
    $linkurl = $this->results['image_perms'][$i];
    $url = $this->options['custom_link_url'];
    $originalurl = $this->results['image_originals'][$i];
    
    if( 'original' == $link && !empty($photourl) ){
      $this->out .= '<a href="' . $photourl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( ('tumblr' == $link || '1' == $link)&& !empty($linkurl) ){
      $this->out .= '<a href="' . $linkurl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( 'link' == $link && !empty($url) ){
      $this->out .= '<a href="' . $url . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>'; 
      return true;
    }elseif( 'fancybox' == $link && !empty($originalurl) ){
      $this->out .= '<a href="' . $originalurl . '" class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" title='."'". $photocap ."'".'>'; 
      return true;
    }  
    return false;    
  }
/**
 *  Update photo number count
 *  
 *  @ Since 1.2.2
 */
  function updateCount(){
    if( $this->options['tumblr_photo_number'] != count( $this->results['image_urls'] ) ){
      $this->options['tumblr_photo_number'] = count( $this->results['image_urls'] );
    }
  }

/**
 *  Get Parent CSS
 *  
 *  @ Since 1.2.2
 */
  function get_parent_css(){
    $opts = $this->options;
    $return = 'width:100%;max-width:'.$opts['widget_max_width'].'%;padding:0px;';
    if( 'center' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'margin:0px auto;text-align:center;';
    }
    elseif( 'right' == $opts['widget_alignment'] || 'left' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'float:' . $opts['widget_alignment'] . ';text-align:' . $opts['widget_alignment'] . ';';
    }
    else{
      $return .= 'margin:0px auto;text-align:center;';
    }
    return $return;
 }
 
/**
 *  Add Image Function
 *  
 *  @ Since 1.2.2
 *
 ** Possible change: place original image as 'alt' and load image as needed
 */
  function add_image($i,$css=""){
    $this->out .= '<img id="'.$this->wid.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$this->shadow.' '.$this->border.' '.$this->curves.' '.$this->highlight.'" src="' . $this->results['image_urls'][$i] . '" ';
    $this->out .= 'title='."'". $this->results['image_captions'][$i] ."'".' alt='."'". $this->results['image_captions'][$i] ."' "; // Careful about caps with ""
    $this->out .= 'border="0" hspace="0" vspace="0" style="'.$css.'"/>'; // Override the max-width set by theme
  }
  
/**
 *  Credit Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_credit_link(){
    if( !$this->options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$this->wid.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $this->out .=  $by_link;    
    }  
  }
  
/**
 *  User Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_user_link(){
    $userlink = $this->results['user_link'];
    if($userlink){ 
      if($this->options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="float:'.$this->options['widget_alignment'].';max-width:'.$this->options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; 
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>'; // Only breakline if floating
      }
    }
  }
  
/**
 *  Setup Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function add_lightbox_call(){
    if( "fancybox" == $this->options['tumblr_image_link_option'] ){
      $this->out .= '<script>jQuery(window).load(function() {'.$this->get_lightbox_call().'})</script>';
    }   
  }
  
/**
 *  Get Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function get_lightbox_call(){
    $this->set_lightbox_rel();
  
    $lightbox = $this->get_option('general_lightbox');
    $lightbox_style = $this->get_option('general_lightbox_params');
    $lightbox_style = str_replace( array("{","}"), "", $lightbox_style);
    $lightbox_style = str_replace( "'", "\'", $lightbox_style);
    
    $setRel = 'jQuery( "#'.$this->wid.'-AlpinePhotoTiles_container a.AlpinePhotoTiles-lightbox" ).attr( "rel", "'.$this->rel.'" );';
    
    if( 'fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancybox){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancybox( { '.$lightbox_style.' } );}';  
    }elseif( 'prettyphoto' == $lightbox ){
      //theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook
      $lightbox_style = ($lightbox_style?$lightbox_style:'theme:"facebook",social_tools:false');
      return $setRel.'if(jQuery().prettyPhoto){jQuery( "a[rel^=\''.$this->rel.'\']" ).prettyPhoto({ '.$lightbox_style.' });}';  
    }elseif( 'colorbox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'height:"80%"');
      return $setRel.'if(jQuery().colorbox){jQuery( "a[rel^=\''.$this->rel.'\']" ).colorbox( {'.$lightbox_style.'} );}';  
    }elseif( 'alpine-fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancyboxForAlpine){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancyboxForAlpine( { '.$lightbox_style.' } );}';  
    }
    return "";
  }
  
/**
 *  Set Lightbox "rel"
 *  
 *  @ Since 1.2.3
 */
 function set_lightbox_rel(){
    $lightbox = $this->get_option('general_lightbox');
    $custom = $this->get_option('hidden_lightbox_custom_rel');
    
    if( $custom && !empty($this->options['custom_lightbox_rel']) ){
      $this->rel = $this->options['custom_lightbox_rel'];
      $this->rel = str_replace('{rtsq}',']',$this->rel); // Decode right and left square brackets
      $this->rel = str_replace('{ltsq}','[',$this->rel);
    }elseif( 'fancybox' == $lightbox ){
      $this->rel = 'alpine-fancybox-'.$this->wid;
    }elseif( 'prettyphoto' == $lightbox ){
      $this->rel = 'alpine-prettyphoto['.$this->wid.']';
    }elseif( 'colorbox' == $lightbox ){
      $this->rel = 'alpine-colorbox['.$this->wid.']';
    }else{
      $this->rel = 'alpine-fancybox-safemode-'.$this->wid;
    }
 }
  
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_vertical(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
                      
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';

        for($i = 0;$i<$opts['tumblr_photo_number'];$i++){
          $has_link = $this->get_link($i);  // Add link
          $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
          $this->add_image($i,$css); // Add image
          if( $has_link ){ $this->out .= '</a>'; } // Close link
        }
        
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close vertical-parent

      $this->add_user_link();

    $this->out .= '</div>'; // Close container
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');

    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-vertical-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_cascade(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        for($col = 0; $col<$opts['style_column_number'];$col++){
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$opts['style_column_number']).'%;float:left;margin:0;">';
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
          for($i = $col;$i<$opts['tumblr_photo_number'];$i+=$opts['style_column_number']){
            $has_link = $this->get_link($i); // Add link
            $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
            $this->add_image($i,$css); // Add image
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
          $this->out .= '</div></div>';
        }
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
          
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close cascade-parent

      $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
      
      $this->add_user_link();

    // Close container
    $this->out .= '</div>';
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
   
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-cascade-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }

/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_hidden(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        $this->out .= '<div id="'.$this->wid.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'; 
        
          for($i = 0;$i<$opts['tumblr_photo_number'];$i++){
            $has_link = $this->get_link($i); // Add link
            $css = "";
            $this->add_image($i,$css); // Add image
            
            // Load original image size
            if( "gallery" == $opts['style_option'] && !empty( $this->results['image_originals'][$i] ) ){
              $this->out .= '<img class="AlpinePhotoTiles-original-image" src="' . $this->results['image_originals'][$i]. '" />';
            }
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
        $this->out .= '</div>';
        
        $this->add_credit_link();       
      
      $this->out .= '</div>'; // Close parent  

      $this->add_user_link();
      
    $this->out .= '</div>'; // Close container
    
    $disable = $this->get_option("general_loader");
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->out .= '<script>';
      if(!$disable){
        $this->out .= '
               jQuery(document).ready(function() {
                jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").addClass("loading"); 
               });';
      }
    $this->out .= '
           jQuery(window).load(function() {
            jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").removeClass("loading");
            if( jQuery().AlpinePhotoTilesPlugin ){
              jQuery("#'.$this->wid.'-hidden-parent").AlpinePhotoTilesPlugin({
                id:"'.$this->wid.'",
                style:"'.($opts['style_option']?$opts['style_option']:'windows').'",
                shape:"'.($opts['style_shape']?$opts['style_shape']:'square').'",
                perRow:"'.($opts['style_photo_per_row']?$opts['style_photo_per_row']:'3').'",
                imageLink:'.($opts['tumblr_image_link']?'1':'0').',
                imageBorder:'.($opts['style_border']?'1':'0').',
                imageShadow:'.($opts['style_shadow']?'1':'0').',
                imageCurve:'.($opts['style_curve_corners']?'1':'0').',
                imageHighlight:'.($opts['style_highlight']?'1':'0').',
                lightbox:'.($opts['tumblr_image_link_option'] == "fancybox"?'1':'0').',
                galleryHeight:'.($opts['style_gallery_height']?$opts['style_gallery_height']:'0').', // Keep for Compatibility
                galRatioWidth:'.($opts['style_gallery_ratio_width']?$opts['style_gallery_ratio_width']:'800').',
                galRatioHeight:'.($opts['style_gallery_ratio_height']?$opts['style_gallery_ratio_height']:'600').',
                highlight:"'.$highlight.'",
                pinIt:'.($opts['pinterest_pin_it_button']?'1':'0').',
                siteURL:"'.get_option( 'siteurl' ).'",
                callback: '.($opts['tumblr_image_link_option'] == "fancybox"?'function(){'.$this->get_lightbox_call().'}':'""').'
              });
            }
          });
        </script>';
        
  }
 
}

?>
