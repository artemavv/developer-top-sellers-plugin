<?php


class Dts_Frontend extends Dts_Core {

  /**
   * Handler for "top_selling_developers" shortcode
   * 
   * @param array $atts
   * @return string HTML for the developer list
   */
  public static function render_top_sellers( $atts ) {
    
     
    $input_fields = [
      'title'         => 'Top Bestsellers',
    ];
    
    extract( shortcode_atts( $input_fields, $atts ) );
    
    $top_sellers = Dts_Plugin::get_top_sellers( 30 ); // get [ slug => sales ]
    $developers = Dts_Plugin::get_developer_list( true ); // get [ slug => name ]
    
    foreach ( $top_sellers as $slug => $sales ) {
      $dev_name = $developers[$slug] ?? '---';
      $top_sellers[$slug] = $dev_name;
    }
      
    $out = self::render_developer_list( $top_sellers, $title );
    
    return $out;
  }
  
  /**
   * Prepares HTML for the list of developers ( split into N columns by 10 developers)
   * 
   * @param array $devs
   * @param string $title
   * @return string
   */
  public static function render_developer_list( array $devs, string $title = 'Top Bestsellers' ) {
    
    $out = '<div class="dts-developer-list-header"><h2 class="title">' . $title . '</h2></div>';
    $out .= '<div class="dts-developer-list" style="display: flex; padding-bottom: 20px; justify-content: space-evenly;">';
    $out .= '<div class="list-body">';
      
    $counter = 0;
    
    foreach ( $devs as $slug => $dev_name ) {
      
      $out .= '<div><a href="/developer/' . $slug . '">' . $dev_name. '</a></div>';
      
      $counter++;
      if ( ($counter) % 10 == 0 && $counter < count($devs) ) {
        $out .= '</div><div class="list-body">';
      }
    }
    
    $out .= '</div>';
    $out .= '</div>';
    
    return $out;
  }
  
  
  /**
   * Handler for "weekly_bestsellers_slider" shortcode
   * 
   * @param array $atts
   * @return string HTML for the developer list
   */
  public static function render_weekly_slider( $atts ) {
    
     
    $input_fields = [
      'mode'      => 'desktop',
      'debug'     => 0
    ];
    
    extract( shortcode_atts( $input_fields, $atts ) );
    
    $top_sellers = Dts_Plugin::get_top_sellers( 5, true ); // get [ slug => sales ]
    
    if ( $mode === 'desktop' ) {
      $developer_images = Dts_Plugin::get_developer_list( true, 'developer_image' ); // get [ slug => image ]
    }
    else {
      $developer_images = Dts_Plugin::get_developer_list( true, 'developer_image_mobile' ); // get [ slug => image ]
    }
    
    foreach ( $top_sellers as $slug => $sales ) {
      $dev_image_id = $developer_images[$slug] ?? 0;
      $top_seller_images[ $slug ] = $dev_image_id;
    }
      
    $out = self::render_developer_slider( $top_seller_images, $mode, $debug );
    
    return $out;
  }
  
  /**
   * Prepares HTML for the slider of developers ( mode either 'desktop' or 'mobile' )
   * 
   * @param array $devs
   * @param string $mode
   * @return string
   */
  public static function render_developer_slider( array $devs, $mode = 'desktop', $debug_display = false ) {
    
    $out = '<div class="dts-developer-slider">';
    $out .= '<div class="slick-slider">';
    
    foreach ( $devs as $slug => $img_id ) {
      
      if ( $img_id ) {
        $image_src = wp_get_attachment_image_src( $img_id, 'full' );
        $out .= '<div><a href="/developer/' . $slug . '"><img src=' . $image_src . '/></a></div>';
      }
      else {
        if ( $debug_display ) {
          $out .= '<div><a href="/developer/' . $slug . '">[no image for "' . $slug .'"]</a></div>';
        }
      }
      
    }
    
    $out .= '</div>';
    $out .= '</div>';
    
    return $out;
  }
  
}

