<?php
/**
 * Alpine PhotoTile for Tumblr: Photo Retrieval Function
 * The PHP for retrieving content from Tumblr.
 *
 * @since 1.0.0
 */
 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////    Generate Image Content    ////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function APTFTbyTAP_photo_retrieval($id, $tumblr_options, $defaults){  
  $APTFTbyTAP_tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_user_id']) ? 'uid' : $tumblr_options['tumblr_user_id'], $tumblr_options );
  $APTFTbyTAP_tumblr_uid = @ereg_replace('[[:cntrl:]]', '', $APTFTbyTAP_tumblr_uid ); // remove ASCII's control characters
  $APTFTbyTAP_tumblr_custom_url = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_custom_url']) ? 'groupid' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
  $APTFTbyTAP_tumblr_custom_url = @ereg_replace('[[:cntrl:]]', '', $APTFTbyTAP_tumblr_custom_url ); // remove ASCII's control characters

  $key = 'tumblr'.APTFTbyTAP_VER.'-'.$tumblr_options['tumblr_source'].'-'.$APTFTbyTAP_tumblr_uid.'-'.$APTFTbyTAP_tumblr_groupid.'-'.$APTFTbyTAP_tumblr_set.'-'.$APTFTbyTAP_tumblr_tags.'-'.$tumblr_options['tumblr_photo_number'].'-'.$tumblr_options['tumblr_photo_size'].'-'.$tumblr_options['tumblr_display_link'];

  if ( class_exists( 'theAlpinePressSimpleCacheV2' ) && APTFTbyTAP_CACHE ) {
    $cache = new theAlpinePressSimpleCacheV2();  
    $cache->setCacheDir( APTFTbyTAP_CACHE );
    
    if( $cache->exists($key) ) {
      $results = $cache->get($key);
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
  $APTFTbyTAP_linkurl = array();
  $APTFTbyTAP_photocap = array();
  $APTFTbyTAP_photourl = array();
          
  // Determine image size id
  $APTFTbyTAP_size_id = 2;
  switch ($tumblr_options['tumblr_photo_size']) {
    case 75:
      $APTFTbyTAP_size_id = 5;
    break;
    case 100:
      $APTFTbyTAP_size_id = 4;
    break;
    case 250:
      $APTFTbyTAP_size_id = 3;
    break;
    case 400:
      $APTFTbyTAP_size_id = 2;
    break;
    case 500:
      $APTFTbyTAP_size_id = 1;
    break;
  }  
  
  // Retrieve content using curl_init and PHP_serial
 if ( curl_init() && function_exists('json_decode') ) {
    // @ is shut-up operator
    // For reference: http://www.tumblr.com/services/feeds/

    switch ($tumblr_options['tumblr_source']) {
    case 'user':
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
      $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
      $tumblr_uid = str_replace('http:','',$tumblr_uid );
      $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
      $request = 'http://api.tumblr.com/v2/blog/'.$tumblr_uid.'.tumblr.com/posts?api_key=GhKB8A19ZFhO3rWpBhjKfJUistNDgQwIYu6tHlzzg4pPT3WZwH';
    break;
    case 'custom':
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
      $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
      $tumblr_uid = str_replace('http:','',$tumblr_uid );
      $request = 'http://api.tumblr.com/v2/blog/'.$tumblr_uid.'posts?api_key=GhKB8A19ZFhO3rWpBhjKfJUistNDgQwIYu6tHlzzg4pPT3WZwH';
    break;
    } 

    $ci = @curl_init($request);
    //@curl_setopt($ci, CURLOPT_URL, $request);
    @curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
    $_tumblrurl = @curl_exec($ci);
    @curl_close($ci);
    
    $_tumblr_json = @json_decode($_tumblrurl);

    if(empty($_tumblr_json) || 200 != $_tumblr_json->meta->status ){
      $hidden .= '<!-- Failed using curl_init() and PHP_Serial @ '.$request.' -->';
      $continue = false;
    }else{
      $response = $_tumblr_json->response;
      $response_blog = $response->blog;
      $APTFTbyTAP_title = $response_blog->title;
      $APTFTbyTAP_link = $response_blog->url;
      $found = $response_blog->posts;
      
      $APTFTbyTAP_content =  $response->posts;

      if( $tumblr_options['tumblr_photo_number'] > $found ){ $tumblr_options['tumblr_photo_number'] = $found;}
      
      $i = 0;
      foreach($APTFTbyTAP_content as $post){
        if( $i<$tumblr_options['tumblr_photo_number'] ){
          $post_url = $post->post_url;
          $post_cap = $post->caption;
          if( $post_url && count($post->photos) ){
            foreach( $post->photos as $photo ){
              if( $i<$tumblr_options['tumblr_photo_number'] ){
                $sizes = $photo->alt_sizes;
                $APTFTbyTAP_linkurl[$i] = $post_url;
                $APTFTbyTAP_photocap[$i] = ''; //$post_cap;
                $APTFTbyTAP_photourl[$i] = $sizes[$APTFTbyTAP_size_id]->url;
                
                $APTFTbyTAP_originalurl[$i] = $sizes[1]->url;
                $APTFTbyTAP_photourl[$i] = $sizes[0]->url;
                
                foreach( $sizes as $currentsize ){
                  if( $currentsize->width >= $tumblr_options['tumblr_photo_size'] && $currentsize->url ){
                    $APTFTbyTAP_photourl[$i] = $currentsize->url;
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
      if(!empty($APTFTbyTAP_linkurl) && !empty($APTFTbyTAP_photourl)){
        if( $tumblr_options['tumblr_display_link'] ) {
          $APTFTbyTAP_user_link = '<div class="APTFTbyTAP-display-link" >';
          $APTFTbyTAP_user_link .='<a href="'.$APTFTbyTAP_link.'" target="_blank" >';
          $APTFTbyTAP_user_link .= $APTFTbyTAP_title;
          $APTFTbyTAP_user_link .= '</a></div>';
        }
        // If content successfully fetched, generate output...
        $continue = true;
        $hidden  .= '<!-- Success using curl_init() and JSON -->';
      }else{
        $hidden .= '<!-- No photos found using curl_init() and PHP_Serial @ '.$request.' -->';  
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
    $APTFTbyTAP_size_id = 2;
    switch ($tumblr_options['tumblr_photo_size']) {
      case 75:
        $APTFTbyTAP_size_id = 5;
      break;
      case 100:
        $APTFTbyTAP_size_id = 4;
      break;
      case 250:
        $APTFTbyTAP_size_id = 3;
      break;
      case 400:
        $APTFTbyTAP_size_id = 2;
      break;
      case 500:
        $APTFTbyTAP_size_id = 1;
      break;
    }  
    switch ($tumblr_options['tumblr_source']) {
    case 'user':
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
      $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
      $tumblr_uid = str_replace('http:','',$tumblr_uid );
      $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
      $request = 'http://' . $tumblr_uid . '.tumblr.com/api/read?start=0&number=' .$tumblr_options['tumblr_photo_number']. '&type=photo';
    break;
    case 'custom':
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
      $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
      $tumblr_uid = str_replace('http:','',$tumblr_uid );
      $request = 'http://' . $tumblr_uid . '/api/read?start=0&number=' .$tumblr_options['tumblr_photo_number']. '&type=photo';
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
      if( $_tumblr_xml && $_tumblr_xml->posts[0]) {
        foreach( $_tumblr_xml->posts[0]->post as $p ) {
          if( $s<$tumblr_options['tumblr_photo_number'] ){     
            // list of link urls
            $APTFTbyTAP_linkurl[$s] = (string) $p['url'];
            // list of photo urls
            $APTFTbyTAP_photourl[$s] = (string) $p->{"photo-url"}[$APTFTbyTAP_size_id];
            $APTFTbyTAP_originalurl[$s] = (string) $p->{"photo-url"}[1];
            $APTFTbyTAP_photocap[$s] = (string) $p["slug"];
            $s++;
          }else{
            break;
          }
        }
      }
      if(!empty($APTFTbyTAP_linkurl) && !empty($APTFTbyTAP_photourl)){
        // If set, generate tumblr link
        if( $tumblr_options['display-link'] ) {
          $APTFTbyTAP_user_link = '<div class="APTFTbyTAP-display-link">';
          if( 'custom' == $tumblr_options['tumblr_source'] ){
            $APTFTbyTAP_user_link .='<a href="http://' . $tumblr_uid . '/" target="_blank" >';          
          }else{
            $APTFTbyTAP_user_link .='<a href="http://' . $tumblr_uid . '.tumblr.com/" target="_blank" >';
          }
          $APTFTbyTAP_user_link .= $_tumblr_xml->tumblelog[title];
          $APTFTbyTAP_user_link .= '</a></div>';
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
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_user_id']) ? '' : $tumblr_options['tumblr_user_id'], $tumblr_options );
      $tumblr_uid = str_replace(array('/',' '),'',$tumblr_uid);
      $tumblr_uid = str_replace('http:','',$tumblr_uid );
      $tumblr_uid = str_replace('.tumblr.com','',$tumblr_uid);
      $request = 'http://' . $tumblr_uid . '.tumblr.com/rss';
    break;
    case 'custom':
      $tumblr_uid = apply_filters( APTFTbyTAP_HOOK, empty($tumblr_options['tumblr_custom_url']) ? '' : $tumblr_options['tumblr_custom_url'], $tumblr_options );
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
      $APTFTbyTAP_title = @APTFTbyTAP_specialarraysearch($rss,'title');
      $APTFTbyTAP_title = $APTFTbyTAP_title['0']['data'];
      $APTFTbyTAP_link = @APTFTbyTAP_specialarraysearch($rss,'link');
      $APTFTbyTAP_link = $APTFTbyTAP_link['0']['data'];
      $rss_data = @APTFTbyTAP_specialarraysearch($rss,'item');

      $s = 0; // simple counter
      if ($rss_data != NULL ){ // Check again
        foreach ( $rss_data as $item ) {
          if( $s<$tumblr_options['tumblr_photo_number'] ){
            $APTFTbyTAP_linkurl[$s] = $item['child']['']['link']['0']['data'];    
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
                $APTFTbyTAP_photourl_current = @preg_replace(array('/(.+)src="/i','/"(.+)/') , '',$matches[ 0 ]);
                // Finally, change the size. 
                  // [] specifies single character and \w is any word character
                $APTFTbyTAP_photourl[$s] = @preg_replace('/[_]500[.]/', "_".$tumblr_options['tumblr_photo_size'].".", $APTFTbyTAP_photourl_current );
                $APTFTbyTAP_originalurl[$s] = $APTFTbyTAP_photourl_current;
                // Could set the caption as blank instead of default "Photo", but currently not doing so.
                $APTFTbyTAP_photocap[$s] = $item['child']['']['title']['0']['data'];
                $s++;
              }
            }
          }
          else{
            break;
          }
        }
      }
      if(!empty($APTFTbyTAP_linkurl) && !empty($APTFTbyTAP_photourl)){
        if( $tumblr_options['tumblr_display_link'] ) {
          $APTFTbyTAP_user_link = '<div class="APTFTbyTAP-display-link" >';
          $APTFTbyTAP_user_link .='<a href="'.$APTFTbyTAP_link.'" target="_blank" >';
          $APTFTbyTAP_user_link .= $APTFTbyTAP_title;
          $APTFTbyTAP_user_link .= '</a></div>';
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
    
  $results = array('continue'=>$continue,'message'=>$message,'hidden'=>$hidden,'user_link'=>$APTFTbyTAP_user_link,'image_captions'=>$APTFTbyTAP_photocap,'image_urls'=>$APTFTbyTAP_photourl,'image_perms'=>$APTFTbyTAP_linkurl,'image_originals'=>$APTFTbyTAP_originalurl);
  
  if( true == $continue && !$disablecache && $cache ){     
    $cache_results = $results;
    if(!is_serialized( $cache_results  )) { $cache_results  = maybe_serialize( $cache_results ); }
    $cache->put($key, $cache_results);
    $cachetime = APTFTbyTAP_get_option( 'cache_time' );
    if( $cachetime && is_numeric($cachetime) ){
      $cache->setExpiryInterval( $cachetime*60*60 );
    }
  }
  
  return $results;
}
?>