<?php


class Dts_Frontend extends Dts_Core {

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
      
      $out .= '<div><a href="/developers/' . $slug . '">' . $dev_name. '</a></div>';
      
      $counter++;
      if ( ($counter) % 10 == 0 && $counter < count($devs) ) {
        $out .= '</div><div class="list-body">';
      }
    }
    
    $out .= '</div>';
    $out .= '</div>';
    
    return $out;
  }
}

