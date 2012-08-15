<?php
/**
 * Alpine PhotoTile for Tumblr: Widget Options
 *
 * @since 1.0.0
 *
 */
 
  function APTFTbyTAP_option_positions(){
    $options = array(
      'top' => array(
        'title' => '',
        'options' =>array('widget_title')
      ),
      'left' => array(
        'title' => 'Tumblr Settings',
        'options' =>array('tumblr_source','tumblr_user_id','tumblr_custom_url','tumblr_image_link','tumblr_display_link','tumblr_photo_size' )
      ),
      'right' => array(
        'title' => 'Style Settings',
        'options' =>array('style_option','style_shape','style_gallery_height','style_photo_per_row','style_column_number','tumblr_photo_number','style_shadow','style_border','style_curve_corners')
      ),
      'bottom' => array(
        'title' => 'Format Settings',
        'options' =>array('widget_alignment','widget_max_width','widget_disable_credit_link')
      ),
    );
    return $options;
  }
  
  function APTFTbyTAP_option_defaults(){
    $options = array(
      'widget_title' => array(
        'name' => 'widget_title',
        'title' => 'Title : ',
        'type' => 'text',
        'sanitize' => 'nohtml',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),
      'tumblr_source' => array(
        'name' => 'tumblr_source',
        'title' => 'Retrieve Photos From : ',
        'type' => 'select',
        'valid_options' => array(
          'user' => array(
            'name' => 'user',
            'title' => 'Username'
          ),
          'custom' => array(
            'name' => 'custom',
            'title' => 'Custom URL'
          )     
        ),
        'description' => '',
        'parent' => 'APTFTbyTAP-parent', 
        'trigger' => 'tumblr_source',
        'default' => 'user'
      ),
      'tumblr_user_id' => array(
        'name' => 'tumblr_user_id',
        'title' => 'Tumblr Username : ',
        'type' => 'text',
        'sanitize' => 'nospaces',
        'description' => '{username}.tumblr.com',
        'child' => 'tumblr_source', 
        'hidden' => 'custom',
        'since' => '1.1',
        'default' => ''
      ),
      'tumblr_custom_url' => array(
        'name' => 'tumblr_custom_url',
        'title' => 'Tumblr Custom URL : ',
        'type' => 'text',
        'sanitize' => 'url',
        'description' => '{www.example.com}',
        'child' => 'tumblr_source', 
        'hidden' => 'user',
        'since' => '1.1',
        'default' => ''
      ),      
      'tumblr_image_link' => array(
        'name' => 'tumblr_image_link',
        'title' => 'Link images to Tumblr source.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),
      'tumblr_display_link' => array(
        'name' => 'tumblr_display_link',
        'title' => 'Display link to Tumblr page.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),      
      'tumblr_photo_size' => array(
        'name' => 'tumblr_photo_size',
        'title' => 'Photo Size : ',
        'type' => 'select',
        'valid_options' => array(
          '75' => array(
            'name' => 75,
            'title' => '75px'
          ),
          '100' => array(
            'name' => 100,
            'title' => '100px'
          ),
          '240' => array(
            'name' => 240,
            'title' => '240px'
          ),
          '500' => array(
            'name' => 500,
            'title' => '500px'
          ),
          '640' => array(
            'name' => 640,
            'title' => '640px'
          )      
        ),
        'description' => '',
        'since' => '1.1',
        'default' => '100'
      ),
      'style_option' => array(
        'name' => 'style_option',
        'title' => 'Style : ',
        'type' => 'select',
        'valid_options' => array(
          'vertical' => array(
            'name' => 'vertical',
            'title' => 'Vertical'
          ),
          'windows' => array(
            'name' => 'windows',
            'title' => 'Windows'
          ),
          'bookshelf' => array(
            'name' => 'bookshelf',
            'title' => 'Bookshelf'
          ),
          'rift' => array(
            'name' => 'rift',
            'title' => 'Rift'
          ),
          'floor' => array(
            'name' => 'floor',
            'title' => 'Floor'
          ),
          'wall' => array(
            'name' => 'wall',
            'title' => 'Wall'
          ),
          'cascade' => array(
            'name' => 'cascade',
            'title' => 'Cascade'
          ),
          'gallery' => array(
            'name' => 'gallery',
            'title' => 'Gallery'
          )           
        ),
        'description' => '',
        'parent' => 'APTFTbyTAP-parent',
        'trigger' => 'style_option',
        'since' => '1.1',
        'default' => 'vertical'
      ),
      'style_shape' => array(
        'name' => 'style_shape',
        'title' => 'Shape : ',
        'type' => 'select',
        'valid_options' => array(
          'rectangle' => array(
            'name' => 'rectangle',
            'title' => 'Rectangle'
          ),
          'square' => array(
            'name' => 'square',
            'title' => 'Square'
          )              
        ),
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical cascade floor wall rift bookshelf gallery',
        'since' => '1.1',
        'default' => 'vertical'
      ),   
      'style_gallery_height' => array(
        'name' => 'style_gallery_height',
        'title' => 'Gallery Size : ',
        'type' => 'select',
        'valid_options' => array(
          '2' => array(
            'name' => 2,
            'title' => 'XS'
          ),
          '3' => array(
            'name' => 3,
            'title' => 'Small'
          ),
          '4' => array(
            'name' => 4,
            'title' => 'Medium'
          ),
          '5' => array(
            'name' => 5,
            'title' => 'Large'
          ),
          '6' => array(
            'name' => 6,
            'title' => 'XL'
          ),
          '7' => array(
            'name' => 7,
            'title' => 'XXL'
          )             
        ),
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical cascade floor wall rift bookshelf windows',
        'since' => '1.1',
        'default' => '3'
      ),       
      'style_photo_per_row' => array(
        'name' => 'style_photo_per_row',
        'title' => 'Photos per row : ',
        'type' => 'range',
        'min' => '1',
        'max' => '20',
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical cascade windows',
        'since' => '1.1',
        'default' => '4'
      ),
      'style_column_number' => array(
        'name' => 'style_column_number',
        'title' => 'Number of columns : ',
        'type' => 'range',
        'min' => '1',
        'max' => '10',
        'description' => '',
        'child' => 'style_option',
        'hidden' => 'vertical floor wall bookshelf windows rift gallery',
        'since' => '1.1',
        'default' => '2'
      ),          
      'tumblr_photo_number' => array(
        'name' => 'tumblr_photo_number',
        'title' => 'Number of photos : ',
        'type' => 'range',
        'min' => '1',
        'max' => '20',
        'description' => '',
        'since' => '1.1',
        'default' => '4'
      ),
      'style_shadow' => array(
        'name' => 'style_shadow',
        'title' => 'Add slight image shadow.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),   
      'style_border' => array(
        'name' => 'style_border',
        'title' => 'Add white image border.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),
      'style_curve_corners' => array(
        'name' => 'style_curve_corners',
        'title' => 'Add slight curve to corners.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),          
      'widget_alignment' => array(
        'name' => 'widget_alignment',
        'title' => 'Photo alignment : ',
        'type' => 'select',
        'valid_options' => array(
          'left' => array(
            'name' => 'left',
            'title' => 'Left'
          ),
          'center' => array(
            'name' => 'center',
            'title' => 'Center'
          ),
          'right' => array(
            'name' => 'right',
            'title' => 'Right'
          )            
        ),
        'since' => '1.1',
        'default' => 'center'
      ),    
      'widget_max_width' => array(
        'name' => 'widget_max_width',
        'title' => 'Max widget width (%) : ',
        'type' => 'text',
        'sanitize' => 'int',
        'min' => '1',
        'max' => '100',
        'description' => "To reduce the widget width, input a percentage (between 1 and 100). If photos are smaller than widget area, reduce percentage until desired width is achieved.",
        'since' => '1.1',
        'default' => '100'
      ),        
      'widget_disable_credit_link' => array(
        'name' => 'widget_disable_credit_link',
        'title' => 'Disable the tiny link in the bottom left corner, though I have spent months developing this plugin and would appreciate the credit.',
        'type' => 'checkbox',
        'description' => '',
        'since' => '1.1',
        'default' => ''
      ),      
    );
    return $options;
  }
  
  
  ?>
